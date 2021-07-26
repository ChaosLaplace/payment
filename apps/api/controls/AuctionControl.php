<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/AuctionModel.php';
require_once ROOT . 'models/AuctionCategoryModel.php';
require_once ROOT . 'models/AuctionRoomModel.php';
require_once ROOT . 'models/AuctionSupplierModel.php';
require_once ROOT . 'models/MessageTplModel.php';
require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'funcs/redis_event.fn.php';

/**
 * 拍卖
 */
class AuctionControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(id) from auction where {$sql_where}";
        $total_row = DB::getVar($sql);

        if( $total_row <= 0 ) {
            $this->resp([
                'total_row'     => 0,
                'total_page'    => 0,
                'auction_list'  => [],
            ]);
            return true;
        }

        $page_num  = input('pageNum') ? input('pageNum') : 1;
        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;

        list($total_page, $start_num) = app_paging($total_row, $page_num, $page_size);

        $name_field = 'name_' . $GLOBALS['request']['lang'];

        $sql = "select id, {$name_field} as `name`, cover_photo, start_price, current_bid, preview_start_time, bid_end_time, 
                    reserve_people_num, bid_people_num 
                from auction where {$sql_where} order by id desc limit {$start_num}, {$page_size}";
        $auction_list = DB::getData($sql);

        foreach($auction_list as &$auction) {
            $auction['start_price'] = fen2yuan($auction['start_price']);
            $auction['current_bid'] = fen2yuan($auction['current_bid']);
        }

        $this->resp([
            'total_row'     => $total_row,
            'total_page'    => $total_page,
            'auction_list'  => $auction_list,
        ]);
    }

    public function search() {

    }

    /**
     * 拍品详情
     */
    public function show() {
        $id = input('id');

        $name_field = 'name_' . $GLOBALS['request']['lang'];
        $sql = "select id, {$name_field} as `name`, product_photos, start_price, incr_price, bond, max_bid_num, 
                    leftover_bid_num, max_people_num, reserve_people_num, 
                    bid_people_num, current_bid, bid_start_time, bid_end_time
                from auction where id=?i";
        $auction = DB::getLine($sql, [$id]);

        if(!$auction) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '404未找到');
            return false;
        }

        $auction['product_photos'] = explode(',', $auction['product_photos']);
        $auction['start_price'] = fen2yuan($auction['start_price']);
        $auction['incr_price'] = fen2yuan($auction['incr_price']);
        $auction['current_bid'] = fen2yuan($auction['current_bid']);

        // 产品详情
        $content_field = 'content_' . $GLOBALS['request']['lang'];
        $sql = "select {$content_field} as content from auction_content where auction_id=?i";
        $auction_content = DB::getLine($sql, [$auction['id']]);

        $auction['content'] = $auction_content ? $auction_content['content'] : '';

        $this->resp([
            'auction' => $auction
        ]);
    }

    /**
     * 预交保证金
     */
    public function bond() {
        $auction_id = input('auctionId');

        $sql = "select id, grade_limit, bond, bid_start_time, status from auction where id=?i";
        $auction = DB::getLine($sql, [$auction_id]);

        if( !$auction ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '404，拍品不存在');
            return false;
        }

        $sql = "select id, grade from `user` where id=?i";
        $user = DB::getLine($sql, [$this->uid]);

        if( !$user ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '404，用户不存在');
            return false;
        }

        // 判断等级
        if( $user['grade'] < $auction['grade_limit'] ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '用户等级不够，不能预约');
            return false;
        }

        // 是否已经交过保证金
        $sql = "select id from auction_bond where auction_id=?i and user_id=?i";
        $auction_bond = DB::getLine($sql, [$auction_id, $this->uid]);

        if($auction_bond) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '已经预约过了，不要重复预约');
            return false;
        }

        $is_reserve = AuctionModel::bond($this->uid, $auction_id, $auction['grade_limit']);

        if( !$is_reserve ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '预约失败，余额不足');
            return false;
        }

        // if ( $auction['status'] == AuctionModel::STATUS_PREVIEW ) {
        //     $wait_time = $auction['bid_start_time'] - time();
        //     $route = "Auction:startPreview:{$auction_id}";
            
        //     if ( $wait_time >= 5 * 60 ) {
        //         redis_event_add($route, $wait_time + 5 * 60);
        //         redis_event_add($route, $wait_time + 1 * 60);
        //         redis_event_add($route, $wait_time);
        //     }
        //     else if ( $wait_time >= 1 * 60 ) {
        //         redis_event_add($route, $wait_time + 1 * 60);
        //         redis_event_add($route, $wait_time);
        //     }
        //     else {
        //         redis_event_add($route, $wait_time);
        //     }
        // }

        $this->resp(true);
    }

    /**
     * 拍卖房间列表
     */
    public function roomList() {
        $room_list = AuctionRoomModel::listRooms();

        $this->resp([
            'room_list' => $room_list
        ]);
    }

    /**
     * 分类
     */
    public function categoryList() {
        $category_list = AuctionCategoryModel::listCategories();

        $this->resp([
            'category_list' => $category_list
        ]);
    }

    /**
     * 拍品推荐
     */
    public function recommend() {

    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and category_id=$1", 'f'=>input('categoryId')],
            ['s'=>"and room_id=$1", 'f'=>input('roomId')],
        ];

        // 状态
        $status = input('status') ? input('status') : AuctionModel::STATUS_BID;
        $factor_list[] = ['s'=>"and status='$1'", 'f'=>$status];

        if( $min_price = input('minPrice') && $max_price = input('maxPrice') ) {
            $min_price = yuan2fen($min_price);
            $max_price = yuan2fen($max_price);

            $factor_list[] = ['s'=>"and start_price>={$min_price} and start_price<={$max_price}", 'f'=>true];
        }

        return sql_where($factor_list);
    }

}
