<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/*
  ----------------------------------------------------------------
  全局配置文件
  ----------------------------------------------------------------
 */

// 版本号
$GLOBALS['app']['version'] = '0.1';

/*
 * 运行环境
 * 通过定义不同的运行环境，框架自动适应错误、日志等处理方式
 *
 * 框架支持三种产品状态
 * 		开发: development
 * 		维护: servicing
 * 		产品: production
 */
$GLOBALS['app']['env'] = 'development';

// 资源域名
$GLOBALS['app']['rs_domain']    = 'http://rs.natasa.com/';
// 图片域名
$GLOBALS['app']['img_domain']   = 'http://img.natasa.com/';

// 数据密钥
$GLOBALS['app']['secret'] = '123456';

// 预展时长
$GLOBALS['app']['pre_len'] = 60 * 60;

// 运费
$GLOBALS['app']['postage'] = 30 * 100;
// 最晚支付时间
$GLOBALS['app']['last_paid_time'] = 1 * 60 * 60;
// 最晚收货确认时间
$GLOBALS['app']['receipt_confirm_time'] = 14 * 24 * 60 * 60;

// 用户默认头像
$GLOBALS['app']['default_avatar'] = 'http://yyptp.oss-accelerate.aliyuncs.com/2021/0713/80c33f816f959a1be479a0df104089d6.jpg';

$GLOBALS['app']['room_subscript'] = [
    ['id'=>'NEW', 'name_zh'=>'新手', 'name_en'=>'new', 'name_local'=>'new'],
    ['id'=>'ACE', 'name_zh'=>'高手', 'name_en'=>'ace', 'name_local'=>'ace'],
];

// 收款银行卡，后期如果有需要则改到数据库中
$GLOBALS['app']['rec_bank'] = [
    'id'            => 1,
    'bank_id'       => 1,
    'bank_name'     => '建设银行',
    'contract_bank' => '北京海淀支行',
    'account_num'   => '6217200625694568256',
    'username'      => '张三',
];

// 提现最低限额，分
$GLOBALS['app']['exchange_limit'] = 100;

// 本地化语言
$GLOBALS['request']['locale'] = 'vi';

// 请求语言
$GLOBALS['request']['lang'] = 'zh';

// 设置session信息
$GLOBALS['session']['name']     = 'natasa';
$GLOBALS['session']['domain']   = 'natasa.com';
$GLOBALS['session']['expire']   = 3600;

// token过期时间
$GLOBALS['token']['expire']   = 24 * 60 * 60;

// 日志配置
$GLOBALS['log']['output']   = 'file';    // file、console、database
$GLOBALS['log']['level']    = 'debug';   // debug、info、warn、error

// redis config
$GLOBALS['redis']['default']['host'] = 'localhost';
$GLOBALS['redis']['default']['port'] = '6379';
$GLOBALS['redis']['default']['auth'] = '';
$GLOBALS['redis']['default']['db']   = '0';

// 过期事件订阅库 redis config
// 因为此库的过期事件都会触发回调，为了方便管理和性能，所以使用一个独立的库
$GLOBALS['redis']['event']['host'] = 'localhost';
$GLOBALS['redis']['event']['port'] = '6379';
$GLOBALS['redis']['event']['auth'] = '';
$GLOBALS['redis']['event']['db']   = '1';

// 默认数据库配置
$GLOBALS['db']['default']['driver']     = 'mysql';
$GLOBALS['db']['default']['hostname']   = 'localhost';
$GLOBALS['db']['default']['username']   = 'root';
$GLOBALS['db']['default']['password']   = 'root';
$GLOBALS['db']['default']['database']   = 'natasa';
$GLOBALS['db']['default']['port']       = '3306';
$GLOBALS['db']['default']['charset']    = 'utf8bm4';
