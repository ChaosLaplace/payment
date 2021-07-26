<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/*
  ----------------------------------------------------------------
    框架默认配置文件

    @author marvin
  ----------------------------------------------------------------
*/

/** 版本号 */
$GLOBALS['xxoo']['version']            = '3.5';

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

// 数据缓存
$GLOBALS['app']['data_cache']          = ROOT . 'data/cache/data/';

// 图片缓存
$GLOBALS['app']['image_cache']         = ROOT . 'data/cache/image/';

// 页面缓存
$GLOBALS['app']['page_cache']          = ROOT . 'data/cache/page/';

// 页面缓存最大缓存时长为1个月
$GLOBALS['app']['page_cache_max_len']  = 30 * 24 * 60 * 60;

// 页面缓存子目录量扩散因子
$GLOBALS['app']['page_cache_spread']   = 800;

// 上传目录
$GLOBALS['app']['upload_dir']          = ROOT . 'public/uploads/';

// 日志目录
$GLOBALS['app']['log_dir']             = ROOT . 'logs/';

/*
 * xxoo支持两种形式的url：
 *   path info: PATH_INFO
 *   query string: QUERY_STRING
 */
$GLOBALS['app']['protocol'] = 'PATH_INFO';

/*
 * 应用程序入口
 * 默认为空，需要url重写支持
 * 如果不重写url，则一般填为index.php
 */
$GLOBALS['app']['index'] = '';

// 时区
$GLOBALS['app']['timezone'] = 'PRC';

// 设置当前请求语言，默认设置为简体中文
$GLOBALS['app']['lang'] = 'zh_cn';

// 设置当前请求语言，默认设置为简体中文
$GLOBALS['app']['charset'] = 'UTF-8';
