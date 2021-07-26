<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class AccountControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(ua.id) from user_account ua, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'    => 0,
                'account_list' => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $depict_field = 'change_depict_' . $GLOBALS['request']['lang'];
        $sql = "select ua.id, ua.user_id, ua.subject_id, ua.balance, ua.change_type, ua.change_amount, 
                    ua.{$depict_field} as change_depict, ua.created, u.nickname, u.country_code, u.phone 
                from user_account ua, `user` u 
                where {$sql_where} order by ua.id desc {$page_info['limit']}";
        $account_list = DB::getData($sql);

        foreach($account_list as &$acc) {
            $acc['balance'] = fen2yuan($acc['balance']);
            if( $acc['change_type'] == 'INCOME' ) {
                $acc['change_amount'] = '+' . fen2yuan($acc['change_amount']);
            }
            else {
                $acc['change_amount'] = '-' . fen2yuan($acc['change_amount']);
            }
        }

        $this->resp([
            'total_row'    => $total_row,
            'account_list' => $account_list,
        ]);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and ua.user_id=u.id", 'f'=>true],
        ];

        // 搜索用户
        if( $user_query = input('user') ) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s'=>"and ua.user_id in ({$user_ids})", 'f'=>true];
        }

        return sql_where($factor_list);
    }

}
