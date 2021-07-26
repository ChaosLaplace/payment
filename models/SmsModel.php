<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'libs/Sms.lib.php';
require_once XXOO . 'libs/RedisCache.lib.php';

/**
 * 验证码
 * 
 * @author Chaos
 */
class SmsModel {

    const CACHE_LIST    = 'sms:';
    /** sms 发送频率 */
    const SMS_FREQUENCY = 1 * 60;

    /**
     * 发送验证码 判断频率
     * @param string $country_code 国际区号
     * @param string $phone 手机号
     * @return bool 是否成功
     */
    public static function send($country_code, $phone) {
        $phone_number = $country_code . $phone;
        $code = self::__randCode();

        $cache_name = self::CACHE_LIST . $phone_number;
        if( RedisCache::get($cache_name) ) {
            return false;
        }

        $ret = Sms::sendSms($phone_number, $code);
        $ret_arr = json_decode($ret['output'], true);
        if( !isset($ret_arr['code']) || $ret_arr['code'] != '0' ) {
            return false;
        }

        RedisCache::set($cache_name, $code, self::SMS_FREQUENCY);

        return true;
    }

    /**
     * 校验验证码
     * @param string $country_code 国际区号
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return bool 是否成功
     */
    public static function validation($country_code, $phone, $code) {
        $cache_name = self::CACHE_LIST . $country_code . $phone;
        if ( $code == RedisCache::get($cache_name) ) {
            return true;
        }
        return false;
    }

    /**
     * 生成验证码
     * @param int $length 验证码的长度
     * @return string 验证码
     */
    private static function __randCode($length = 5) {
        $str   = '23456789abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $count = strlen($str) - 1;

        $code  = '';
        for ($i = 0; $i < $length; ++$i) {
            $index = mt_rand(0, $count);
            $code .= $str[$index];
        }
        return $code;
    }
}
