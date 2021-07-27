<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

class ErrorCode {

    const PARAMETER_ERROR   = 600;        // 表單參數驗證未通過
    const LOGIN_ERROR       = 601;        // 登入驗證未通過
    const AUTH_ERROR        = 602;        // 驗證碼未通過驗證

    const UNDEFINED_USER    = 700;        // 不存在商戶

    const PERMISSION_DENIED = 800;        // 沒有權限
}
