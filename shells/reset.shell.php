<?php

/**
 * 重置
 * 每天凌晨12点执行
 *
 * @author marvin
 */

require_once dirname(__DIR__) . '/xxoo/shell.boot.php';

// 重置用户出价次数
$sql = "select id from `user` order by id desc limit";
$sql = "update `user` set surplus_bid_num=has_bid_num";