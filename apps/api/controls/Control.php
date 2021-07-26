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
        header('Content-Type:application/json');

        $data = self::__convertHump($data, false);

        echo json_encode([
            'code' => '200',
            'message' => 'SUCCESS',
            'data' => $data
        ]);
    }

    public function respError($code, $message) {
        header('Content-Type:application/json');
        echo json_encode(array(
            'code'    => $code,
            'message' => $message,
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

}