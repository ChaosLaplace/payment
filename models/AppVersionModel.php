<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

class AppVersionModel {

    /**
     * 根据终端类型，获取最大版本号
     * @param $terminal
     * @return bool|int|mixed
     */
    public static function maxNum($terminal) {
        $sql = "select max(num) from app_version where terminal=?s";
        $num = DB::getVar($sql, [$terminal]);

        return $num ? $num : 0;
    }

    /**
     * 根据终端类型，获取下一版本号
     * @param $terminal
     * @return bool|int|mixed
     */
    public static function nextNum($terminal) {
        // 加锁
        $num = self::maxNum($terminal);
        return $num + 1;
    }

}
