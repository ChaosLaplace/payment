<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * redis缓存类，单例模式
 *
 * @author marvin
 */
class RedisCache {

    private static $instance = null;

    private $redis;

    private function __construct() {
        $conf = $GLOBALS['redis']['default'];
        if( !isset($conf['db']) ) {
            $conf['db'] = '0';
        }

        try {
            $this->redis = new Redis();
            $this->redis->connect($conf['host'], $conf['port']);

            if( isset($conf['auth']) && !empty($conf['auth']) ) {
                $this->redis->auth($conf['auth']);
            }

            $this->redis->select($conf['db']);

            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } catch(Exception $e) {
            trigger_error('Can\'t connect redis service ' . $e->getMessage(), E_USER_ERROR);
        }
    }

    public static function getInstance() {
        if( self::$instance == null ) {
            self::$instance = new RedisCache();
        }
        return self::$instance->redis;
    }

    /**
     * 取值
     * 如果读取的值为json字符串，则转换为关联数组后返回
     * @param string $k 键名
     * @return string|array|false
     */
    public static function get($k) {
        $redis = self::getInstance();
        $v = $redis->get($k);

        if($v === false) {
            return false;
        }

        $data = json_decode($v, true);
        if(!empty($data) && is_array($data)) {
            return $data;
        }

        return $v;
    }

    /**
     * 赋值
     * 如果写入写入的值为对象或数组，则转换为json字符串后再写入
     * @param string $k 键
     * @param mixd $v 值
     * @param int $exp 过期时间(秒)，默认为不过期
     */
    public static function set($k, $v, $exp=-1) {
        $redis = self::getInstance();

        if(is_object($v) || is_array($v)) {
            $v = json_encode($v);
        }

        if($exp == -1) {
            $redis->set($k, $v);
        } else {
            $redis->setex($k, $exp, $v);
        }
    }

    public static function del($k) {
        $redis = self::getInstance();
        $redis->del($k);
    }

    public static function expire($k, $time=0) {
        $redis = self::getInstance();
        return $redis->expire($k, $time);
    }

    public static function lpush($k, $v) {
        $redis = self::getInstance();
        $redis->lpush($k, $v);
    }

    public static function rpop($k) {
        $redis = self::getInstance();
        return $redis->rpop($k);
    }

    public static function hset($k, $f, $v) {
        $redis = self::getInstance();
        return $redis->hset($k, $f, $v);
    }

    public static function hget($k, $f) {
        $redis = self::getInstance();
        return $redis->hget($k, $f);
    }

    public static function hgetall($k) {
        $redis = self::getInstance();
        return $redis->hgetall($k);
    }

    public static function hdel($k, $f) {
        $redis = self::getInstance();
        return $redis->hdel($k, $f);
    }

}
