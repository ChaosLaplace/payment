<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 
 * @author Chaos 
 */
class AdControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据标识，展示广告
     * @param string ident 广告标识
     * @return array 返回广告讯息
     */
    public function show() {
        $ident        = input('ident');
        $pic_language = 'pic_' . $GLOBALS['request']['locale'];
        $txt_language = 'txt_' . $GLOBALS['request']['locale'];
        
        $sql = "select id from `ad_space` where ident=?s limit 1";
        $uid = DB::getVar($sql, [$ident]);

        $sql = "select {$pic_language}, {$txt_language}, link 
        from `ad` where ad_space_id=?i and is_stop='N' group by id";
        $ad  = DB::getData($sql, [$uid]);

        $this->resp($ad);
    }
}
