<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 短信
 * 
 * @author Chaos
 */
class Sms {

    const SEND_SMS      = 'http://api.wftqm.com/api/sms/mtsend';
    const SEARCH_SMS    = 'http://api.wftqm.com/api/sms/getSmsCdr';
    // 应用名称 验证
    const APP_KEY       = '3VAUIDfP';
    const SECRET_KEY    = 'eivLwTFR';
    // 应用名称 营销
    // const APP_KEY       = 'Yem9rXsh';
    // const SECRET_KEY    = '7pH0zaPr';

    /**
     * 发送短信
     * @param $phone 手机号
     * @param $content 短信内容
     * @return array 返回讯息
     */
    public static function sendSms($phone, $content) {
        $data = array(
            'appkey'    => self::APP_KEY,
            'secretkey' => self::SECRET_KEY,
            'phone'     => $phone,
            'content'   => urlencode($content),
        );
        
        $ret = self::__curlForm(self::SEND_SMS, $data);
        return $ret;
    }

    /**
     * 查询短信
     * @param $date 查询该天（yyyyMMdd）
     * @param $page_size 数量（小于1000条）
     * @param $page 起始页
     * @return array 返回讯息
     */
    public static function searchSms($date, $page_size, $page) {
        $data = array(
            'appkey'    => self::APP_KEY,
            'secretkey' => self::SECRET_KEY,
            'date'      => $date,
            'page_size' => $page_size,
            'page'      => $page,
        );
        
        $ret = self::__curlForm(self::SEARCH_SMS, $data);
        return $ret;
    }

    /**
     * 发送表单请求
     * @param $url 请求地址
     * @param array $data 请求参数
     * @return array 返回讯息
     */
    private static function __curlForm($url, $data = null) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        // 在尝试连接时等待的秒数
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , 15);
        // 最大执行时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        // 忽略https证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        // 设置post请求
        curl_setopt($curl, CURLOPT_POST, 1);
        // 设置请求参数
        if(!empty($data)) {
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $output = curl_exec($curl);

        // 获取错误信息
        $error_message = '';
        if( $output === false ) {
            $error_message = curl_error($curl);
        }

        // 获取状态码
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return array(
            'status_code'   => $status_code,
            'output'        => $output,
            'error_message' => $error_message,
        );
    }
}
