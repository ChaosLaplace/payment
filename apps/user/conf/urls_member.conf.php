<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

return [
    'member/user_list'                       => array('c'=>'member/User'),
    'member/user_show'                       => array('c'=>'member/User', 'f'=>'show'),

    'member/grade_list'                 => array('c'=>'member/Grade'),
    'member/grade_add'                  => array('c'=>'member/Grade', 'f'=>'add'),
    'member/grade_update'               => array('c'=>'member/Grade', 'f'=>'update'),
    'member/grade_delete'               => array('c'=>'member/Grade', 'f'=>'delete'),

    'member/grade_order_list'           => array('c'=>'member/GradeOrder'),
    'member/grade_order_init'           => array('c'=>'member/GradeOrder', 'f'=>'init'),

    'member/account_list'               => array('c'=>'member/Account'),

    'member/message_list'               => array('c'=>'member/Message'),
];
