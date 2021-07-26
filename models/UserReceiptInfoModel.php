<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 收货资讯
 *
 * @author Chaos
 */
class UserReceiptInfoModel {
    
    /**
     * 查询收货资讯（不包含已删除资讯）
     * @param int $uid 会员 ID
     * @return array 返回收货资讯
     */
    public static function listReceiptInfosGroupById($uid) {
        $sql = "select * from `user_receipt_info` where user_id=?i and is_delete='N' group by id";
        return DB::getData($sql, [$uid]);
    }
}
