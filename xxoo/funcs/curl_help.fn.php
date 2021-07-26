<?php if ( !defined('XXOO') ) exit('No direct script access allowed');


/*
  --------------------------------------------------------------
	curl辅助函数集
	@author marvin
  --------------------------------------------------------------
*/

function curl_get() {

}

function curl_post($url, $data = null, $header = null) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    // 设置请求头
    if(!empty($header)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, 0);
    }

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
