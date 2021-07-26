<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once XXOO . 'libs/RedisCache.lib.php';

/**
 * 替换讯息模板
 *
 * @author Chaos
 */
class MessageTplModel {

    const CACHE_LIST               = 'message:';

    /** 购买VIP分润消息 */
    const TPL_BUY_VIP_SHARE        = 'buy_vip_share';
    /** 会员佣金明细消息 */
    const TPL_USER_BROKERAGE       = 'user_brokerage';
    /** 可提领佣金转帐至现金消息 */
    const TPL_BROKERAGE_TO_BALANCE = 'brokerage_to_balance';
    /** 佣金明细表全部解锁消息 */
    const TPL_BROKERAGE_UNLOCK     = 'brokerage_unlock';
    /** 拍卖品预约消息 */
    const TPL_AUCTION_RESERVE      = 'auction_reserve';

    /**
     * 模板替换购买VIP分润消息
     * @param string $username 用户名
     * @param int $datetime 时间戳，秒
     * @param int $grade 等级
     * @param int $amount 佣金，元
     * @return array 讯息模板替换完后返回
     */
    public static function getBuyVipShareMessage($username, $datetime, $grade, $amount) {
        $cache_name = self::CACHE_LIST . self::TPL_BUY_VIP_SHARE;
        if( !$vip_share_tpl = RedisCache::get($cache_name) ) {
            $sql = "select * from `message_tpl` where ident=?s limit 1";
            $vip_share_tpl = DB::getLine($sql, [self::TPL_BUY_VIP_SHARE]);

            RedisCache::set($cache_name, $vip_share_tpl);
        }

        $replace_str   = array(
            'username' => $username,
            'datetime' => $datetime,
            'grade'    => $grade,
            'amount'   => $amount,
        );
        $replace_key   = null;

        foreach ($replace_str as $key => $str) {
            $replace_key = "{\$$key}";

            $vip_share_tpl['content_zh']    = 
            str_replace($replace_key, $str, $vip_share_tpl['content_zh']);

            $vip_share_tpl['content_en']    = 
            str_replace($replace_key, $str, $vip_share_tpl['content_en']);

            $vip_share_tpl['content_local'] = 
            str_replace($replace_key, $str, $vip_share_tpl['content_local']);
        }
        
        $ret = array(
            'zh'    => $vip_share_tpl['content_zh'],
            'en'    => $vip_share_tpl['content_en'],
            'local' => $vip_share_tpl['content_local'],
        );
        return $ret;
    }

    /**
     * 模板替换会员佣金明细消息
     * @param string $username 用户名
     * @param int $datetime 时间戳，秒
     * @param int $grade 等级
     * @param int $amount 佣金，元
     * @return array 讯息模板替换完后返回
     */
    public static function getUserBrokerageMessage($username, $datetime, $grade, $amount) {
        $cache_name = self::CACHE_LIST . self::TPL_USER_BROKERAGE;
        if( !$user_brokerage_tpl = RedisCache::get($cache_name) ) {
            $sql = "select * from `message_tpl` where ident=?s limit 1";
            $user_brokerage_tpl = DB::getLine($sql, [self::TPL_USER_BROKERAGE]);

            RedisCache::set($cache_name, $user_brokerage_tpl);
        }

        $replace_str   = array(
            'username' => $username,
            'datetime' => $datetime,
            'grade'    => $grade,
            'amount'   => $amount,
        );
        $replace_key   = null;

        foreach ($replace_str as $key => $str) {
            $replace_key = "{\$$key}";

            $user_brokerage_tpl['content_zh']    = 
            str_replace($replace_key, $str, $user_brokerage_tpl['content_zh']);

            $user_brokerage_tpl['content_en']    = 
            str_replace($replace_key, $str, $user_brokerage_tpl['content_en']);

            $user_brokerage_tpl['content_local'] = 
            str_replace($replace_key, $str, $user_brokerage_tpl['content_local']);
        }
        
        $ret = array(
            'zh'    => $user_brokerage_tpl['content_zh'],
            'en'    => $user_brokerage_tpl['content_en'],
            'local' => $user_brokerage_tpl['content_local'],
        );
        return $ret;
    }

    /**
     * 可提领佣金转帐至现金消息
     * @param string $username 用户名
     * @param int $datetime 时间戳，秒
     * @param int $amount 佣金，元
     * @return array 讯息模板替换完后返回
     */
    public static function getBrokerageToBalanceMessage($username, $datetime, $amount) {
        $cache_name = self::CACHE_LIST . self::TPL_BROKERAGE_TO_BALANCE;
        if( !$brokerage_to_balance_tpl = RedisCache::get($cache_name) ) {
            $sql = "select * from `message_tpl` where ident=?s limit 1";
            $brokerage_to_balance_tpl = DB::getLine($sql, [self::TPL_BROKERAGE_TO_BALANCE]);
            
            RedisCache::set($cache_name, $brokerage_to_balance_tpl);
        }

        $replace_str   = array(
            'username' => $username,
            'datetime' => $datetime,
            'amount'   => $amount,
        );
        $replace_key   = null;

        foreach ($replace_str as $key => $str) {
            $replace_key = "{\$$key}";

            $brokerage_to_balance_tpl['content_zh']    = 
            str_replace($replace_key, $str, $brokerage_to_balance_tpl['content_zh']);

            $brokerage_to_balance_tpl['content_en']    = 
            str_replace($replace_key, $str, $brokerage_to_balance_tpl['content_en']);

            $brokerage_to_balance_tpl['content_local'] = 
            str_replace($replace_key, $str, $brokerage_to_balance_tpl['content_local']);
        }
        
        $ret = array(
            'zh'    => $brokerage_to_balance_tpl['content_zh'],
            'en'    => $brokerage_to_balance_tpl['content_en'],
            'local' => $brokerage_to_balance_tpl['content_local'],
        );
        return $ret;
    }

    /**
     * 佣金明细表全部解锁
     * @param string $username 用户名
     * @param int $datetime 时间戳，秒
     * @param int $amount 佣金，元
     * @return array 讯息模板替换完后返回
     */
    public static function getBrokerageUnlockMessage($username, $datetime, $amount) {
        $cache_name = self::CACHE_LIST . self::TPL_BROKERAGE_UNLOCK;
        if( !$brokerage_unlock_tpl = RedisCache::get($cache_name) ) {
            $sql = "select * from `message_tpl` where ident=?s limit 1";
            $brokerage_unlock_tpl = DB::getLine($sql, [self::TPL_BROKERAGE_UNLOCK]);
            
            RedisCache::set($cache_name, $brokerage_unlock_tpl);
        }

        $replace_str   = array(
            'username' => $username,
            'datetime' => $datetime,
            'amount'   => $amount,
        );
        $replace_key   = null;

        foreach ($replace_str as $key => $str) {
            $replace_key = "{\$$key}";

            $brokerage_unlock_tpl['content_zh']    = 
            str_replace($replace_key, $str, $brokerage_unlock_tpl['content_zh']);

            $brokerage_unlock_tpl['content_en']    = 
            str_replace($replace_key, $str, $brokerage_unlock_tpl['content_en']);

            $brokerage_unlock_tpl['content_local'] = 
            str_replace($replace_key, $str, $brokerage_unlock_tpl['content_local']);
        }
        
        $ret = array(
            'zh'    => $brokerage_unlock_tpl['content_zh'],
            'en'    => $brokerage_unlock_tpl['content_en'],
            'local' => $brokerage_unlock_tpl['content_local'],
        );
        return $ret;
    }

    /**
     * 拍卖品预约消息
     * @param string $auction_name 拍卖品名称
     * @param int $last_time 时间戳，秒
     * @param string $room_name 房间名称
     * @return array 讯息模板替换完后返回
     */
    public static function getAuctionReserveMessage($auction_name, $last_time, $auction_room) {
        $cache_tpl = self::TPL_AUCTION_RESERVE;
        $tpl_message = self::__tplMessage($cache_tpl);

        $replace_str   = array(
            'auction_name' => $auction_name,
            'last_time'    => $last_time,
            'auction_room' => $auction_room,
        );
        $replace_key   = null;

        foreach ($replace_str as $key => $str) {
            $replace_key = "{\$$key}";

            $tpl_message['content_zh']    = 
            str_replace($replace_key, $str, $tpl_message['content_zh']);

            $tpl_message['content_en']    = 
            str_replace($replace_key, $str, $tpl_message['content_en']);

            $tpl_message['content_local'] = 
            str_replace($replace_key, $str, $tpl_message['content_local']);
        }
        
        $ret = array(
            'zh'    => $tpl_message['content_zh'],
            'en'    => $tpl_message['content_en'],
            'local' => $tpl_message['content_local'],
        );
        return $ret;
    }

    private static function __tplMessage($cache_tpl) {
        $cache_name = self::CACHE_LIST . $cache_tpl;
        if( !$tpl_message = RedisCache::get($cache_name) ) {
            $sql = "select * from `message_tpl` where ident=?s limit 1";
            $tpl_message = DB::getLine($sql, [$cache_tpl]);
            
            RedisCache::set($cache_name, $tpl_message);
        }
        return $tpl_message;
    }
}
