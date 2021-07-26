<?php if ( !defined('XXOO') ) exit('No direct script access allowed');


/*
  --------------------------------------------------------------
	array辅助函数集
	@author marvin
  --------------------------------------------------------------
*/

function arr_get($in, $k, $fill='') {
    return isset($in[$k]) ? $in[$k] : $fill;
}

/**
 * 通过标记符号，将二维数组中某一列的值，拼接成字符串
 * @param array $in 二维数组
 * @param string $k 键名，默认为id列
 * @param string $glue 连接符号，默认逗号
 * @return string
 */
function arr_col_implodes($in, $k='id', $glue=',') {
    if( empty($in) ) {
        return '';
    }

    $cols = [];
    foreach($in as $item) {
        $cols[$item[$k]] = $item[$k]; // 去重
    }

    return !empty($cols) ? implode($glue, $cols) : '';
}

/**
 * 将数组转换为k=>v数组
 * @param array $in 数组
 * @param string $k 键名列，默认为id列
 * @param string $v 值列，默认为空
 * @return array
 */
function arr_to_map($in, $k='id', $v='') {
    $map = array();

    foreach($in as $item) {
        $map[ $item[$k] ] = !empty($v) ? $item[$v] : $item;
    }
    return $map;
}

/**
 * 从数组随机取出一个或多个单元，
 * 与php原来的array_rand()不同的返回的不是key，而是实际的值
 * @param array $in
 * @param int $num
 * @return mixed
 */
function arr_rand_val($in, $num=1) {
    if( count($in) < $num ) {
        $num = count($in);
    }

    $rand_keys = array_rand($in, $num);

    if( is_array($rand_keys) ) {
        if (count($rand_keys) == 1) {
            $rand_keys   = array($rand_keys);
        }

        $rand_arr = array();
        foreach( $rand_keys as $k ) {
            $rand_arr[] = $in[$k];
        }
        return $rand_arr;
    } else {
        return $in[$rand_keys];
    }
}

