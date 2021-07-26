<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/**
 * 表单辅助函数
 * @author marvin
 */

/**
 * 生成下拉列表框
 * @param $field 表单域name和id名称
 * @param $opts 表单选项列表，二维数据，如：array( array('k'=>'1','v'=>'男'), array('k'=>'2','v'=>'女') )
 * @param string $selected 默认选中项
 * @param string $clas css类名
 * @return string
 */
function form_dropdown($field, $opts, $selected='', $clas='form-control') {
    $s = '<select name="'.$field.'" id="'.$field.'" class="'.$clas.'">';
    $s .= '<option value=""></option>';
    if($opts){
        foreach( $opts as $opt ) {
            if( $opt['k'] == $selected && $selected !== false ) {
                $s .= '<option value="'.$opt['k'].'" selected>'.$opt['v'].'</option>';
            }
            else {
                $s .= '<option value="'.$opt['k'].'">'.$opt['v'].'</option>';
            }
        }
    }
    return $s . '</select>';
}

function form_dropdown_opts( $opts, $selected='', $blank='' ) {
    $s = '<option value="">'. $blank .'</option>';
    if($opts) {
        foreach( $opts as $opt ) {
            if( $opt['k'] == $selected ) {
                $s .= '<option value="'.$opt['k'].'" selected>'.$opt['v'].'</option>';
            }
            else {
                $s .= '<option value="'.$opt['k'].'">'.$opt['v'].'</option>';
            }
        }
    }
    return $s;
}



