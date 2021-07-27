<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'libs/auth/GoogleAuthenticator.php';
require_once ROOT . 'funcs/sign.fn.php';

class UserControl extends Control {

    /** 用户类型 */
    const USER_TYPE = 'NORMAL';
    /** 是否有權限 */
    const IS_VALID  = 'N';

    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if( !$this->__loginFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '表單參數驗證未通過');
            return false;
        }

        $merchant_id = input('merchantId'); // 帳號
        $login_auth  = input('loginAuth');  // 密碼
        $google_auth = input('googleAuth'); // 驗證碼

        // 查詢登入用戶
        $sql  = "select login_auth, google_auth from `user` where merchant_id=?s";
        $user = DB::getLine($sql, [$merchant_id]);

        // 驗證 密碼
        if ( $user['login_auth'] !== sign($login_auth, $GLOBALS['app']['secret']) ) {
            $this->respError(ErrorCode::LOGIN_ERROR, '登入驗證未通過');
            return false;
        }
        
        // $ga = new PHPGangsta_GoogleAuthenticator();
        // // 驗證 Google 驗證碼
        // if ( !$ga->verifyCode($user['google_auth'], $google_auth) ) {
        //     $this->respError(ErrorCode::AUTH_ERROR, '驗證碼未通過驗證');
        //     return false;
        // }

        $this->resp('Y');
    }

    public function register() {
        $ga = new PHPGangsta_GoogleAuthenticator();
        // 生成密钥，每个用户唯一一个，为用户保存起来用于验证
        $secret = $ga->createSecret();
    }

    public function googleAuth() {
        if( !$this->__googleAuthFormValidator() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '表單參數驗證未通過');
            return false;
        }

        $manager_id  = input('managerId');  // 管理員 ID
        $login_auth  = input('loginAuth');  // 管理員 密碼
        $google_auth = input('googleAuth'); // 管理員 驗證碼
        $merchant_id = input('merchantId'); // 商戶帳號

        // 查詢操作者權限
        $sql  = "select type, login_auth, google_auth, is_valid from `user` where id=?i";
        $manager = DB::getLine($sql, [$manager_id]);
        if ( $manager['type'] === self::USER_TYPE && $manager['is_valid'] === self::IS_VALID ) {
            $this->respError(ErrorCode::PERMISSION_DENIED, '沒有權限');
            return false;
        }

        // 驗證 密碼
        if ( $manager['login_auth'] !== sign($login_auth, $GLOBALS['app']['secret']) ) {
            $this->respError(ErrorCode::LOGIN_ERROR, '登入驗證未通過');
            return false;
        }

        $ga = new PHPGangsta_GoogleAuthenticator();
        // 驗證 Google 驗證碼
        // if ( !$ga->verifyCode($manager['google_auth'], $google_auth) ) {
        //     $this->respError(ErrorCode::AUTH_ERROR, '驗證碼未通過驗證');
        //     return false;
        // }

        // 查詢商戶號
        $sql  = "select type, google_auth from `user` where merchant_id=?s";
        $merchant = DB::getLine($sql, [$merchant_id]);
        if ( $merchant['type'] !== self::USER_TYPE ) {
            $this->respError(ErrorCode::UNDEFINED_USER, '不存在商戶');
            return false;
        }

        // 下面为生成二维码，内容是一个URI地址（otpauth://totp/账号?secret=密钥&issuer=标题）
        $qrcode_url = $ga->getQRCodeGoogleUrl($merchant_id, $merchant['google_auth']
        , $GLOBALS['app']['secret']);

        $this->resp([
            'qrcode_url' => $qrcode_url
        ]);
    }

    private function __loginFormValidator() {
        $validator = new Validator();
        $validator->setRule('merchantId', array('required'));
        $validator->setRule('loginAuth', array('required'));
        $validator->setRule('googleAuth', array('required'));
        return $validator->validate();
    }

    private function __googleAuthFormValidator() {
        $validator = new Validator();
        $validator->setRule('managerId', array('required'));
        $validator->setRule('loginAuth', array('required'));
        $validator->setRule('googleAuth', array('required'));
        $validator->setRule('merchantId', array('required'));
        return $validator->validate();
    }
}
