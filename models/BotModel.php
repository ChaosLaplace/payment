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
    const EXPLODE   = '||';

    const LOG_PATH_INFO  = ROOT . 'logs/bot/info_log.txt';
    const LOG_PATH_ERROR = ROOT . 'logs/bot/error_log.txt';

    /** 錯誤代碼 */
    const ERROR_DB_INSERT = '入群失敗';
    const ERROR_DB_DELETE = '退群失敗';

    /**
     * 驗證 token
     */
    public static function verifyToken() {
        return strtoupper( md5( strtoupper( md5( $GLOBALS['bot']['token'] ) ) ) );
    }

    /**
     * 查詢管理員是否存在
     */
    public static function getBotAdminById($tg_id) {
        $sql = "select id from bot_admin where tg_id=?s";
        return DB::getVar($sql, [$tg_id]);
    }

    /**
     * 查詢群組是否存在
     */
    public static function getBotGroupByGroupId($tg_group_id) {
        $sql = "select id from bot_group where tg_group_id=?s";
        return DB::getVar($sql, [$tg_group_id]);
    }
    
    /**
     * 判斷命令
     */
    public static function getCommand($data) {
        $command = explode(self::EXPLODE, $data);
        if ( !isset($command['1']) ) {
            $command['0'] = 1;
        }
        
        return $command;
    }

    /**
     * 添加管理員
     */
    public static function addAdmin($bot_admin, $tg_id) {
        if ( !$bot_admin ) {
            $data = array(
                'tg_id' => $tg_id
            );
            DB::insert('bot_admin', $data);
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
     * 判斷機器人 入群新增 || 退群刪除
     */
    public static function chatMember($data) {
        // 入群
        if ( isset($data['message']['new_chat_members']) && $data['message']['new_chat_member']['is_bot'] ) {
            // 驗證機器人 ID
            if ( self::BOT_ID !== $data['message']['new_chat_member']['id'] ) {
                return false;
            }
            
            // 查詢群組是否存在 不存在則新增
            if ( !self::getBotGroupByGroupId($data['message']['chat']['id']) ) {
                $ret = array(
                    'tg_group_id'   => $data['message']['chat']['id'],
                    'tg_group_name' => $data['message']['chat']['title']
                );
                
                // 操作失敗返回消息
                if ( !DB::insert('bot_group', $ret) ) {
                    self::__errorMessage(self::ERROR_DB_INSERT, $data['message']['from']['id']);
                }
            }
        }

        // 退群
        if ( isset($data['message']['left_chat_member']) && $data['message']['left_chat_member']['is_bot'] ) {
            // 驗證機器人 ID
            if ( self::BOT_ID !== $data['message']['left_chat_member']['id'] ) {
                return false;
            }
            
            // 查詢群組是否存在 存在則刪除
            if ( $tg_group_id = self::getBotGroupByGroupId($data['message']['chat']['id']) ) {
                $sql = "delete from bot_group where id=?i";

                // 操作失敗返回消息
                if ( !DB::runSql($sql, [$tg_group_id]) ) {
                    self::__errorMessage(self::ERROR_DB_DELETE, $data['message']['from']['id']);
                }
            }
        }
    }

    /**
     * 機器人群发圖文
     */
    public static function sendMessage($msg, $photo_id) {
        if ( $photo_id === false ) {
            $url          = $GLOBALS['bot']['url'] . 'sendMessage';
            $send['text'] = $msg;
        }
        else {
            $url             = $GLOBALS['bot']['url'] . 'sendPhoto';
            $send['caption'] = $msg;
            $send['photo']   = $photo_id;
        }

        $group_list  = self::listGroupId(); 

        $count       = 1;
        $return_data = '';
        $decode_data = '';
        $error_log   = '';
        foreach ($group_list as $group) {
            $send['chat_id'] = $group['tg_group_id'];
            
            $return_data     = self::__curlJson($url, $send);
            $decode_data     = json_decode($return_data['data'], true);
            if( !$decode_data['ok'] ) {
                self::__errorLog($decode_data);
            }

            if ( $count % 10 === 0 ) {
                usleep(20000);
            }
        }
    }
    
    /**
     * 機器人私聊
     */
    public static function sendNormalMessage($msg, $chat_id) {
        $url             = $GLOBALS['bot']['url'] . 'sendMessage';
        $send['text']    = $msg;
        $send['chat_id'] = $chat_id;

        $return_data     = self::__curlJson($url, $send);
        $decode_data     = json_decode($return_data['data'], true);
        if( !$decode_data['ok'] ) {
            self::__errorLog($decode_data);
        }
    }

    /**
     * 機器人回复消息
     */
    public static function replyMessage($msg, $chat_id, $message_id) {
        $url             = $GLOBALS['bot']['url'] . 'sendMessage';
        $send['text']    = $msg;
        $send['chat_id'] = $chat_id;
        $send['reply_to_message_id'] = $message_id;

        $return_data     = self::__curlJson($url, $send);
        $decode_data     = json_decode($return_data['data'], true);
        if( !$decode_data['ok'] ) {
            self::__errorLog($decode_data);
        }
    }

    /**
     * 操作失敗 機器人返回錯誤消息
     */
    private static function __errorMessage($error_code, $admin_id) {
        $url             = $GLOBALS['bot']['url'] . 'sendMessage';
        $send['text']    = $error_code;
        $send['chat_id'] = $admin_id;

        $return_data     = self::__curlJson($url, $send);
        $decode_data     = json_decode($return_data['data'], true);
        self::__errorLog($decode_data);
    }

    private static function __errorLog($data) {
        $data['date'] = date('Y-m-d H:i:s');
        $error_log    = json_encode($data, true) . PHP_EOL . PHP_EOL;
        file_put_contents(self::LOG_PATH_ERROR, $error_log, FILE_APPEND);
    }

    /**
     * 模拟post进行url请求
     */
    private static function __curlJson($url, $param = [], $timeout = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json') );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param) );
        // 设置允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // https请求 不验证 证书 & host
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
