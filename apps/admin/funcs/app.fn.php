<?php if(!defined("XXOO")) exit("No direct script access allowed");

// require_once ROOT . 'funcs/qrcode.fn.php';
// require_once ROOT . 'libs/tcpdf/tcpdf.php';

/**
 * 访问拦截钩子
 * 首先检查用户是否登录，如果已登录再检查是否有访问权限
 */
function access_hook() {
    // echo json_encode($_SERVER);

    // $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
    
    // // 设置文档信息 
    // $pdf->SetCreator('Helloweba'); 
    // $pdf->SetAuthor('yueguangguang'); 
    // $pdf->SetTitle('Welcome to helloweba.com!'); 
    // $pdf->SetSubject('TCPDF Tutorial'); 
    // $pdf->SetKeywords('TCPDF, PDF, PHP'); 
    
    // // 设置页眉和页脚信息 
    // $pdf->SetHeaderData('logo.png', 30, 'Hello', '亞洲生化科技股份有限公司',  
    //     array(0,64,255), array(0,64,128)); 
    // $pdf->setFooterData(array(0,64,0), array(0,64,128)); 
    
    // // 设置页眉和页脚字体 
    // $pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
    // $pdf->setFooterFont(Array('helvetica', '', '8')); 
    
    // // 设置默认等宽字体 
    // $pdf->SetDefaultMonospacedFont('courier'); 
    
    // // 设置间距 
    // $pdf->SetMargins(15, 27, 15); 
    // $pdf->SetHeaderMargin(5); 
    // $pdf->SetFooterMargin(10); 
    
    // // 设置分页 
    // $pdf->SetAutoPageBreak(TRUE, 25); 
    
    // // set image scale factor 
    // $pdf->setImageScale(1.25); 
    
    // // set default font subsetting mode 
    // $pdf->setFontSubsetting(true); 
    
    // //设置字体 
    // $pdf->SetFont('stsongstdlight', '', 14); 
    
    // $pdf->AddPage(); 
    
    // $str1 = '領貨卷 : B032135460'; 
    
    // $pdf->Write(0,$str1,'', 0, 'L', true, 0, false, false, 0); 
    
    // //输出PDF 
    // $res = $pdf->Output('t.pdf', 'S');

    // $cache_name = APP . 'funcs/test.pdf';
    // file_put_contents($cache_name, $res);
}

/**
 * 攔截生成碼
 */
function qrcode_hook() {
    // 緩衝生成檔案
    // $cache_name = APP . 'funcs/test.txt';
    // file_put_contents( $cache_name , ob_get_contents() );
    
    // if ( $cache = file_get_contents( $cache_name ) ) {
    //     $data = json_decode($cache, true);
    //     /**
    //     *phpqrcode.php提供了一个关键的png()方法，有关png()方法的参数说明如下
    //     *参数1：要转成二维码的url地址
    //     *参数2：默认为false，不生成文件，只将二维码图片返回；若为true，则需要给出存放生成二维码图片的路径
    //     *参数3：控制二维码容错率，不同的参数表示二维码可被覆盖的区域百分比；这个参数可传递的值分别是L(QR_ECLEVEL_L，7%)，M(QR_ECLEVEL_M，15%)，Q(QR_ECLEVEL_Q，25%)，H(QR_ECLEVEL_H，30%)
    //     *参数4：控制生成图片的大小，默认为4
    //     *参数5：控制生成二维码的空白区域大小
    //     *参数6：保存二维码图片并显示出来，前提是参数2必须传递图片路径
    //     **/
    //     $qrcode = new \QRcode();
    //     // 要转成二维码的url地址
    //     $url = $data['data'];
    //     // 容错率
    //     $errorLevel = 'L';
    //     // 生成图片大小
    //     $size = '4';
    //     // 若二维码图片未正常输出，需先清除缓存
    //     ob_clean();
    //     // 调用 png() 方法生成二维码
    //     $qrcode->png($url, false, $errorLevel, $size);
    // }
}

/**
 * 清除緩存
 */
function boot_hook() {
    ob_clean();
    exit;
}
