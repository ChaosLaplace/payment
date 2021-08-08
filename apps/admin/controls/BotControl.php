<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/BotModel.php';

class BotControl {    
    
    public function index() {
        $data = json_decode( file_get_contents('php://input'), true);

        // 驗證 token
        if ( BotModel::verifyToken() !== input('token') ) {
            return false;
        }

        // 驗證發送訊息的管理員
        if ( isset($data['message']['from']['id'])
        && $bot_admin = BotModel::getBotAdminById($data['message']['from']['id']) ) {

            // 判斷機器人 入群新增 || 退群刪除
            BotModel::chatMember($data);
        }

        $data['date'] = date('Y-m-d H:i:s');
        $info_log     = json_encode($data, true) . PHP_EOL . PHP_EOL;
        file_put_contents( BotModel::LOG_PATH_INFO, $info_log, FILE_APPEND);

        // 判断是否为私聊
        if ( isset($data['message']['chat']) && $data['message']['chat']['type'] === 'private' ) {
            $msg = '更多功能开发中，当前请勿私聊机器人哦，么么哒';
            BotModel::sendNormalMessage($msg, $data['message']['chat']['id']);
            return false;
        }

        // 判斷有沒有傳圖
        $photo_id   = false;
        if ( !isset($data['message']['photo']) ) {
            // 判斷指令
            $text = $data['message']['text'] ?? '';
        }
        else {
            // 判斷指令
            $text     = $data['message']['caption'] ?? '';
            $photo_id = $data['message']['photo']['0']['file_id'];
        }
        $command = BotModel::getCommand($text);

        switch ($command['0']) {
            case 1:
            break;
            // add-添加管理員
            case '/add':
                // 驗證機器人接收訊息的群組
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id']  ) {
                    BotModel::addAdmin($bot_admin, $data['message']['from']['id']);
                }
            break;
            // send-群发圖文
            case '/send':
                // 驗證機器人接收訊息的群組
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id']  ) {
                    BotModel::sendMessage($command['1'], $photo_id);
                }
            break;
            default:
                $msg = '不支持此命令！';
                BotModel::replyMessage($msg, $data['message']['chat']['id'], $data['message']['message_id']);
            break;
        }
    }
}
