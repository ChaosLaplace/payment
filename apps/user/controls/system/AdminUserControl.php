<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'funcs/arr_help.fn.php';

class AdminUserControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $factor_list = [
            ['s'=>"and username like '$1%'", 'f'=>input('username')],
            ['s'=>"and tel='$1%'", 'f'=>input('tel')],
            ['s'=>"and mail='$1%'", 'f'=>input('mail')],
            ['s'=>"and is_delete='$1", 'f'=>input('status')]
        ];
        $sql_where = sql_where($factor_list);

        $sql = "select count(id) from sys_admin where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'  => 0,
                'admin_list' => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select * from sys_admin where {$sql_where} order by id desc {$page_info['limit']}";
        $admin_list = DB::getData($sql);

        // 拼装角色列表
        $admin_ids = arr_col_implodes($admin_list);
        $sql = "select ar.admin_id, r.role_name 
                from sys_admin_role ar, sys_role r 
                where ar.role_id=r.id and ar.admin_id in ($admin_ids)";
        $admin_role_list = DB::getData($sql);

        foreach($admin_list as &$admin) {
            $role_arr = array();
            foreach($admin_role_list as $ar) {
                if( $admin['id'] == $ar['admin_id'] ) {
                    $role_arr[] = $ar['role_name'];
                }
            }
            $admin['roles'] = implode('，', $role_arr);
            $admin['created'] = date('Y-m-d', strtotime($admin['created']));
            $admin['status'] = $admin['is_delete'];
        }

        $this->resp([
            'total_row'  => $total_row,
            'admin_list' => $admin_list,
        ]);
    }

    public function add() {
        $admin = $this->__formData();
        $admin['password'] = password_encode(input('password'), $GLOBALS['app']['secret']);
        $admin_id = DB::insert('sys_admin', $admin);

        $role_id_arr = input('roles');
        $this->__addAdminRoleList($admin_id, $role_id_arr);

        $this->resp(true);
    }

    public function update() {
        $admin_id = input('id');

        $admin = $this->__formData();
        DB::update('sys_admin', $admin, "id={$admin_id}");

        $sql = "delete from sys_admin_rolle where admin_id=?i";
        DB::runSql($sql, [$admin_id]);

        $role_id_arr = input('roles');
        $this->__addAdminRoleList($admin_id, $role_id_arr);

        $this->resp(true);
    }

    public function resetPassword() {
        $admin_id = input('id');
        $password = password_encode(input('password'), $GLOBALS['app']['secret']);

        $sql = "update sys_admin set password=?s where id=?i";
        DB::runSql($sql, [$password, $admin_id]);

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "update sys_admin set is_delete='Y' where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function show() {
        $id = input('id');
        $sql = "select * from sys_admin where id=?i";
        $admin = DB::getLine($sql, [$id]);

        $data = [
            'admin' => $admin
        ];

        $this->resp($data);
    }

    public function isExistUsername() {
        $this->isExist('sys_admin', 'username');
    }

    public function isExistMail() {
        $this->isExist('sys_admin', 'mail');
    }

    public function isExistTel() {
        $this->isExist('sys_admin', 'tel');
    }

    private function __formData() {
        return [
            'username'  => input('username'),
            'mail'      => input('mail'),
            'tel'       => input('tel'),
        ];
    }

    private function __filter() {

    }

    private function __formValidation() {
        $validator = new Validator();
        $validator->setRule('username', array('required'));
        $validator->setRule('mail', array('required'));
        $validator->setRule('tel', array('required'));
        $validator->setRule('password', array('required'));
        return $validator->validate();
    }

    private function __addAdminRoleList($admin_id, $role_id_arr) {
        $admin_role_list = [];
        foreach($role_id_arr as $role_id) {
            $admin_role_list[] = [
                'admin_id'  => $admin_id,
                'role_id'   => $role_id
            ];
        }

        DB::inserts('sys_admin_role', $admin_role_list);
    }

}