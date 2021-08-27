<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/LogModel.php';

/**
 * 滲透
 *
 * @author Chaos
 */
class PenetrationModel {
    
    /**
     * 檢查開啟的 Port
     */
    public static function fsockPort($data) {
        // php phpstudy_pro/WWW/payment/shells/redis_subscribe.shell.php
        // parse_str($data, $parameter);
        LogModel::log($data, 'fsockPort');
        // $status = 'CLOSE';
        // if ( fsockopen('admin.payment.com', $data['port'], $errno, $errstr, 1) ) {
        //     $status = 'OPEN';
        // }
        // LogModel::log('[' . $status . '] 端口名稱 : ' . $data['msg'] . ', 端口 : ' . $data['port'], 'verifyPort');
    }
}
