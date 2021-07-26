<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class AdSpaceModel {

    public static function add($space_data) {
        $ad_space_id = DB::insert('ad_space', $space_data);

        self::__initAdList($ad_space_id, $space_data['type'], $space_data['materiel_num']);
    }

    public static function update($ad_space_id, $space_data) {
        DB::update('ad_space', $space_data, "id={$ad_space_id}");

        $sql = "delete from ad where ad_space_id=?i";
        DB::runSql($sql, [$ad_space_id]);

        self::__initAdList($ad_space_id, $space_data['type'], $space_data['materiel_num']);
    }

    public static function delete($ad_space_id) {
        $sql = "delete from ad where ad_space_id=?i";
        DB::runSql($sql, [$ad_space_id]);

        $sql = "delete from ad_space where id=?i";
        DB::runSql($sql, [$ad_space_id]);
    }

    private static function __initAdList($ad_space_id, $type, $num) {
        $ad_list = [];
        for($i=0; $i<$num; $i++) {
            $ad_list[] = [
                'ad_space_id' => $ad_space_id,
                'type'        => $type
            ];
        }

        DB::inserts('ad', $ad_list);
    }

}


