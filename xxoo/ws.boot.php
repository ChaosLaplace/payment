<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  ---------------------------------------------------------------------------
	GatewayWorker web socket引导文件

    @author marvin
  ---------------------------------------------------------------------------
*/

// 设置时区（中国）
date_default_timezone_set('PRC');

// 载入框架配置
require(XXOO . 'xxoo.conf.php');

// 载入公共配置
require(ROOT . 'conf/app.conf.php');

// 载入应用程序配置
require(APP . 'conf/app.conf.php');

// 定义框架启动引导方式
$GLOBALS['app']['boot'] = 'ws';

// 载入日志工具类
require(XXOO . 'libs/Log.lib.php');

// 载入框架基础函数库
require(XXOO . 'funcs/xxoo.fn.php');

// 载入公共函数库
require(ROOT . 'funcs/app.fn.php');

// 载入应用程序函数库
require(APP . 'funcs/app.fn.php');

// 载入框架数据库操作函数
require(XXOO . 'libs/DB.lib.php');

require(APP . 'Action.php');