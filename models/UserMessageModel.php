<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 待通知讯息
 *
 * @author Chaos
 */
class UserMessageModel {
    
    /**
     * 新增用户待通知讯息
     * @param int $uid 用户 ID
     * @param array $message_tpl 替换后的讯息模板
     */
    public static function saveUserMessage($uid, $message) {
        $user_message = [
            'user_id'       => $uid,
            'content_zh'    => $message['zh'],
            'content_en'    => $message['en'],
            'content_local' => $message['local'],
        ];
        return DB::insert('user_message', $user_message);
    }
}
