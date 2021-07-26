<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/BroadcastModel.php';

class BroadcastControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $broadcast_list = BroadcastModel::listLastBroadcasts();

        $this->resp([
            'broadcast_list' => $broadcast_list
        ]);
    }

}
