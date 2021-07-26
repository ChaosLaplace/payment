<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class AdControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $factor_list = [
            ['s'=>"and ad.ad_space_id=ad_space.id", 'f'=>true],
        ];
        $sql_where = sql_where($factor_list);

        $sql = "select count(ad.id) from ad, ad_space where {$sql_where}";
        $total_row = DB::getVar($sql);

        if ($total_row <= 0) {
            $this->resp([
                'total_row'  => 0,
                'ad_list'    => [],
            ]);
            return;
        }

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select ad.*, ad_space.name as ad_space_name
                from ad, ad_space 
                where {$sql_where} order by ad.id desc {$page_info['limit']}";
        $ad_list = DB::getData($sql);

        foreach($ad_list as &$ad) {
            $ad['pic_zh_url'] = img_url($ad['pic_zh']);
            $ad['pic_en_url'] = img_url($ad['pic_en']);
            $ad['pic_local_url'] = img_url($ad['pic_local']);
        }

        $this->resp([
            'total_row' => $total_row,
            'ad_list'   => $ad_list,
        ]);
    }

    public function update() {

    }

    public function stop() {

    }

    private function __dataForm() {
        $data = [];
        return $data;
    }

}