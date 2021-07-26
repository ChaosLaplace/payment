<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/*
  --------------------------------------------------------------
	公共函数集
	@author marvin
  --------------------------------------------------------------
*/

/**
 * 生成站点url
 * 根据URL协议和入口页配置生成站点URL
 * @param string $uri
 * @param string $domain
 * @return string
 */
function site_url( $uri = '', $domain='') {
    if( empty($domain) ) {
        $domain = base_url();
    }

    if( empty($uri) ) {
        return $domain;
    }

    $portal_page = $GLOBALS['app']['index'];
    if( !empty($portal_page) ) {
        $portal_page = $GLOBALS['app']['index'] . '/';
    }

    $url = '';
    if( $GLOBALS['app']['protocol'] == 'PATH_INFO' ) {	// pathinfo
        $url = $domain . $portal_page . $uri;
    }
    else {	// query string
        $url = $domain . $portal_page . '?p=' . $uri;
    }

    return $url;
}

/**
 * 获取站点基础URL
 * app.conf.php中site_domain的配置，如果配置不是以'/'结束，添加'/'
 * @return string
 */
function base_url() {
    return $GLOBALS['app']['site_domain'];
}

/**
 * 重定向
 * @param string $url
 */
function redirect($url) {
    // 如果是相对地址，转化成绝对地址
    if( !preg_match("/(http|https):\/\//i", $url) ) {
        $url = site_url( $url );
    }
    header( "location:{$url}" );
    exit;
}

/**
 * cookie操作函数，取值/赋值
 * @param string $name
 * @param mixed $value
 * @param int $expire
 * @param string $path
 * @param string $domain
 * @return bool|mixed
 */
function cookie($name, $value=null, $expire=null, $path='/', $domain='') {
    if( is_null($value) ) {
        if( !isset($_COOKIE[$name]) ) {
            return false;
        }

        $value = $_COOKIE[$name];

        // 为了兼容老版本session id
        if( is_base64($value) ) {
            $value = base64_decode( $_COOKIE[$name] );
        }
        return unserialize( $value );
    } else {
        if( empty($expire) ) {
            $expire = time() + 24 * 60 * 60 * 30;
        }
        $value = serialize( $value );
        setcookie( $name, base64_encode($value), $expire, $path, $domain );
    }
}

/**
 * 消息提示
 * @param string $message
 * @param string $typ success | info | warning | danger
 * @return string
 */
function flash_message($message='', $typ='success') {
    $cookie_name = '__xxoo_message';

    if( !empty($message) ) {    // add
        $message = "<div class=\"alert alert-{$typ} alert-dismissible\" role=\"alert\">"
                     ."<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>"
                     ."{$message}"
                   ."</div>";
        setcookie($cookie_name, base64_encode($message), time()+3600, '/');
    }
    else {  // get
        if( isset($_COOKIE[$cookie_name]) ) {
            $message = base64_decode( $_COOKIE[$cookie_name] );
            setcookie($cookie_name,  '', time()-3600, '/');
            return $message;
        }
        return '';
    }
}


/**
 * 获取输入，GET/POST
 * 自动去除前后空格，对string值进入xss过滤
 * @param $k
 * @param bool $fill
 * @return array|bool|mixed|string
 */
function input($k, $fill = false) {
    $val = input_raw($k);

    if( empty($val) || $val === $fill ) {
        return $val;
    }

    if( is_string($val) ) {
        $val = xss_remove($val);
        return $val;
    }

    if( is_array($val) ) {
        $arr = array();
        foreach($val as $k=>$v) {
            if( is_string($v) ) {
                $v = xss_remove($v);
            }
            $arr[$k] = $v;
        }
        return $arr;
    }

    return $val;
}

/**
 * 获取输入，GET/POST
 * 自动去除前后空格，不进行xss过滤
 * @param $k
 * @param bool $fill
 * @return bool|string
 */
function input_raw($k, $fill = false) {
    if( !isset($_REQUEST[$k]) ) {
        return false;
    }

    $val = $_REQUEST[$k];
    if( !is_array($val) ) {
        $val = trim($val);
    }

    if( empty($val) && $fill === false ) {
        return $val;
    }

    if( empty($val) && $fill !== false ) {
        return $fill;
    }

    return $val;
}

/**
 * 移除xss攻击
 * @param $val
 * @return mixed
 */
function xss_remove( $val ) {
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java/0script>
    // note that you have to handle splits with /n, /r, and /t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|///'."'";
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

        // @ @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are /t, /n, and /r
    $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}