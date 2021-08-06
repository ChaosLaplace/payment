<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'libs/auth/GoogleAuthenticator.php';

class TestControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        echo 'test';
        // $ga = new PHPGangsta_GoogleAuthenticator();
        // // 下面为生成二维码，内容是一个URI地址（otpauth://totp/账号?secret=密钥&issuer=标题）
        // $qrcode_url = $ga->getQRCodeGoogleUrl('1', 'test', $GLOBALS['app']['secret']);

        // $this->resp($qrcode_url);
    }
}
