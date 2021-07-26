<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * 生成签名字符串
 * @param string $text 签名内容
 * @param string $secret 签名密钥
 * @return string
 */
function sign($text, $secret) {
    return md5( md5($text) . $secret );
}