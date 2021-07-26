<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	sql 帮助函数
	包括：按条件生成where语句

	@author marvin
  --------------------------------------------------------------
*/

/**
 * 根据条件拼接sql where片段
 * 主要解决前台可选一项或多项条件进行查询时的sql拼接
 *
 * 拼接规则：
 * 's'=>sql缩写，必须，伪sql片段，$1..$n为反向引用，引用后面的值数组
 * 'f'=>fill缩写，必须，sql片段中要填充的值
 * 'c'=>condition缩写，选填，默认判断不为空，如果设置了条件则用所设置的条件
 *
 * $factor_list = array(
 *		array('s'=>'and a.id=$1', 'f'=>12 ),
 *		array('s'=>"and a.name like '%$1'", 'f'=>'zhangsan'),
 *		array('s'=>'and a.age > $1', 'f'=>18),
 *		array('s'=>'or (a.time>$1 and a.time<$2)', 'f'=>array(2186789, 389876789), 'c'=>(1==1) )
 * );
 * @param array $factor_list
 */
function sql_where(array $factor_list) {
    $where_sql = ' 1=1 ';
    if(empty($factor_list)) {
        return $where_sql;
    }
    foreach($factor_list as $factor) {
        // 如果用户没有设置条件，默认条件为填充值不能为空
        // 如果用户设置了条件，则使用用户所设置的条件
        $condition = isset($factor['c']) ? $factor['c'] : !empty($factor['f']);

        if( $condition || $factor['f'] ==='0') {
            $sql_part = $factor['s'];
            if( is_array( $factor['f'] ) ) {
                $i = 1;
                foreach( $factor['f'] as $v ) {
                    $sql_part = str_replace( '$'.$i, $v, $sql_part );
                    $i++;
                }
            }else {
                $sql_part = str_replace( '$1', $factor['f'], $sql_part );
            }
            $where_sql .= " {$sql_part} ";
        }
    }
    return $where_sql;
}

/**
 * 生成一条随机sql
 * @param string $table 表名
 * @param string $sql_where where条件语句片断
 * @return string
 */
function sql_rand( $table, $sql_where='' ) {
    return "select * from `{$table}` where 1=1 and {$sql_where}
        and id >= (select floor( max(id) * rand()) from `{$table}` ) order by id limit 1";
}