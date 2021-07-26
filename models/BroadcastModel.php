<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class BroadcastModel {

    const CACHE_LIST = 'broadcast:list';

    public static function listLastBroadcasts($num=10) {
        $now = time();
        $field = 'message_' . $GLOBALS['request']['locale'];
        $cache_name = self::CACHE_LIST . $GLOBALS['request']['locale'];

        if( $broadcast_list = RedisCache::get($cache_name) ) {
            $sql = "select {$field} as message from boadcast 
                    where start_time>=?i and end_time<=?i order by id desc limit ?i";
            $broadcast_list = DB::getData($sql, [$now, $num]);

            RedisCache::set($cache_name, $broadcast_list, 5 * 60);
        }
        return $broadcast_list;
    }

}
