<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/GradeModel.php';

class GradeControl extends Control{

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select * from grade order by id desc";
        $grade_list = DB::getData($sql);

        if( empty($grade_list) ) {
            $this->resp([
                'total_row'  => 0,
                'grade_list' => []
            ]);
            return;
        }

        foreach($grade_list as &$grade) {
            $grade['price'] = fen2yuan($grade['price']);
            $grade['created'] = date('Y-m-d', strtotime($grade['created']));
        }

        $this->resp([
            'total_row'  => count($grade_list),
            'grade_list' => $grade_list
        ]);
    }

    public function add() {
        $data = $this->__formData();

        GradeModel::add($data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        GradeModel::update($data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {

    }

    private function __formData() {
        $data = [
            'name_zh'        => input('nameZh'),
            'name_en'        => input('nameEn'),
            'name_local'     => input('nameLocal'),
            'icon'           => input('icon'),
            'grade'          => input('grade'),
            'bid_num'        => input('bidNum'),
            'bid_share'      => input('bidShare'),
            'transfer_share' => input('transferShare'),
            'buy_vip_share'  => input('buyVipShare'),
        ];

        if($price = input('price')) {
            $data['price'] = yuan2fen($price);
        }

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        return $data;
    }

}
