<?php if (!defined('XXOO')) { exit('No direct script access allowed'); }

require_once ROOT . 'libs/OssUpload.lib.php';

class UploadControl extends Control {

    public function __construct() {
        parent::__construct();
    }

    public function image() {
        $upload = new OssUpload();

        if( $info = $upload->upload('xupload') ) {
            $rs = array(
                'status'    => 'succeed',
                'http_url'  => $info['http_url'],
            );
        } else {
            $rs = array(
                'status'    => 'error',
                'message'   => $upload->errmsg(),
            );
        }
        $this->resp($rs);
    }

    public function quillImage() {
        $config = [
            'allowedTypes'  => 'gif|png|jpg|jpeg',     //允许的文件格式,
            'domain'        => $GLOBALS['app']['img_domain']
        ];

        $fileUpload = new OssUpload($config);

        if( $file_info = $fileUpload->upload('quillImage') ) {
            $this->resp([
                'path' => $file_info['http_url'] . '?x-oss-process=image/resize,w_750'
            ]);
        }
        else {
            $message = $fileUpload->errmsg();
            $this->respError('601', $message);
        }
    }
}
