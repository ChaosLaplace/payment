<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

class AdminUserModel {

    public static function getIdByUsername($username) {
        $sql = "select id from sys_admin where username=?s limit 1";
        return DB::getLine($sql, [$username]);
    }

}
