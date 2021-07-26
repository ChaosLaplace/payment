<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	扩展函数集
	系统在载入Controller文件前，自动加载入本文件
  --------------------------------------------------------------
*/

/**
 * 资源url构造函数
 * @param $uri
 * @return string
 */
function rs_url($uri) {
    if($GLOBALS['app']['env'] === 'development') {
        return $GLOBALS['app']['rs_domain'] . $uri . '?v=' . rand(1, 1000);
    } else {
        return $GLOBALS['app']['rs_domain'] . $uri . '?v=' . $GLOBALS['app']['version'];
    }
}

/**
 * 生成图片缩略图url地址
 * 如果传入的是绝对地址，则不作处理直接返回
 * @param string $uri
 * @return string
 */
function img_url($uri) {
    if( empty($uri) ) {
        return '';
    }

    if( preg_match('/^http/i', $uri) ) {
        return $uri;
    } else {
        return $GLOBALS['app']['img_domain'] . $uri;
    }
}

/**
 * 从k=>v数组中，返回指定key对应的值
 * 如不不存在，则返回空字符串
 * @param $in
 * @param $k
 * @return mixed|string
 */
function pick($in, $k) {
    return isset($in[$k]) ? $in[$k] : '';
}

/**
 * 元转分
 * @param $amount
 * @return mixed
 */
function yuan2fen($amount) {
    if($amount == 0) {
        return $amount;
    }
    return $amount * 100;
}

/**
 * 分转元
 * @param $amount
 * @return float
 */
function fen2yuan($amount) {
    if($amount == 0) {
        return $amount;
    }
    return number_format($amount / 100, 2);
}

/**
 * 秒转分钟
 * @param $second
 * @return float
 */
function second2minute($second) {
    return $second / 60;
}

