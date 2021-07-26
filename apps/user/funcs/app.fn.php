<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

// require_once XXOO . 'libs/Session.lib.php';

/**
 * 访问拦截
 * 从请求头中获取token，检查token是否有效，
 * 如果有效则判定用户为已登录，再检查用户是否有访问权限
 */
function intercept() {

}

/**
 * 密码加密
 * @param $text
 * @param $secret
 * @return string
 */
function password_encode($text, $secret) {
    return md5(md5($text) . $secret);
}



