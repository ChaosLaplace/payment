<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 拍品保证金
 */
class AuctionBondControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(ab.id) from auction_bond ab, auction a, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row' => 0,
                'bond_list' => [],
            ]);
            return;
        }

        $auction_name_filed = 'name_' . $GLOBALS['request']['lang'];
        $sql = "select ab.id, ab.bond_amount, ab.pay_time, ab.finitsh_time, ab.status, 
                    u.nickname, u.country_code, u.phone, a.{$auction_name_filed}, a.cover_photot 
                from auction_bond ab, auction a, `user` u where {$sql_where} order by ab.id desc";
        $bond_list = DB::getData($sql);

        foreach($bond_list as &$bond) {
            $bond['bond_amount'] = fen2yuan($bond['bond_amount']);
            $bond['pay_time'] = fen2yuan($bond['pay_time']);
            $bond['finish_time'] = fen2yuan($bond['finish_time']);
        }

        $this->resp([
            'total_row' => $total_row,
            'bond_list' => $bond_list,
        ]);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and ab.user_id=u.id", 'f'=>true],
            ['s'=>"and ab.auction_id=a.id", 'f'=>true],
        ];

        // 搜索用户
        if ($user_query = input('user')) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s' => "and a.user_id in ({$user_ids})", 'f' => true];
        }

        // 搜索商品

        return sql_where($factor_list);
    }

}
