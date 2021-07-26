<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

require_once XXOO . 'libs/ip/IpLocation.lib.php';

/**
 * 获取客户端真实ip
 * @return string
 */
function ip_get() {
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } elseif (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else {
        $ip = "unknown";
    }

    $pos = stripos($ip, ',');

    if ($pos !== FALSE) {
        $ip = substr($ip, 0, $pos);
    }
    return $ip;
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

function ip_long_get() {
    $ip = ip_get();
    return ip2long($ip);
}
