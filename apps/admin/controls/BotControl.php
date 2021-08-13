<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/BotModel.php';

class BotControl {    
    
    public function index() {
        $data = json_decode( file_get_contents('php://input'), true);

        // 驗證 token
        if ( BotModel::verifyToken() !== input('token') ) {
            return false;
        }

        // 判斷為回調訊息
        if ( isset($data['callback_query']) ) {
            $this->callback($data);
            return false;
        }

        // 驗證發送訊息的管理員
        if ( isset($data['message']['from']['id'])
        && $bot_admin = BotModel::getBotAdminById($data['message']['from']['id']) ) {

            // 判斷機器人 入群新增 || 退群刪除
            BotModel::chatMember($data);
        }
        
        // 驗證發送訊息的管理員
        if ( isset($data['my_chat_member']['from']['id'])
        && $bot_admin = BotModel::getBotAdminById($data['my_chat_member']['from']['id']) ) {

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

        // 判斷是不是要給機器人的指令
        if( !isset($data['message']['entities']['0']['type']) && !isset($data['message']['caption_entities']['0']['type']) ) {
            if ( $data['message']['entities']['0']['type'] !== 'bot_command' && $data['message']['caption_entities']['0']['type'] !== 'bot_command' ) {
                return false;
            }
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
            // help-机器人命令使用说明
            case '/help':
                // 驗證機器人接收訊息的群組
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id'] ) {
                    $msg = '机器人命令使用说明 : 
1.命令格式为 /单词
/add     添加管理員
/send    群发消息、圖文
/delete  删除管理员
/stop    停止机器人运行
/restart 恢复机器人运行

2.命令使用方法 /单词 + 空格 + 文字(少数不需要 空格 + 文字)
/add
/send hello
/delete @bh379
/stop
/restart';
                    
                    BotModel::sendNormalMessage($msg, $data['message']['chat']['id']);
                }
            break;
            // start-顯示按鈕
            case '/start':
                // BotModel::showButton($msg, $data['message']['chat']['id']);
            break;
            // add-添加管理員
            case '/add':
                // 驗證機器人接收訊息的群組
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id'] ) {
                    BotModel::addAdmin($bot_admin, $data['message']['from']['id'], $data['message']['from']['username'] );
                }
            break;
            // send-群发圖文
            case '/send':
                // 驗證機器人接收訊息的群組 && 驗證管理員
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id'] && $bot_admin ) {
                    BotModel::sendMessage($command['1'], $photo_id);
                }
            break;
            // delete-删除管理员
            case '/delete':
                // 驗證機器人接收訊息的群組 && 驗證管理員
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id'] && $bot_admin ) {
                    if( !BotModel::deleteAdmin($command['1']) ) {
                        $msg = '没有这个瓜皮';
                        BotModel::replyMessage($msg, $data['message']['chat']['id'], $data['message']['message_id']);
                    }
                }
            break;
            // stop-停止机器人运行
            case '/stop':
                // 驗證機器人接收訊息的群組 && 驗證管理員
                if ( BotModel::BOT_GROUP === $data['message']['chat']['id'] && $bot_admin ) {
                    $msg = '机器人即将停止，访问 ' . BotModel::BOT_WEBHOOK . '/bot/restart 重启机器人';
                    BotModel::replyMessage($msg, $data['message']['chat']['id'], $data['message']['message_id']);
                    
                    BotModel::stopBot();
                }
            break;
            default:
                $msg = '不支持此命令或仅限管理员使用！';
                BotModel::replyMessage($msg, $data['message']['chat']['id'], $data['message']['message_id']);
            break;
        }
    }

    /**
     * 恢复机器人运行
     */
    public function restart() {
        $decode_data = BotModel::restartBot();
        if ( $decode_data['ok'] ) {
            echo $decode_data['description'];
        }
        else {
            echo json_encode($decode_data);
        }
    }

    /**
     * 回調訊息
     */
    public function callback($data) {
        $msg = $data['callback_query']['data'];
        BotModel::sendNormalMessage($msg, $data['callback_query']['from']['id']);
    }
}
