<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once APP . 'models/RBACModel.php';

class MineControl extends Control {

    public function workplace() {
        $login_info = [
            'time'      => '2021-04-15 13:26',
            'location'  => '重庆涪陵'
        ];

        $stat = [
            'user_num'  => 12085,
            'actor_num' => 60,
            'art_num'   => 156
        ];

        $this->resp([
            'login_info' => $login_info,
            'stat' => $stat
        ]);
    }

    public function info() {
        $sql = "select username as `name`, avatar from sys_admin where id=?i";
        $admin = DB::getLine($sql, [$this->adminId]);

        $func_list = RBACModel::listFuncByAdmin($this->adminId);
        $permissions = [];
        foreach($func_list as $func) {
            $permissions[] = $func['path'];
        }

        $this->resp([
            'adminInfo'   => $admin,
            'permissions' => $permissions,
        ]);
    }

    public function basicInfo() {
        $sql = "select id, username, avatar, tel, mail from sys_admin where id=?i";
        $admin = DB::getLine($sql, [$this->adminId]);

        $this->resp($admin);
    }

    public function changePassword() {
        $sql = "select password from sys_admin where id=?i";
        $store_password = DB::getVar($sql, [$this->adminId]);
        if( $store_password != password_encode(input('oldPassword'), $GLOBALS['app']['secret']) ) {
            $this->respError(ErrorCode::OLD_PASSWORD_ERROR, '旧密码错误');
        }

        $password = password_encode(input('password'), $GLOBALS['app']['secret']);
        $sql = "update sys_admin set password=?s where id=?i";
        DB::runSql($sql, [$this->adminId]);

        $this->resp(true);
    }

    public function changeName() {
        $username = input('username');

        $sql = "update sys_admin set username=?s where id=?i";
        DB::runSql($sql, [$username, $this->adminId]);

        $this->resp(true);
    }

    public function changeTel() {
        $tel = input('tel');

        $sql = "update sys_admin set tel=?s where id=?i";
        DB::runSql($sql, [$tel, $this->adminId]);

        $this->resp(true);
    }

    public function changeMail() {
        $mail = input('mail');

        $sql = "update sys_admin set mail=?s where id=?i";
        DB::runSql($sql, [$mail, $this->adminId]);

        $this->resp(true);
    }

    private function __basicForm() {
        return [
            'username'  => input('username'),
            'tel'       => input('tel'),
            'mail'      => input('mail'),
        ];
    }

}
