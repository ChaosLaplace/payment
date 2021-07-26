<?php if (!defined('XXOO')) {exit('No direct script access allowed');}
/**
 * 系统管理模块url
 */
return array(

    'system/admin_list'                 => array('c'=>'system/AdminUser'),
    'system/admin_add'                  => array('c'=>'system/AdminUser', 'f'=>'add'),
    'system/admin_update'               => array('c'=>'system/AdminUser', 'f'=>'update'),
    'system/admin_delete'               => array('c'=>'system/AdminUser', 'f'=>'delete'),
    'system/admin_reset_password'       => array('c'=>'system/AdminUser', 'f'=>'resetPassword'),
    'system/admin_is_exist_username'    => array('c'=>'system/AdminUser', 'f'=>'isExistUsername'),
    'system/admin_is_exist_tel'         => array('c'=>'system/AdminUser', 'f'=>'isExistTel'),
    'system/admin_is_exist_mail'        => array('c'=>'system/AdminUser', 'f'=>'isExistMail'),

    'system/role_list'                  => array('c'=>'system/Role'),
    'system/role_add'                   => array('c'=>'system/Role', 'f'=>'add'),
    'system/role_update'                => array('c'=>'system/Role', 'f'=>'update'),
    'system/role_delete'                => array('c'=>'system/Role', 'f'=>'delete'),
    'system/role_is_exist_name'         => array('c'=>'system/Role', 'f'=>'isExistName'),
    'system/role_opts'                  => array('c'=>'system/Role', 'f'=>'opts'),
    'system/role_func_list'             => array('c'=>'system/Role', 'f'=>'funcList'),

    'system/func_tree'                  => array('c'=>'system/Func'),
    'system/func_item'                  => array('c'=>'system/Func', 'f'=>'item'),
    'system/func_add'                   => array('c'=>'system/Func', 'f'=>'add'),
    'system/func_update'                => array('c'=>'system/Func', 'f'=>'update'),
    'system/func_delete'                => array('c'=>'system/Func', 'f'=>'delete'),
    'system/func_name_is_exist'         => array('c'=>'system/Func', 'f'=>'isExistName'),
    'system/func_path_is_exist'         => array('c'=>'system/Func', 'f'=>'isExistPath'),

    'system/error_list'                 => array('c'=>'system/ErrorLog'),
    'system/error_show'                 => array('c'=>'system/ErrorLog', 'f'=>'show'),
    'system/error_delete'               => array('c'=>'system/ErrorLog', 'f'=>'delete'),
    'system/error_delete_list'          => array('c'=>'system/ErrorLog', 'f'=>'deleteList'),

    'system/app_version_list'           => array('c'=>'system/AppVersion'),
    'system/app_version_add'            => array('c'=>'system/AppVersion', 'f'=>'add'),
    'system/app_version_update'         => array('c'=>'system/AppVersion', 'f'=>'update'),
    'system/app_version_delete'         => array('c'=>'system/AppVersion', 'f'=>'delete'),
    'system/app_version_show'           => array('c'=>'system/AppVersion', 'f'=>'show'),

    'system/ad_space_list'              => array('c'=>'system/AdSpace'),
    'system/ad_space_add'               => array('c'=>'system/AdSpace', 'f'=>'add'),
    'system/ad_space_update'            => array('c'=>'system/AdSpace', 'f'=>'update'),
    'system/ad_space_delete'            => array('c'=>'system/AdSpace', 'f'=>'delete'),
    'system/ad_space_is_exist_ident'    => array('c'=>'system/AdSpace', 'f'=>'isExistIdent'),

);