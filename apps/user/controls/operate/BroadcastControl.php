<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class BroadcastControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $factor_list = [

        ];
        $sql_where = sql_where($factor_list);

        $sql = "select count(id) from broadcast";
        $total_row = DB::getVar($sql);

        if( $total_row <= 0 ) {
            $this->resp([
                'total_row'      => 0,
                'broadcast_list' => []
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select * from broadcast where {$sql_where} order by id desc {$page_info['limit']}";
        $broadcast_list = DB::getData($sql);

        foreach($broadcast_list as &$b) {
            $b['start_time'] = $b['start_time'] * 1000;
            $b['start_end'] = $b['end_time'] * 1000;
            $b['start_time_format'] = date('Y-m-d H:i:s', $b['start_time']);
            $b['end_time_format'] = date('Y-m-d H:i:s', $b['end_time']);
        }

        $this->resp([
            'total_row'      => $total_row,
            'broadcast_list' => $broadcast_list,
        ]);
    }

    public function add() {
        $data = $this->__formData();
        $data['creater_id'] = $this->adminId;
        $data['create_time'] = time();

        DB::insert('broadcast', $data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();
        $data['updater_id'] = $this->adminId;
        $data['update_time'] = time();

        DB::update('broadcast', $data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from broadcast where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function __formData() {
        $data = [
            'message_zh'    => input('messageZh'),
            'message_en'    => input('messageEn'),
            'message_local' => input('messageLocal'),
            'start_time'    => input('startTime'),
            'end_time'      => input('endTime'),
        ];

        $data['type'] = 'MANUAL';   // 手动
        $data['play_num'] = ($play_num = input('playNum')) ? $play_num : -1;

        return $data;
    }

}