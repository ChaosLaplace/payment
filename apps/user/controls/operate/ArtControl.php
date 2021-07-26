<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class ArtControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $factor_list = [

        ];
        $sql_where = sql_where($factor_list);

        $sql = "select count(id) from article where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'  => 0,
                'art_list' => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select id, ident, title_zh, title_en, title_local, create_time 
                from article where {$sql_where} order by id desc {$page_info['limit']}";
        $art_list = DB::getData($sql);

        foreach($art_list as &$art) {
            $art['create_time'] = date('Y-m-d', $art['create_time']);
        }

        $this->resp([
            'total_row'  => $total_row,
            'art_list'   => $art_list,
        ]);
    }

    public function add() {
        $data = $this->__formData();
        $data['creater_id'] = $this->adminId;
        $data['create_time'] = time();

        DB::insert('article', $data);

        $this->resp(true);
    }

    public function update() {
        $id = input('id');

        $data = $this->__formData();
        DB::update('article', $data, "id={$id}");

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from article where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function show() {
        $id = input('id');

        $sql = "select id, ident, title_zh, title_en, title_local, content_zh, content_en, content_local 
                from article where id=?i";
        $art = DB::getLine($sql, [$id]);

        $this->resp( [
            'art' => $art
        ]);
    }

    public function isExistIdent() {
        $this->isExist('article', 'ident');
    }

    private function __formData() {
        $data = [
            'title_zh'      => input('titleZh'),
            'title_en'      => input('titleEn'),
            'title_local'   => input('titleLocal'),
            'ident'         => input('ident'),
            'content_zh'    => input('contentZh'),
            'content_en'    => input('contentEn'),
            'content_local' => input('contentLocal'),
        ];

        return $data;
    }

}
