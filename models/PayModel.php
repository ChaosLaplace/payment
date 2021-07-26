<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

/**
 * 支付模块
 *
 * @author marvin
 */
class PayModel {

    const CHANNEL_LIST_CACHE = 'channel_list';

    public static function getChannelById($channel_id) {
        $channel_list = self::__channelList();
        $channel = isset($channel_list[$channel_id]) ? $channel_list[$channel_id] : false;
        return $channel ? self::__channelLocal($channel) : false;
    }

    public static function getChannelNameById($channel_id) {
        $channel = self::getChannelById($channel_id);
        return $channel ? $channel['name'] : '';
    }

    private static function __channelList() {
        if(!$channel_list = RedisCache::get(self::CHANNEL_LIST_CACHE)) {
            $sql = "select id, name_zh, name_en, name_local, ident, icon from pay_channel order by id asc";
            $channel_list = DB::getData($sql);

            $channel_list = arr_to_map($channel_list);

            RedisCache::set(self::CHANNEL_LIST_CACHE, $channel_list);
        }
        return $channel_list;
    }

    private static function __channelLocal($channel) {
        $field = 'name_' . $GLOBALS['request']['lang'];
        return [
            'id'    => $channel['id'],
            'name'  => $channel[$field],
            'ident' => $channel['ident'],
            'icon'  => $channel['icon'],
        ];
    }

}
