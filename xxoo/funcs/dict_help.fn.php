<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/**
 * 字典辅助函数
 * @author marvin
 */
require_once ROOT . 'conf/dict.conf.php';

/**
 * 根据group、k值，翻译字典
 * @param string $group 组名
 * @param string $k k值
 * @return array|bool
 */
function dict($group, $k=null) {
    if( $k === null ) {
        if( !isset($GLOBALS['dict'][$group]) ) {
            return array();
        }

        $dict_list = array();
        foreach($GLOBALS['dict'][$group] as $k=>$v) {
            $dict_list[] = array(
                'k' => $k,
                'v' => $v
            );
        }
        return $dict_list;
    }
    else {
        return isset( $GLOBALS['dict'][$group][$k] ) ? $GLOBALS['dict'][$group][$k] : '';
    }
}