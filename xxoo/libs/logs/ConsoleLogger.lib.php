<?php if( !defined('XXOO') ) exit('No direct script access allowed');

require_once XXOO . 'libs/logs/ChromePhp.lib.php';

class ConsoleLogger {

    public function __construct() {

    }

    public function log($level, $info) {
        if($GLOBALS['app']['boot'] == 'http') {
            if( $level == 'error' ) {
                ChromePhp::error($info);
            }
            else if($level == 'warn') {
                ChromePhp::warn($info);
            }
            else if($level == 'info'){
                ChromePhp::info($info);
            }
            else {
                ChromePhp::log($info);
            }
        }
        else {
            echo $info;
        }
    }

}