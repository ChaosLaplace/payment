<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/AppVersionModel.php';

class AppVersionControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $factor_list = [

        ];
        $sql_where = sql_where($factor_list);

        $sql = "select count(id) from app_version";
        $total_row = DB::getVar($sql);

        if( $total_row <= 0 ) {
            $this->resp([
                'total_row'      => 0,
                'version_list' => []
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select * from app_version where {$sql_where} order by id desc {$page_info['limit']}";
        $version_list = DB::getData($sql);

        foreach($version_list as &$v) {
            $v['created'] = date('Y-m-d H:i', strtotime($v['created']));
        }

        $this->resp([
            'total_row'    => $total_row,
            'version_list' => $version_list,
        ]);
    }

    public function add() {
        $data = $this->__formData();
        $num = AppVersionModel::nextNum($data['terminal']);
        $data['num'] = $num;

        DB::insert('app_version', $data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');
        $data = $this->__formData();

        DB::update('app_version', $data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from app_version where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function show() {
        $id = input('id');

        $sql = "select * from app_version where id=?i";
        $version = DB::getLine($sql, [$id]);

        $this->resp([
            'version' => $version
        ]);
    }

    public function __formData() {
        $data = [
            'terminal'      => input('terminal'),
            'is_must'       => input('isMust'),
            'size'          => input('size'),
            'url'           => input('url'),
            'content_zh'    => input('contentZh'),
            'content_en'    => input('contentEn'),
            'content_local' => input('contentLocal'),
        ];

        if( $remark = input('remark') ) {
            $data['remark'] = $remark;
        }

        return $data;
    }

}
