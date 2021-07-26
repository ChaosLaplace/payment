<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * Session类，依赖于redis实现
 *
 * @author marvin
 */
class Session {

    private static $instance = null;

    private $sid        = '';       // session id
    private $data       = array();  // session数据
    private $redis      = null;     // redis实例对象
    private $isDestroy  = false;

    public $name        = '__xxoo_session';
    public $path        = '/';
    public $domain      = '';
    public $expire      = 3600;
    public $ck          = 'default'; // redis连接信息配置

    public static function getInstance() {
        if( self::$instance == null ) {
            self::$instance = new Session();
        }
        return self::$instance;
    }

    public static function get($k) {
        $ses = self::getInstance();
        return $ses->getValue($k);
    }

    public static function set($k, $v, $expire=3600) {
        $ses = self::getInstance();
        $ses->setValue($k, $v, $expire);
    }

    public static function del($k) {
        $ses = self::getInstance();
        $ses->delValue($k);
    }

    public static function destroy() {
        $ses = self::getInstance();
        $ses->destroySession();
    }

    public function __construct() {
        $this->setConfig();

        if( !isset($GLOBALS['redis'][$this->ck]) ) {
            trigger_error('Redis configuration not found in $GLOBALS[\'redis\']'."['{$this->ck}']", E_USER_ERROR);
        }

        $this->start();

        // 程序结束时保存数据到redis
        register_shutdown_function( array($this, 'saveToRedis') );
    }

    private function setConfig() {
        if( !isset($GLOBALS['session']) || empty($GLOBALS['session']) ) {
            return false;
        }

        foreach($GLOBALS['session'] as $k=>$v) {
            $this->$k = $v;
        }
    }

    private function createSid() {
        $ses_id = '';
        while (strlen($ses_id) < 32) {
            $ses_id .= mt_rand(0, mt_getrandmax());
        }
        return md5(uniqid($ses_id, TRUE));
    }

    private function start() {
        $this->expire = time() + $this->expire;

        if( isset($_COOKIE[$this->name]) && !empty($_COOKIE[$this->name])) {
            $this->sid = $_COOKIE[$this->name];

            $this->connect();

            $data = $this->redis->get($this->sid);

            if( !empty($data) ) {
                $data = json_decode($data);
                $this->data = $this->objectToArray($data);
            }
        }
        else {
            $this->sid = $this->createSid();
        }

        setcookie($this->name, $this->sid, $this->expire, $this->path, $this->domain);
    }

    public function getValue($k) {
        if( !isset($this->data[$k]) ) {
            return false;
        }
        return $this->data[$k];
    }

    public function setValue($k, $v, $expire='') {
        $this->expire = $expire;

        $this->data[$k] = $v;
    }

    public function delValue($k) {
        unset( $this->data[$k] );
    }

    public function destroySession() {
        $this->expire = 1;
        $this->data = array();
        $this->isDestroy = true;

        $this->saveToRedis();
        setcookie($this->name, $this->sid, $this->expire, $this->path, $this->domain);
    }

    private function connect() {
        if( !empty($this->redis) ) {
            return;
        }

        $conf = $GLOBALS['redis'][$this->ck];
        try {
            $redis = new Redis();
            $redis->connect($conf['host'], $conf['port']);
            $redis->auth($conf['auth']);
            $redis->setOption( Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP );
            $redis->setOption( Redis::OPT_READ_TIMEOUT, -1 );

            $this->redis = $redis;
        } catch(Exception $e) {
            trigger_error('Can\'t connect redis service ' . $e->getMessage(), E_USER_ERROR);
        }
    }

    public function saveToRedis() {
        if( $this->isDestroy ) {
            $data = json_encode($this->data);
            $this->redis->setex($this->sid, $this->expire, $data);
            return;
        }

        if( empty($this->redis) ) {
            $this->connect();
        }

        if( $this->expire <= 0 ) {
            $this->redis->del($this->sid);
        }
        else {
            $data = json_encode($this->data);
            $this->redis->setex($this->sid, $this->expire, $data);
        }
    }

    public function objectToArray($obj) {
        $arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if(is_array($arr)){
            return array_map([$this, __FUNCTION__], $arr);
        } else {
            return $arr;
        }
    }


}