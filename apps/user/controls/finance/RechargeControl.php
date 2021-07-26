<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/PayModel.php';
require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/UserAccountModel.php';

/**
 * 充值
 */
class RechargeControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(ur.id) from user_recharge ur, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'      => 0,
                'recharge_list'  => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select ur.*, u.nickname, u.country_code, u.phone 
                from user_recharge ur, `user` u 
                where {$sql_where} order by ur.id desc {$page_info['limit']}";
        $recharge_list = DB::getData($sql);

        foreach($recharge_list as &$recharge) {
            $recharge['pay_channel_name'] = PayModel::getChannelNameById($recharge['pay_channel_id']);
            $recharge['pay_amount'] = fen2yuan($recharge['pay_amount']);
            $recharge['recharge_amount'] = fen2yuan($recharge['recharge_amount']);
            $recharge['created'] = date('Y-m-d H:i', $recharge['create_time']);
        }

        $this->resp([
            'total_row'     => $total_row,
            'recharge_list' => $recharge_list
        ]);
    }

    /**
     * 确认充值
     */
    public function agree() {
        $id = input('id');

        $sql = "select id, user_id, recharge_amount from user_recharge where id=?i";
        $recharge = DB::getLine($sql, [$id]);

        // 收款
        $rec_amount = yuan2fen( input('recAmount') );           // 收款金额
        $data = [
            'trade_sn'   => input('tradeSn'),
            'rec_amount' => $rec_amount,
            'rec_time'   => time(),
            'is_receipt' => 'Y',
            'admin_id'   => $this->adminId,
        ];

        if( $remark = input('remark') ) {
            $data['remark'] = $remark;
        }
        DB::update('user_recharge', $data, "id={$id}");

        // 充值
        $recharge_amount = yuan2fen( input('rechargeAmount') ); // 充值金额
        $trade = [
            'subject_id' => UserAccountModel::SUBJECT_RECHARGE,
            'rel_table'  => 'user_recharge',
            'rel_id'     => $id
        ];
        UserAccountModel::income($recharge['user_id'], $recharge['recharge_amount'], $trade);

        $data = [
            'finish_time' => time(),
            'status'        => 'SUCCESS'
        ];
        DB::update('user_recharge', $data, "id={$id}");

        // 发消息

        $this->resp(true);
    }

    /**
     * 拒绝充值
     */
    public function refuse() {
        $id = input('id');
        $data = [
            'status'    => 'FAIL',
            'admin_id'  => $this->uid,
            'remark'    => input('remark')
        ];

        DB::update('user_recharge', $data, "id={$id}");

        // 发消息

        $this->resp(true);
    }

    public function isExistTradeSn() {
        $this->isExist('user_recharge', 'trade_sn');
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and ur.user_id=u.id", 'f'=>true],
            ['s'=>"and ur.pay_channel_id=$1", 'f'=>input('payChannelId')],
            ['s'=>"and ur.trade_sn='$1'", 'f'=>input('tradeSn')],
            ['s'=>"and ur.is_paid='$1'", 'f'=>input('isPaid')],
            ['s'=>"and ur.is_recharge='$1'", 'f'=>input('isRecharge')],
            ['s'=>"and ur.pay_amount=$1", 'f'=>input('payAmount')],
        ];

        // 搜索用户
        if( $user_query = input('user') ) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s'=>"and ur.user_id in ({$user_ids})", 'f'=>true];
        }

        if( $pay_amount = input('payAmount') ) {
            $pay_amount = yuan2fen($pay_amount);
            $factor_list[] = ['s'=>"and ur.pay_amount=$1", 'f'=>$pay_amount];
        }

        if( $start_create_time = input('startCreateTime') && $end_create_time = input('endCreateTime') ) {
            $factor_list[] = ['s'=>"and ur.create_time>={$start_create_time} and ur.create_time<={$end_create_time}", 'f'=>true];
        }

        $sql_where = sql_where($factor_list);

        return $sql_where;
    }

}
