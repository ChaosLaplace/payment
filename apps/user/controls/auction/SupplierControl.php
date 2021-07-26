<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class SupplierControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select * from auction_supplier order by id desc";
        $supplier_list = DB::getData($sql);

        if( empty($supplier_list) ) {
            $this->resp([
                'total_row'     => 0,
                'supplier_list' => []
            ]);
            return;
        }

        foreach($supplier_list as &$supplier) {
            $supplier['created'] = date('Y-m-d', strtotime($supplier['created']));
        }

        $this->resp([
            'total_row'     => count($supplier_list),
            'supplier_list' => $supplier_list
        ]);
    }

    public function item() {
        $id = input('id');

        $sql = "select * from auction_supplier where id=?i";
        $item = DB::getLine($sql, [$id]);

        $item['logo_url'] = img_url($item['logo']);

        $this->resp([
            'item' => $item
        ]);
    }

    public function add() {
        $data = $this->__formData();

        DB::insert('auction_supplier', $data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        DB::update('auction_supplier', $data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from auction_supplier where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function isExistNameZh() {
        $this->isExist('auction_supplier', 'name_zh');
    }

    public function isExistNameEn() {
        $this->isExist('auction_supplier', 'name_en');
    }

    public function isExistNameLocal() {
        $this->isExist('auction_supplier', 'name_local');
    }

    private function __formData() {
        $data = [
            'name_zh'       => input('nameZh'),
            'name_en'       => input('nameEn'),
            'name_local'    => input('nameLocal'),
            'logo'          => input('logo'),
            'intro_zh'      => input('introZh'),
            'intro_en'      => input('introEn'),
            'intro_local'   => input('introLocal'),
        ];

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        return $data;
    }

}

