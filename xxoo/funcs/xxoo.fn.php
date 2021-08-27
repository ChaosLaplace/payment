<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  ---------------------------------------------------------------
	xxoo框架基础函数库

  	@author marvin
  ---------------------------------------------------------------
*/

/**
 * 異常處理函數
 */
function show_exception($exception) {
    echo '<b>Exception:</b>' , $exception->getMessage();
    exit;
}

/**
 * 错误处理函数，用自定义错误处理函数，接管PHP错误处理
 * @param int $error_num
 * @param string $message
 * @param string $file_path
 * @param string $line
 */
function show_error($error_num, $message='', $file_path='', $line='') {
    $position = '';
    if( !empty($file_path) && !empty($line) ) {
        $position = "Error on line $line in $file_path";
    }

    if( $GLOBALS['app']['boot'] == 'http' ) {
        if( $GLOBALS['app']['env'] == 'development' ) {
            //ob_clean();
            include XXOO . 'errors/50x.php';
        }
        else {
            header("HTTP/1.0 500 Service internal error");
            include APP . 'errors/50x.php';
        }
    }

    Log::error([$message, $position]);

    exit;
}

/**
 * 注册shutdown函数，捕获至命错误
 */
function fetch_fatal_error() {
    $error = error_get_last();
    if( !empty($error) ) {
        show_error($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

/**
 * 如果用户设置了自动转义，取消自动转义
 */
function transcribe() {
    /*
    // magic_quotes_gpc can't be turned off
    if( !function_exists('get_magic_quotes_gpc') ) {
        return;
    }

    if( get_magic_quotes_gpc() ) {
        for($i = 0, $_SG = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST), $c = count($_SG); $i < $c; ++$i) {
            $_SG[$i] = array_map('stripslashes', $_SG[$i]);
        }
    }
    */
}


/**
 * 显示404页面
 */
function show_404() {
    header('HTTP/1.1 404 Not Found');

    if( $GLOBALS['app']['env'] == 'development' ) {
        $url = $GLOBALS['request']['url'];
        include XXOO . 'errors/404.php';
    }
    else {
        include APP.'errors/404.php';
    }
    exit;
}


/**
 * 解析URI
 * 根据URI获取请求所需的类、方法、参数、拦截器等信息
 * @param string $uri
 * @return bool
 */
function parse_uri($uri) {
    $urls_file = APP.'conf/urls.conf.php';
    if( !file_exists($urls_file) ) {
        trigger_error("Can\'t find file - {$urls_file}");
    }

    $urls = include($urls_file);	// 载入URL配置
    if( !isset( $urls ) || !is_array( $urls ) ) {
        trigger_error("Invalid urls config in {$urls_file}");
    }

    foreach($urls as $pattern=>$c) {
        if( preg_match("#^{$pattern}$#", $uri, $matches) ) {
            $GLOBALS['request']['pattern'] = $pattern;
            // URL中携带的参数
            //$matches = array_slice($matches, 1);
            $GLOBALS['request']['params'] = array();
            if( isset( $matches[1] ) ) {
                $GLOBALS['request']['params'] = array_slice($matches, 1);
            }

            // 额外参数
            if( isset( $c['p'] ) ) {
                $GLOBALS['request']['params'] = array_merge( $GLOBALS['request']['params'], $c['p'] );
            }

            // 控制器
            if( isset( $c['c'] ) ) {
                $GLOBALS['request']['file'] = 'controls/'. $c['c'] . 'Control.php';
                $cname = explode('/', $c['c']);
                $GLOBALS['request']['class'] = end( $cname ) . 'Control';
            }

            // action方法
            $GLOBALS['request']['fn'] = isset( $c['f'] ) ? $c['f'] : 'index';

            // 钩子
            if( isset( $c['h'] ) ) {
                if( isset($c['h']['weld']) ) {	// 单个钩子
                    mount_hook( $c['h'] );
                }
                else {	// 多个钩子
                    foreach( $c['h'] as $hc ) {
                        mount_hook( $hc );
                    }
                }
            }

            // 页面缓存 disk
            if( isset( $c['s'] ) ) {
                $GLOBALS['request']['page_cache'] = intval($c['s']);
            }
            return true;
        }
    }

    return false;
}

/**
 * 挂载一个钩子
 * @param array $hc
 * @param array $params
 */
function mount_hook( $hc, $params=array() ) {

    // 配置文件中的参数
    if( isset($hc['p']) ) {
        $params = array_merge($params, $hc['p']);
    }

    // 执行顺序
    $seq = 0;
    if( isset($hc['seq']) ) {
        $seq = $hc['seq'];
    }

    add_func_coll($hc['weld'], $hc['fn'], $params, $seq);
}

/**
 * 挂载钩子集
 * @param string $uri
 */
function mount_hooks($uri) {
    $hook_conf_file =  APP.'conf/hooks.conf.php';
    if( !file_exists($hook_conf_file) ) trigger_error("Can\'t find file - {$hook_conf_file}");
    $hooks = include( $hook_conf_file );	// 载入URL配置
    if( !isset($hooks) || !is_array($hooks) ) trigger_error('Invalid hooks config');

    foreach( $hooks as $pattern=>$hc ) {
        if( preg_match( "#^$pattern$#", $uri, $matches ) ) {
            $params = array();
            if( isset( $matches[1] ) ) {
                $params = array_slice($matches, 1);
            }
            mount_hook($hc, $params);
        }
    }
}

/**
 * 按降序排列钩子，usort函数的回调函数
 * @param $a
 * @param $b
 * @return int
 */
function sort_func_coll($a, $b) {
    if ($a['seq'] == $b['seq']) return 0;
    return $a['seq'] < $b['seq'] ? 1 : -1;	// 按降序排列
}

/**
 * 添加欲执行函数到函数集
 * @param string $weld
 * @param string $fn
 * @param array $params
 */
function add_func_coll( $weld, $fn, $params = array(), $seq=0 ) {
    if( empty( $GLOBALS['__sc_func_coll'] ) ) {
        $GLOBALS['__sc_func_coll'] = array();
    }
    if( empty( $GLOBALS['__sc_func_coll'][$weld] ) ) {
        $GLOBALS['__sc_func_coll'][$weld] = array();
    }
    array_push( $GLOBALS['__sc_func_coll'][$weld], array('fn'=>$fn, 'params'=>$params, 'seq'=>$seq) );
}

/**
 * 执行函数集
 * @param string $weld
 */
function run_func_coll( $weld ) {
    if( empty( $GLOBALS['__sc_func_coll'][$weld] ) || ! is_array( $GLOBALS['__sc_func_coll'][$weld] ) ) {
        return FALSE;
    }

    // 执行函数集
    $hooks = $GLOBALS['__sc_func_coll'][$weld];

    if( empty($hooks) ) return;

    usort($hooks, 'sort_func_coll');	// 排序要执行的钩子

    foreach( $hooks as $h ) {
        call_user_func_array( $h['fn'], $h['params'] );
    }
    return TRUE;
}

/**
 * 获取site_domain
 * @return string
 */
function get_site_domain() {
    $http_protocol = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https://' : 'http://';
    $site_domain = rtrim( $http_protocol . $_SERVER ['HTTP_HOST'], '/' ) . '/';
    $__droot = str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] );
    $__root  = str_replace( '\\', '/', APP );
    $__root  = trim( str_replace( $__droot, '', $__root ), '/' );
    return $site_domain . $__root;
}

/**
 * 获取当前url
 * @return string
 */
function get_current_url() {
    $http_protocol = ( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https://' : 'http://';
    return $http_protocol . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * 获取当前uri
 */
function get_current_uri() {
    $uri = 'default';
    if( $GLOBALS['app']['protocol'] == 'PATH_INFO' ) { 	// pathinfo url
        $uri = !empty( $_SERVER['PATH_INFO'] ) ? trim( $_SERVER['PATH_INFO'], '/') : 'default';
    }
    else {	// query string url
        $uri = !empty( $_GET['p'] ) ? $_GET['p'] : 'default';
    }
    return $uri;
}

if(!function_exists('lang')) {
    /**
     * 读取语言文件
     * 根据全局语言设置载入相应的语言文件
     * @param string $key
     * @return string
     */
    function lang($key) {
        static $language = array();
        if( empty( $language ) ) {
            $lang_file = APP . 'language/' .$GLOBALS['request']['lang'].'.lang.php';
            if( !file_exists( $lang_file ) ) show_error( 'Can\'t find file - ' . $lang_file );
            $lang = require_once( $lang_file );	// 载入URL配置
            if( !isset( $lang ) || !is_array( $lang ) ) show_error( 'Invalid language config in ' . $lang_file );
            $language = $lang;
        }

        if( isset( $language[$key] ) ) {
            return $language[$key];
        }
        else {
            show_error( $key .' not found in '. $GLOBALS['request']['lang'].'.lang.php' );
        }
    }
}

/**
 * 渲染视图
 * @param string $tpl 模板路径
 * @param array $data 视图数据
 * @param bool $is_return 是否返回渲染结果
 * @return void/string
 */
function render( $tpl = '', $data = array(), $is_return = FALSE ) {
    $_tpl = APP . 'views/' . $tpl . '.tpl.php';

    extract($data);

    ob_start();
    include($_tpl);
    $contents = ob_get_contents();
    ob_end_clean();

    if( $is_return ) {
        return $contents;
    }
    else {
        echo $contents;
    }
}

/**
 * 对象转数组,使用get_object_vars返回对象属性组成的数组
 * @param object $obj
 * @return array
 */
function object2array($obj) {
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr)){
        return array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

/**
 * 数组转对象
 * @param array $arr
 * @return object
 */
function array2object($arr) {
    if(is_array($arr)){
        return (object) array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

/**
 * 安全输出
 * 对输出字符串转义，避免xss攻击
 * htmlspecialchars的短版别名函数
 * @param mixd $out
 */
function secho( $output ) {
    echo htmlspecialchars( $output );
}

/**
 * 递规目录，如果目录不存在，创建目录
 * @param string $dir
 */
function mkdirs($dir) {
    if( !is_dir( $dir ) )	{
        if( ! mkdirs( dirname($dir) ) ) {
            return false;
        }
        if( ! mkdir($dir,0777) ) {
            return false;
        }
    }
    return true;
}

/**
 * 删除文件夹，包括子目录和文件
 * @param $dir
 */
function deldirs( $dir ){
    if ( $handle = opendir( "$dir" ) ) {
        while ( false !== ( $item = readdir( $handle ) ) ) {
            if ( $item != "." && $item != ".." ) {
                if ( is_dir( "$dir/$item" ) ) {
                    del_dirs( "$dir/$item" );
                } else {
                    @unlink( "$dir/$item" );
                }
            }
        }
        closedir( $handle );
    }
}


/**
 * 页面级文件缓存，即网页静态化
 * @param string $url
 * @param int $cache_time
 */
if( !function_exists('page_cache') ) {
    function page_cache($url, $cache_time) {
        // 如果缓存时间小于，就设置缓存时间为最长
        if( $cache_time == -1 ) {
            $cache_time = $GLOBALS['app']['page_cache_max_len'];
        }

        $fileCache = SimpleFileCache::getInstance( page_cache_path($url) );

        // 如果缓存存在，直接读取缓存输出
        // 如果不存在或是已过期，则注册一个shutdown函数，然后开启输出缓冲区
        if( $content = $fileCache->cache($url) ) {
            echo $content;
            exit;
        } else {
            register_shutdown_function('page_cache_write', $url, $cache_time);
            ob_end_clean();
            ob_start();
        }
    }
}

/**
 * 写入页面缓存
 * @param $url
 * @param $cache_time
 */
function page_cache_write($url, $cache_time) {
    $fileCache = SimpleFileCache::getInstance( page_cache_path($url) );
    $content = ob_get_contents();
    ob_end_clean();

    $fileCache->setCache($url, $cache_time, $content);
    echo $content;
}

/**
 * 获取页面缓存路径
 * @param $url
 * @return string
 */
function page_cache_path( $url ) {
    $file_name_int = crc32( md5($url) );
    $file_name_int = abs( $file_name_int );
    return $GLOBALS['app']['page_cache']
    .($file_name_int % $GLOBALS['app']['page_cache_spread']).'/'
    .($file_name_int % $GLOBALS['app']['page_cache_spread'] * 2).'/';
}

/**
 * 页面组件，直接在视图层调用执行
 */
function widget() {
    $params_num = func_num_args();

    // 未传递任何参数
    if( $params_num <= 0 ) {
        trigger_error( "At least one parameter is required in widget() function", E_USER_ERROR );
    }

    $widget_path = '';
    $params = array();
    for($i=0; $i<$params_num; $i++) {
        if($i == 0) {
            $widget_path = func_get_arg($i);
        }
        else {
            $params[] = func_get_arg($i);
        }
    }

    $widget_info = explode('.', $widget_path);

    // 传入的组件格式信息不合法
    if( !isset($widget_info[0]) || !isset($widget_info[1]) ) {
        trigger_error( "Illegal widget info format: {$widget_path}", E_USER_ERROR );
    }

    $widget_file        = APP . "widgets/{$widget_info[0]}Widget.php";  // 组件文件路径
    $widget_class_name  = $widget_info[0] . 'Widget';                   // 组件类名
    $widget_method_name = $widget_info[1];                              // 组件方法名

    // 检测组件文件是否存在
    if( !is_file($widget_file) ) {
        trigger_error( "Can\'t find widget file: {$widget_file}", E_USER_ERROR );
    }
    require_once($widget_file);


    // 检测组件类是否存在
    if( !class_exists( $widget_class_name ) ) {
        trigger_error( "Can\'t find widget class:{$widget_class_name}", E_USER_ERROR );
    }

    $widget_class = new $widget_class_name();   // 实例化组件对象

    // 检测组件方法是否存在
    if( !method_exists( $widget_class, $widget_method_name ) ) {
        trigger_error( "Can\'t find widget method: {$widget_method_name} in {$widget_class_name} class", E_USER_ERROR );
    }

    // 执行组件
    call_user_func_array( array($widget_class, $widget_method_name), $params );

}

