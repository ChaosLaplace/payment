<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/*
  ----------------------------------------------------------------------
  URL请求资源配置
  eg:
  'regx' => array(
  	'weld'	=> 'pre_control', 		// 挂钩点
  	'file'	=> 'xxx.hook.php', 		// 钩子文件
  	'fn'	=> 'func',				// 钩子函数名
  );
  ----------------------------------------------------------------------
 */

return array(
     '.*' => array(
         'weld'  => 'pre_control',
         'fn'    => 'intercept'
     )
);
