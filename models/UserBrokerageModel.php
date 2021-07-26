<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'models/UserMessageModel.php';
require_once ROOT . 'models/MessageTplModel.php';

/**
 * 用户佣金明细表
 * 
 * @author Chaos
 */
class UserBrokerageModel {

    /**
     * 入库用户佣金明细表
     * @param int $from_user_id 贡献佣金的会员 ID
     * @param int $change_amount 佣金按规则分润
     * @param int $to_user_id 该会员的上级 ID 目前按照 1~3 级顺序
     * @param array $trade 用户佣金明细表参数
     */
    public static function add($from_user_id, $change_amount, $to_user_id, array $trade) {
        $from_user_is_valid     = UserModel::getUserIsValid($from_user_id);

        $trade['is_lock']       = 
        UserModel::updateUserBrokerage($from_user_is_valid, $change_amount, $to_user_id);
        
        $trade['change_amount'] = $change_amount;
        $trade['from_user_id']  = $from_user_id;

        self::__change($to_user_id, $trade);

        return true;
    }

    /**
     * 可提领佣金转帐至现金
     * @param int $uid 会员 ID
     * @param int $amount 会员提领的佣金
     * @return string bool 有可提领的佣金才能转帐
     */
    public static function transfer($uid, $amount) {
        $sql = "select nickname, brokerage_enable from `user` where id=?i";
        $user_info = DB::getLine($sql, [$uid]);
        if ( $amount > $user_info['brokerage_enable'] ) {
            return false;
        }

        $sql = "update `user` set brokerage=brokerage - {$amount}
            ,brokerage_enable=brokerage_enable - {$amount} where id=?i";
        DB::runSql($sql, [$uid]);

        $now = date('Y-m-d H:i:s');

        $message_tpl_brokerage_to_balance = 
        MessageTplModel::getBrokerageToBalanceMessage($user_info['nickname'], $now, $amount);

        $trade['change_depict_zh']    = $message_tpl_brokerage_to_balance['zh'];
        $trade['change_depict_en']    = $message_tpl_brokerage_to_balance['en'];
        $trade['change_depict_local'] = $message_tpl_brokerage_to_balance['local'];
        $trade['subject_id']          = UserAccountModel::SUBJECT_EXCHANGE;
        $trade['rel_table']           = 'user';
        $trade['rel_id']              = $uid;

        UserAccountModel::income($uid, $amount, $trade);

        return true;
    }

    /**
     * 佣金明细表全部解锁
     * @param int $from_user_id 贡献佣金的会员 ID
     * @return string bool 成为有效会员才能解锁
     */
    public static function unlock($from_user_id) {
        if ( $to_user_brokerage_list = self::listSumAmountGroupByUserId($from_user_id) ) {
            $from_user_nickname = UserModel::getUserNickname($from_user_id);

            $user_id = null;
            $total_amount = null;
            $now = date('Y-m-d H:i:s');
            foreach ($to_user_brokerage_list as $value) {
                $user_id = $value['user_id'];
                $total_amount = $value['total_amount'];

                UserModel::updateUserBrokerageLockToEnable($user_id, $total_amount);

                $message_tpl_brokerage_unlock = 
                MessageTplModel::getBrokerageUnlockMessage($from_user_nickname, $now, $total_amount);

                UserMessageModel::saveUserMessage($user_id, $message_tpl_brokerage_unlock);
            }

            $ids = self::listIdsById($from_user_id);
            self::updateUserBrokerageUnlock($ids);

            return true;
        }
        return false;
    }

    /**
     * 统计用户佣金明细表的佣金
     * @param int $from_user_id 贡献佣金的会员 ID
     * @return array [{"userId":1, "totalAmount":"0"}] 返回解锁后能得到的佣金
     */
    public static function listSumAmountGroupByUserId($from_user_id) {
        $sql = "select user_id, sum(change_amount) as total_amount
        from `user_brokerage` where from_user_id={$from_user_id} group by user_id";
        return DB::getData($sql, [$from_user_id]);
    }

    /**
     * 统计用户的佣金明细表索引 index
     * @param int $from_user_id 贡献佣金的会员 ID
     * @return string 1,2,3 返回明细表的索引 index
     */
    public static function listIdsById($from_user_id) {
        $sql     = "select id from `user_brokerage` where from_user_id={$from_user_id}";
        $id_list = DB::getData($sql, [$from_user_id]);
        $id_list = array_column($id_list, 'id');
        return implode(',', $id_list);
    }

    /**
     * 依照佣金明细表 ID 解锁
     * @param array $ids 佣金明细表 ID
     */
    public static function updateUserBrokerageUnlock($ids) {
        $sql = "update `user_brokerage` set is_lock='N' where id in ($ids)";
        return DB::runSql($sql);
    }

    /**
     * 入库用户佣金明细表
     * @param int $to_user_id 该会员的上级 ID 目前按照 1~3 级顺序
     * @param array $trade 用户佣金明细表参数
     */
    private static function __change($uid, array $trade) {
        $trade['user_id'] = $uid;
        return DB::insert('user_brokerage', $trade);
    }
}
