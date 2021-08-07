<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/BotModel.php';

class BotControl {    
    
    public function index() {
        $data = json_decode( file_get_contents('php://input'), true);

        // 驗證發送訊息的管理員
        if ( isset($data['message']['from']['id'])
        && $bot_admin = BotModel::getBotAdminById($data['message']['from']['id']) ) {

            // 判斷機器人 入群新增 || 退群刪除
            BotModel::chatMember($data);
        }

        $data['date'] = date('Y-m-d H:i:s');
        $info_log     = json_encode($data, true) . PHP_EOL . PHP_EOL;
        file_put_contents( BotModel::LOG_PATH_INFO, $info_log, FILE_APPEND);

        // 驗證機器人接收訊息的群組
        if ( !isset($data['message']['chat']['id'])
        || BotModel::BOT_GROUP !== $data['message']['chat']['id'] ) {
            return false;
        }

        // 判斷有沒有傳圖
        $photo_id   = false;
        if ( !isset($data['message']['photo']) ) {
            // 判斷指令
            $text    = isset($data['message']['text']) ? $data['message']['text'] : '';
            $command = BotModel::getCommand($text);
        }
        else {
            // 判斷指令
            $text     = isset($data['message']['caption']) ? $data['message']['caption'] : '';
            $command  = BotModel::getCommand($text);

            $photo_id = $data['message']['photo']['0']['file_id'];
        }

        switch ($command['0']) {
            // add-添加管理員
            case '/add':
                BotModel::addAdmin($bot_admin, $data['message']['from']['id']);
            break;
            // send-群发圖文
            case '/send':
                BotModel::sendMessage($command['1'], $photo_id);
            break;
            default:
            break;
        }
    }
}
