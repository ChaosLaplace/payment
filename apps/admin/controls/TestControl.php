<?php if (!defined('XXOO')) {exit('No direct script access allowed');}


class TestControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function test() {
        $data = 'test';

        $this->resp($data);
    }
}
