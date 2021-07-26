<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * Json Web Token
 * object2array 函数依赖于框架基类函数库xxoo.fn.php
 *
 * @author marvin
 */
class Jwt {

    public static function encode(array $payload, $secret, $alg='SHA256') {
        if( !isset($payload['iat']) || !isset($payload['exp']) ) {
            trigger_error("Json web token must define iat and exp in payload");
        }

        $secret = md5($secret);

        $header = array(
            'typ'   => 'JWT',
            'alg'   => $alg
        );

        $jwt = base64_encode(json_encode($header)) . '.' . base64_encode(json_encode($payload));
        return $jwt . '.' . self::signature($jwt, $secret, $alg);
    }

    public static function decode($jwt, $secret) {
        $secret = md5($secret);
        $tokens = explode('.', $jwt);

        if( count($tokens) != 3 ) {     // token格式错误
            return false;
        }

        list($header64, $payload64, $sign) = $tokens;

        $header = json_decode(base64_decode($header64));
        $header = object2array($header);

        if( empty($header['alg']) ) {
            return false;
        }

        $input = $header64 . '.' . $payload64;
        if(self::signature($input, $secret, $header['alg']) !== $sign) {
            return false;
        }

        $payload = json_decode(base64_decode($payload64));
        $payload = object2array($payload);
        //$time = $_SERVER['REQUEST_TIME'];
        $time = time();
        if( isset($payload['iat']) && $payload['iat'] > $time ) {
            //return false;
        }

        if( isset($payload['exp']) && $payload['exp'] < $time ) {
            //return false;
        }

        return $payload;
    }

    private static function signature($input, $secret, $alg) {
        return hash_hmac($alg, $input, $secret);
    }

}