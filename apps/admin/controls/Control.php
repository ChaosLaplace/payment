<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/DB.lib.php';
require_once XXOO . 'libs/Validator.lib.php';
require_once XXOO . 'funcs/str_help.fn.php';
require_once XXOO . 'funcs/sql_help.fn.php';
require_once XXOO . 'funcs/dict_help.fn.php';
require_once XXOO . 'funcs/form_help.fn.php';
require_once XXOO . 'funcs/paging_help.fn.php';
require_once APP  . 'controls/ErrorCode.php';

class Control {

    const PAGE_SIZE = 10;

    public $uid;

    public function __construct() {
        $this->uid = 1;

        $locale = explode('/', $_SERVER['REQUEST_URI']);
        if($locale['1'] == $GLOBALS['request']['locale']) {
            $GLOBALS['request']['locale'] = 'local';
        }
        else {
            $GLOBALS['request']['locale'] = $locale['1'];
        }
    }

    public function resp($data) {
        self::__addHeader();

        $data = self::__convertHump($data, false);

        echo json_encode([
            'code' => '200',
            'msg' => 'SUCCESS',
            'data' => $data
        ]);
    }

    public function respError($code, $message) {
        self::__addHeader();
        echo json_encode(array(
            'code'    => $code,
            'msg' => $message,
            'data' => []
        ));
    }

    /**
     * 将数组key从下划线命名转换为驼峰命名
     * @param $arr
     * @param false $ucfirst
     * @return array
     */
    private static function __convertHump($arr, $ucfirst = false) {
        if ( !is_array($arr) ) {
            return $arr;
        }

        $temp = [];
        foreach($arr as $key => $value) {
            $key1 = str_underline_to_hump($key, $ucfirst);
            $value1 = self::__convertHump($value, $ucfirst);
            $temp[$key1] = $value1;
        }

        return $temp;
    }

    /**
     * 跨域使用
     */
    private static function __addHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS,PATCH');
        header('Access-Control-Allow-Headers: DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization,token,access_token,version,device_type,Origin,lang,at');
        if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
            // 浏览器页面ajax跨域请求会请求2次，第一次会发送OPTIONS预请求,不进行处理，直接exit返回，但因为下次发送真正的请求头部有带token，所以这里设置允许下次请求头带token否者下次请求无法成功
            header('Access-Control-Allow-Headers:x-requested-with,content-type,token,access_token,version,device_type,Origin,lang,at');
            exit('ok');
        }
        header('Content-Type:application/json');
    }
}
