<?php

/*
  ---------------------------------------------------------------------------
	shell脚本引导文件

    @author marvin
  ---------------------------------------------------------------------------
*/

// 设置时区（中国）
date_default_timezone_set('PRC');

// 项目根目录路径
define('ROOT', dirname(__DIR__).'/');

// xxoo目录路径
define('XXOO', ROOT.'xxoo/');

// 载入框架配置
require(XXOO.'xxoo.conf.php');

// 载入公共配置
require(ROOT . 'conf/app.conf.php');

// 载入框架基础函数库
require(XXOO.'funcs/xxoo.fn.php');

// 定义框架启动引导方式
$GLOBALS['app']['boot'] = 'shell';

// 载入日志工具类
require(XXOO.'libs/Log.lib.php');

// 关闭错误输出，由xxoo框架处理php错误
error_reporting(0);

// 设置错误处理器，自定义错误处理
set_error_handler('show_error');

// 捕获致命错误
register_shutdown_function('fetch_fatal_error');

// 载入框架数据库操作函数
require(XXOO.'libs/DB.lib.php');

// 载入公共函数库
require(ROOT.'funcs/app.fn.php');

// 载入框架函数库
require(XXOO.'funcs/app.fn.php');


