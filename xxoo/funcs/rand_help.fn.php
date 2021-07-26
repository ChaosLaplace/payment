<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	随机辅助函数集
	@author marvin
  --------------------------------------------------------------
*/

/**
 * 产生指定范围的随机浮点数，两位小数点
 * @param int $min 最小值
 * @param int $max 最大值
 * @param int $round 保留小数位
 * @return int
 */
function rand_float($min=0, $max=1, $round=2) {
    $rand = $min + mt_rand() / mt_getrandmax() * ( $max - $min );
    return round( $rand, $round );
}

/**
 * 生成带前缀的随机整数
 * @param int $len
 * @param string $prefix
 * @return string
 */
function rand_num($len, $prefix = null) {
    $min = 1;
    $max = 1;
    for($i=1; $i<$len; $i++) {
        $min .= $i-1;
        $max .= $i;
    }
    $num = mt_rand($min, $max);
    if($prefix != null){
        $num = $prefix.$num;
    }
    return $num;
}

/**
 * 生成指定长度的由数字、字母组成的随机字符串
 * @param int $len
 * @return string
 */
function rand_string($len) {
    $str_pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($str_pool) - 1;

    $str = '';
    for( $i=0; $i<$len; $i++ ) {
        $pos = rand(0, $max);
        $str .= $str_pool[ $pos ];
    }
    return $str;
}