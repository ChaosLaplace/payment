<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 用户账户模块
 *
 * @author marvin
 */
class UserAccountModel {

    const INCOME = 'INCOME';
    const EXPEND = 'EXPEND';

    const SUBJECT_RECHARGE  = 1;    // 充值
    const SUBJECT_EXCHANGE  = 2;    // 提现
    const SUBJECT_BUY_GRADE = 3;    // 购买vip
    const SUBJECT_RUSH      = 4;    // 冲正
    const SUBJECT_BROKERAGE = 5;    // 佣金转入
    const SUBJECT_BOND      = 6;    // 交纳保证金
    const SUBJECT_PRODUCT   = 7;    // 购买商品

    public static function getBalance($uid) {
        $sql = "select balance from `user` where id=?i";
        return DB::getVar($sql, [$uid]);
    }

    /**
     * 收入
     * @param $uid
     * @param $amount
     * @param $trade
     * @return bool
     */
    public static function income($uid, $amount, $trade) {
        // 事务 ===============================================================

        $sql = "update `user` set balance=balance+?i where id=?i";
        DB::runSql($sql, [$amount, $uid]);

        $trade['change_type'] = self::INCOME;
        $trade['change_amount'] = $amount;

        // 描述翻译

        self::__change($uid, $trade);

        return true;
    }

    /**
     * 支出
     * @param $uid
     * @param $amount
     * @param $trade
     * @return bool
     */
    public static function expend($uid, $amount, $trade) {
        $balance = self::getBalance($uid);

        if($balance < $amount) {
            return false;
        }

        // 事务 ===================================================================

        $sql = "update `user` set balance=balance-?i where id=?i";
        DB::runSql($sql, [$amount, $uid]);

        $trade['change_type'] = self::EXPEND;
        $trade['change_amount'] = $amount;

        self::__change($uid, $trade);

        return true;
    }

    private static function __change($uid, $trade) {
        $balance = self::getBalance($uid);

        $trade['user_id'] = $uid;
        $trade['balance'] = $balance;

        return DB::insert('user_account', $trade);
    }

}
