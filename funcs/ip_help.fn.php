<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

require_once XXOO . 'libs/ip/IpLocation.lib.php';

/**
 * 获取客户端真实ip
 * @return string
 */
function ip_get() {
    $ip = '未知IP';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return is_ip($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $ip;
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return is_ip($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
    }
    else {
        return is_ip($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }
}

function is_ip($str) {
    $ip = explode('.',$str);
    $ip_count = count($ip);
    for($i = 0; $i < $ip_count; ++$i) { 
        if( $ip[$i] > 255 ) { 
            return false; 
        } 
    } 
    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str); 
}

/**
 * 根据ip，获取所对应的地区
 * 如果不能识别则返回空字符串
 * @param $ip
 * @return mixed
 */
function ip_area($ip) {
    $ip_location = new IpLocation();
    $ip_info = $ip_location->getlocation($ip);

    return $ip_info['country'];
}
