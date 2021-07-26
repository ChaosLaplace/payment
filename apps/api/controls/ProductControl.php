<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'models/ProductCategoryModel.php';
require_once ROOT . 'funcs/redis_event.fn.php';

/**
 * 商品
 */
class ProductControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 显示商品列表
     */
    public function index() {
        $category_id = input('categoryId');

        $sql = "select count(id) from product where category_id=?i";
        $total_row = DB::getVar($sql, [$category_id]);

        if( $total_row <= 0 ) {
            $this->resp([
                'total_row'     => 0,
                'total_page'    => 0,
                'product_list'  => [],
            ]);
            return true;
        }

        $page_num  = input('pageNum') ? input('pageNum') : 1;
        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;

        list($total_page, $start_num) = app_paging($total_row, $page_num, $page_size);

        $title_field = 'title_' . $GLOBALS['request']['lang'];

        $sql = "select id, {$title_field} as title, cover_photo, price, store_num, sale_num 
                from product where category_id=?i order by id desc limit {$start_num}, {$page_size}";
        $product_list = DB::getData($sql, [$category_id]);

        foreach($product_list as &$product) {
            $product['price'] = fen2yuan($product['price']);
        }

        $this->resp([
            'total_row'     => $total_row,
            'total_page'    => $total_page,
            'product_list'  => $product_list,
        ]);
    }

    public function search() {

    }

    /**
     * 商品详情
     */
    public function show() {
        $id = input('id');

        $title_field = 'title_' . $GLOBALS['request']['lang'];
        $sql = "select id, {$title_field} as title, product_photos, price, store_num, sale_num
                from product where id=?i";
        $product = DB::getLine($sql, [$id]);

        if(!$product) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '404未找到');
            return false;
        }

        $product['product_photos'] = explode(',', $product['product_photos']);
        $product['start_price'] = fen2yuan($product['price']);

        // 产品详情
        $content_field = 'content_' . $GLOBALS['request']['lang'];
        $sql = "select {$content_field} as content from product_content where product_id=?i";
        $product_content = DB::getLine($sql, [$product['id']]);

        $product['content'] = $product_content ? $product_content['content'] : '';

        $this->resp([
            'product' => $product
        ]);
    }


    /**
     * 商品分类列表
     */
    public function categoryList() {
        $category_list = ProductCategoryModel::listCategories();

        $this->resp([
            'category_list' => $category_list
        ]);
    }

    /**
     * 购买商品
     */
    public function buy() {
        if( !$this->__productFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '购买商品表单验证未通过');
            return false;
        }

        $uid = $this->uid;

        // 查询预设地址
        $sql = "select username, country_code, phone, address from user_receipt_info
                where user_id=?i and is_default='Y' and is_delete='N'";
        $defalut_address = DB::getLine($sql, [$uid]);
        if ( !$defalut_address ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '未找到预设地址');
            return false;
        }

        // 查询商品
        $product_id = input('productId');
        $sql = "select price, store_num from product where id=?i and is_online='Y' and is_delete='N'";
        $product = DB::getLine($sql, [$product_id]);
        if ( !$product ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '未找到商品');
            return false;
        }

        $price  = yuan2fen( input('price') );
        $amount = input('amount');
        $total_price = yuan2fen( input('totalPrice') );
        // 确认商品 库存 && 价格 是否一致
        if ( !($amount <= $product['store_num']) || !($price == $product['price'])
            || !($total_price == $price * $amount) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '库存 或 价格 不一致');
            return false;
        }

        $now = time();
        $product_order = array(
            'order_sn'             => self::orderSn(),
            'user_id'              => $uid,
            'product_id'           => $product_id,
            'price'                => $price,
            'amount'               => $amount,
            'postage'              => $GLOBALS['app']['postage'],
            'total_price'          => $total_price + $GLOBALS['app']['postage'],
            'last_paid_time'       => $now + $GLOBALS['app']['last_paid_time'],
            'rec_username'         => $defalut_address['username'],
            'rec_phone'            => $defalut_address['country_code'] . $defalut_address['phone'],
            'rec_address'          => $defalut_address['address'],
            'receipt_confirm_time' => $now + $GLOBALS['app']['receipt_confirm_time'],
            'create_time'          => $now,
        );
        // 新增订单
        $rel_id = DB::insert('product_order', $product_order);

        $trade = array(
            'subject_id'          => UserAccountModel::SUBJECT_PRODUCT,
            'change_depict_zh'    => '购买商品',
            'change_depict_en'    => '购买商品',
            'change_depict_local' => '购买商品',
            'rel_table'           => 'product_order',
            'rel_id'              => $rel_id
        );

        if ( !UserAccountModel::expend($uid, $total_price, $trade) ) {
            $wait_time = $GLOBALS['app']['last_paid_time'];
            $route     = "Product:orderCancel:{$rel_id}";
            redis_event_add($route, $wait_time);

            $this->respError(ErrorCode::PARAMETER_ERROR, '余额不足');
            return false;
        }
        // 余额够付订单的话 修改订单状态 & 修改商品库存
        $sql = "update product_order set is_paid='Y', paid_time={$now}, status='2' where id=?i";
        DB::runSql($sql, [$rel_id]);

        $sql = "update product set store_num=store_num - {$amount} where id=?i";
        DB::runSql($sql, [$product_id]);

        $this->resp('Y');
    }

    /**
     * 查询商品订单各种状态
     */
    public function status() {
        if( !$this->__statusFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '订单状态表单验证未通过');
            return false;
        }
        
        $status = input('status');

        $sql = "select order_sn, product_id, price, amount, postage, total_price
                from product_order where user_id=?i and status={$status} order by id desc";
        $product_order = DB::getData($sql, [$this->uid]);

        $title = 'title_' . $GLOBALS['request']['locale'];
        $sql   = "select {$title} from product where id=?i";

        $product_title_arr = [];
        foreach ($product_order as &$product) {
            if ( in_array($product['product_id'], $product_title_arr) ) {
                $product['product_title'] = $product_title_arr[$product['product_id']];
                continue;
            }

            $product['product_title'] = DB::getVar($sql, [$product['product_id']]);
            $product_title_arr[$product['product_id']] = $product['product_title'];
        }

        $this->resp([
            'product_order' => $product_order
        ]);
    }

    /**
     * 生成商品订单号
     */
    private function orderSn($length = 5) {
        $str   = '0123456789';
        $count = strlen($str) - 1;

        $rand_num  = '';
        for ($i = 0; $i < $length; ++$i) {
            $index = mt_rand(0, $count);
            $rand_num .= $str[$index];
        }

        return date('YmdHi') . $rand_num;
    }

    private function __productFormValidator() {
        $validator = new Validator();
        $validator->setRule('productId', array('required'));
        $validator->setRule('price', array('required'));
        $validator->setRule('amount', array('required'));
        $validator->setRule('totalPrice', array('required'));
        return $validator->validate();
    }

    private function __statusFormValidator() {
        $validator = new Validator();
        $validator->setRule('status', array('required'));
        return $validator->validate();
    }
}
