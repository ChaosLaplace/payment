<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class UserControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select count(id) from `user`";
        $total_row = DB::getVar($sql);

        if($total_row <= 0) {
            $this->resp([
                'total_row' => 0,
                'user_list' => []
            ]);
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select * from `user` order by id desc {$page_info['limit']}";
        $user_list = DB::getData($sql);

        foreach($user_list as &$user) {
            $user['balance'] = fen2yuan($user['balance']);
            $user['brokerage'] = fen2yuan($user['brokerage']);
            $user['last_login_time'] = date('Y-m-d H:i', $user['last_login_time']);
            $user['reg_time'] = date('Y-m-d', $user['reg_time']);
        }

        $this->resp([
            'total_row' => $total_row,
            'user_list' => $user_list
        ]);
    }

    public function show() {

    }

}
