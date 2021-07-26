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
$GLOBALS['redis']['default']['host'] = '127.0.0.1';
$GLOBALS['redis']['default']['port'] = '6379';
$GLOBALS['redis']['default']['auth'] = '';

// 默认数据库配置
$GLOBALS['db']['default']['driver']     = 'mysql';
$GLOBALS['db']['default']['hostname']   = '127.0.0.1';
$GLOBALS['db']['default']['username']   = 'root';
$GLOBALS['db']['default']['password']   = '123456';
$GLOBALS['db']['default']['database']   = 'natasa';
$GLOBALS['db']['default']['port']       = '3306';
$GLOBALS['db']['default']['charset']    = 'utf8';
