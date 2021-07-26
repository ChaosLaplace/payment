<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'funcs/arr_help.fn.php';

/**
 * RBAC模块
 * @author marvin
 */
class RBACModel {

    const ROOT_ID   = 0;        // 根节点id
    const ROOT_NAME = 'root';   // 根节点名称

    /**
     * 获取全部功能的树结构
     * @return array
     */
    public static function funcTree() {
        $sql = "select id, pid, func_name as `name`, grade from sys_func order by seq asc";
        $func_list = DB::getData($sql);

        return self::__createNode(self::ROOT_ID, $func_list);
    }

    /**
     * 根据功能id，获取该功能的所有子功能
     * @param $func_id
     * @return array
     */
    public static function listChildFunc($func_id) {
        $sql = "select id, pid from sys_func order by id asc";
        $func_list = DB::getData($sql);
        return self::__findChild($func_id, $func_list);
    }

    public static function listFuncByAdmin($admin_id) {
        $sql = "select role_id from sys_admin_role where admin_id=?i";
        $role_list = DB::getData($sql, [$admin_id]);
        $role_ids = arr_col_implodes($role_list, 'role_id');

        $sql = "select f.* from sys_func f, sys_role_func rf where f.id=rf.func_id and rf.role_id in({$role_ids})";
        $func_list = DB::getData($sql);

        // 去重
        $func_map = [];
        foreach($func_list as $func) {
            $func_map[$func['id']] = $func;
        }

        return $func_map;
    }

    public static function getGrade($pid) {
        $sql = "select grade from sys_func where id=?i";
        $parent_grade = DB::getVar($sql, [$pid]);

        return $parent_grade + 1;
    }

    private static function __findChild($pid, $func_list) {
        static $child_list = [];
        foreach($func_list as $func) {
            if($func['pid'] == $pid) {
                $child_list[] = $func;
                self::__findChild($func['id'], $func_list);
            }
        }
        return $child_list;
    }

    private static function __createNode($pid, $func_list) {
        $ret = [];
        foreach($func_list as $func) {
            if($pid == $func['pid']) {
                $node = [
                    'title'       => $func['name'],
                    'key'         => $func['id'],
                    'grade'       => $func['grade'],
                    'scopedSlots' => ['title' => 'custom'],
                ];

                $children = self::__createNode($func['id'], $func_list);
                if( !empty($children) ) {
                    $node['children'] = $children;
                }

                $ret[] = $node;
            }
        }
        return $ret;
    }

}