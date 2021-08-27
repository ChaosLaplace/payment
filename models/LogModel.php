<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * log
 *
 * @author Chaos
 */
class LogModel {
    
    /**
     * log
     */
    public static function log($data, $file_name = 'test') {        
        $log_dir  = $GLOBALS['app']['log_dir'] . date('Ymd') . '/';
        $log_file = $log_dir . $file_name . '.log';

        if( !file_exists($log_file) ) {
            self::mkDirs($log_dir);
            self::mkFile($log_file);
        }
        
        $time = '[' . date('Y-m-d H:i:s') . '] -> ';
		$data = $time . var_export($data, true) . PHP_EOL . PHP_EOL;
        file_put_contents($log_file, $data, FILE_APPEND);
    }

    private static function mkDirs($dir) {
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

    private static function mkFile($file_path) {
        $file = fopen($file_path, 'a');
        fclose($file);
        @chmod($file_path, 0777);
    }
}
