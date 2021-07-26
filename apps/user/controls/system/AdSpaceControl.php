<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/AdSpaceModel.php';

class AdSpaceControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select * from ad_space order by id desc";
        $ad_space_list = DB::getData($sql);

        if( empty($ad_space_list) ) {
            $this->resp([
                'total_row' => 0,
                'ad_space_list' => []
            ]);
            return;
        }

        foreach($ad_space_list as &$ad_space) {
            $ad_space['created'] = date('Y-m-d', strtotime($ad_space['created']));
        }

        $this->resp([
            'total_row' => count($ad_space_list),
            'ad_space_list' => $ad_space_list,
        ]);
    }

    public function add() {
        $data = $this->__formData();

        AdSpaceModel::add($data);

        $this->resp(true);
    }

    public function update() {
        $id = $this->__formData();
        $data = $this->__formData();

        AdSpaceModel::update($id, $data);

        $this->resp(true);
    }

    public function delete() {
        $id = $this->__formData();

        AdSpaceModel::delete($id);

        $this->resp(true);
    }

    public function isExistIdent() {
        $this->isExist('ad_space', 'ident');
    }

    public function __formData() {
        $data = [
            'name'          => input('name'),
            'ident'         => input('ident'),
            'type'          => input('type'),
            'width'         => input('width'),
            'height'        => input('height'),
            'materiel_num'  => input('materielNum'),
        ];

        if( $remark = input('remark') ) {
            $data['remark'] = $remark;
        }

        return $data;
    }

}
