<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  ----------------------------------------------------------------------
	框架web引导程序
	加载应用程序配置、基础类库，实现整个框架流程

	@author marvin
  ----------------------------------------------------------------------
*/

// 目录分隔符
define('DS', DIRECTORY_SEPARATOR);

// 设置时区（中国）
date_default_timezone_set('PRC');

// 载入框架配置
require(XXOO.'xxoo.conf.php');

// 载入公共配置
require(ROOT.'conf/app.conf.php');

// 载入应用程序配置
require(APP.'conf/app.conf.php');

// 定义框架启动引导方式
$GLOBALS['app']['boot'] = 'http';

// 载入日志工具类
require(XXOO.'libs/Log.lib.php');

// 如果当前环境是维护状态，则返回503状态码，并显示正在维护页面
if($GLOBALS['app']['env'] === 'servicing'){
    header('HTTP/1.1 503 Service Unavailable');
    include(XXOO.'errors/503.php');
    exit;
}

// 关闭错误输出，由xxoo框架处理php错误
error_reporting(0);

// 设置http响应头字符编码
header("Content-Type:text/html;charset={$GLOBALS['app']['charset']}");

// 载入框架核心函数
require(XXOO.'funcs/xxoo.fn.php');

// 設置異常處理器
// set_exception_handler('show_exception');

// 设置错误处理器，自定义错误处理
set_error_handler('show_error');

// 捕获致命错误
register_shutdown_function('fetch_fatal_error');

// 框架函数
require(XXOO.'funcs/app.fn.php');

// 载入公共函数库
require(ROOT.'funcs/app.fn.php');

// 载入应用程序函数库
require(APP.'funcs/app.fn.php');

// 载入框架数据库操作函数
require(XXOO.'libs/DB.lib.php');

// 如果用户没有设置site_domain，则自动配置生成site_domain
if(!isset($GLOBALS['app']['site_domain'])) {
    $GLOBALS['app']['site_domain'] = get_site_domain();
}

// 获取当前请求URL
$GLOBALS['request']['url'] = get_current_url();

// 获取请求URI
$GLOBALS['request']['uri'] = get_current_uri();

// 解析URI，如果不存在则响应404
if(!parse_uri($GLOBALS['request']['uri'])) {
    Log::error('404 url不存在');
    show_404();
}

// 如果在url中定义了页面缓存时间，同时为产品状态，则直接读取缓存
if(isset($GLOBALS['request']['page_cache']) && $GLOBALS['app']['env']=='production') {
    page_cache($GLOBALS['request']['url'], $GLOBALS['request']['page_cache']);
}

// 挂载钩子
mount_hooks($GLOBALS['request']['uri']);

// 载入基类，强制定义
require_once APP . 'controls/Control.php';

// Controller预处理
$__cont_file = APP . $GLOBALS['request']['file'];
if( !is_file( $__cont_file ) ) {
    Log::error("Can\'t find controller file: {$__cont_file}");
    trigger_error("Can\'t find controller file: {$__cont_file}", E_USER_ERROR);
}
require($__cont_file);

$__class_name = $GLOBALS['request']['class'];
if( !class_exists( $__class_name ) ) {
    Log::error("Can\'t find class: {$__class_name}");
    trigger_error("Can\'t find class: {$__class_name}", E_USER_ERROR);
}

$__xxoo = new $__class_name();

$__method_name = $GLOBALS['request']['fn'];
if( !method_exists( $__xxoo, $__method_name ) ) {
    Log::error("Can\'t find method: {$__method_name}");
    trigger_error("Can\'t find method: {$__method_name}", E_USER_ERROR);
}

$__params = isset($GLOBALS['request']['params']) ? $GLOBALS['request']['params'] : array();

// 执行action 前置钩子
run_func_coll('pre_control');

// 执行action()方法
call_user_func_array(array($__xxoo, $__method_name), $__params);

// 执行action 后置钩子
run_func_coll('post_control');

exit;