<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class ProductOrderControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql_where = $this->__filter();

        $sql = "select count(o.id) from product_order o, product p, `user` u where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'  => 0,
                'order_list' => [],
            ]);
            return true;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $title_field = 'title_' . $GLOBALS['request']['lang'];
        $sql = "select o.id, o.order_sn, o.price, o.amount, o.postage, o.total_price, o.status, o.created, 
                    p.cover_photo, p.{$title_field} as title, u.nickname, u.country_code, u.phone 
                from product_order o, product p, `user` u 
                where {$sql_where} order by id desc {$page_info['limit']}";
        $order_list = DB::getData($sql);

        foreach($order_list as &$order) {
            $order['price'] = fen2yuan($order['price']);
            $order['total_price'] = fen2yuan($order['total_price']);
            $order['postage'] = fen2yuan($order['postage']);
        }

        $this->resp([
            'total_row'  => $total_row,
            'order_list' => $order_list
        ]);

    }

    public function show() {

    }

    /**
     * 发货
     */
    public function send() {
        $id = input('id');

        $data = [
            'send_company'  => input('sendCompany'),
            'send_order_sn' => input('sendOrderSn'),
            'is_send'       => 'Y',
            'send_time'     => time(),
        ];

        DB::update('product_order', $data, $id);

        $this->resp(true);
    }

    /**
     * 取消
     */
    public function cancel() {
        $id = input('id');

        $data = [
            'cancel_reason' => input('cancelReason'),
            'is_cancel'     => 'Y',
            'cancel_time'   => time(),
        ];

        DB::update('product_order', $data, $id);

        $this->resp(true);
    }

    private function __filter() {
        $factor_list = [
            ['s'=>'and o.product_id=p.id', 'f'=>true],
            ['s'=>'and o.user_id=u.id', 'f'=>true],
            ['s'=>"and p.status='$1'", 'f'=>input('status')],
        ];

        return sql_where($factor_list);
    }

}
