<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

require_once XXOO . 'libs/RedisCache.lib.php';

/**
 *
 * @param string $route auction:preview:1
 * @param int $time 过期时间，秒
 */
function redis_event_add($route, $time) {
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

        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    } catch(Exception $e) {
        trigger_error('Can\'t connect redis service ' . $e->getMessage(), E_USER_ERROR);
    }

    $redis->setex($route, $time, '1');
    return true;
}
