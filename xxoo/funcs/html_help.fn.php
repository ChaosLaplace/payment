<?php

/**
 * 压缩html，清除换行符,清除制表符,去掉注释标记
 * @param $text
 * @return mixed
 */
function compress_html($text) {
    $text = str_replace("\r\n", '', $text);//清除换行符
    $text = str_replace("\n", '', $text);//清除换行符
    $text = str_replace("\t", '', $text);//清除制表符
    $pattern = array(
        "/> *([^ ]*) *</",  //去掉注释标记
        "/[\s]+/",
        "/<!--[^!]*-->/",
        "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );

    $replace = array (
        ">\\1<",
        " ",
        "",
        "\"",
        "\"",
        ""
    );
    return preg_replace($pattern, $replace, $text);
}