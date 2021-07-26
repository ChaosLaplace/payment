<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/UserModel.php';
require_once ROOT . 'models/GradeModel.php';
require_once ROOT . 'models/UserAccountModel.php';
require_once ROOT . 'models/ProfitShareModel.php';
require_once ROOT . 'models/MessageTplModel.php';

/**
 * 用户等级
 *
 * @author marvin
 */
class GradeControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * vip购买展示
     */
    public function showcase() {
        $user_grade = UserModel::getGrade($this->uid);
        if(!$user_grade) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '获取用户当前等级错误');
            return false;
        }

        $vip_list = [];
        $grade_list = GradeModel::listGrades();
        foreach($grade_list as $grade) {
            if($grade['grade'] > 0) {   // 不要0级
                $vip = [
                    'id'      => $grade['id'],
                    'name'    => $grade['name'],
                    'grade'   => $grade['grade'],
                    'bid_num' => $grade['bid_num'],
                    'icon'    => $grade['icon'],
                    'disable' => 'Y',       // 是否禁用
                    'differ_price' => 0,    // 应补差价
                ];

                if($grade['grade'] > $user_grade['grade']) {
                    $vip['disable'] = 'N';
                    $vip['differ_price'] = fen2yuan($grade['price'] - $user_grade['price']); // 差价
                }

                // 计算完差价后，再转换
                $vip['price'] = fen2yuan($grade['price']);

                $vip_list[] = $vip;
            }
        }

        $current_grade = [
            'id'    => $user_grade['id'],
            'name'  => $user_grade['name'],
            'icon'  => $user_grade['icon'],
            'grade' => $user_grade['grade'],
            'price' => fen2yuan($user_grade['price']),
        ];

        $this->resp([
            'current_grade' => $current_grade,
            'grade_list'    => $vip_list,
        ]);
    }

    /**
     * 购买vip
     * @return false
     */
    public function buy() {
        $grade_id = input('gradeId');
        $payment_amount = yuan2fen(input('payAmount'));

        $old_grade = UserModel::getGrade($this->uid);
        if( !$old_grade ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '获取用户原等级错误');
            return false;
        }

        $new_grade = GradeModel::getById($grade_id);
        if( !$new_grade ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '获取购买等级错误');
            return false;
        }

        // 计算差价
        $differ_price = $new_grade['price'] - $old_grade['price'];
        if( $payment_amount != $differ_price) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '提交的差价和实际的不一致');
            return false;
        }

        // 等级购买订单
        $order = [
            'user_id'       => $this->uid,
            'new_grade_id'  => $new_grade['id'],
            'new_grade'     => $new_grade['grade'],
            'old_grade_id'  => $old_grade['id'],
            'old_grade'     => $old_grade['grade'],
            'price'         => $new_grade['price'],
            'differ_price'  => $differ_price,
            'create_time'   => time(),
        ];
        $order_id = DB::insert('grade_order', $order);

        // 付款
        $depict = MessageTplModel::getBuyVipShareMessage('张三', '2007-05-18', '3', '0.03');
        $trade = [
            'subject_id'    => UserAccountModel::SUBJECT_BUY_GRADE,
            'change_depict_zh'     => $depict['zh'],
            'change_depict_en'     => $depict['en'],
            'change_depict_local'  => $depict['local'],
            'rel_table'     => 'grade_order',
            'rel_id'        => $order_id,
        ];
        $is_paid = UserAccountModel::expend($this->uid, $payment_amount, $trade);

        if( !$is_paid ) {
            $this->respError(ErrorCode::PARAMETER_ERROR, '支付失败，余额不足');
            return false;
        }

        // 修改订单
        $now = time();
        $sql = "update grade_order set is_paid='Y', paid_time={$now} where id=?i";
        DB::runSql($sql, [$this->uid]);

        // 修改用户等级和出价次数
        $append_bid_num = $new_grade['bid_num'] - $old_grade['bid_num']; // 应补出价次数
        $sql = "update `user` set grade_id=?i, grade=?i, has_bid_num=?i, surplus_bid_num=surplus_bid_num + ?i where id=?i";
        DB::runSql($sql, [$new_grade['id'], $new_grade['grade'], $new_grade['bid_num'], $append_bid_num, $this->uid]);

        // 分佣
        ProfitShareModel::addWaitQueue($this->uid, ProfitShareModel::SUBJECT_BUY_VIP_SHARE,
            $differ_price, $new_grade['buy_vip_share'], 'grade_order', $order_id);

        $this->resp(true);
    }

}
