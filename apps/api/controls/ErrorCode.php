<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class ErrorCode {

    const PARAMETER_ERROR = 600;        // 表单参数验证错误
    const LOGIN_FAIL = 601;             // 用户登录认证错误
    const OLD_PASSWORD_ERROR = 602;     // 旧密码错误

    // 手机验证码错误
    const SMS_CAPTCHA_ERROR = 603;

}
