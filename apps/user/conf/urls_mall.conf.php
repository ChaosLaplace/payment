<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

return [
    'mall/category_list'                 => array('c'=>'mall/Category'),
    'mall/category_add'                  => array('c'=>'mall/Category', 'f'=>'add'),
    'mall/category_update'               => array('c'=>'mall/Category', 'f'=>'update'),
    'mall/category_delete'               => array('c'=>'mall/Category', 'f'=>'delete'),
    'mall/category_is_exist_name_zh'     => array('c'=>'mall/Category', 'f'=>'isExistNameZh'),
    'mall/category_is_exist_name_en'     => array('c'=>'mall/Category', 'f'=>'isExistNameEn'),
    'mall/category_is_exist_name_local'  => array('c'=>'mall/Category', 'f'=>'isExistNameLocal'),

    'mall/product_list'                  => array('c'=>'mall/Product'),
    'mall/product_init'                  => array('c'=>'mall/Product', 'f'=>'init'),
    'mall/product_add'                   => array('c'=>'mall/Product', 'f'=>'add'),
    'mall/product_update'                => array('c'=>'mall/Product', 'f'=>'update'),
    'mall/product_delete'                => array('c'=>'mall/Product', 'f'=>'delete'),
    'mall/product_item'                  => array('c'=>'mall/Product', 'f'=>'item'),
    'mall/product_change_online'         => array('c'=>'mall/Product', 'f'=>'changeOnline'),

    'mall/product_order_list'            => array('c'=>'mall/ProductOrder'),
    'mall/product_order_show'            => array('c'=>'mall/ProductOrder', 'f'=>'show'),
    'mall/product_order_send'            => array('c'=>'mall/ProductOrder', 'f'=>'send'),
    'mall/product_order_cancel'          => array('c'=>'mall/ProductOrder', 'f'=>'cancel'),

];
