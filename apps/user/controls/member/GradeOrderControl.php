<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/GradeModel.php';
require_once ROOT . 'models/UserModel.php';

class GradeOrderControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(u.id) from grade_order o, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'  => 0,
                'order_list' => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select o.*, u.nickname, u.country_code, u.phone 
                from grade_order o, `user` u 
                where {$sql_where} order by id desc {$page_info['limit']}";
        $order_list = DB::getData($sql);

        foreach($order_list as &$order) {
            $order['price'] = fen2yuan($order['price']);
            $order['differ_price'] = fen2yuan($order['differ_price']);
            $order['share_amount'] = fen2yuan($order['share_amount']);
        }

        $this->resp([
            'total_row'  => $total_row,
            'order_list' => $order_list,
        ]);
    }

    public function init() {
        $this->resp([
            'grade_opts' => GradeModel::opts()
        ]);
    }

    private function __filter() {
        $factor_list = [
            ['s' => "and o.user_id=u.id", 'f' => true],
            ['s' => "and o.new_grade_id=$1", 'f' => input('newGradeId')],
            ['s' => "and o.is_paid=$1", 'f' => input('isPaid')],
        ];

        // 搜索用户
        if( $user_query = input('user') ) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s'=>"and ur.user_id in ({$user_ids})", 'f'=>true];
        }

        if( $start_create_time = input('startCreateTime') && $end_create_time = input('endCreateTime') ) {
            $factor_list[] = ['s'=>"and create_time>={$start_create_time} and create_time<={$end_create_time}", 'f'=>true];
        }

        return sql_where($factor_list);
    }

}
