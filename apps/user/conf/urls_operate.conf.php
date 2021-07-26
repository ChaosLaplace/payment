<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

return [
    'operate/article_list'              => array('c'=>'operate/Art'),
    'operate/article_add'               => array('c'=>'operate/Art', 'f'=>'add'),
    'operate/article_update'            => array('c'=>'operate/Art', 'f'=>'update'),
    'operate/article_delete'            => array('c'=>'operate/Art', 'f'=>'delete'),
    'operate/article_show'              => array('c'=>'operate/Art', 'f'=>'show'),
    'operate/article_is_exist_ident'    => array('c'=>'operate/Art', 'f'=>'isExistIdent'),

    'operate/broadcast_list'            => array('c'=>'operate/Broadcast'),
    'operate/broadcast_add'             => array('c'=>'operate/Broadcast', 'f'=>'add'),
    'operate/broadcast_update'          => array('c'=>'operate/Broadcast', 'f'=>'update'),
    'operate/broadcast_delete'          => array('c'=>'operate/Broadcast', 'f'=>'delete'),
    'operate/broadcast_show'            => array('c'=>'operate/Broadcast', 'f'=>'show'),

    'operate/ad_list'                   => array('c'=>'operate/Ad'),
    'operate/ad_update'                 => array('c'=>'operate/Ad', 'f'=>'update'),
    'operate/ad_stop'                   => array('c'=>'operate/Ad', 'f'=>'stop'),

];
