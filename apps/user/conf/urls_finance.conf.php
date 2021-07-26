<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

return [
    'finance/recharge_list'                          => array('c'=>'finance/Recharge'),
    'finance/recharge_agree'                         => array('c'=>'finance/Recharge', 'f'=>'agree'),
    'finance/recharge_refuse'                        => array('c'=>'finance/Recharge', 'f'=>'refuse'),
    'finance/recharge_is_exist_trade_sn'             => array('c'=>'finance/Recharge', 'f'=>'isExistTradeSn'),

    'finance/exchange_list'                          => array('c'=>'finance/Exchange'),
    'finance/exchange_agree_init'                    => array('c'=>'finance/Exchange', 'f'=>'agreeInit'),
    'finance/exchange_agree'                         => array('c'=>'finance/Exchange', 'f'=>'agree'),
    'finance/exchange_refuse'                        => array('c'=>'finance/Exchange', 'f'=>'refuse'),
    'finance/exchange_is_exist_trade_sn'             => array('c'=>'finance/Exchange', 'f'=>'isExistTradeSn'),
];
