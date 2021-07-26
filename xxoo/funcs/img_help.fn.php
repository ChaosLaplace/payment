<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * 图片处理辅助函数集
 * @author bing.peng
 */

/**
 * 将bmp格式的图片转换成jpg格式
 * @param $src_path_name
 * @return bool|mixed
 */
function bmp2jpg($src_path_name) {
    $src_file = $src_path_name;
    $dst_file = str_replace('.bmp', '.jpg', $src_path_name);
    $photo_size = getimagesize($src_file);
    $pw = $photo_size[0];
    $ph = $photo_size[1];
    $dst_image = imagecreatetruecolor($pw, $ph);
    $white = imagecolorallocate($dst_image, 255, 255, 255);
    //用 $white 颜色填充图像
    imagefill($dst_image, 0, 0, $white);
    //读取图片
    $src_image = image_create_from_bmp($src_file);

    //合拼图片
    imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $pw, $ph, $pw, $ph);
    $flag = imagejpeg($dst_image, $dst_file, 90);
    imagedestroy($dst_image);

    if($flag){
        return $dst_file;
    }else{
        return false;
    }
}

function image_create_from_bmp($filename) {
    if (!$f1 = fopen($filename, "rb")) {
        trigger_error( "fopen {$filename} be fail", E_USER_ERROR );
    }

    $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
    if ($FILE['file_type'] != 19778) {
        trigger_error( "file type not equal to 19778", E_USER_ERROR );
    }

    $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
        '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
        '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
    $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
    if ($BMP['size_bitmap'] == 0)
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ($BMP['decal'] == 4)
        $BMP['decal'] = 0;

    $PALETTE = array();
    if ($BMP['colors'] < 16777216) {
        $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
    }

    $IMG = fread($f1, $BMP['size_bitmap']);
    $VIDE = chr(0);

    $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
    $P = 0;
    $Y = $BMP['height'] - 1;
    while ($Y >= 0) {
        $X = 0;
        while ($X < $BMP['width']) {
            switch ($BMP['bits_per_pixel']) {
                case 32:
                    $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                    break;
                case 24:
                    $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                    break;
                case 16:
                    $COLOR = unpack("n", substr($IMG, $P, 2));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    break;
                case 8:
                    $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    break;
                case 4:
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 2) % 2 == 0)
                        $COLOR[1] = ($COLOR[1] >> 4);
                    else
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    break;
                case 1:
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 8) % 8 == 0)
                        $COLOR[1] = $COLOR[1] >> 7;
                    elseif (($P * 8) % 8 == 1)
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    elseif (($P * 8) % 8 == 2)
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    elseif (($P * 8) % 8 == 3)
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    elseif (($P * 8) % 8 == 4)
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    elseif (($P * 8) % 8 == 5)
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    elseif (($P * 8) % 8 == 6)
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    elseif (($P * 8) % 8 == 7)
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    break;
                default:
                    return false;
                    break;
            }

            imagesetpixel($res, $X, $Y, $COLOR[1]);
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P+=$BMP['decal'];
    }
    fclose($f1);
    return $res;
}

