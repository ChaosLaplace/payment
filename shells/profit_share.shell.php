<?php

/**
 * 上级分润
 * 每分钟执行一次
 *
 * @author Chaos
 */
require_once dirname(__DIR__) . '/xxoo/shell.boot.php';
require_once XXOO . 'libs/RedisCache.lib.php';
require_once ROOT . 'models/UserBrokerageModel.php';

$queue_name = 'profit_share:wait_queue';

$task = null;
while(true) {
    if( !$task = RedisCache::rpop($queue_name)) {
        break;
    }

    $parent_list = findParents($task['uid']);
    if( count($parent_list) <= 0 ) {
        continue;
    }

    $grade_rate_arr = explode('/', $task['rule']);

    foreach($parent_list as $index=>$to_uid) {
        $rate = isset($grade_rate_arr[$index]) ? $grade_rate_arr[$index] : 0;
        $profit_amount = $task['amount'] * $rate;

        $trade = [
            'subject_id' => $task['subject'],
            'rel_table'  => $task['rel_table'],
            'rel_id'     => $task['rel_id'],
            'change_depict_zh'    => '分润',
            'change_depict_en'    => '分润',
            'change_depict_local' => '分润',
        ];
        UserBrokerageModel::add($task['uid'], $profit_amount, $to_uid, $trade);
    }

}


function findParents($uid) {
    static $parent_list = [];

    $sql = "select pid from `user` where id=?i";
    $user = DB::getLine($sql, [$uid]);

    if( empty($user['pid']) || count($parent_list) == 3 ) {
        return $parent_list;
    }
    else {
        $parent_list[] = $user['pid'];
        findParents($user['pid']);
    }
}
