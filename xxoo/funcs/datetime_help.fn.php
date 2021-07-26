<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/*
  --------------------------------------------------------------
	日期时间辅助函数
  --------------------------------------------------------------
*/

/**
 * 获取统计时间，如：5小时13分28秒
 * @param $time 秒数
 * @return string
 */
function dt_duration($time) {
    if( $time <= 0 ) {
        return '-';
    }

    $duration_str = '';
    if( $time >= 3600 ) {
        $hour = intval( $time / 3600 );
        $duration_str .= "{$hour}小时";
        $time -= $hour * 3600;
    }

    if( $time >= 60 ) {
        $minute = intval( $time / 60 );
        $duration_str .= "{$minute}分";
        $time -= $minute * 60;
    }

    if( $time < 60 ) {
        $second = $time;
        $duration_str .= "{$second}秒";
    }

    return $duration_str;
}

function dt_dhms($time) {
    $day = $hour = $minute = $second = 0;

    // 天
    if($time > 24 * 60 * 60) {
        $day = intval($time / (24 * 60 * 60));
        $time -= $day * 24 * 60 * 60;
    }

    // 小时
    if($time > 60 * 60) {
        $hour = intval($time / (60 * 60));
        $time -= $hour * 60 * 60;
    }

    // 分
    if($time > 60 ) {
        $minute = intval($time / 60);
        $time -= $minute * 60;
    }

    // 秒
    if( $time < 60 ) {
        $second = $time;
    }

    return [$day, $hour, $minute, $second];
}

/**
 * 获取友好时间
 * @param string|int $time
 * @return string
 */
function dt_friendly($time) {
    static $regions = array(
        31622400 => array('%年前', 31622400),
        2592000  => array('%月前', 2592000),
        1728000  => array('%天前', 86400),
        1296000  => array('半个月前', 1296000),
        86400    => array('%天前', 86400),
        61200    => array('%小时前', 3600),
        43200    => array('半天前', 43200),
        3600     => array('%小时前', 3600),
        2100     => array('%分钟前', 60),
        1800     => array('半小时前', 1800),
        60       => array('%分钟前', 60),
        35       => array('半分钟前', 1)
    );
    $now            = time();
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }

    $difference = $now - $time;
    if( $difference < 1 ) return '';
    foreach ($regions as $k => $v) {
        if ($difference >= $k) {
            // 具体
            if (strpos($v[0], '%') !== FALSE) {
                $ret = floor($difference / $v[1]);
                return str_replace('%', $ret, $v[0]);
            }
            // 大概
            else {
                return $v[0];
            }
        }
    }
    return "{$difference}秒前";
}
