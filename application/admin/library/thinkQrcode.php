<?php

namespace app\admin\library;

use think\Config;

class thinkQrcode extends \dh2y\qrcode\QRcode
{
    public function __construct(){

        $this->config= Config::get('qrcode.');

        if(isset($this->config['cache_dir'])&&$this->config['cache_dir']!=''){
            $this->cache_dir = $this->config['cache_dir'];
        }else{
            $this->cache_dir = 'uploads/qrcode';
        }


        if (!file_exists($this->cache_dir)){
            mkdir ($this->cache_dir,0775,true);
        }
        $qrlibPath=$_SERVER["DOCUMENT_ROOT"]."/../vendor/dh2y/think-qrcode/src/phpqrcode/qrlib.php";
        /*require("phpqrcode/qrlib.php");*/
        require($qrlibPath);
    }
}