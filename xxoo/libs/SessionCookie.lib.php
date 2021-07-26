<?php if ( !defined('XXOO') ) exit('No direct script access allowed');
/**
 * session类，cookie实现
 *
 * @author marvin
 */
class SessionCookie {

    private $userdata;								// 用户数据
    private $expiration 		= 7200;				// 过期时间
    private $cookiename 		= '_xxoo_session';	// cookie名称
    private $cookiepath 		= '/';				// cookie路径
    private $cookieDomain 		= '';

    public function __construct() {

        if( $this->read() ) {	// session存在
            $this->update();
        }
        else {					// session不存在或过期
            $this->create();
        }
    }

    /**
     * 创建session
     * @return void
     */
    private function create() {
        $sessId = $this->generateSessId();

        $this->userdata = array(
            'session_id' 	=> $sessId,
            'last_activity'	=> $this->getTime()
        );

        $this->setCookie();
    }

    /**
     * 生成session ID
     * @return string 返回生成的session ID
     */
    private function generateSessId() {
        $sessId = '';
        while (strlen($sessId) < 32) {
            $sessId .= mt_rand(0, mt_getrandmax());
        }
        return md5(uniqid($sessId, TRUE));
    }

    /**
     * 获取用户数据
     * @param string $item
     * @return array
     */
    public function userdata($item) {
        return ( ! isset($this->userdata[$item])) ? FALSE : $this->userdata[$item];
    }

    /**
     * 设置用户数据
     * @param $newdata
     * @param $newval
     * @return void
     */
    public function setUserdata( $newdata = array(), $newval = '' ) {
        if (is_string($newdata)) {
            $newdata = array($newdata => $newval);
        }
        if (count($newdata) > 0) {
            foreach ($newdata as $key => $val) {
                $this->userdata[$key] = $val;
            }
        }
        $this->update();
    }

    /**
     * 注销用户数据
     * @param string $key
     * @return void
     */
    public function unsetUserdata($key) {
        unset( $this->userdata[$key] );
        $this->update();
    }

    /**
     * 销毁session
     * @return void
     */
    public function destroy() {
        setcookie( $this->cookiename, '', ($this->getTime() - 31500000), '/');
    }

    /**
     * 更新数据
     * @return void
     */
    private function update() {
        $this->userdata['session_id'] = $this->generateSessId();
        $this->userdata['last_activity'] = $this->getTime();
        $this->setCookie();
    }

    /**
     * 读取session，如果session不存在或过期返回false
     * @return bool
     */
    public function read() {
        // cookie不存在
        if( ! isset($_COOKIE[$this->cookiename]) ) {
            return FALSE;
        }

        // 读取cookie数据
        $data = $this->unserialize( $_COOKIE[$this->cookiename] );
        foreach( $data as $key => $val ) {
            $key = base64_decode($key);
            $val = base64_decode($val);
            $this->userdata[$key] = $val;
        }

        // 已过期
        if( $this->userdata['last_activity'] + $this->expiration < $this->getTime() ) {
            $this->destroy();
            return FALSE;
        }

        return TRUE;
    }

    private function setCookie() {
        $base64_data = array();
        if( !empty( $this->userdata ) ) {
            foreach( $this->userdata as $key=>$value ) {
                $key = base64_encode($key);
                $value = base64_encode($value);
                $base64_data[$key] = $value;
            }
        }

        if( !empty( $base64_data ) ) {
            $cookie_data = $this->serialize($base64_data);
        }
        else {
            $cookie_data = '';
        }

        $result = setcookie(
            $this->cookiename,
            $cookie_data,
            $this->getTime() + $this->expiration,
            '/'
        );
    }

    /**
     * 获取当前系统时间的毫秒数
     * @return int
     */
    private function getTime() {
        return time() + 8 * 60 * 60 * 1000;
    }

    /**
     * 序列化
     * @param $data
     * @return void
     */
    private function serialize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = str_replace('\\', '{{slash}}', $val);
            }
        }
        else {
            $data = str_replace('\\', '{{slash}}', $data);
        }

        return serialize($data);
    }

    /**
     * 反序列化
     * @param $data
     * @return void
     */
    private function unserialize($data) {
        $data = @unserialize( $this->strip_slashes($data) );

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = str_replace('{{slash}}', '\\', $val);
            }

            return $data;
        }

        return str_replace('{{slash}}', '\\', $data);
    }


    private function strip_slashes($str) {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = strip_slashes($val);
            }
        }
        else {
            $str = stripslashes($str);
        }
        return $str;
    }

}
