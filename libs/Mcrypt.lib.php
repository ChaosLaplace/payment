<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * mcrypt 类
 * @author marvin
 */

class Mcrypt {
	/**
	 * 加密des
	 * @param string $encrypt 加密前的字符串
	 * @param string $encryption_key 密钥
	 * @return string 加密后的字符串
	 */
	public static function encrypt( $encrypt, $encryption_key ) {
		$iv = mcrypt_create_iv(
			mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ),
			MCRYPT_RAND
		);
		$passcrypt = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			$encryption_key,
			$encrypt,
			MCRYPT_MODE_ECB,
			$iv
		);
		$encode = base64_encode( $passcrypt );
		return $encode;
	} 
	
	/**
	 * 解密des
	 * @param string $decrypt 要解密的字符串
	 * @param string $encryption_key 密钥
	 * @return string 解密后的字符串
	 */
	public static function decrypt( $decrypt, $encryption_key ) {
		$decoded = base64_decode( $decrypt );
		$iv = mcrypt_create_iv(
			mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ),
			MCRYPT_RAND
		);
		$decrypted = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			$encryption_key,
			$decoded,
			MCRYPT_MODE_ECB,
			$iv
		);
		return $decrypted;
	}

    public static function des_decrypt($str, $key) {

        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);

        $len = strlen($str);

        $block = mcrypt_get_block_size('des', 'ecb');

        $pad = ord($str[$len - 1]);

        return substr($str, 0, $len - $pad);

    }

}