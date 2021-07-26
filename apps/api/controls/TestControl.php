<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class TestControl extends Control {

    /** 商户号 */
    const number = '210739975';
    /** 通道编码 */
    const code = '1';
    /** 网关地址 */
    const url = 'http://login.boqingbot.online/Pay_Index.html';
    /** 商户md5key */
    const md5key = 'dUJQLqsLLBxjuiRmJLLVeu2lKTcHAzYI';

    public function __construct() {
        parent::__construct();
    }

    public function test() {
    }

    public function pay() {
        $data = array(
            'pay_memberid'    => self::number,
            'pay_orderid'     => 'a3565465112', // 订单号唯一, 字符长度20
            'pay_applydate'   => date('Y-m-d H:i:s'), // 时间格式：2016-12-26 18:18:18
            'pay_bankcode'    => self::code,
            'pay_notifyurl'   => 'http://api.natasa.com/zh/test', // 服务端返回地址.（POST返回数据）
            'pay_callbackurl' => 'http://api.natasa.com/zh/test', // 页面跳转返回地址
            'pay_amount'      => '215.00', // 商品金额
        );
        $sign = $this->getSign($data, self::md5key);
        $data['pay_md5sign'] = $this->rsa($sign);
        $data['type'] = 'json';
        $curl = $this->curlUrlencoded(self::url, $data);
        if ($curl['code'] != '200') {
            return $this->resp('curl code != 200');
        }

        $ret = json_decode($curl['data'], true);
        return $this->resp($ret);

        if ($ret['status'] == 'error') {
            return $this->resp('res status error');
        }

        $this->resp('下单成功');
    }

    public function notify() {
        $data = json_decode( file_get_contents('php://input'), true );
        $key  = $data['sign'];

        $sign = $this->getSign($data, self::md5key);
        if ($sign !== $key || $data['status'] !== '2') {
            if ($sign === $key) {
                echo 'fail 支付失败' . json_encode($data);
            }
            else {
                echo 'fail 签名错误' . $sign;
            }
            exit();
        }

        echo 'OK';
    }

    /**
     * 签名
     */
    public function getSign($data, $key) {
        $sign = '';
        ksort($data);
        foreach ($data as $k => $v) {
            if ($k === 'sign' || $v === '') {
                continue;
            }
            $sign .= $k . '=' . $v . '&';
        }
        $sign .= 'key=' . $key;
        $sign = strtoupper( md5($sign) );

        $sign;

        return $sign;
    }

    /**
     * RSA
     */
    public function rsa($signStr) {
        $privateKey = 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC1KP8wwrSp2dTF8FmTWZwxOw3sGBBH0pDDfZBlMQsjNtEg97cfAIuesCRD+Tq5mUc4a5h+O0r1mIAW1XGfeS6kfCFvPQQJ/gD2C20gTgIIAUIR0ltSAgWOkLYHnY8V4llIkaDEWNwGZj09la2CVhE6FCVkpoWQq93iDXk0RVR43rqWXq7Jm/8qmxGqTz/0fF2n5zmcuEL/iJuFii2bHTwQBYTLnJWFssaByWetpneHXdj0q/aTXctxUkYKFnE46A8DyzufPG6/ax8DN/u7/zWXzQMHdvjOzqrwjjuK+FhBRSgP3BBY5SWZti8ExCcfzCkD6yTfM9IxvKHp6jVRxwBXAgMBAAECggEBALJYnPpdBM0eRUlkuFrG4mzZDXi7q/N5SckbKXdlk+hkA/gnxuC/gbSFBf7hMr4bNzFFQ8gqCT7FlnxkP0rXW2LVTXkcoYhQxpNzZkhiY7+LtYakCAExOlVDA+F1bLMHtgwqShJixKGML4gcfceNgcIiqTlwcpedi4AK8aLTCjk+qdOYpojJffD0tNd8BE1Y+NF+DZv8YONU1LQoE0jbCoiSoFbHKvSf7bWHMw5D4dkXbLdXgKbrCReri5cmyaGv1SA2buwT3WxyszZZsMpEeCfbXr6fRjw9VUTMSrzaykTJi8E639S6hB5Y9mEfuea9e9GewWJP6LIa1i11fihb9mkCgYEA8OfgXBBLyr4W4Glb739V0yxcuW3AZ2CU2vXk4l5kWY/dtFOyIGgOZXdrAJiKVWkiifQ0gjR0JxWpz2YC1pKyfUCHLI0fp1ttaFFkoO/KYijZQZAJxrpLFy/9grU2gNNyCqPAihJqh7X9qysZOj83jfgHWEO6qsCvKLxSHSkw/HMCgYEAwILNRj1HbtkrLto4V+ZZ+aWyGGCKz4o/yZaE0clPD7YmJvQWPVAqNFleVTiwipQ+3VIVeSFmlrfDu7bIiqtAIenh6QZ1syQ8T7aqrxYZHOqMO5qlFkJ6nKCxi7JKat//4LUh//AzhJdbD93RTlM9ulonIx0Qr2o2xfdZ75uj940CgYEAupz0eHyLKadsszQKapD6G9ZekamKBMqVJLScNBqoo5RFSp4W+vGATWtfMRv49Ma3YaQRVNdrLqeiXi2If00uBMaKr6E2Zv701n1OdTirTrST7yyz1gSTjIe5WpojeVHSIpnM2WgAq9X3hbOiHDPCRjBfCCmXHRSb2vNerIYvEy8CgYEAjG1dzATbXLKx22V3gDcaHw+NUFbsKuRQD37quBU+xNk8D2Ix7tvRTYp7U3Mc5JmrGcrMuyVwLjUyZHW659xv1c1D67W4mcqu6/71lu5ptzwouzndftVTl3loydxuiHOJtaKrgIT5L5kw5ewKyDXUa2Fj9ys2hp7WAaajRjOkcekCgYB4JCVLypswVUpT+N/O2i8avmdzCPrdRlnEbNafUy4qL3SaHYjLM5VnmjY5dLNzLKSd7KOtHBxekB364YQcFNeVl73ZE/wfbpisG4ZaMUInj7a+YVQ22c9RmHN6KoyeDZDOPawjxwc9kPcCgF5wrXNCzH/hZ9g/6/3/cgrhNq083Q==';
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        
        $key = openssl_pkey_get_private($privateKey);
        openssl_sign($signStr, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);

        if (empty($sign)) {
            return false;
        }

        return base64_encode($sign);
    }

    /**
     * 模拟post进行url请求
     */
    function curlUrlencoded($url, $param, $timeout = 60) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 502) {
            return self::message('400', '系统繁忙, 请稍后再试...');
        }

        if ( curl_errno($ch) ) {
            return self::message('408', "[三方网关 : {$url}]" . curl_error($ch));
        }
        curl_close($ch);

        return self::message('200', $data);
    }

    private function message($code, $data) {
        return array(
            'code'=> $code,
            'data'=> $data
        );
    }
}