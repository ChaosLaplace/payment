<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/GradeModel.php';

class UserModel {

    /**
     * 根据条件查找用户，返回用户id字符串
     * @param string $query id/nickname/电话
     */
    public static function findUserIds($query) {
        if( preg_match('/^\d+$/', $query) ) {
            // 如果查询字符串为全数字，且大于6位，则视为电话号码，先查询
            // 如果没找到再视为id
            if( count($query) > 6 ) {
                $sql = "select id from `user` where phone=?s limit 1";
                $user_id = DB::getVar($sql, [$query]);
                if($user_id) {
                    return $user_id;
                }
            }

            $sql = "select id from `user` where id=?i";
            return DB::getVar($sql, [$query]);
        }
        else {
            // nickname，调用sphinx查询
        }
        return false;
    }

    /**
     * 根据用户id, 获取用户等级
     * @param $uid
     * @return array|false
     */
    public static function getGrade($uid) {
        $sql = "select grade_id, grade from `user` where id=?i";
        $user = DB::getLine($sql, [$uid]);

        if($user) {
            return GradeModel::getById($user['grade_id']);
        }
        else {
            return false;
        }
    }

    /**
     * 用户密码加密
     * @param $passwd
     * @param $secret
     * @return string
     */
    public static function encodePassword($passwd, $secret) {
        return md5( md5($passwd) . $secret );
    }

    /**
     * 判断手机号是否已存在
     * @param $country_code
     * @param $phone
     * @return bool
     */
    public static function isExistPhone($country_code, $phone) {
        $sql = "select id from `user` where country_code=?s and phone=?s limit 1";
        $user = DB::getLine($sql, [$country_code, $phone]);

        return $user ? true : false;
    }

    /**
     * 根据邀请码获取用户id
     * @param $invite_code
     * @return bool|int|mixed
     */
    public static function getIdByInviteCode($invite_code) {
        $sql = "select id from `user` where invite_code=?s limit 1";
        $uid = DB::getVar($sql, [$invite_code]);
        return $uid ? $uid : 0;
    }

    /**
     * 根据用户id，生成邀请码
     * @return string
     */
    public static function createInviteCode($uid) {
        $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';

        $num = $uid;

        $code = '';
        while( $num > 0 ) {
            $mod = $num % 35;
            $num = ($num - $mod) / 35;
            $code = $source_string[$mod].$code;
        }

        if( empty($code[3]) ) {
            $code = str_pad($code, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * 根据用户id，生成用户昵称
     * @param $uid
     * @return string
     */
    public static function createNickname($uid) {
        // uid不足6位时，前导补0
        if( empty($code[5]) ) {
            $uid = str_pad($uid, 6, '0', STR_PAD_LEFT);
        }
        return 'U' . $uid;
    }

    /**
     * 查询贡献佣金的会员昵称
     * @param int $from_user_id 贡献佣金的会员 ID
     * @return string 贡献佣金的会员昵称
     * 
     * @author Chaos
     */
    public static function getUserNickname($from_user_id) {
        $sql = "select nickname from `user` where id=?i";
        return DB::getVar($sql, [$from_user_id]);
    }

    /**
     * 获取该会员的上级列表
     * @param int $uid 该会员 ID
     * @param int $level 阶层数
     * @return array [{"pid":1, "brokerage":0}, {"pid":2, "brokerage":0}] 上级列表
     * 
     * @author Chaos
     */
    public static function listParents($uid, $level = 3) {
        $sql = "select id, pid, brokerage from `user` where id=?i";

        $parents_info = null;
        $parents_list = [];
        
        $origin_level = $level;

        while ($level > 0) {
            $parents_info = DB::getLine($sql, [$uid]);

            if ( !$parents_info ) {
                break;
            }

            $uid = $parents_info['pid'];

            if ( $origin_level === $level-- ) {
                continue;
            }

            $parents_list[] = array(
                'pid'       => $parents_info['id'],
                'brokerage' => $parents_info['brokerage'],
            );
        }
        return $parents_list;
    }

    /**
     * 查询贡献佣金的会员是否为有效用户
     * @param int $from_user_id 贡献佣金的会员 ID
     * @param string 'Y' | 'N' `is_valid` 栏位是否为有效用户
     * 
     * @author Chaos
     */
    public static function getUserIsValid($from_user_id) {
        $sql = "select is_valid from `user` where id=?i";
        return DB::getVar($sql, [$from_user_id]);
    }

    /**
     * 添加佣金至该会员上级的 总佣金 & 可用佣金 | 锁定佣金
     * @param int $from_user_is_valid 贡献佣金的会员是否为有效用户
     * @param int $change_amount 佣金按规则分润
     * @param int $to_user_id 该会员的上级 ID 目前按照 1~3 级顺序
     * @return string 'Y' | 'N' `is_lock` 栏位是否锁定
     * 
     * @author Chaos
     */
    public static function updateUserBrokerage($from_user_is_valid, $change_amount, $to_user_id) {
        $ret = 'Y';

        $sql = "update `user` set brokerage=brokerage + {$change_amount}";
        if ( $from_user_is_valid === 'Y') {
            $sql .= ",brokerage_enable=brokerage_enable + {$change_amount} where id=?i";
            $ret  = 'N';
        }
        else {
            $sql .= ",brokerage_lock=brokerage_lock + {$change_amount} where id=?i";
        }
        DB::runSql($sql, [$to_user_id]);

        return $ret;
    }

    /**
     * 会员佣金解锁
     * @param int $uid 会员 ID
     * @param int $amount 解锁的佣金
     * 
     * @author Chaos
     */
    public static function updateUserBrokerageLockToEnable($uid, $amount) {
        $sql = "update `user` set brokerage_enable=brokerage_enable + {$amount}
                ,brokerage_lock=brokerage_lock - {$amount} where id=?i";
        return DB::runSql($sql, [$uid]);
    }

    /**
     * 查询支付密码
     * @param int $uid 会员 ID
     * @return string 返回支付密码
     * 
     * @author Chaos
     */
    public static function getPayPassword($uid) {
        $sql = "select pay_password from `user` where id=?i";
        return DB::getVar($sql, [$uid]);
    }

    /**
     * 查询登录密码
     * @param int $uid 会员 ID
     * @return string 返回登录密码
     * 
     * @author Chaos
     */
    public static function getLoginPassword($uid) {
        $sql = "select login_password from `user` where id=?i";
        return DB::getVar($sql, [$uid]);
    }

    /**
     * 查询邀请码
     * @param int $uid 会员 ID
     * @return string 返回邀请码
     * 
     * @author Chaos
     */
    public static function getInviteCode($uid) {
        $sql = "select invite_code from `user` where id=?i";
        return DB::getVar($sql, [$uid]);
    }

}
