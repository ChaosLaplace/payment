<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class AuctionRoomModel {

    const CACHE_LIST = 'auction:room:list';

    public static function add($data) {
        DB::insert('auction_room', $data);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function update($data, $where) {
        DB::update('auction_room', $data, $where);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function delete($id) {
        $sql = "delete from auction_room where id=?i";
        DB::runSql($sql, [$id]);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function getNameById($id) {
        $room_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];
        return isset($room_list[$id][$field]) ? $room_list[$id][$field] : '';
    }

    public static function getGradeLimitById($id) {
        $room_list = self::__list();
        return isset($room_list[$id]['grade_limit']) ? $room_list[$id]['grade_limit'] : false;
    }

    public static function listRooms() {
        $room_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['locale'];

        $ret = [];
        foreach($room_list as $room) {
            $ret[] = [
                'id'        => $room['id'],
                'name'      => $room[$field],
                'icon'      => $room['icon'],
                'subscript' => $room['subscript'],
            ];
        }
        return $ret;
    }
    
    public static function opts() {
        $room_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $opts = [];
        foreach($room_list as $room) {
            $opts[] = [
                'k' => $room['id'],
                'v' => $room[$field]
            ];
        }
        return $opts;
    }

    private static function __list() {
        if(!$room_list = RedisCache::get(self::CACHE_LIST)) {
            $sql = "select * from `auction_room` order by seq asc";
            $room_list = DB::getData($sql);

            $room_list = arr_to_map($room_list);

            RedisCache::set(self::CACHE_LIST, $room_list);
        }
        return $room_list;
    }

}
