<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * app接口用的简单分页
 * @param int $total_rows 总记录数
 * @param int $page_num 页号，即当前页
 * @param int $page_size 页面大小，即每页记录数
 * @return array($total_page, $start_num)
 */
function app_paging($total_rows, $page_num=1, $page_size=10) {
    if($total_rows <= 0) {
        return array(0, 0);
    }

    $total_page = ceil($total_rows / $page_size);

    $start_num = ($page_num - 1) * $page_size;
    $start_num = ($start_num < 0) ? 0 : $start_num;

    return array($total_page, $start_num);
}

/**
 * 分页函数，返回分页相关信息
 * array(
 *      'upto'      => 10,  数据库开始行
 *      'limit'     => 'limit 10, 20', 分页sql语句片段
 *      'paging'    => 3,   当前页号
 *      'previous'  => 2,   上一页页号
 *      'next'      => 4,   下一页页号
 *      'total_page'=> 10,  总页数
 *      'page_rows' => 20,  每页记录数
 *      'tag'       => 'page', 每天参数标签
 *      'pages'     => array(), 分页码数组
 *      'html'      => '',  分页html代码
 * )
 * @param $total_row 总记录数
 * @param $page_row 每页显示记录数
 * @param int $current 当前页号
 * @param string $tag 分页参数标签
 * @return array()
 */
function paging($total_rows, $page_rows=30, $current=0, $tag='page') {
    $arr = array();
    // 计算总页数
    $total_page = ceil( $total_rows / $page_rows );
    if( $total_page <= 0 ) { $total_page = 0; }
    // 从GET/POST请求中获取当前页码
    $paging = 1;
    if( empty($current) ) {
        if (isset($_GET[$tag])) {
            $paging = $_GET[$tag];
        } elseif (isset($_POST[$tag])) {
            $paging = $_POST[$tag];
        }
    }
    else {
        $paging = $current;
    }
    if( $paging < 1 ) {
        $paging = 1;
    }
    elseif( $paging > $total_page ) {
        $paging = $total_page;
    }
    $upto = ($paging - 1) * $page_rows;
    $upto = ($upto < 0) ? 0 : $upto;
    $arr['upto'] 		= $upto;	// 数据库开始行
    $arr['limit'] 		= ' LIMIT '.$arr['upto'].','.$page_rows;	// mysql limit sql part
    $arr['paging'] 		= $paging;	// 当前页
    $arr['previous'] 	= ($paging <= 1) ? $paging : ($paging - 1); 		// 上一页
    $arr['next'] 		= ($paging >= $total_page) ? $total_page : ($paging + 1); // 下一页
    $arr['total_page'] 	= $total_page;	// 总页数
    $arr['total_rows']  = ($total_rows < 0) ? 0 : $total_rows;	// 总记录数
    $arr['page_rows'] 	= $page_rows;	// 每页记录数
    $arr['tag'] 		= $tag;
    // 只有一页
    if( $total_page == 1 ) {
        $arr['pages'] = array(1);
    }else{
        // 开始页码
        // 为了防止超过www站的宽度所以将显示个数调小了
        $start = $paging - 5;
        if( $start < 1 ) {
            $start = 1;
        }

        // 结束页码
        $end = $paging + 5;
        if( ($end+1) - $start < 10 ) {	// 不够10个页码时，补够
            $end += 10 - $end;
        }
        if( $end > $total_page ) {	// 超过总页数
            $end = $total_page;
            $start = ($total_page - 10 < 1) ? 1 : $total_page - 10;
        }

        $arr['pages'] = array();
        for( $i=$start; $i<=$end; $i++ ) {
            $arr['pages'][] = $i;
        }
    }
    if( $arr['total_page'] <= 1 ) {
        $html = '';
    }else{
        $http_protocol = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https://' : 'http://';
        $url = $http_protocol . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $query_string = '';
        $new_url = '';
        if( $pos = strpos( $url, '?' ) ) {
            $new_url = substr( $url, 0, $pos+1 );
            $query_string = substr( $url, $pos+1 );
        }
        else {
            $new_url = $url . '?';
        }
        // 重新组装url请求参数
        if( !empty($query_string) ) {
            $query_string_arr = explode( '&', $query_string );
            $qs_arr = array();
            $pattern = '/^'.$tag.'=\d+$/';
            foreach( $query_string_arr as $qs ) {
                if( !preg_match($pattern, $qs) ) {
                    $qs_arr[] = $qs;
                }
            }
            if( count($qs_arr) > 0 ) {
                $new_url .= implode( '&', $qs_arr ) . '&';
            }
        }
        $new_url .= $tag . '=';

        $html = '';
        if( $arr['paging'] > 1 ) {
            $html .= '<a href="'.$new_url.$arr['previous'] .'">上一页</a>';
        }
        else {
            $html = "<span>上一页</span>";
        }

        foreach( $arr['pages'] as $i ) {
            if( $i == $arr['paging'] ) {
                $html .= "<span class='current'>". $i ."</span>";
            }
            else {
                $html .= "<a href='".$new_url.$i."'>$i</a>";
            }
        }

        if( $arr['paging'] == $arr['total_page'] ) {
            $html .= "<span>下一页</span>";
        }
        else {
            $html .= '<a href="'.$new_url.$arr['next'] .'">下一页</a>';
        }
    }
    $arr['html'] = $html;
    return $arr;
}