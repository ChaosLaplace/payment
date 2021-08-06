<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/BotModel.php';

class BotControl {    
    
    public function index() {
        $data = json_decode( file_get_contents('php://input'), true);

        $log = date('Y-m-d H:i:s') . PHP_EOL . json_encode($data) . PHP_EOL . PHP_EOL;
        file_put_contents( ROOT . 'logs/bot/log.txt', $log, FILE_APPEND);
        
        // 判斷機器人 入群新增 || 退群刪除
        BotModel::chatMember($data);

        // 判斷指令
        $text = isset($data['message']['text']) ? $data['message']['text'] : '';
        $command = BotModel::getCommand($text);

        // 驗證機器人接收訊息的群組
        if ( isset($data['message']['chat']['id'])
        && BotModel::BOT_GROUP !== $data['message']['chat']['id'] ) {
            return false;
        }

        switch ($command['0']) {
            // add-添加管理員
            case '/add':
                BotModel::addAdmin($data['message']['from']['id']);
            break;
            // send-群发
            case '/send':
                BotModel::sendMessage($command['1']);
            break;
            default:
            break;
        }
    }
}
