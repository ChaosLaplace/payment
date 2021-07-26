<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/PayModel.php';
require_once ROOT . 'models/BankModel.php';
require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'models/UserMessageModel.php';

class ExchangeControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(ue.id) from user_exchange ue, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'      => 0,
                'exchange_list'  => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select ue.*, u.nickname, u.country_code, u.phone 
                from user_exchange ue, `user` u 
                where {$sql_where} order by ue.id desc {$page_info['limit']}";
        $exchange_list = DB::getData($sql);

        foreach($exchange_list as &$exchange) {
            $exchange['rec_bank_name'] = BankModel::getNameById($exchange['rec_bank_id']);
            $exchange['pay_bank_name'] = BankModel::getNameById($exchange['pay_bank_id']);
            $exchange['apply_amount'] = fen2yuan($exchange['apply_amount']);
            $exchange['pay_amount'] = fen2yuan($exchange['pay_amount']);
            $exchange['created'] = date('Y-m-d H:i', $exchange['create_time']);
        }

        $this->resp([
            'total_row'     => $total_row,
            'exchange_list' => $exchange_list
        ]);
    }

    public function agreeInit() {
        $this->resp([
            'bank_opts' => BankModel::opts()
        ]);
    }

    /**
     * 同意提现
     */
    public function agree() {
        $id = input('id');

        $sql = "select id, user_id, apply_amount from user_exchange where id=?i";
        $ue = DB::getLine($sql, [$id]);

        $trade_sn = input('tradeSn');
        $pay_amount = yuan2fen(input('payAmount'));
        $data = [
            'pay_bank_id'     => input('payBankId'),
            'pay_account_num' => input('payAccountNum'),
            'pay_amount'      => $pay_amount,
            'trade_sn'        => $trade_sn,
            'status'          => 'SUCCESS',
            'finish_time'     => time(),
            'remark'          => input('remark'),
            'admin_id'        => $this->adminId,
        ];
        DB::update('user_exchange', $data, "id={$id}");

        // 将用户设为有效用户
        $sql = "update `user` set is_valid='Y' where id=?i";
        DB::runSql($sql, [$ue['id']]);

        // 通知消息
        $message = [
            'zh'    => '同意提现',
            'en'    => '同意提现',
            'local' => '同意提现',
        ];
        UserMessageModel::saveUserMessage($ue['id'], $message);

        // 解锁上级佣金

        $this->resp(true);
    }

    /**
     * 拒绝提现
     */
    public function refuse() {
        $id = input('id');

        $sql = "select id, user_id, apply_amount from user_exchange where id=?i";
        $ue = DB::getLine($sql, [$id]);

        $data = [
            'status'      => 'FAIL',
            'finish_time' => time(),
            'remark'      => input('remark'),
            'admin_id'    => $this->adminId,
        ];
        DB::update('user_exchange', $data, "id={$id}");

        // 冲正
        $trade = [
            'subject_id'          => UserAccountModel::SUBJECT_RUSH,
            'change_depict_zh'    => '冲正',
            'change_depict_en'    => '冲正',
            'change_depict_local' => '冲正',
            'rel_table'           => 'user_exchange',
            'rel_id'              => $id
        ];
        UserAccountModel::income($ue['user_id'], $ue['apply_amount'], $trade);

        // 通知消息
        $message = [
            'zh'    => '冲正',
            'en'    => '冲正',
            'local' => '冲正',
        ];
        UserMessageModel::saveUserMessage($ue['id'], $message);

        $this->resp(true);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>"and ue.user_id=u.id", 'f'=>true],
        ];

        // 搜索用户
        if( $user_query = input('user') ) {
            $user_ids = UserModel::findUserIds($user_query);
            $factor_list[] = ['s'=>"and ur.user_id in ({$user_ids})", 'f'=>true];
        }

        if( $exchange_amount = input('exchangeAmount') ) {
            $exchange_amount = yuan2fen($exchange_amount);
            $factor_list[] = ['s'=>"and ue.exchange_amount=$1", 'f'=>$exchange_amount];
        }

        if( $start_create_time = input('startCreateTime') && $end_create_time = input('endCreateTime') ) {
            $factor_list[] = ['s'=>"and ue.create_time>={$start_create_time} and ue.create_time<={$end_create_time}", 'f'=>true];
        }

        $sql_where = sql_where($factor_list);

        return $sql_where;
    }

}
