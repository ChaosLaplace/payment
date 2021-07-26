<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class ErrorLogControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $sql = "select count(id) from sys_error_log";
        $total_row = DB::getVar($sql);

        $page_size = input('pageSize') ? input('pageSize') : self::PAGE_SIZE;
        $page_info = paging($total_row, $page_size);

        $sql = "select id, title, message, created 
                from sys_error_log order by id desc {$page_info['limit']}";
        $log_list = DB::getData($sql);

        $this->resp([
            'totalRow' => $total_row,
            'logList'  => $log_list
        ]);
    }

    public function show() {
        $id = input('id');
    }

    public function delete() {
        $id = input('id');

        $sql = "delete from sys_error_log where id=?i";
        DB::runSql($sql, [$id]);

        $this->resp(true);
    }

    public function deleteList() {
        $ids = input('ids');
        $ids = implode(',', $ids);

        $sql = "delete from sys_error_log where id in({$ids})";
        DB::runSql($sql);

        $this->resp(true);
    }

}    