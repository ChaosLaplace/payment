<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

$urls = array(
    'test'                       => array('c'=>'Test', 'f'=>'test'),

    'user'                       => array('c'=>'User'),
    'user/login'                 => array('c'=>'User', 'f'=>'login'),
    'user/register'              => array('c'=>'User', 'f'=>'register'),
    'user/google/auth'           => array('c'=>'User', 'f'=>'googleAuth'),
);

return $urls;

// 多國語系
// $new_urls = [];
// foreach($urls as $k => $v) {
//     $k = '(zh|en|vi)/' . $k;
//     $new_urls[$k] = $v;
// }
// return $new_urls;
