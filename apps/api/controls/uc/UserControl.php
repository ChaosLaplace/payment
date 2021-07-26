<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/BankModel.php';
require_once ROOT . 'models/SmsModel.php';
require_once ROOT . 'models/UserReceiptInfoModel.php';

require_once ROOT . 'libs/OssUpload.lib.php';

/**
 * APP 个人信息操作
 * 
 * @author Chaos
 */
class UserControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 查询会员昵称
     * @return string 返回会员昵称
     */
    public function nickname() {
        $nickname = UserModel::getUserNickname($this->uid);

        $this->resp([
            'nickname' => $nickname
        ]);
    }

    /**
     * 修改会员昵称
     * @param string nickname 会员昵称
     */
    public function nicknamePro() {
        $data = array(
            'nickname' => input('nickname'),
        );
        DB::update('user', $data, "id={$this->uid}");

        $this->resp('Y');
    }

    /**
     * 查询会员手机号
     * @return string 返回会员手机号
     */
    public function phone() {
        $sql  = "select country_code, phone from `user` where id=?i";
        $user = DB::getLine($sql, [$this->uid]);

        $this->resp([
            'country_code' => $user['country_code'],
            'phone' => $user['phone']
        ]);
    }

    /**
     * 修改会员手机号
     * @param string countryCode 会员国际区号
     * @param string phone 会员手机号
     * @param string smsCode 验证码
     */
    public function phonePro() {
        $country_code = input('countryCode');
        $phone        = input('phone');
        $sms_code     = input('smsCode');

        // 短信验证码
        /*
        if( !SmsModel::validation($country_code, $phone, $sms_code) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '手机验证码错误');
            return false;
        }
        */

        $data = array(
            'country_code' => $country_code,
            'phone' => $phone
        );
        DB::update('user', $data, "id={$this->uid}");

        $this->resp('Y');
    }

    /**
     * 绑定银行卡
     */
    public function bindBank() {
        $sql = "select id, bank_id, contract_bank, username, account_num 
                from user_bank where user_id=?i limit 1";
        $bank = DB::getLine($sql, [$this->uid]);
        if($bank) {
            $bank['name'] = BankModel::getNameById($bank['bank_id']);
        }

        $this->resp([
            'bank_opts' => BankModel::opts(),
            'bank'      => $bank
        ]);
    }

    public function bindBankPro() {
        if( !$this->__bindBankFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '绑定银行卡表单验证未通过');
            return false;
        }

        $data = [
            'user_id'       => $this->uid,
            'bank_id'       => input('bankId'),
            'contract_bank' => input('contractBank'),
            'username'      => input('username'),
            'account_num'   => input('accountNum'),
        ];

        $sql = "select id from `user_bank` where user_id=?i";
        $ub = DB::getLine($sql, [$this->uid]);

        if($ub) { // 修改
            DB::update('user_bank', $data, "id={$ub['id']}");
        }
        else { // 新增
            DB::insert('user_bank', $data);
        }

        $this->resp('Y');
    }

    /**
     * 查询真实姓名 & 真实身份证
     * @return string 返回真实姓名 & 真实身份证
     */
    public function realIdentity() {
        $sql = "select real_name, real_id_card from `user` where id=?i";
        $real_info = DB::getLine($sql, [$this->uid]);

        $this->resp([
            'real_name'    => $real_info['real_name'],
            'real_id_card' => $real_info['real_id_card']
        ]);
    }

    /**
     * 修改真实姓名 & 真实身份证
     * @param string realName 会员真实姓名
     * @param string realIdCard 会员真实身份证
     */
    public function realIdentityPro() {
        $data = array(
            'real_name'    => input('realName'),
            'real_id_card' => input('realIdCard')
        );
        DB::update('user', $data, "id={$this->uid}");

        $this->resp('Y');
    }

    /**
     * 查询收货资讯
     * @return string 返回收货资讯
     */
    public function receiptInfo() {
        $sql = "select * from `user_receipt_info` where user_id=?i and is_delete='N'";
        $receipt_info_list = DB::getData($sql, [$this->uid]);

        $this->resp($receipt_info_list);
    }

    /**
     * 修改收货资讯
     */
    public function receiptUpdate() {
        if( !$this->__receiptFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '修改收货资讯表单验证未通过');
            return false;
        }

        $id = input('id');
        $data = array(
            'user_id'      => $this->uid,
            'username'     => input('username'),
            'country_code' => input('countryCode'),
            'phone'        => input('phone'),
            'address'      => input('address'),
        );

        if( input('setDefault') ) {
            $sql = "update user_receipt_info set is_default='N' where user_id=?i";
            DB::runSql($sql, [$this->uid]);

            $data['is_default'] = 'Y';
        }

        DB::update('user_receipt_info', $data, "id={$id}");

        $this->resp('Y');
    }

    /**
     * 软删除收货资讯
     */
    public function receiptDelete() {
        $id = input('id');

        $sql = "update `user_receipt_info` set is_delete='Y' where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp('Y');
    }

    /**
     * 设置为预设地址
     */
    public function receiptSetDefault() {
        $id = input('id');

        $sql = "update user_receipt_info set is_default='N' where user_id=?i";
        DB::runSql($sql, [$this->uid]);

        $sql = "update user_receipt_info set is_default='Y' where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp('Y');
    }

    /**
     * 查询是否存在支付密码
     */
    public function payPassword() {
        $sql = "select pay_password from `user` where id=?i";
        $user = DB::getLine($sql, [$this->uid]);

        $this->resp([
            'has_old_password' => empty($user['pay_password']) ? 'N' : 'Y'
        ]);
    }

    /**
     * 修改支付密码
     * @param string oldPayPassword 会员原支付密码
     * @param string newPayPassword 会员新支付密码
     * @param string checkPayPassword 会员确认支付密码
     * @return bool 返回成功或失败
     */
    public function payPasswordPro() {
        if( !$this->__PasswordFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '修改密码表单验证错误');
            return false;
        }

        $sql = "select pay_password from `user` where id=?i";
        $store_old_passwd = DB::getVar($sql, [$this->uid]);

        $old_passwd = input('oldPassword');

        if($store_old_passwd) {
            if( $store_old_passwd != UserModel::encodePassword($old_passwd, $GLOBALS['app']['secret']) ) {
                $this->respError(ErrorCode::PARAMETER_ERROR, '原始密码错误');
                return false;
            }
        }

        $new_passwd = input('newPassword');
        $new_passwd = UserModel::encodePassword($new_passwd, $GLOBALS['app']['secret']);

        $sql = "update `user` set pay_password=?s where id=?i";
        DB::runSql($sql, [$new_passwd, $this->uid]);

        $this->resp('Y');
    }

    /**
     * 查询是否存在登入密码
     */
    public function loginPassword() {
        $sql = "select login_password from `user` where id=?i";
        $user = DB::getLine($sql, [$this->uid]);

        $this->resp([
            'has_old_password' => empty($user['login_password']) ? 'N' : 'Y'
        ]);
    }

    /**
     * 修改登入密码
     * @return bool 返回成功或失败
     */
    public function loginPasswordPro() {
        if( !$this->__PasswordFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '修改密码表单验证错误');
            return false;
        }

        $sql = "select login_password from `user` where id=?i";
        $store_old_passwd = DB::getVar($sql, [$this->uid]);

        $old_passwd = input('oldPassword');

        if($store_old_passwd) {
            if( $store_old_passwd != UserModel::encodePassword($old_passwd, $GLOBALS['app']['secret']) ) {
                $this->respError(ErrorCode::PARAMETER_ERROR, '原始密码错误');
                return false;
            }
        }

        $new_passwd = input('newPassword');
        $new_passwd = UserModel::encodePassword($new_passwd, $GLOBALS['app']['secret']);

        $sql = "update `user` set login_password=?s where id=?i";
        DB::runSql($sql, [$new_passwd, $this->uid]);

        $this->resp('Y');
    }

    /**
     * 查询邀请码
     * @return string 返回邀请码
     */
    public function inviteCode() {
        $invite_code = UserModel::getInviteCode($this->uid);

        $this->resp([
            'invite_code' => $invite_code
        ]);
    }

    /**
     * 上传头像
     * @param string avatar 头像
     */
    public function uploadAvatar() {
        $id     = $this->uid;
        $avatar = input('avatar');

        /*
        $data = array(
            'avatar' => $avatar,
        );
        return DB::update('user', $data, "id={$id}");

        $this->respError(ErrorCode::PARAMETER_ERROR, '头像错误');
        return false;
        */
    }

    private function __bindBankFormValidator() {
        $validator = new Validator();
        $validator->setRule('bankId', array('required'));
        $validator->setRule('contractBank', array('required'));
        $validator->setRule('username', array('required'));
        $validator->setRule('accountNum', array('required'));
        return $validator->validate();
    }

    private function __PasswordFormValidator() {
        $validator = new Validator();
        $validator->setRule('newPassword', array('required'));
        return $validator->validate();
    }

    private function __receiptFormValidator() {
        $validator = new Validator();
        $validator->setRule('username', array('required'));
        $validator->setRule('countryCode', array('required'));
        $validator->setRule('phone', array('required'));
        $validator->setRule('address', array('required'));
        return $validator->validate();
    }
}
