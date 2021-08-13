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
$GLOBALS['bot']['token']     = '1632116104:AAGJ6ctNYHBrYjp2YzKNYYpan4Tm3fg-fHs';
$GLOBALS['bot']['md5_token'] = '6BB00FF0E662CF83C92BF9CD20F63EDD';
$GLOBALS['bot']['url']       = 'http://8.210.136.49:8081/bot' . $GLOBALS['bot']['token'] . '/';

// 资源域名
$GLOBALS['app']['rs_domain']    = 'http://rs.payment.com/';
// 图片域名
$GLOBALS['app']['img_domain']   = 'http://img.payment.com/';

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

// 提现最低限额，分
$GLOBALS['app']['exchange_limit'] = 100;

// 本地化语言
$GLOBALS['request']['locale'] = 'vi';

// 请求语言
$GLOBALS['request']['lang'] = 'zh';

// 设置session信息
$GLOBALS['session']['name']     = 'boqingbot';
$GLOBALS['session']['domain']   = 'boqingbot.online';
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
$GLOBALS['db']['default']['username']   = 'zxbot';
$GLOBALS['db']['default']['password']   = 'Z58ks8X43dyZDRtY';
$GLOBALS['db']['default']['database']   = 'zxbot';
$GLOBALS['db']['default']['port']       = '3306';
$GLOBALS['db']['default']['charset']    = 'utf8bm4';
