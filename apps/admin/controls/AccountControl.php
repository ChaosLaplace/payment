<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/LogModel.php';
require_once ROOT . 'funcs/random_user_agent.fn.php';
require_once ROOT . 'funcs/redis_event.fn.php';

class AccountControl extends Control {

    const TRIGGER_TIME = 10;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 顯示小號資料
     */
    public function index() {
        $ret = array();
    }

    /**
     * 用 csv 檔案 新增 || 更新 小號資料
     */
    public function upload() {
    }

    /**
     * 檢查小號狀態
     */
    public function verify() {
        // 驗證傳遞的參數是否合法
        // if( !$this->__verifyFormValidator() ) {
        //     $this->respError(ErrorCode::PARAMETER_ERROR, '表單參數驗證未通過');
        //     return false;
        // }

        // $sql = "select status, webcookie from card where id=?s";
        // $id  = input('id') ?: 22; // 操作者帳號
        // // 驗證使用者權限
        // if ( !$card = DB::getLine($sql, [$id]) ) {
        //     $this->respError(ErrorCode::PERMISSION_DENIED, '沒有權限');
        //     return false;
        // }

        // $verify_card = $this->__verifyCard($card['webcookie']);

        // $where = array(
        //     'id'  => $id,
        //     'num' => '0'
        // );

        // $ret['status'] = 1;

        // if ( strpos($verify_card, 'm no login') !== false ) {
        //     $data['status'] = '0';
        //     // DB::update('card', $data, $where);

        //     // $ret['msg'] = '小号已无效';
        //     $ret['msg'] = 'fail';
        // }
        // else {
        //     // 小號有效就不更新
        //     if ( $card['status'] !== 1 ) {
        //         $data['status'] = '1';
        //         // DB::update('card', $data, $where);
        //     }

        //     // $ret['msg'] = '小号CK有效';
        //     $ret['msg'] = 'ok';
        // }

        // 模擬的頁面
        $data['referer'] = 'https://wqs.jd.com/';
        // 請求的 url
        // $data['url']     = 'https://wqs.jd.com/my/asset.html';
        $data['url']     = '103.96.1.117';

        // $data['referer'] = "https://home.m.jd.com/myJd/newhome.action?sceneval=2&ufc=&";
		// $data['url'] = "https://wq.jd.com/user_redpoint/ClearUserRedPoint?type=4&sceneid=2&callback=ClearUserRedPoint&xx=1165";
        // $data['url'] = 'https://p.m.jd.com/cart/cart.action?fromnav=1';
        
        $data['cookie']  = 'pt_key=AAJhHFZkADAf_gbI_SlIQ8Vr11pSonoAtfZ74hh5ljxwCA-rrKP_r2WAgsD1-xaUHegP42inRNs; pt_pin=jd_FTDNfmNglHlT; s_key=AAJhHFZkADCA5XCZB8H2gQykFj4TDS5dHVSOwZXH0envpbl3QwHNW5i1gt2ZML0RrdGHpq_wnLc; s_pin=jd_FTDNfmNglHlT';

        $rand_iP = mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
        $data['header_arr'] = array(
            'CLIENT-IP:'       . $rand_iP,
            'X-FORWARDED-FOR:' . $rand_iP
		);

        // 請求的資料
        // $data = array(
        //     // 'test' => '博晴支付',
        //     // 'test1' => '博晴支付'
        // );
        // $data['data'] = http_build_query($data);
        $data['data'] = '';

        $data['agent'] = random_user_agent();
        // $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36';

        // $verify_card = $this->curl($data);
        // $this->resp($verify_card);
        // echo $verify_card;

        $verify_port = $this->verifyPort($data['url']);
        $this->resp($verify_port);        
    }

    /**
     * 小號風控
     */
    public function limit() {
    }

    /**
     * 掃描端口
     */
    public function verifyPort($url) {
        $port_arr = array(
            'Ftp'               => 21,
            'Telnet'            => 23,
            'Smtp'              => 25,
            'Finger'            => 79,
            'Http'              => 80,
            'Pop3'              => 110,
            'Location Service'  => 135,
            'Netbios-NS'        => 137,
            'Netbios-DGM'       => 138,
            'Netbios-SSN'       => 139,
            'IMAP'              => 143,
            'Https'             => 443,
            'Microsoft-DS'      => 445,
            'MSSQL'             => 1433,
            'MYSQL'             => 3306,
            'Terminal Services' => 3389,
            'Web_8080'          => 8080,
            'Web_8081'          => 8081,
            'test'              => 3020,
        );

        // $data['url'] = $url;
        // 訂閱事件
        $route = '';
        foreach($port_arr as $msg => $port) {
            // $data['msg']  = $msg;
            // $data['port'] = $port;

            // $route .= http_build_query($data);
            $route = 'Penetration:fsockPort:' . $port;
            redis_event_add($route, self::TRIGGER_TIME);
        }

        return $port_arr;
    }

    /**
     * curl
     */
    private function curl($data) {
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $data['url']);
        curl_setopt($ch, CURLOPT_REFERER, $data['referer']);
        curl_setopt($ch, CURLOPT_COOKIE, $data['cookie']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $data['header_arr']);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        // http
		// curl_setopt( $ch, CURLOPT_PROXYTYPE, 0 );
		// curl_setopt( $ch, CURLOPT_PROXY, "http://" . $proxyip );
		// curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC );
		// 设置请求方式
		// curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		// curl_setopt($ch, CURLOPT_POST, false);
		// curl_setopt( $ch, CURLOPT_POSTFIELDS, urldecode( http_build_query( $data ) ) );
        // 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $data['agent']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close( $ch );

        if ($err) {
            LogModel::log($err, 'curl_err');

            return $err;
        }

        LogModel::log($res, 'curl_res');
		return $res;
	}

    private function __verifyFormValidator() {
        $validator = new Validator();
        $validator->setRule('id', array('required'));
        return $validator->validate();
    }
}
