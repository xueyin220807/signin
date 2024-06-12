<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $jump_url=$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/Index/index/scanQrcode';
        header('Location: '.$jump_url);
        return $this->view->fetch();
    }
    public function scanQrcode(){
        return $this->view->fetch();
    }

}
