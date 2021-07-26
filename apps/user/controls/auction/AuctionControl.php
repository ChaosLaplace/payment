<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'funcs/datetime_help.fn.php';
require_once ROOT . 'models/AuctionModel.php';
require_once ROOT . 'models/AuctionCategoryModel.php';
require_once ROOT . 'models/AuctionRoomModel.php';
require_once ROOT . 'models/AuctionSupplierModel.php';
require_once ROOT . 'funcs/redis_event.fn.php';
require_once APP  . 'models/AdminUserModel.php';

class AuctionControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(id) from auction where {$sql_where}";
        $total_row = DB::getVar($sql);

        if($total_row <= 0) {
            $this->resp([
                'total_row'     => 0,
                'auction_list'  => []
            ]);
            return true;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select * from auction where {$sql_where} order by id desc {$page_info['limit']}";
        $auction_list = DB::getData($sql);

        $name_field = 'name_' . $GLOBALS['request']['lang'];
        foreach($auction_list as &$auc) {
            $auc['name'] = $auc[$name_field];
            unset($auc['name_zh'], $auc['name_en'], $auc['name_local']);
            //$auc['create_time'] = date('Y-m-d H:i', $auc['create_time']);
            $auc['room_name'] = AuctionRoomModel::getNameById($auc['room_id']);
            $auc['category_name'] = AuctionCategoryModel::getNameById($auc['category_id']);
            $auc['supplier_name'] = AuctionSupplierModel::getNameById($auc['supplier_id']);
            $auc['current_bid_price'] = fen2yuan($auc['current_bid_price']);
            $auc['start_price'] = fen2yuan($auc['start_price']);
            $auc['incr_price'] = fen2yuan($auc['incr_price']);
            $auc['cost_price'] = fen2yuan($auc['cost_price']);
            $auc['min_price'] = fen2yuan($auc['min_price']);
            $auc['max_price'] = fen2yuan($auc['max_price']);
            $auc['bond'] = fen2yuan($auc['bond']);
            $auc['preview_time_len'] = dt_duration($auc['preview_time_len']);
            $auc['bid_time_len'] = dt_duration($auc['bid_time_len']);
        }

        $this->resp([
            'total_row'    => $total_row,
            'auction_list' => $auction_list
        ]);
    }

    public function init() {
        $this->resp([
            'room_opts'     => AuctionRoomModel::opts(),
            'category_opts' => AuctionCategoryModel::opts(),
            'supplier_opts' => AuctionSupplierModel::opts(),
        ]);
    }

    public function add() {
        $data = $this->__formData();

        $time = time();
        if($data['preview_start_time'] <= $time) {
            $data['status'] = AuctionModel::STATUS_PREVIEW;
        }

        $auction_id = DB::insert('auction', $data);

        $content_data = $this->__contentFormData();
        $content_data['auction_id'] = $auction_id;
        DB::insert('auction_content', $content_data);

        if( $data['status'] == AuctionModel::STATUS_WAIT) {
            $wait_time = $data['bid_start_time'] - $data['preview_start_time'];
            $route = "Auction:startPreview:{$auction_id}";
            redis_event_add($route, $wait_time);
        }

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        DB::update('auction', $data, "id={$id}");

        $content_data = $this->__contentFormData();
        DB::update('auction_content', $content_data, "auction_id={$id}");

        $this->resp(true);
    }

    public function delete() {

    }

    public function item() {
        $id = input('id');

        $sql = "select * from auction where id=?i";
        $auction = DB::getLine($sql, [$id]);

        list($preview_day, $preview_hour, $preview_minute, $preview_second) = dt_dhms($auction['preview_time_len']);
        $auction['preview_day'] = $preview_day;
        $auction['preview_hour'] = $preview_hour;
        $auction['preview_minute'] = $preview_minute;

        list($bid_day, $bid_hour, $bid_minute, $bid_second) = dt_dhms($auction['bid_time_len']);
        $auction['bid_day'] = $bid_day;
        $auction['bid_hour'] = $bid_hour;
        $auction['bid_minute'] = $bid_minute;

        $this->resp([
            'auction' => $auction
        ]);
    }

    public function show() {
        $id = input('id');

        $sql = "select * from auction where id=?i";
        $auction = DB::getLine($sql, [$id]);

        $auction['cover_photo'] = img_url($auction['cover_photo']);
        $auction['room'] = AuctionRoomModel::getNameById($auction['room_id']);
        $auction['category'] = AuctionCategoryModel::getNameById($auction['category_id']);
        $auction['supplier'] = AuctionSupplierModel::getNameById($auction['supplier_id']);
        $auction['cost_price'] = fen2yuan($auction['cost_price']);
        $auction['start_price'] = fen2yuan($auction['start_price']);
        $auction['min_price'] = fen2yuan($auction['min_price']);
        $auction['max_price'] = fen2yuan($auction['max_price']);
        $auction['bond'] = fen2yuan($auction['bond']);
        $auction['current_bid_price'] = fen2yuan($auction['current_bid_price']);
        $auction['preview_start_time'] = date('Y-m-d H:i:s', $auction['preview_start_time']);
        $auction['bid_start_time'] = date('Y-m-d H:i:s', $auction['bid_start_time']);
        $auction['bid_end_time'] = date('Y-m-d H:i:s', $auction['bid_end_time']);

        $auction['preview_time_len'] = dt_duration($auction['preview_time_len']);
        $auction['bid_time_len'] = dt_duration($auction['bid_time_len']);

        $auction['product_photo_list'] = explode(',', $auction['product_photos']);

        $this->resp([
            'auction' => $auction
        ]);
    }

    private function __formData() {
        $data = [
            'supplier_id'    => input('supplierId'),
            'category_id'    => input('categoryId'),
            'room_id'        => input('roomId'),
            'name_zh'        => input('nameZh'),
            'name_en'        => input('nameEn'),
            'name_local'     => input('nameLocal'),
            'cover_photo'     => input('coverPhoto'),
            'product_photos'  => input_raw('productPhotos'),
            'cost_price'      => input('costPrice'),
            'start_price'     => input('startPrice'),
            'min_price'       => input('minPrice'),
            'max_price'       => input('maxPrice'),
            'bond'            => input('bond'),
            'max_bid_num'      => input('maxBidNum'),
            'max_people_num'   => input('maxPeopleNum'),
            'preview_start_time' => input('preStartTime'),
        ];

        $data['leftover_bid_num'] = $data['max_bid_num'];

        // 等级限制
        $data['grade_limit'] = AuctionRoomModel::getGradeLimitById($data['room_id']);

        // 预展时长
        $data['preview_time_len'] = ((int)input('previewDay')) * 24 * 60 * 60
            + ((int)input('previewHour')) * 60 * 60 + ((int)input('previewMinute')) * 60;

        // 竞拍时长
        $data['bid_time_len'] = ((int)input('bidDay')) * 24 * 60 * 60
            + ((int)input('bidHour')) * 60 * 60 + ((int)input('bidMinute')) * 60;

        // 竞拍起止时间
        $data['bid_start_time'] = $data['preview_start_time'] + $data['preview_time_len'];
        $data['bid_end_time']   = $data['bid_start_time'] + $data['bid_time_len'];

        $data['create_time'] = time();

        if( $from_url = input('fromUrl') ) {
            $data['from_url'] = $from_url;
        }

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        if( $seq = input('remark') ) {
            $data['remark'] = $seq;
        }

        return $data;
    }

    private function __contentFormData() {
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

    private function __filter() {
        $factor_list = [
            ['s'=>"and from_type='$1'", 'f'=>input('fromType')],
            ['s'=>"and category_id=$1", 'f'=>input('categoryId')],
            ['s'=>"and room_id=$1", 'f'=>input('roomId')],
            ['s'=>"and status='$1'", 'f'=>input('status')],
        ];

        // 拍品 id 和 名称搜索


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


        return sql_where($factor_list);
    }

}

