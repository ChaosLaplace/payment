<?php if( !defined('XXOO') ) exit('No direct script access allowed');

class FileLogger {

    private $logDir;

    public function __construct() {
        $this->logDir = $GLOBALS['app']['log_dir'];
    }

    public function log($level, $text) {
        $log_dir = $this->logDir . date('Ymd');
        $log_file = $log_dir . '/app.log';

        if( !file_exists($log_file) ) {
            $this->mkDirs($log_dir);
            $this->mkFile($log_file);
        }

        // 随机检查文件大小，如果超过5M，则备份文件
        //if( rand(1, 20) == 10 ) {
        //    $this->mvfile($log_file);
        //}

        file_put_contents($log_file, $text, FILE_APPEND | LOCK_EX);
    }

    private function mkDirs($dir) {
        if( !is_dir($dir) )	{
            if( ! self::mkdirs( dirname($dir) ) ) {
                return false;
            }
            if( ! mkdir($dir, 0777) ) {
                return false;
            }
        }
        return true;
    }

    private function mkFile($file_path) {
        $file = fopen($file_path, 'a');
        fclose($file);
        @chmod($file_path, 0777);
    }

    private function mvFile($file) {
        /*
        if( !file_exists($file) ) {
            return false;
        }

        // 如果文件，超过5M
        $new_file = dirname($file) . '/' . rand(1000, 9999) . '.log';
        if( filesize($file) > 10 * 1024 ) {
            rename($file, $new_file);
        }
        */

        return true;
    }


}