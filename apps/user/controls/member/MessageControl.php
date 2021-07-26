<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class MessageControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(m.id) from user_message m, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'    => 0,
                'message_list' => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select m.*, u.nickname, u.phone 
                from user_message m, `user` u 
                where {$sql_where} order by id desc {$page_info['limit']}";
        $message_list = DB::getData($sql);

        $this->resp([
            'total_row'    => $total_row,
            'message_list' => $message_list,
        ]);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and m.user_id=u.id", 'f'=>true],
        ];

        // 搜索用户
        if( $user_query = input('user') ) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s'=>"and m.user_id in ({$user_ids})", 'f'=>true];
        }

        return sql_where($factor_list);
    }

}
