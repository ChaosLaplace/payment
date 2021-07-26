<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 商品
 *
 * @author Chaos
 */
class ProductModel {
    
    /**
     * 取消订单
     * @param int $rel_id 订单 ID
     * @return bool
     */
    public static function orderCancel($rel_id) {
        $sql = "update product_order set status='5' where id=?i";
        return DB::runSql($sql, [$rel_id]);
    }
}
