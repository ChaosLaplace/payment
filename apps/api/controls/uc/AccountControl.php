<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'models/BankModel.php';

class AccountControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {

    }

    public function show() {

    }

    public function recharge() {
        $sql = "select bank_id, account_num from user_bank where user_id=?i limit 1";
        $user_bank = DB::getLine($sql, [$this->uid]);

        if(!$user_bank) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '还没有绑定银行卡');
            return false;
        }

        $user_bank['bank_name'] = BankModel::getNameById($user_bank['bank_id']);

        $this->resp([
            'user_bank' => $user_bank,
            'rec_bank'  => $GLOBALS['app']['rec_bank'],
        ]);
    }

    public function rechargePro() {
        if( !$this->__rechargeFormData() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '充值表单验证未通过');
            return false;
        }

        // 支付渠道
        $pay_channel_id = input('payChannelId');

        // 付款银行
        $user_bank_id = input('userBankId');
        $sql = "select bank_id, username, account_num 
                from user_bank where user_id=?i limit 1";
        $user_bank = DB::getLine($sql, [$user_bank_id]);
        if( empty($user_bank) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '付款银行有误');
            return false;
        }
        else {
            $user_bank['bank_name'] = BankModel::getNameById($user_bank['bank_id']);
        }

        // 收款银行
        $rec_bank_id = input('recBankId');
        $rec_bank = $GLOBALS['app']['rec_bank'];
        if( empty($rec_bank) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '收款银行有误');
            return false;
        }

        // 支付金额
        $pay_amount = yuan2fen(input('payAmount'));

        $data = [
            'pay_channel_id'    => $pay_channel_id,
            'user_id'           => $this->uid,
            'pay_bank_id'       => $user_bank['bank_id'],
            'pay_bank_name'     => $user_bank['bank_name'],
            'pay_account_num'   => $user_bank['account_num'],
            'pay_username'      => $user_bank['username'],
            'pay_amount'        => $pay_amount,
            'pay_time'          => time(),
            'rec_bank_id'       => $rec_bank['bank_id'],
            'rec_bank_name'     => $rec_bank['bank_name'],
            'rec_account_num'   => $rec_bank['account_num'],
            'rec_username'      => $rec_bank['username'],
            'create_time'       => time(),
        ];

        // 充值金额，单列出来，以应对充多少送多少之类的活动
        $data['recharge_amount'] = $pay_amount;

        DB::insert('user_recharge', $data);

        $this->resp(true);
    }

    public function exchange() {
        $sql = "select id, bank_id, contract_bank, username, account_num 
                from user_bank where user_id=?i limit 1";
        $user_bank = DB::getLine($sql, [$this->uid]);

        if( !$user_bank ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '还没有绑定银行卡');
            return false;
        }

        $user_bank['bank_name'] = BankModel::getNameById($user_bank['bank_id']);

        // 可提现金额
        $balance = UserAccountModel::getBalance($this->uid);
        $balance = fen2yuan($balance);

        $this->resp([
            'user_bank'     => $user_bank,
            'limitAmount'   => fen2yuan($GLOBALS['app']['exchange_limit']), // 用户提现最低限额
            'balance'       => $balance,
        ]);
    }

    public function exchangePro() {
        if( !$this->__exchangeFormData() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '提现表单验证未通过');
            return false;
        }

        $user_bank_id = input('userBankId');
        $apply_amount = yuan2fen(input('applyAmount'));

        $sql = "select * from user_bank where id=?i";
        $user_bank = DB::getLine($sql, [$user_bank_id]);
        if( !$user_bank ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '提现银行卡不存在');
            return false;
        }

        $balance = UserAccountModel::getBalance($this->uid);
        if( $apply_amount > $balance ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '余额不足');
            return false;
        }

        $data = [
            'user_id'           => $this->uid,
            'rec_bank_id'       => $user_bank['bank_id'],
            'rec_bank_contract' => $user_bank['contract_bank'],
            'rec_username'      => $user_bank['username'],
            'rec_account_num'   => $user_bank['account_num'],
            'apply_amount'      => $apply_amount,
            'create_time'       => time(),
        ];
        $exchange_id = DB::insert('user_exchange', $data);

        $trade = [
            'subject_id'            => UserAccountModel::SUBJECT_EXCHANGE,
            'change_depict_zh'      => '',
            'change_depict_en'      => '',
            'change_depict_local'   => '',
            'rel_table'             => 'user_exchange',
            'rel_id'                => $exchange_id
        ];
        UserAccountModel::expend($this->uid, $apply_amount, $trade);

        $this->resp(true);
    }

    private function __exchangeFormData() {
        $validator = new Validator();
        $validator->setRule('userBankId', array('required'));
        $validator->setRule('applyAmount', array('required'));
        return $validator->validate();
    }

    private function __rechargeFormData() {
        $validator = new Validator();
        $validator->setRule('payChannelId', array('required'));
        $validator->setRule('userBankId', array('required'));
        $validator->setRule('recBankId', array('required'));
        $validator->setRule('payAmount', array('required'));
        return $validator->validate();
    }

}
