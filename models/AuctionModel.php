<?php  if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserAccountModel.php';

/**
 * 拍卖
 */
class AuctionModel {

    /** 初始化状态 */
    const STATUS_WAIT    = 'WAIT';
    /** 预展中 */
    const STATUS_PREVIEW = 'PREVIEW';
    /** 竞拍中 */
    const STATUS_BID     = 'BID';
    /** 结束 */
    const STATUS_FINISH  = 'FINISH';

    /**
     * 开始预展
     * @param $id
     */
    public static function startPreview($id) {
        echo '==============id:' . $id . "\n";
    }

    /**
     * 开始竞拍
     * @param $id
     */
    public static function startBid($id) {

    }

    /**
     * 结束竞拍
     * @param $id
     */
    public static function endBid($id) {

    }

    /**
     * 检查尾款是否到账
     * @param $id
     */
    public static function checkFinalPayment($id) {

    }

    /**
     * 自动转拍
     * @param $id
     */
    public static function autoTransfer($id) {

    }

    /**
     * 保证金
     */
    public static function bond($user_id, $auction_id, $bond_amount) {
        // 开启事务
        $data = [
            'auction_id'    => $auction_id,
            'user_id'       => $user_id,
            'bond_amount'   => $bond_amount,
            'paid_time'     => time()
        ];
        $auction_bond_id = DB::insert('auction_bond', $data);

        $trade = [
            'subject_id'        => UserAccountModel::SUBJECT_BOND,
            'change_depict_zh'  => '交纳保证金',
            'change_depict_en'  => '交纳保证金',
            'change_depict_local' => '交纳保证金',
            'rel_table'         => 'auction_bond',
            'rel_id'            => $auction_bond_id,
        ];

        $is_expend = UserAccountModel::expend($user_id, $bond_amount, $trade);
        if( !$is_expend ) {
            // 回滚
        }

        // 提醒消息

        return true;
    }
	
	/**
     * 获取当前出价
     * @param $auction_id
     */
    public static function currentBid($auction_id) {

    }

    /**
     * 用户出价
     * @param $user_id
     * @param $auction_id
     * @param $bid_amount
     */
    public static function userBid($user_id, $auction_id, $bid_amount) {

    }


}
