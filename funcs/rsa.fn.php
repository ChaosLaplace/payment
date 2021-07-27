<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

// 公钥
function getPublicKey() {
    return "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjVY7iJd7sQFW5JjTwvvi/WWrBPtomLL1e7v1C0JVwoADi4tY9H7ctzSsm36anv/6U/Uz2UPrHkB2+ofBX50mxm/sK1oGsZFur91fnyqP+KIZgOP3FqV6AoDRJvRGq6F2l0obfF1JJtIPtpTg8cXQ19HwV0DXiJDQmdhk/OwRwXs4y+ywYMgzdZKmOYob/22RhAGJPaPh2ut15nf1PCKc0m/dqPdXjhba6QlCjYY0b4ILpSOEh4NjmlS6W+UPri9CPW8J8uLOut6p7XXClFkkBWcOLoU4JUjXJm7FVoYhQcmnVbsj8Lqk4lOTRp4JQPHmu/ALSCFQ3ql1mIKqQgoWcwIDAQAB";
}

// 私钥
function getPrivateKey() {
    return $priKey = "MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCNVjuIl3uxAVbkmNPC++L9ZasE+2iYsvV7u/ULQlXCgAOLi1j0fty3NKybfpqe//pT9TPZQ+seQHb6h8FfnSbGb+wrWgaxkW6v3V+fKo/4ohmA4/cWpXoCgNEm9EaroXaXSht8XUkm0g+2lODxxdDX0fBXQNeIkNCZ2GT87BHBezjL7LBgyDN1kqY5ihv/bZGEAYk9o+Ha63Xmd/U8IpzSb92o91eOFtrpCUKNhjRvggulI4SHg2OaVLpb5Q+uL0I9bwny4s663qntdcKUWSQFZw4uhTglSNcmbsVWhiFByadVuyPwuqTiU5NGnglA8ea78AtIIVDeqXWYgqpCChZzAgMBAAECggEAJAnz/j1aLeVAieOqQ5LE64YsGIYewqkHTXi84BUXFxRbUw7CLP3YO+LzjGa4+IgfBZ+FjAX0gu8/J1zMYxwIUhsh5l/7SvXheniBNG2+7lAvHp2CWMTzGoZMTpmFF6iBO8yKu6hxybNQjGbkAOJHAXEnzqfbDZiXiFMUVSXA1Uu782VJiDBtvSRiih5JxIj8Fq+8Rk9QVM5M0EzfD91xu3DTL2XjFOA466uPO+oZYfBA7/A7Xi6oNgi3rHJlMajI/vVI/hMaUGLeM3KDi+X6ntgzgff58x2S6llgQcbRPHAkbWANZmFHOrjOA926l64K69CUmv7miI42WxU7LyFEcQKBgQDO5T6lkbMasWht+n2WpZYrNmyiGjPlyLDHm+CoWBwGRje0Hx3vNn8tO1In/miscjWm+vvprrJD8F64Rq8MXiYEV0Gi4iqawEJKLhka4q0FJ3DI1vBQeaZi0b2pifenXyO/5/r8lkWL0uOYmJCl099iZer1yRdPW8/3+b6vL2PPqQKBgQCu4bZ4Cw7RI9jZRkhAnb14SvrmKETxbCir1MKaMhmmwdvel+FtcnUyqqjMCmZLCJgiLRnfxvg2XyxpST/aeH6bnGVB4UY8t//8cXu1D7l4lEthFSpQmxzHNYsXnVN/LpCBTXsdAZhqLPzb7INHP2lGpceOMBNvOHp/VNdXFw32uwKBgHHV6ok4yF4IfNf+OfmYdC6kjFMIrcNhj/rUBmE50XmWSvHdKSBEUOc3O3Xr4fX4BRMyB0dFKqp85/DmW0wnxS0gQWn4nM52uEKOaJvFWsN6NvEaajEtLeIzWvKcidnaXBYr8onluLA9QaLlVrkXAVLnlsbTdPLiJmRfUo6bOYS5AoGBAK4tXmg3Uz2Xv22/o0zADqqVu0iqiOgPOn7RvDSJe6Tr5cnQaZRxcCrTcKUwImvPFn7Lfr6zVBoLNpdyfidg5XClFvDDpwnolQvgFz7hd2+R+3Wo9+kqZEJAoNUXO8crIH+4JMtiAGSXhAyihqnajUbw7E1AssqObOsPHB7AdobrAoGAEsxu6uxBHJz9GiGV8drYYh2hkFa31ed6d0oj+gKndzz25RdygN0kAGaSy1piJcqVfcat+KR5AHsoMG/jhpckvrpXVYykNX71FnAyQsQeQWua0JNtwpYFHEM3O0UF5DmZi9IX7ySBUENYwkSXoECViqeIQOi2cww+8rQ51sc1ouU=";
}

// 使用RSA公钥加密数据 结果是base64
function encryptRSA($dataStr, $publicKey) {
    try {
        if ( empty($publicKey) ) {
            return false;
        }
        if ( empty($dataStr) ) {
            return false;
        }
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($publicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $publicKey = openssl_pkey_get_public($res); //解析公钥
        openssl_public_encrypt($dataStr, $encrypted, $publicKey);
        if (empty($encrypted)) {
            return false;
        }
        return base64_encode($encrypted);
    } catch (Exception $e) {
        return false;
    }
}

// 使用自己的RSA私钥解析数据 
function decryptRSA($encryptedStr) {
    try {
        if ( empty($encryptedStr) ) {
            return false;
        }
        $privateKey = getPrivateKey();
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        $key = openssl_pkey_get_private($res); //解析私钥
        $encrypted = base64_decode($encryptedStr);
        //解密
        openssl_private_decrypt($encrypted, $decrypted, $key);
        if (empty($decrypted)) {
            return false;
        }
        return $decrypted;
    } catch (Exception $e) {
        return false;
    }
}

// sha256WithRSA 私钥签名加密
function getSign($dataStr) {
    try {
        if ( empty($dataStr) ) {
            return false;
        }
        $privateKey = getPrivateKey();
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        echo $privateKey."\n";
        
        $key = openssl_pkey_get_private($privateKey);
        openssl_sign($dataStr, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);
        if (empty($sign)) {
            return false;
        }
        $sign = base64_encode($sign);
        return $sign;
    } catch (Exception $e) {
        return false;
    }
}

// 验证 sha256WithRSA 签名   sign是签名后的base64   dataStr是签名的内容
function verify($dataStr, $sign) {
    try {
        if ( empty($dataStr) ) {
            return false;
        }
        $publicKey = getPublicKey();
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($publicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $key = openssl_pkey_get_public($res);
        $data = openssl_verify($dataStr, base64_decode($sign), $key, OPENSSL_ALGO_SHA256);
        return $data;
    } catch (Exception $e) {
        return false;
    }
}
