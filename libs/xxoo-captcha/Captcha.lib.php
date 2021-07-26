<?php ! defined('XXOO') && exit( 'No direct script access allowed' );
/**
 * 验证码类
 * @author marvin
 */
class Captcha {

    private $width;
    private $height;
    private $num;
    private $code;

    public function __construct($width=60, $height=40, $num=4) {
        $this->width = $width;
        $this->height = $height;
        $this->num = $num;

        $this->setCode();
    }

    public function getCode() {
        return $this->code;
    }

    public function setCode( $code=null ) {
        if( empty($code) ) {    // 如果为空，则生成
            $this->code = $this->createCode();
        }
        else {
            $this->code = $code;
        }
    }

    private function createCode() {
        $str = "23456789abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ";

        $code = '';
        for ($i = 0; $i < $this->num; $i++) {
            $code .= $str[ rand(0, strlen($str) - 1) ];
        }
        return $code;
    }

    public function output() {
        $twistSize = 5;     // 设置字体的扭曲度
        $fontSize = $this->height >= 10 ? $this->height - 5 : 5;
        $im = imagecreatetruecolor($this->width, $this->height);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255) );

        $stringColor = imagecolorallocate($im, 17, 158, 20);
        imagettftext ($im, $fontSize, rand(-3, 3), $this->width*0.05, $this->height*0.8, $stringColor, ROOT.'libs/xxoo-captcha/fonts/VeraSansBold.ttf', $this->code);

        //扭曲，变形
        $distortion_im = imagecreatetruecolor ($this->width*1.6 , $this->height);
        imagefill($distortion_im, 0, 0, imagecolorallocate($distortion_im,255,255,255) );
        for ( $i=0; $i<$this->width; $i++) {
            for ( $j=0; $j<$this->height; $j++) {
                $rgb = imagecolorat($im, $i , $j);
                if( (int)($i+20+sin($j/$this->height*2*M_PI)*($twistSize+2)) <= imagesx($distortion_im) && (int)($i+20+sin($j/$this->height*2*M_PI)*($twistSize+2)) >=0 ) {
                    imagesetpixel ($distortion_im, (int)($i+20+sin($j/$this->height*2*M_PI-M_PI*0.4)*$twistSize) , $j , $rgb);
                }
            }
        }

        //imageline
        //for($i=0; $i <= 1; $i++) {
            $linecolor = imagecolorallocate($distortion_im, 17, 158, 20);
            $lefty = rand(1, $this->height-1);
            $righty = rand(1, $this->height-1);
            imageline($distortion_im, 0, $lefty, imagesx($distortion_im), $righty, $linecolor);
        //}

        //pixel
        /*
        for($i=0; $i <= 64; $i++) {
            $pointcolor = imagecolorallocate($distortion_im, 17, 158, 20);
            imagesetpixel($distortion_im, rand(0, imagesx($distortion_im)), rand(0, imagesy($distortion_im)), $pointcolor);
        }
        */

        //边框
        //imagerectangle($distortion_im, 0, 0, imagesx($distortion_im)-1, imagesy($distortion_im)-1, imagecolorallocate($distortion_im, 17, 158, 20) );

        ob_clean();
        header('Content-type: image/jpeg');
        imagejpeg ($distortion_im);
        imagedestroy($im);
        imagedestroy($distortion_im);
    }

    private function bend() {

    }
}
