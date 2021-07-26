<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';

/**
 * 分润模块
 *
 * @author marvin
 */
class ProfitShareModel {

    const QUEUE_NAME = 'profit_share:wait_queue';

    const SUBJECT_BUY_VIP_SHARE = 1;    // 购买vip分润
    const SUBJECT_BID_SHARE = 2;        // 出价分润
    const SUBJECT_TRANSFER_SHARE = 3;   // 转拍成交分润

    /**
     * 添加到待分润对队
     * @param int $uid 用户id
     * @param int $subject_id 科目id
     * @param int $amount 金额
     * @param string $rule 分润规则
     * @param string $rel_table 关键词
     * @param string $rel_id 分润规则
     */
    public static function addWaitQueue($uid, $subject_id, $amount, $rule, $rel_table, $rel_id) {
        $item = [
            'uid'       => $uid,
            'subject'   => $subject_id,
            'amount'    => $amount,
            'rule'      => $rule,
            'rel_table' => $rel_table,
            'rel_id'    => $rel_id,
        ];
        RedisCache::lpush(self::QUEUE_NAME, $item);
    }

}
