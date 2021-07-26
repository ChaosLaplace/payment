<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class RoleControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select * from sys_role order by id desc";
        $role_list = DB::getData($sql);

        foreach($role_list as &$role) {
            $role['created'] = date('Y-m-d', strtotime($role['created']));
        }

        $this->resp([
            'roleList' => $role_list
        ]);
    }

    public function add() {
        if( $this->__formValidation() ) {

        }

        $data = $this->__formData();
        $role_id = DB::insert('sys_role', $data);

        $func_id_arr = input('funcIds');
        $this->__saveRoleFuncList($role_id, $func_id_arr);

        $this->resp(true);
    }

    public function update() {
        if( $this->__formValidation() ) {

        }

        $id = input('id');
        $data = $this->__formData();
        DB::update('sys_role', $data, "id={$id}");

        $sql = "delete from sys_role_func where role_id=?i";
        DB::runSql($sql, [$id]);

        $func_id_arr = input('funcIds');
        $this->__saveRoleFuncList($id, $func_id_arr);

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from sys_role_func where role_id=?i";
        DB::runSql($sql, [$id]);

        $sql = "delete from sys_role where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function funcList() {
        $role_id = input('roleId');
        $func_ids = [];

        $sql = "select func_id from sys_role_func where role_id=?i";
        $func_list = DB::getData($sql, [$role_id]);

        if( !empty($func_list) ) {
            foreach ($func_list as $func) {
                $func_ids[] = $func['func_id'];
            }
        }

        $this->resp([
            'funcIds' => $func_ids
        ]);
    }

    public function opts() {
        $sql = "select id, role_name from sys_role order by id desc";
        $role_list = DB::getData($sql);

        $this->resp([
            'opts' => $role_list
        ]);
    }

    public function isExistName() {
        $this->isExist('sys_role', 'role_name');
    }

    public function __formData() {
        return [
            'role_name' => input('roleName')
        ];
    }

    private function __formValidation() {
        $validator = new Validator();
        $validator->setRule('roleName', array('required'));
        return $validator->validate();
    }

    private function __saveRoleFuncList($role_id, $func_id_arr) {
        $role_func_list = [];
        foreach($func_id_arr as $func_id) {
            $role_func_list[] = [
                'role_id' => $role_id,
                'func_id' => $func_id
            ];
        }

        if(!empty($role_func_list)) {
            DB::inserts('sys_role_func', $role_func_list);
        }
    }

}