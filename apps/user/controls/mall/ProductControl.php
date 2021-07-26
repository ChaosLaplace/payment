<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/ProductCategoryModel.php';
require_once APP  . 'models/AdminUserModel.php';

class ProductControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(id) from product where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'     => 0,
                'product_list'  => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $title_field = 'title_' . $GLOBALS['request']['lang'];
        $sql = "select id, category_id, $title_field as title, cover_photo, price, store_num, sale_num, 
                    is_online, create_time  
                from product where {$sql_where} order by id desc {$page_info['limit']}";
        $product_list = DB::getData($sql);

        foreach($product_list as &$product) {
            $product['category'] = ProductCategoryModel::getNameById($product['category_id']);
            $product['cover_photo_url'] = img_url($product['cover_photo']);
            $product['price'] = fen2yuan($product['price']);
            $product['created'] = date('Y-m-d', $product['create_time']);
        }

        $this->resp([
            'total_row'     => $total_row,
            'product_list'  => $product_list,
        ]);
    }

    public function init() {
        $this->resp([
            'category_opts' => ProductCategoryModel::opts()
        ]);
    }

    public function add() {
        $data = $this->__formData();
        $data['creater_id'] = $this->adminId;
        $data['create_time'] = time();

        $id = DB::insert('product', $data);

        $content_data = $this->__formContentData();
        $content_data['product_id'] = $id;
        DB::insert('product_content', $content_data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();
        $data['updater_id'] = $this->adminId;
        $data['update_time'] = time();

        DB::update('product', $data, "id={$id}");

        $content_data = $this->__formContentData();
        DB::update('product_content', $content_data, "product_id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "update product set is_online='N', is_delete='Y' where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function item() {
        $id = input('id');
        $sql = "select p.id, p.category_id, p.title_zh, p.title_en, p.title_local, p.cover_photo, p.product_photos, 
                    p.price, p.store_num, p.seq, p.from_url, pc.content_zh, pc.content_en, pc.content_local 
                from product p, product_content pc where p.id=?i and p.id=pc.product_id limit 1";
        $item = DB::getLine($sql, [$id]);

        if($item) {
            $item['cover_photo_url'] = img_url($item['cover_photo']);

            $photo_url_list = [];
            $photo_uri_list = explode(',', $item['product_photos']);
            foreach($photo_uri_list as $photo) {
                $photo_url_list[] = img_url($photo);
            }

            $item['product_photo_uri_list'] = $photo_uri_list;
            $item['product_photo_url_list'] = $photo_url_list;
        }

        $this->resp([
            'item' => $item
        ]);
    }

    public function changeOnline() {
        $id = input('id');
        $is_online = input('isOnline');

        $sql = "update product set is_online=?s where id=?i";
        DB::runSql($sql, [$is_online, $id]);

        $this->resp(true);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and category_id=$1", 'f'=>input('categoryId')],
            ['s'=>"and is_online='$1'", 'f'=>input('isOnline')],
            ['s'=>"and sale_num=$1", 'f'=>input('saleNum')],
            ['s'=>"and store_num=$1", 'f'=>input('storeNum')],
            ['s'=>"and is_delete='N'", 'f'=>true],
        ];

        // 标题搜索用coreseek完成

        if($price = input('price')) {
            $price = yuan2fen($price);
            $factor_list[] = ['s'=>"and price=$1", 'f'=>$price];
        }

        if( $creater = input('creater') ) {
            $creater_id = AdminUserModel::getIdByUsername($creater);
            if($creater_id) {
                $factor_list[] = ['s' => "and creater_id=$1", 'f' => $creater_id];
            }
            else {
                $factor_list[] = ['s' => "and creater_id=0", 'f' => true];
            }
        }

        if( $start_create_time = input('startCreateTime') && $end_create_time = input('endCreateTime') ) {
            $factor_list[] = ['s'=>"and create_time>={$start_create_time} and create_time<={$end_create_time}", 'f'=>true];
        }

        if( $updater = input('updater') ) {
            $updater_id = AdminUserModel::getIdByUsername($updater);
            $factor_list[] = ['s'=>"and updater_id=$1", 'f'=>$updater_id];
        }

        if( $start_update_time = input('startUpdateTime') && $end_update_time = input('endUpdateTime') ) {
            $factor_list[] = ['s'=>"and update_time>={$start_update_time} and create_time<={$end_update_time}", 'f'=>true];
        }

        $sql_where = sql_where($factor_list);

        return $sql_where;
    }

    private function __formData() {
        $data = [
            'category_id'       => input('categoryId'),
            'title_zh'          => input('titleZh'),
            'title_en'          => input('titleEn'),
            'title_local'       => input('titleLocal'),
            'cover_photo'       => input('coverPhoto'),
            'product_photos'    => input_raw('productPhotos'),
            'price'             => yuan2fen(input('price')),
            'store_num'         => input('storeNum')
        ];

        if( $from_url = input('fromUrl') ) {
            $data['from_url'] = $from_url;
        }

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        return $data;
    }

    private function __formContentData() {
        $data = [
            'content_zh'    => input('contentZh'),
            'content_en'    => input('contentEn'),
            'content_local' => input('contentLocal'),
        ];

        if( empty($data['content_en']) ) {
            $data['content_en'] = $data['content_zh'];
        }

        if( empty($data['content_local']) ) {
            $data['content_local'] = $data['content_zh'];
        }

        return $data;
    }
}