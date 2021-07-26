<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * APP 好友操作
 * 
 * @author Chaos
 */
class FriendControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 查询会员好友
     * @return string 返回会员好友
     */
    public function show() {
        $id     = $this->uid;

        $sql    = "select id, nickname from `user` where pid=?i group by id";
        $friend = DB::getData($sql, [$id]);
        $total_friend = count($friend);

        // 需添加分页功能 --------------------------------------------------------------
        
        $this->resp([
            'total_friend' => $total_friend,
            'friend'       => $friend,
        ]);
    }

    /**
     * 查询会员好友贡献详情
     * @return string 返回会员好友贡献详情
     */
    public function info() {
        $id           = $this->uid;
        $from_user_id = input('fromUserId');
        $change_depict_language = 'change_depict_' . $GLOBALS['request']['locale'];

        $sql          = "select subject_id, change_amount, {$change_depict_language}
        from `user_brokerage` where from_user_id=?i and user_id=?i";
        $friend_info  = DB::getData($sql, [$from_user_id, $id]);

        // 需添加分页功能 --------------------------------------------------------------

        $this->resp([
            'friend_info' => $friend_info
        ]);
    }
}