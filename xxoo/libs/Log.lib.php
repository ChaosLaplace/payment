<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * 日志类
 *
 * @author marvin
 */
class Log {

    private static $instance = null;

    private $logger;

    private $levels = array(
        'debug' => 1,
        'info'  => 2,
        'warn'  => 3,
        'error' => 4
    );

    private static function getInstance() {
        if( self::$instance == null ) {
            self::$instance = new Log();
        }
        return self::$instance;
    }

    private function __construct() {
        if( !isset($GLOBALS['log']['output']) ) {

        }

        $output = $GLOBALS['log']['output'];

        if($output == 'file') {
            require_once XXOO . 'libs/logs/FileLogger.lib.php';
            $this->logger = new FileLogger();
        }
        else if($output == 'database') {
            require_once XXOO . 'libs/logs/DatabaseLogger.lib.php';
            $this->logger = new DatabaseLogger();
        }
        else {
            require_once XXOO . 'libs/logs/ConsoleLogger.lib.php';
            $this->logger = new ConsoleLogger();
        }
    }

    public static function debug($info) {
        $logger = self::getInstance();
        $logger->log('debug', $info);
    }

    public static function info($info) {
        $logger = self::getInstance();
        $logger->log('info', $info);
    }

    public static function warn($info) {
        $logger = self::getInstance();
        $logger->log('warn', $info);
    }

    public static function error($info) {
        $logger = self::getInstance();
        $logger->log('error', $info);
    }

    private function log($level, $info) {
        // 小于指定级别，直接忽略
        if( $this->levels[$level] < $this->levels[ $GLOBALS['log']['level'] ] ) {
            return false;
        }

        // 格式化日志信息
        $time = date('H:i:s');
        if( is_array($info) && $level == 'error' ) {
            $text = '';
            foreach($info as $k=>$row) {
                $text .= "[{$time}] " . $row . PHP_EOL;
            }
        }
        else if( is_string($info) ) {
            $text = "[{$time}] " . $info . PHP_EOL;
        }
        else {

            $text = "[{$time}] " . json_encode($info) . PHP_EOL;
        }

        $this->logger->log($level, $text);
    }

}