<?php

/*
  ---------------------------------------------------------------------------
	应用程序入口
  ---------------------------------------------------------------------------
*/

// 项目根目录路径
define('APP', dirname(__FILE__).'/');

// xxoo目录路径
define('XXOO', dirname(dirname(APP)).'/xxoo/');

// root路径
define('ROOT', dirname(XXOO).'/');

// 运行方式
define('MODE', 'rest');

// 载入框架引导文件
require(XXOO . 'http.boot.php');