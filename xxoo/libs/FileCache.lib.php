<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * 简单文件缓存，单例类
 *
 * @author marvin
 */
class FileCache {

    private static $instance = NULL;	// 对象实例
    private $expire = 3600;				// 缓存过期时间
    private $path;						// 缓存目录

    /**
     * 构造方法，私有
     * @param string $path 缓存目录
     */
    private function __construct($path='') {
        if( empty($path) ) {
            $this->path = $GLOBALS['app']['data_cache'];
        } else {
            $this->path = $path;
        }
    }

    /**
     * 获取缓存类实例，单例
     * @param string $path 缓存目录
     * @return object
     */
    public static function getInstance($path='') {
        if( (self::$instance != null) && (self::$instance->path == $path) ) {
            return self::$instance;
        }
        else {
            self::$instance = new FileCache($path);
            return self::$instance;
        }
    }

    public function setCache($key, $expire, $value) {
        $this->expire = $expire;
        $this->cache($key, $value);
    }

    public function getCache($key) {
        $this->cache($key);
    }

    /**
     * 读/写缓存
     * 如果只传一个$key值，该方法为读缓存
     * @param string $key 缓存名称
     * @param mixed $value 缓存内容
     */
    public function cache($key, $value='') {
        $file = $this->path . $this->genFilename( $key );
        if( empty($value) ) {	// 读取cache
            return $this->__fetch( $file );
        }
        else {	// 写cache
            // 如果目录不存在，创建目录
            if( !file_exists( $this->path ) ) {
                $this->_mkdirs( $this->path );
            }
            $this->__put( $file, $value );
        }
    }

    /**
     * 删除缓存
     * @param string $filename 缓存文件
     */
    public function delete($key) {
        $file = $this->path . $this->genFilename( $key );
        if( file_exists($file) ) {
            @unlink( $file );
        }
    }

    /**
     * 生成缓存文件名
     * @param string $name 文件名
     * @return string
     */
    public function genFilename( $name ) {
        return md5( $name );
    }

    /**
     * 读取缓存
     * 如果有缓存返回缓存内容；如果缓存不存返回false；如果缓存已过期，删除过期缓存文件，返回false
     * @param string $file 缓存文件
     * @return mixed
     */
    private function __fetch( $file ) {
        if( file_exists( $file ) ) {	// 缓存文件存在
            $temp = explode( '<<%-==-%>>', file_get_contents( $file ) );
            $timeout = $temp[0];
            if( time() < $timeout ) {	// 有效缓存
                return unserialize(base64_decode($temp[1]));
            }
            else {	// 缓存过期
                $this->delete( $file );
                return FALSE;
            }
        }
        else {	// 缓存不存在
            return FALSE;
        }
    }

    /**
     * 写缓存
     * @param string $file 缓存路径
     * @param mixed $value 缓存内容
     */
    private function __put( $file, $value ) {
        // 在缓存文件中添加过期时间戳
        $value = base64_encode(serialize($value));
        $value = ( time() + $this->expire ) . '<<%-==-%>>' .  $value;

        // 写文件，独占锁
        file_put_contents($file, $value, LOCK_EX);
    }

    /**
     * 递归创建目录
     * @param string $dir
     */
    private function _mkdirs( $dir ) {
        if( !is_dir($dir) ) {
            if( !mkdirs(dirname($dir)) ){
                return false;
            }
            if( !mkdir($dir,0777) ){
                return false;
            }
        }
        return true;
    }
}
