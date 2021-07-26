<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class BankModel {

    const BACK_LIST_CACHE = 'bank_list';

    public static function opts() {
        $bank_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $opts = [];
        foreach($bank_list as $bank) {
            $opts[] = [
                'k' => $bank['id'],
                'v' => $bank[$field]
            ];
        }
        return $opts;
    }

    public static function getNameById($bank_id) {
        $bank_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];
        return isset($bank_list[$bank_id][$field]) ? $bank_list[$bank_id][$field] : '';
    }

    public static function listBanks() {
        $bank_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $ret = [];
        foreach($bank_list as $bank) {
            $ret[] = [
                'id'    => $bank['id'],
                'name'  => $bank[$field],
                'ident' => $bank['ident'],
                'icon'  => $bank['icon'],
            ];
        }

        return $ret;
    }

    private static function __list() {
        if(!$bank_list = RedisCache::get(self::BACK_LIST_CACHE)) {
            $sql = "select id, name_zh, name_en, name_local, ident, icon 
                    from `bank` 
                    where is_stop='N' order by seq asc";
            $bank_list = DB::getData($sql);

            $bank_list = arr_to_map($bank_list);

            RedisCache::set(self::BACK_LIST_CACHE, $bank_list);
        }
        return $bank_list;
    }

}
