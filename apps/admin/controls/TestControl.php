<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class TestControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        // $url = 'http://payboqing.made9992.com:83/order/getUrl';

        // $data = array(
        //     'mch_id'     => '34829056',
        //     'order_sn'   => '20210816151047559948sve',
        //     'notify_url' => 'https://www.boqingpay.top/Pay_YsAlism_notifyurl.html',
        //     'money'      => '2001',
        //     'format'     => 'json',
        //     'ptype'      => '1',
        //     'client_ip'  => '86.97.87.225',
        //     'goods_desc' => '充值',
        //     'time'       => '1629097847'
        // );
        // $data['sign'] = $this->getSign($data);
        // $data = json_encode($data, true);

        // $tj = array(
        //     'json' => $data
        // );
        // // 设置选项，包括URL
        // $url .= '?' . http_build_query($tj);

        // $ga = new PHPGangsta_GoogleAuthenticator();
        // // 下面为生成二维码，内容是一个URI地址（otpauth://totp/账号?secret=密钥&issuer=标题）
        // $qrcode_url = $ga->getQRCodeGoogleUrl('1', 'test', $GLOBALS['app']['secret']);

        // $this->resp($url);
        $this->resp('test');
    }

    /**
     * 签名
     */
    public function getSign($data) {
        ksort($data);

        $md5str = '';
        foreach ($data as $k => $v) {
            if ($k == 'sign' || $v === '') {
                continue;
            }
            $md5str .= $k . '=' . $v . '&';
        }

        // return strtoupper( md5($md5str . 'key=' . self::MERCHANT_MD5_KEY) );
        return md5($md5str . 'key=979ad2ce99e34e46b928839318875f6b');
    }
}
