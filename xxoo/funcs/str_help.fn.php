<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	string辅助函数集
	@author marvin
  --------------------------------------------------------------
*/

/**
 * 下划线转骆峰
 * @param $str
 * @param bool $ucfirst 是否转换首字母
 * @return string
 */
function str_underline_to_hump($str, $ucfirst = true) {
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ', '', lcfirst($str));
    return $ucfirst ? ucfirst($str) : $str;
}

/**
 * 截取指定长度的字符串，并在结尾添加指定符号
 * @param String $str
 * @param int $len
 * @param string $append
 * @return string
 */
function str_cut($str, $len, $append='...') {
    if( mb_strlen($str, 'utf-8') <= $len ) {
        return $str;
    }
    else {
        $str = mb_substr($str, 0, $len, 'utf-8');
        return $str . $append;
    }
}

/**
 * 搜索并替换字符串，可指定替换次数
 * @param $search 要查找的字符串
 * @param $replace 要替换的字符串
 * @param $subject 被搜索的字符串
 * @param $limit 替换次数
 * @return mixed
 */
function str_replace_limit($search, $replace, $subject, $limit=-1) {
    if( $limit == -1 ) {        // 全部替换
        return str_replace($search, $replace, $subject);
    }
    else if( $limit == 1 ) {    // 仅替换一次
        $pos = strpos($subject, $search);
        if ($pos === false) {
            return $subject;
        }
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    else {
        if (is_array($search)) {
            foreach ($search as $k=>$v) {
                $search[$k] = '`' . preg_quote($search[$k], '`') . '`';
            }
        }
        else {
            $search = '`' . preg_quote($search, '`') . '`';
        }
        return preg_replace($search, $replace, $subject, $limit);
    }
}

/**
 * 字符串清洗，清除字符串中的html标签和空白字符
 * @param $text
 * @return mixed
 */
function str_clean($text) {
    $text = strip_tags($text);
    return string_strip_blank($text);
}

