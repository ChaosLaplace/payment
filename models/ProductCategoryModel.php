<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class ProductCategoryModel {

    const CACHE_LIST = 'mall:category:list';

    public static function add($data) {
        DB::insert('product_category', $data);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function update($data, $where) {
        DB::update('product_category', $data, $where);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function delete($id) {
        $sql = "delete from product_category where id=?i";
        DB::runSql($sql, [$id]);
        RedisCache::del(self::CACHE_LIST);
    }

    public static function getNameById($id) {
        $category_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];
        return isset($category_list[$id][$field]) ? $category_list[$id][$field] : '';
    }

    public static function listCategories() {
        $category_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $ret = [];
        foreach($category_list as $cat) {
            $ret[] = [
                'id'    => $cat['id'],
                'name'  => $cat[$field],
                'icon'  => $cat['icon'],
            ];
        }
        return $ret;
    }
    
    public static function opts() {
        $category_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $opts = [];
        foreach($category_list as $category) {
            $opts[] = [
                'k' => $category['id'],
                'v' => $category[$field]
            ];
        }
        return $opts;
    }

    private static function __list() {
        if(!$category_list = RedisCache::get(self::CACHE_LIST)) {
            $sql = "select * from product_category order by seq asc";
            $category_list = DB::getData($sql);

            $category_list = arr_to_map($category_list);

            RedisCache::set(self::CACHE_LIST, $category_list);
        }
        return $category_list;
    }

}
