<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'funcs/ip_help.fn.php';
require_once XXOO . 'libs/Jwt.lib.php';
require_once APP  . 'models/RBACModel.php';
require_once XXOO . 'funcs/datetime_help.fn.php';
require_once ROOT . 'funcs/redis_event.fn.php';

class AppControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        //$key = 'S' . rand(10000, 99999);
        $id = rand(100, 999);
        $route = 'auction:startPreview:' . $id;
        // 类:方法:参数1
        redis_event_add($route, 3);
        echo 'set 11111111111';
    }

    public function login() {
        if( !$this->__validateLoginForm() ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '用户名或密码错误');
            return false;
        }

        $account = input('account');
        $password = input('password');

        $col = 'mail';
        if(preg_match('/^[0-9]+$/', $account)) {
            $col = 'tel';
        }

        $sql = "select * from `sys_admin` where {$col}=?s and is_delete='N' limit 1";
        $admin = DB::getLine($sql, [$account]);

        if($admin && $admin['password'] == password_encode($password, $GLOBALS['app']['secret'])) {
            $ip = ip_get();
            $time = time();

            $this->__saveLoginLog($admin['id'], $ip);

            // 将后端urls存入redis
            $func_list = RBACModel::listFuncByAdmin($admin['id']);
            $urls = $this->__getApiUrls($func_list);

            $token = Jwt::encode([
                'iat' => $time,
                'exp' => $time + $GLOBALS['token']['expire'],
                'uid' => $admin['id'],
                'ip'  => $ip,
            ], $GLOBALS['token']['expire']);

            $data = [
                'token' => $token,
            ];

            $this->resp($data);
        }
        else {
            $this->respError(ErrorCode::LOGIN_FAIL, '用户名或密码错误');
        }
    }

    private function __getApiUrls($func_list) {
        $urls = [];
        foreach($func_list as $func) {
            if($func['api_urls'] == '#') {
                continue;
            }
            else if(strpos($func['api_urls'], ',') !== false ) {
                $tmp_arr = explode(',', $func['api_urls']);
                $urls = array_merge($urls, $tmp_arr);
            }
            else {
                $urls[] = $func['api_urls'];
            }
        }
        return $urls;
    }

    private function __saveLoginLog($admin_id, $ip) {
        $log = [
            'admin_id'  => $admin_id,
            'ip'        => $ip,
            'ip_area'   => ip_area($ip)
        ];
        DB::insert('sys_admin_login_log', $log);
    }

    private function __validateLoginForm() {
        $validator = new Validator();
        $validator->setRule('account', array('required'));
        $validator->setRule('password', array('required'));
        return $validator->validate();
    }

}
