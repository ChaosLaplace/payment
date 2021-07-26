<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

/**
 * 
 * @author Chaos 
 */
class ArticleControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据标识，展示文章
     * @param string ident 文章标识
     * @return array 返回文章讯息
     */
    public function show() {
        $ident            = input('ident');
        $title_language   = 'title_' . $GLOBALS['request']['locale'];
        $content_language = 'content_' . $GLOBALS['request']['locale'];

        $sql = "select {$title_language}, {$content_language}
        from `article` where ident=?s limit 1";
        $article = DB::getLine($sql, [$ident]);

        $this->resp($article);
    }
}
