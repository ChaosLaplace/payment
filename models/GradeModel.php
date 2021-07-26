<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';
require_once XXOO . 'funcs/arr_help.fn.php';

class GradeModel {

    const CACHE_NAME = 'grade_list';

    /** 用户初始等级 */
    const USER_INIT_GRADE = 0;

    public static function add($data) {
        DB::insert('grade', $data);
        RedisCache::del(self::CACHE_NAME);
    }

    public static function update($data, $where) {
        DB::update('grade', $data, $where);
        RedisCache::del(self::CACHE_NAME);
    }

    public static function delete() {

    }

    public static function opts() {
        $grade_list = self::__list();
        $field = 'name_' . $GLOBALS['request']['lang'];

        $opts = [];
        foreach($grade_list as $grade) {
            $opts[] = [
                'k' => $grade['id'],
                'v' => $grade[$field]
            ];
        }
        return $opts;
    }

    public static function getById($id) {
        $grade_list = self::__list();
        $grade = isset($grade_list[$id]) ? $grade_list[$id] : false;

        return $grade ? self::__local($grade) : false;
    }

    /**
     * 根据等级值获取等级
     * @param int $grade_num
     * @return false|mixed
     */
    public static function getByGrade($grade_num) {
        $grade_list = self::__list();

        foreach($grade_list as $grade) {
            if($grade['grade'] == $grade_num) {
                return self::__local($grade);
            }
        }

        return false;
    }

    public static function listGrades() {
        $grade_list = self::__list();

        $local_grade_list = [];
        foreach($grade_list as $grade) {
            $local_grade_list[$grade['id']] = self::__local($grade);
        }
        return $local_grade_list;
    }

    private static function __local($grade) {
        $field = 'name_' . $GLOBALS['request']['lang'];

        return [
            'id'            => $grade['id'],
            'name'          => $grade[$field],
            'icon'          => $grade['icon'],
            'grade'         => $grade['grade'],
            'price'         => $grade['price'],
            'bid_num'       => $grade['bid_num'],
            'bid_share'     => $grade['bid_share'],
            'transfer_share'=> $grade['transfer_share'],
            'buy_vip_share' => $grade['buy_vip_share'],
        ];
    }

    private static function __list() {
        if(!$grade_list = RedisCache::get(self::CACHE_NAME)) {
            $sql = "select id, name_zh, name_en, name_local, icon, grade, price, bid_num, 
                        bid_share, transfer_share, buy_vip_share 
                    from grade order by seq asc";
            $grade_list = DB::getData($sql);

            $grade_list = arr_to_map($grade_list);

            RedisCache::set(self::CACHE_NAME, $grade_list);
        }
        return $grade_list;
    }

}
