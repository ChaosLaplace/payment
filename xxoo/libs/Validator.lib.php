<?php if (!defined('XXOO')) {exit('No direct script access allowed');}
/**
 * php验证类
 *
 * @author marvin
 *
 * 示例：
 * $validator = new Validator($_POST);
 * $validator->setRule('username', array('required', 'max'=>10));
 * $validator->setRule('tel', array('required', 'mobile'));
 * if( $validator->validate() ) {
 *     echo "true";
 * }
 * else {
 *    echo "false";
 * }
 *
 */
class Validator {

    protected $_data;
    protected $_ruleList;
    protected $_errorList;

    // 错误提示数组
    protected $_rule_tips_arr = array(
        'required'  => '必填，不能为空',
        'equal'     => '与指定值不相等',
        'regex'     => '格式或值不匹配',
        'len'       => '长度只能%d位',
        'maxlen'    => '长度不能大于%d位',
        'minlen'    => '长度不能小于%d位',
        'rangelen'  => '长度必须在%d~%d之间',
        'min'       => '不能小于%d',
        'max'       => '不能大于%d',
        'range'     => '必须在%d~%d之间',
        'digits'    => '必须是数字',
        'email'     => '错误的邮件地址',
        'url'       => '错误的网址',
        'mobile'    => '错误的手机号码',
        'tel'       => '错误的座机号码',
        'qq'        => '错误的QQ号码',
        'postcode'  => '错误的邮政编码',
        'chinese'   => '必须为中文',
        'prefix'    => '必须以%d开头'
    );

    public function __construct($data=null) {
        if( empty($data) ) {
            $data = $_REQUEST;
        }
        $this->_data = $data;
    }

    public function setRule($filed, $rules) {
        $this->_ruleList[$filed] = $rules;
    }

    public function validate() {
        foreach($this->_ruleList as $filed => $rule_arr) {
            if( !isset($this->_data[$filed]) ) {
                return false;
            }

            $val = $this->_data[$filed];

            foreach($rule_arr as $k=>$v) {
                $rule   = $k;               // 验证规则
                $method = "_{$rule}";       // 验证方法名称
                $params = $v;               // 验证方法所需参数
                $format_params = array();   // 格式化错误提示所需参数

                // 如果k值为数字时，则说明该验证方法不需要额外参数
                // 则直接把v值视为验证规则，参数为null
                if( is_int($k) ) {
                    $method = "_{$v}";
                    $params = null;
                }

                // 组装验证方法所需的参数
                if( empty($params) ) {
                    $params = array($val);
                } else if( is_array($params) ) {
                    $format_params = $params;
                    array_unshift($params, $val);
                } else {
                    $format_params[] = $params;
                    $params = array($val, $params);
                }

                // 执行具体的验证方法
                $rs = call_user_func_array( array($this, $method), $params );

                // 如果验证失败，写入验证提示信息
                // 如果一个字段有多个验规，其中一个规则验证失败后，其它规则将不再验证
                if( !$rs ) {
                    $this->_errorList[$filed] = $this->_errorTips($rule, $params);
                    continue;
                }
            }
        }

        return empty($this->_errorList) ? true : false;
    }

    protected function _errorTips($rule, $params) {
        if( !isset($this->rule_tips_arr[$rule]) ) {
            return 'Illegal value';
        } else {
            return vsprintf( $this->rule_tips_arr[$rule], $params );
        }
    }

    public function getErrorList() {
        return $this->_errorList;
    }

    protected function _required($val) {
        return ($val == null || trim($val) == '' || strlen($val) <= 0) ? false : true;
    }

    protected function _equal($val, $equalTo) {
        return $val == $equalTo;
    }

    protected function _regex($val, $pattern) {
        return preg_match($pattern, $val) ? true : false;
    }

    protected function _len($val, $len) {
        return mb_strlen($val) == $len;
    }

    protected function _maxlen($val, $max) {
        return (mb_strlen($val) <= $max) ? true : false;
    }

    protected function _minlen($val, $min) {
        return (mb_strlen($val) >= $min) ? true : false;
    }

    protected function _rangelen($val, $min, $max) {
        return ((mb_strlen($val) >= $min) && (mb_strlen($val) <= $max)) ? true : false;
    }

    protected function _in($val, $in) {
        if( is_array($in) ) {
            $flag = false;
            foreach($in as $item ) {
                if( $val == $item ) {
                    $flag = true;
                    break;
                }
            }
            return $flag;
        }
        else {
            return (strpos($in, $val) === false) ? false : true;
        }
    }

    protected function _min($val, $min) {
        return ($val >= $min) ? true : false;
    }

    protected function _max($val, $max) {
        return ($val <= $max) ? true : false;
    }

    protected function _range($val, $min, $max) {
        return ($val >= $min && $val <= $max) ? true : false;
    }

    protected function _digits($val) {
        return preg_match('/^[0-9]*[1-9][0-9]*$/', $val) ? true : false;
    }

    protected function _email($val) {
        return preg_match('/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/', $val ) ? true : false;
    }

    protected function _url($val) {
        return preg_match('/https?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $val) ? true : false;
    }

    protected function _mobile($val) {
        return preg_match('/^1[3|4|5|7|8][0-9]\d{8}$/', $val) ? true : false;
    }

    protected function _ip($val) {
        return preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/', $val) ? true : false;
    }

    protected function _tel($val) {
        return preg_match('/^0\d{2,3}\s*-\s*\d{8}$/', $val) ? true : false;
    }

    protected function _chinese($val) {
        return preg_match('/^[\x80-\xff]+$/', $val) ? true : false;
    }

    protected function _qq($val) {
        return preg_match('/^[1-9][0-9]{4,}$/', $val) ? true : false;
    }

    protected function _postcode($val) {
        return preg_match('/^[1-9]\d{5}$/', $val) ? true : false;
    }

    protected function _prefix($val, $format) {
        return preg_match("/^{$format}/", $val) ? true : false;
    }

}