<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class AuctionSupplierModel {

    const CACHE_LIST = 'auction:supplier:list';

    public static function add($data) {

    }

    public static function update() {

    }

    public static function delete() {

    }

    public static function getNameById($id) {
        $supplier_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];
        return isset($supplier_list[$id][$field]) ? $supplier_list[$id][$field] : '';
    }

    public static function opts() {
        $supplier_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $opts = [];
        foreach($supplier_list as $supplier) {
            $opts[] = [
                'k' => $supplier['id'],
                'v' => $supplier[$field],
            ];
        }
        return $opts;
    }

    private static function __list() {
        if(!$supplier_list = RedisCache::get(self::CACHE_LIST)) {
            $sql = "select * from auction_supplier order by id asc";
            $supplier_list = DB::getData($sql);

            $supplier_list = arr_to_map($supplier_list);

            RedisCache::set(self::CACHE_LIST, $supplier_list);
        }
        return $supplier_list;
    }

}

