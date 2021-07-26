<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/ProductCategoryModel.php';

class CategoryControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select * from product_category order by id desc";
        $category_list = DB::getData($sql);

        if( empty($category_list) ) {
            $this->resp([
                'total_row'     => 0,
                'category_list' => []
            ]);
            return;
        }

        foreach($category_list as &$cat) {
            $cat['created'] = date('Y-m-d', strtotime($cat['created']));
        }

        $this->resp([
            'total_row'     => count($category_list),
            'category_list' => $category_list
        ]);
    }

    public function add() {
        $data = $this->__formData();

        ProductCategoryModel::add($data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        ProductCategoryModel::update($data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        ProductCategoryModel::delete($id);

        $this->resp(true);
    }

    public function isExistNameZh() {
        $this->isExist('product_category', 'name_zh');
    }

    public function isExistNameEn() {
        $this->isExist('product_category', 'name_en');
    }

    public function isExistNameLocal() {
        $this->isExist('product_category', 'name_local');
    }

    private function __formData() {
        $data = [
            'name_zh'       => input('nameZh'),
            'name_en'       => input('nameEn'),
            'name_local'    => input('nameLocal'),
        ];

        if( $icon = input('icon') ) {
            $data['icon'] = $icon;
        }

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        return $data;
    }
}


