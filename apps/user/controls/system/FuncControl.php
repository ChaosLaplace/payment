<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once APP . 'models/RBACModel.php';

class FuncControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $func_tree = RBACModel::funcTree();
        $this->resp($func_tree);
    }

    public function add() {
        if($this->__formValidation()) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '参数错误');
        }

        $data = $this->__formData();
        DB::insert('sys_func', $data);

        $this->resp(true);
    }

    public function update() {
        if($this->__formValidation()) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '参数错误');
        }

        $id = input('id');
        $data = $this->__formData();
        DB::update('sys_func', $data, ['id'=>$id]);

        $this->resp(true);
    }

    public function delete() {
        $id = input('id');

        $id_arr = [$id];
        $func_list = RBACModel::listChildFunc($id);
        if( !empty($func_list) ) {
            foreach($func_list as $func) {
                $id_arr[] = $func['id'];
            }
        }

        $ids = implode(',', $id_arr);
        $sql = "delete from sys_func where id in ($ids)";
        DB::runSql($sql);

        $this->resp(true);
    }

    public function item() {
        $id = input('id');

        $sql = "select id, pid, func_name, `path`, api_urls, seq from sys_func where id=?i";
        $item = DB::getLine($sql, [$id]);

        // 将urls中的逗号替换为换行符
        $item['api_urls'] = str_replace(',', '\n', $item['api_urls']);

        // 获取父功能名称
        if($item['pid'] == RBACModel::ROOT_ID) {
            $item['parent_name'] = RBACModel::ROOT_NAME;
        }
        else {
            $sql = "select id, func_name from sys_func where id=?i";
            $parent = DB::getLine($sql, [$item['pid']]);
            $item['parent_name'] = $parent['func_name'];
        }

        $this->resp($item);
    }

    public function isExistName() {
        $this->isExist('sys_func', 'func_name');
    }

    public function isExistPath() {
        $this->isExist('sys_func', 'path');
    }

    private function __formValidation() {
        $validator = new Validator();
        $validator->setRule('pid', array('required'));
        $validator->setRule('funcName', array('required'));
        $validator->setRule('path', array('required'));
        $validator->setRule('seq', array('required'));
        $validator->setRule('api', array('required'));
        return $validator->validate();
    }

    public function __formData() {
        $pid  = input('pid');
        $path = input('path');

        $data = [
            'pid'       => $pid,
            'func_name' => input('funcName'),
            'path'      => $path,
            'seq'       => input('seq'),
        ];

        $data['grade'] = RBACModel::getGrade($pid);

        // 将换行符换为逗号
        $data['api_urls'] = str_replace('\n', ',', input('apiUrls'));

        return $data;
    }

}