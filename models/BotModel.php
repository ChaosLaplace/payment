<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 機器人功能
 * 
 * @author Chaos
 */
class BotModel {

    /** 驗證機器人 ID */
    const BOT_ID    = 1632116104;
    /** 驗證機器人接收訊息的群組 */
    const BOT_GROUP = -595842164;
    /** 分割字串 */
    const EXPLODE   = '~!@';

    /**
     * 判斷機器人 入群新增 || 退群刪除
     */
    public static function chatMember($data) {
        // 入群
        if ( isset($data['message']['new_chat_member']) && $data['message']['new_chat_member']['is_bot'] ) {
            // 驗證機器人 ID
            if ( self::BOT_ID !== $data['message']['new_chat_member']['id'] ) {
                return false;
            }

            // 判斷是管理員操作
            if ( self::getBotAdminById($data['message']['from']['id']) ) {

                $data = array(
                    'tg_group_id' => $data['message']['chat']['id']
                );
                DB::insert('bot_group', $data);
            }
        }

        // 退群
        if ( isset($data['message']['left_chat_member']) && $data['message']['left_chat_member']['is_bot'] ) {
            // 驗證機器人 ID
            if ( self::BOT_ID !== $data['message']['left_chat_member']['id'] ) {
                return false;
            }
            
            // 判斷是管理員操作
            if ( self::getBotAdminById($data['message']['from']['id']) ) {

                $sql = "delete from bot_group where tg_group_id=?s limit 1";
                DB::runSql($sql, [$data['message']['chat']['id']]);
            }
        }
    }
    

    /**
     * 判斷命令
     */
    public static function getCommand($data) {
        return explode(self::EXPLODE, $data);
    }

    /**
     * 添加管理員
     */
    public static function addAdmin($tg_id) {
        if ( !self::getBotAdminById($tg_id) ) {
            $data = array(
                'tg_id' => $tg_id
            );
            DB::insert('bot_admin', $data);
        }
    }
    
    /**
     * 查詢管理員是否存在
     */
    public static function getBotAdminById($tg_id) {
        $sql = "select id from bot_admin where tg_id=?s";
        return DB::getVar($sql, [$tg_id]);
    }

    /**
     * 傳送訊息
     */
    public static function sendMessage($msg) {
        $url_sendMessage   = $GLOBALS['bot']['url'] . 'sendMessage';

        $group_list = self::listGroupId();

        $send['text'] = $msg;
        foreach ($group_list as $group) {
            $send['chat_id'] = $group['tg_group_id'];
            self::__curlJson($url_sendMessage, $send);
        }
    }
    
    /**
     * 查詢發送訊息的群組
     */
    public static function listGroupId() {
        $sql = "select tg_group_id from bot_group";
        return DB::runSql($sql);
    }

    /**
     * 模拟post进行url请求
     */
    private static function __curlJson($url, $param = [], $timeout = 20) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json') );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param) );
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 502) {
            return self::__message('400', '系统繁忙, 请稍后再试...');
        }

        if ( curl_errno($ch) ) {
            return self::__message('408', "[请求网关 : {$url}]" . curl_error($ch));
        }
        curl_close($ch);

        return self::__message('200', $data);
    }

    private static function __message($code, $data) {
        return array(
            'code'=> $code,
            'data'=> $data
        );
    }
}
