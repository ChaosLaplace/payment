<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

return [
    'auction/list'                          => array('c'=>'auction/Auction'),
    'auction/init'                          => array('c'=>'auction/Auction', 'f'=>'init'),
    'auction/add'                           => array('c'=>'auction/Auction', 'f'=>'add'),
    'auction/update'                        => array('c'=>'auction/Auction', 'f'=>'update'),
    'auction/delete'                        => array('c'=>'auction/Auction', 'f'=>'delete'),
    'auction/show'                          => array('c'=>'auction/Auction', 'f'=>'show'),
    'auction/item'                          => array('c'=>'auction/Auction', 'f'=>'item'),

    'auction/bond_list'                     => array('c'=>'auction/AuctionBond'),

    'auction/room_list'                     => array('c'=>'auction/Room'),
    'auction/room_init'                     => array('c'=>'auction/Room', 'f'=>'init'),
    'auction/room_add'                      => array('c'=>'auction/Room', 'f'=>'add'),
    'auction/room_update'                   => array('c'=>'auction/Room', 'f'=>'update'),
    'auction/room_delete'                   => array('c'=>'auction/Room', 'f'=>'delete'),
    'auction/room_is_exist_name_zh'         => array('c'=>'auction/Room', 'f'=>'isExistNameZh'),
    'auction/room_is_exist_name_en'         => array('c'=>'auction/Room', 'f'=>'isExistNameEn'),
    'auction/room_is_exist_name_local'      => array('c'=>'auction/Room', 'f'=>'isExistNameLocal'),

    'auction/category_list'                 => array('c'=>'auction/Category'),
    'auction/category_add'                  => array('c'=>'auction/Category', 'f'=>'add'),
    'auction/category_update'               => array('c'=>'auction/Category', 'f'=>'update'),
    'auction/category_delete'               => array('c'=>'auction/Category', 'f'=>'delete'),
    'auction/category_is_exist_name_zh'     => array('c'=>'auction/Category', 'f'=>'isExistNameZh'),
    'auction/category_is_exist_name_en'     => array('c'=>'auction/Category', 'f'=>'isExistNameEn'),
    'auction/category_is_exist_name_local'  => array('c'=>'auction/Category', 'f'=>'isExistNameLocal'),

    'auction/supplier_list'                 => array('c'=>'auction/Supplier'),
    'auction/supplier_item'                 => array('c'=>'auction/Supplier', 'f'=>'item'),
    'auction/supplier_add'                  => array('c'=>'auction/Supplier', 'f'=>'add'),
    'auction/supplier_update'               => array('c'=>'auction/Supplier', 'f'=>'update'),
    'auction/supplier_delete'               => array('c'=>'auction/Supplier', 'f'=>'delete'),
    'auction/supplier_is_exist_name_zh'     => array('c'=>'auction/Supplier', 'f'=>'isExistNameZh'),
    'auction/supplier_is_exist_name_en'     => array('c'=>'auction/Supplier', 'f'=>'isExistNameEn'),
    'auction/supplier_is_exist_name_local'  => array('c'=>'auction/Supplier', 'f'=>'isExistNameLocal'),

];
