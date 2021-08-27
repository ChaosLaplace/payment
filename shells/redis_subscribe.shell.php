<?php
/**
 * redis 过期事件监听脚本
 * 阻塞式运行，通过linux nohup在后台挂起执行
 */
ini_set('default_socket_timeout', -1);  // 不超时
require_once dirname(__DIR__) . '/xxoo/shell.boot.php';

if( !isset($GLOBALS['redis']['event']) ) {
    trigger_error('Can\'t find redis event conf in app.conf.php', E_USER_ERROR);
}

$conf = $GLOBALS['redis']['event'];

try {
    $redis = new Redis();
    $redis->connect($conf['host'], $conf['port']);

    if( isset($conf['auth']) && !empty($conf['auth']) ) {
        $redis->auth($conf['auth']);
    }

    $redis->select($conf['db']);

    // 设置永不超时
    $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);
}
catch(Exception $e) {
    trigger_error('Can\'t connect redis service ' . $e->getMessage(), E_USER_ERROR);
}

try {
    Log::error("1");
    echo '1';
    $redis->psubscribe(array('__keyevent@' . $conf['db'] . '__:expired'), 'expire_callback');
}
catch(Exception $e) {
    Log::error("2");
    echo '2' . $e;
    trigger_error('Can\'t connect redis service ' . $e->getMessage(), E_USER_ERROR);
}
Log::error("3");
echo '3';

function expire_callback($redis, $pattern, $channel, $msg) {
    Log::error("4");
    // Log::error("expire_callback start");

    // $route = explode(':', $msg);

    // Log::error("route -> {$route}");

    // if( count($route) < 3 ) {
    //     Log::error("Redis subscribe route format error: {$msg}");
    //     trigger_error("Redis subscribe route format error: {$msg}", E_USER_ERROR);
    // }

    // $__class = ucfirst($route[0]) . 'Model';
    // $__method = $route[1];
    // $__params = array_slice($route, 2);

    // $model_file = ROOT . "models/{$__class}.php";
    // if( !is_file( $model_file ) ) {
    //     Log::error("Can\'t find root model file: {$model_file}");
    //     trigger_error("Can\'t find root model file: {$model_file}", E_USER_ERROR);
    // }

    // require_once($model_file);

    // if( !class_exists( $__class ) ) {
    //     Log::error("Can\'t find class: {$__class}");
    //     trigger_error("Can\'t find class: {$__class}", E_USER_ERROR);
    // }

    // if( !method_exists( $__class, $__method ) ) {
    //     Log::error("Can\'t find method: {$__method} in {$__class}");
    //     trigger_error("Can\'t find method: {$__method} in {$__class}", E_USER_ERROR);
    // }

    // call_user_func_array(array($__class, $__method), $__params);
}
