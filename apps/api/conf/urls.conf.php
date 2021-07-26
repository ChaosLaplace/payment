<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

$urls = array(
    'test'                          => array('c'=>'Test', 'f'=>'test'),

    'login'                         => array('c'=>'App', 'f'=>'login'),
    'logout'                        => array('c'=>'App', 'f'=>'logout'),
    'register'                      => array('c'=>'App', 'f'=>'register'),
    'upgrade'                       => array('c'=>'App', 'f'=>'upgrade'),

    'ad/show'                       => array('c'=>'Ad', 'f'=>'show'),

    'article/show'                  => array('c'=>'Article', 'f'=>'show'),

    'broadcast/list'                => array('c'=>'Broadcast'),

    'auction/list'                  => array('c'=>'Auction',),
    'auction/search'                => array('c'=>'Auction', 'f'=>'search'),
    'auction/show'                  => array('c'=>'Auction', 'f'=>'show'),
    'auction/recommend'             => array('c'=>'Auction', 'f'=>'recommend'),
    'auction/reserve'               => array('c'=>'Auction', 'f'=>'reserve'),
    'auction/room/list'             => array('c'=>'Auction', 'f'=>'roomList'),
    'auction/category/list'         => array('c'=>'Auction', 'f'=>'categoryList'),

    'u/auction/bond'                => array('c'=>'Auction', 'f'=>'bond'),

    'product/list'                  => array('c'=>'Product'),
    'product/show'                  => array('c'=>'Product', 'f'=>'show'),
    'product/category/list'         => array('c'=>'Product', 'f'=>'categoryList'),
    'product/buy'                   => array('c'=>'Product', 'f'=>'buy'),
    'product/status'                => array('c'=>'Product', 'f'=>'status'),
    
    'u/dashboard'                   => array('c'=>'uc/U'),

    'u/account/recharge'            => array('c'=>'uc/Account', 'f'=>'recharge'),
    'u/account/recharge_pro'        => array('c'=>'uc/Account', 'f'=>'rechargePro'),
    'u/account/exchange'            => array('c'=>'uc/Account', 'f'=>'exchange'),
    'u/account/exchange_pro'        => array('c'=>'uc/Account', 'f'=>'exchangePro'),

    'u/friend/show'                 => array('c'=>'uc/Friend', 'f'=>'show'),
    'u/friend/info'                 => array('c'=>'uc/Friend', 'f'=>'info'),

    'u/grade/showcase'              => array('c'=>'uc/Grade', 'f'=>'showcase'),
    'u/grade/buy'                   => array('c'=>'uc/Grade', 'f'=>'buy'),

    'u/user/nickname'               => array('c'=>'uc/User', 'f'=>'nickname'),
    'u/user/nickname_pro'           => array('c'=>'uc/User', 'f'=>'nicknamePro'),

    'u/user/phone'                  => array('c'=>'uc/User', 'f'=>'phone'),
    'u/user/phone_pro'              => array('c'=>'uc/User', 'f'=>'phonePro'),

    'u/user/bind_bank'              => array('c'=>'uc/User', 'f'=>'bindBank'),
    'u/user/bind_bank_pro'          => array('c'=>'uc/User', 'f'=>'bindBankPro'),

    'u/user/real_identity'          => array('c'=>'uc/User', 'f'=>'realIdentity'),
    'u/user/real_identity_pro'      => array('c'=>'uc/User', 'f'=>'realIdentityPro'),

    'u/user/receipt_info'           => array('c'=>'uc/User', 'f'=>'receiptInfo'),
    'u/user/receipt_update'         => array('c'=>'uc/User', 'f'=>'receiptUpdate'),
    'u/user/receipt_delete'         => array('c'=>'uc/User', 'f'=>'receiptDelete'),
    'u/user/receipt_set_default'    => array('c'=>'uc/User', 'f'=>'receiptSetDefault'),

    'u/user/pay_password'           => array('c'=>'uc/User', 'f'=>'payPassword'),
    'u/user/pay_password_pro'       => array('c'=>'uc/User', 'f'=>'payPasswordPro'),

    'u/user/login_password'         => array('c'=>'uc/User', 'f'=>'loginPassword'),
    'u/user/login_password_pro'     => array('c'=>'uc/User', 'f'=>'loginPasswordPro'),

    'u/user/invite_code'            => array('c'=>'uc/User', 'f'=>'inviteCode'),

    'u/user/upload_avatar'          => array('c'=>'uc/User', 'f'=>'uploadAvatar'),
);

$new_urls = [];
foreach($urls as $k => $v) {
    $k = '(zh|en|vi)/' . $k;
    $new_urls[$k] = $v;
}
return $new_urls;
