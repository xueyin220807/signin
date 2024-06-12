<?php

namespace app\admin\controller\primary;

use app\admin\library\thinkQrcode;
use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use ZipArchive;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Activity extends Backend
{

    /**
     * Activity模型对象
     * @var \app\admin\model\primary\Activity
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\primary\Activity;

    }
    public function exportqrcode($ids){
        $primary_activity_id=intval($ids);
        $padModel=new \app\admin\model\Pad();
        $pads=$padModel->where("primary_activity_id",$primary_activity_id)->select();
        //var_dump($pads);
        if($pads){
            $qrcode = new thinkQrcode();
            $picfiles=[];
            $time=date("YmdHis");
            foreach($pads as $key=>$val){
                $url = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"]."/index/userpadp/index.html?pad_id=".$val["id"];
                $qrcodePath=$qrcode->png($url)->getPath();
                $systemFilePath=$_SERVER["DOCUMENT_ROOT"].$qrcodePath;

                $newQrcodePath=$_SERVER["DOCUMENT_ROOT"]."/uploads/qrcode/".$time."_".$val["id"]."_".$val["personal"].".png";
                //var_dump($newQrcodePath);
                rename($_SERVER["DOCUMENT_ROOT"].$qrcodePath,$newQrcodePath);
                //var_dump($systemFilePath);
                $picfiles[]=$newQrcodePath;
            }


            $zipRelativeDir="/uploads/primary_activity";
            $zipDir=$_SERVER["DOCUMENT_ROOT"].$zipRelativeDir;
            createDirIfNotExists($zipDir);
            $zip = new ZipArchive();
            $zipFileName='activity_archive_'.$primary_activity_id.'_'.$time.'.zip';
            $zipRelativePath=$zipRelativeDir.'/'.$zipFileName;
            $zipFileNameBySystem = $zipDir.'/'.$zipFileName;

            //var_dump($zipFileName);
            if ($zip->open($zipFileNameBySystem, ZipArchive::CREATE) !== TRUE) {

                throw new \Exception("無法打開 <$zipFileNameBySystem>\n");
            }
            foreach ($picfiles as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();

            $jump_url=$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$zipRelativePath;
            header('Location: '.$jump_url);


        }
    }
    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $rows=$list->items();
        if($rows){
            $padModel=new \app\admin\model\Pad();
            foreach($rows as $key=>&$val){
                $val["pad_count"]=$padModel->where("primary_activity_id",$val["id"])->count();
            }
        }
        $result = ['total' => $list->total(), 'rows' => $rows];
        return json($result);
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
