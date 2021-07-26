<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'funcs/ip_help.fn.php';
require_once XXOO . 'libs/Jwt.lib.php';
require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/GradeModel.php';
require_once ROOT . 'models/SmsModel.php';

class AppControl extends Control {
    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if( !$this->__validateLoginForm() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '表单验证未通过');
            return false;
        }

        $country_code   = input('countryCode');
        $phone          = input('phone');
        $login_password = input('loginPassword');

        $sql = "select id, login_password from `user` where country_code=?s and phone=?s limit 1";
        $user = DB::getLine($sql, [$phone, $country_code]);

        if( $user && $user['login_password'] ==
            UserModel::encodePassword($login_password, $GLOBALS['app']['secret']) ) {
            // 记录登录日志

            $ip = ip_long_get();
            $this->resp([
                'uid'   => $user['id'],
                'token' => $this->__createToken($user['id'], $ip)
            ]);
        }
    }

    public function register() {
        if( !$this->__validateRegisterForm() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '表单验证未通过');
            return false;
        }

        $country_code = input('countryCode');
        $phone = input('phone');

        // 短信验证码
        /*
        $sms_code = input('smsCode');
        if( !SmsModel::validation($country_code, $phone, $sms_code) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '手机验证码错误');
            return false;
        }
        */

        if( UserModel::isExistPhone($country_code, $phone) ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '手机号已注册');
            return false;
        }

        $pid = 0;
        if( $invite_code = input('inviteCode') ) {
            $pid = UserModel::getIdByInviteCode($invite_code);
        }

        // 用户初始等级
        $user_init_grade = GradeModel::getByGrade(GradeModel::USER_INIT_GRADE);

        $ip = ip_long_get();
        $time = time();
        $data = [
            'pid'           => $pid,
            'country_code'  => $country_code,
            'phone'         => $phone,
            'avatar'        => $GLOBALS['app']['default_avatar'],
            'login_password'=> UserModel::encodePassword(input('loginPassword'), $GLOBALS['app']['secret']),
            'grade_id'      => $user_init_grade['id'],
            'grade'         => $user_init_grade['grade'],
            'has_bid_num'   => $user_init_grade['bid_num'],
            'surplus_bid_num' => $user_init_grade['bid_num'],
            'reg_ip'        => $ip,
            'reg_time'      => $time,
        ];
        $uid = DB::insert('user', $data);

        $data = [
            'nickname'      => UserModel::createNickname($uid),
            'invite_code'   => UserModel::createInviteCode($uid),
        ];
        DB::update('user', $data, "id={$uid}");

        // 记录登录日志

        $this->resp([
            'uid'   => $uid,
            'token' => $this->__createToken($uid, $ip)
        ]);
    }

    /**
     * 重置密码
     */
    public function resetPassword() {

    }

    /**
     * APP 更新版本
     * @param int num 版本号
     * @param string terminal 手机类型 IOS/ANDROID
     * @return array 有更高的版本才返回
     * 
     * @author Chaos
     */
    public function upgrade() {
        $terminal = input('terminal');
        $content_language = 'content_' . $GLOBALS['request']['locale'];

        $sql = "select num, size, is_must, url, {$content_language} from `app_version`
                where terminal=?s order by id desc limit 1";
        $app_info = DB::getLine($sql, [$terminal]);

        if ( input('num') != $app_info['num'] || $app_info['is_must'] === 'Y' ) {
            $this->resp($app_info);
        }
    }

    private function __createToken($uid, $ip) {
        $time = time();
        $token = Jwt::encode([
            'iat' => $time,
            'exp' => $time + $GLOBALS['token']['expire'],
            'uid' => $uid,
            'ip'  => $ip,
        ], $GLOBALS['app']['secret']);
        return $token;
    }

    private function __validateLoginForm() {
        $validator = new Validator();
        $validator->setRule('countryCode', array('required'));
        $validator->setRule('phone', array('required'));
        $validator->setRule('loginPassword', array('required'));
        return $validator->validate();
    }

    private function __validateRegisterForm() {
        $validator = new Validator();
        $validator->setRule('countryCode', array('required'));
        $validator->setRule('phone', array('required'));
        $validator->setRule('smsCode', array('required'));
        $validator->setRule('loginPassword', array('required'));
        return $validator->validate();
    }

    private function __validateResetPasswordForm() {

    }

}
