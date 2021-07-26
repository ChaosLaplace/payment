<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/*
  ----------------------------------------------------------------------
  URL请求资源配置
  eg:
  'url' => array(
  'c'	=> 'front/Article', 		// 控制器文件名、类名
  'f'	=> 'show', 					// 方法名
  'p'	=> array(),					// 参数
  'h' => array(),					// 钩子
  );
  ----------------------------------------------------------------------
 */

$urls_default = array(

    'default'       => array('c'=>'App'),

    'test'          => array('c'=>'App','f'=>'test'),

    'login'         => array('c'=>'App', 'f'=>'login'),

    'mine/info'                 => array('c'=>'Mine', 'f'=>'info'),
    'mine/workplace'            => array('c'=>'Mine', 'f'=>'workplace'),
    'mine/basic_info'           => array('c'=>'Mine', 'f'=>'basicInfo'),
    'mine/change_password'      => array('c'=>'Mine', 'f'=>'changePassword'),
    'mine/change_name'          => array('c'=>'Mine', 'f'=>'changeName'),
    'mine/change_tel'           => array('c'=>'Mine', 'f'=>'changeTel'),
    'mine/change_mail'          => array('c'=>'Mine', 'f'=>'changeMail'),

    'quill/image'               => array('c'=>'Upload', 'f'=>'quillImage'),
    'widget/upload_image'       => array('c'=>'Upload', 'f'=>'image'),

);

$urls_auction   = include 'urls_auction.conf.php';
$urls_mall      = include 'urls_mall.conf.php';
$urls_member    = include 'urls_member.conf.php';
$urls_finance   = include 'urls_finance.conf.php';
$urls_operate   = include 'urls_operate.conf.php';
$urls_system    = include 'urls_system.conf.php';


return array_merge($urls_default, $urls_auction, $urls_mall, $urls_member, $urls_finance, $urls_operate, $urls_system);
