<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class TestControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        
        // $myValues = $this->getValuesByArray(); // 一旦我们调用函数将会在这里创建数组
        $myValues = $this->getValuesByYield(); // 一旦我们调用函数将会在这里创建数组
        foreach ($myValues as $value) {
            // echo $value;
        }

        // $ga = new PHPGangsta_GoogleAuthenticator();
        // // 下面为生成二维码，内容是一个URI地址（otpauth://totp/账号?secret=密钥&issuer=标题）
        // $qrcode_url = $ga->getQRCodeGoogleUrl('1', 'test', $GLOBALS['app']['secret']);

        // $this->resp($qrcode_url);
    }
    
    public function getValuesByArray() {
        // 获取初始内存使用量
        echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB' . PHP_EOL;

        $valuesArray = [];
        for ($i = 1; $i < 800000; ++$i) {
            $valuesArray[] = $i;
            // 为了让我们能进行分析，所以我们测量一下内存使用量
            if ( ($i % 200000) == 0 ) {
                // 来 MB 为单位获取内存使用量
                echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB'. PHP_EOL;
            }
        }

        return $valuesArray;
    }

    public function getValuesByYield() {
        // 获取内存使用数据
        echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB' . PHP_EOL;

        for ($i = 1; $i < 800000; ++$i) {
            yield $i;
            // 做性能分析，因此可测量内存使用率
            if ( ($i % 200000) == 0 ) {
                // 内存使用以 MB 为单位
                echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB'. PHP_EOL;
            }
        }
    }
}
