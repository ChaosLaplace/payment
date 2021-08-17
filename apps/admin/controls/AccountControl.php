<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'funcs/random_user_agent.fn.php';

class AccountControl extends Control {

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

        $sql = "select status, webcookie from card where id=?s";
        $id  = input('id') ?: 22; // 操作者帳號
        // 驗證使用者權限
        if ( !$card = DB::getLine($sql, [$id]) ) {
            $this->respError(ErrorCode::PERMISSION_DENIED, '沒有權限');
            return false;
        }

        $verify_card = $this->__verifyCard($card['webcookie']);

        $where = array(
            'id'  => $id,
            'num' => '0'
        );

        $ret['status'] = 1;

        if ( strpos($verify_card, 'm no login') !== false ) {
            $data['status'] = '0';
            // DB::update('card', $data, $where);

            // $ret['msg'] = '小号已无效';
            $ret['msg'] = 'fail';
        }
        else {
            // 小號有效就不更新
            if ( $card['status'] !== 1 ) {
                $data['status'] = '1';
                // DB::update('card', $data, $where);
            }

            // $ret['msg'] = '小号CK有效';
            $ret['msg'] = 'ok';
        }

        // $verify_card = $this->__verifyCard(0);
        // var_dump( $verify_card['res'] );
        $this->resp($verify_card);

        // $test = '';
        // echo $test;
    }

    /**
     * 小號風控
     */
    public function limit() {
    }

    /**
     * curl
     */
    private function __verifyCard($cookie) {        
        $origin  = 'https://wqs.jd.com/';
        // 模擬的頁面
        $referer = $origin;
        // 請求的 url
        $url     = 'https://so.m.jd.com/webportal/channel/m_category?searchFrom=bysearchbox';
        // 請求的資料
        // $data = array(
        //     // 'test' => '博晴支付',
        //     // 'test1' => '博晴支付'
        // );
        // $data = http_build_query($data);
        $data = '';

        $rand_iP = mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
        $header_arr = array(
            'CLIENT-IP:'       . $rand_iP,
            'X-FORWARDED-FOR:' . $rand_iP
		);

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // 解析網頁
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        // 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, random_user_agent());
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close( $ch );

        if ($err) {
            return $err;
        }

		return $res;
	}

    private function __verifyFormValidator() {
        $validator = new Validator();
        $validator->setRule('id', array('required'));
        return $validator->validate();
    }
}
