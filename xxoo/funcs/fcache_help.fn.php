<?php if( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	文件缓存辅助函数
  --------------------------------------------------------------
*/

require_once XXOO . 'libs/FileCache.lib.php';

function file_cache_get($k) {
    $fileCache = FileCache::getInstance();
    return $fileCache->getCache($k);
}

function file_cache_set($k, $v, $expire=3600) {
    $fileCache = FileCache::getInstance();
    $fileCache->setCache($k, $expire, $v);
}

function file_cache_del($k) {
    $fileCache = FileCache::getInstance();
    $fileCache->delete($k);
}