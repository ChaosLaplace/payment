<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/GradeModel.php';
require_once ROOT . 'models/AuctionRoomModel.php';

class RoomControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function init() {
        $subscript_opts = [];
        foreach($GLOBALS['app']['room_subscript'] as $subscript) {
            $subscript_opts[] = [
                'k' => $subscript['id'],
                'v' => $subscript['name_zh'],
            ];
        }

        $data = [
            'grade_opts'     => GradeModel::opts(),
            'subscript_opts' => $subscript_opts
        ];

        $this->resp($data);
    }

    public function index() {
        $sql = "select * from auction_room order by id desc";
        $room_list = DB::getData($sql);

        if( empty($room_list) ) {
            $this->resp([
                'total_row' => 0,
                'room_list' => []
            ]);
            return;
        }

        foreach($room_list as &$room) {
            $room['created'] = date('Y-m-d', strtotime($room['created']));
        }

        $this->resp([
            'total_row' => count($room_list),
            'room_list' => $room_list,
        ]);
    }

    public function add() {
        $data = $this->__formData();

        AuctionRoomModel::add($data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        AuctionRoomModel::update($data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        AuctionRoomModel::delete($id);

        $this->resp(true);
    }

    public function isExistNameZh() {
        $this->isExist('auction_room', 'name_zh');
    }

    public function isExistNameEn() {
        $this->isExist('auction_room', 'name_en');
    }

    public function isExistNameLocal() {
        $this->isExist('auction_room', 'name_local');
    }

    private function __formData() {
        $data = [
            'name_zh'       => input('nameZh'),
            'name_en'       => input('nameEn'),
            'name_local'    => input('nameLocal'),
            'icon'          => input('icon'),
        ];

        if( $subscript = input('subscript') ) {
            $data['subscript'] = $subscript;
        }

        if( $seq = input('seq') ) {
            $data['seq'] = $seq;
        }

        return $data;
    }

}

