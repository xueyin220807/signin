<?php

namespace app\admin\controller;

use app\admin\library\thinkQrcode;
use app\common\controller\Backend;
use chillerlan\QRCode\QRCode;
use fast\Random;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/*use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Renderer\RendererInterface;*/
/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Pad extends Backend
{

    /**
     * Pad模型对象
     * @var \app\admin\model\Pad
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Pad;

    }
    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function innerindex($ids = null)
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
            ->where(function($query) use($ids){
                if($ids){
                    $primary_activity_id=intval($ids);
                    if($primary_activity_id>0){

                        $query->where("primary_activity_id",$primary_activity_id);
                    }
                }
            })
            ->order($sort, $order)
            ->paginate($limit);
        $rows=$list->items();
        if($rows){
            $ActivityModel=new \app\admin\model\primary\Activity();
            $SchoolModel=new \app\admin\model\School();
            $PwoModel=new \app\admin\model\Pwo();
            $OrganizationModel=new \app\admin\model\Organization();
            foreach($rows as $key=>&$val){
                $val["primary_activity_name"]=$ActivityModel->where("id",$val["primary_activity_id"])->value("name");
                $val["school_name"]=$SchoolModel->where("id",$val["school_id"])->value("name");
                $organization_ids=$PwoModel->where("pad_id",$val["id"])->column("organization_id");
                $val["organization"]=$OrganizationModel->where("id","in",$organization_ids)->column("name");
            }
        }
        $result = ['total' => $list->total(), 'rows' => $rows];
        return json($result);
    }
    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add($ids = null)
    {
        if (false === $this->request->isPost()) {
            $activity=\app\admin\model\primary\Activity::where("id",intval($ids))->find();
            $this->assign("activity",$activity);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
       /* var_dump($params);
        exit();*/
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            /*if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);*/
            //$this->model->save
            $organizationModel=new \app\admin\model\Organization();
            $pwoModel=new \app\admin\model\Pwo();
            $pad=new \app\admin\model\Pad();
            $primary_activity_id=intval($params["primary_activity_id"]);
            $school_id=intval($params["school_id"]);


            $padData["primary_activity_id"]=$primary_activity_id;
            $padData["school_id"]=$school_id;
            $padData["personal"]=$params["personal"];

            $pad_id=$pad->insertGetId($padData);
            foreach($params["organization"] as $key=>$val){
                $organization_id=$organizationModel->insertGetId(["name"=>$val]);
                $pwoData["primary_activity_id"]=$primary_activity_id;
                $pwoData["organization_id"]=intval($organization_id);
                $pwoData["pad_id"]=$pad_id;
                $result=$pwoModel->insertGetId($pwoData);
            }
            $userModel=new \app\admin\model\User();
            $personal_id=$userModel->padUserAdd($params["personal"]);
            $padpModel=new \app\admin\model\Padp();
            $padpModel->insertGetId(["pad_id"=>$pad_id,"personal_id"=>$personal_id]);
            $result=$pad_id;
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }
    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function import($ids = null)
    {
        if (false === $this->request->isPost()) {
            $activity=\app\admin\model\primary\Activity::where("id",intval($ids))->find();
            $this->assign("activity",$activity);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        /* var_dump($params);
         exit();*/
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {

            $organizationModel=new \app\admin\model\Organization();
            $pwo=new \app\admin\model\Pwo();
            $pad=new \app\admin\model\Pad();

            //读取xls
            $xlsPath=$_SERVER["DOCUMENT_ROOT"].$params["xls"];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsPath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            foreach ($sheetData as $key=> $row) {
                if($key>0){
                    $school=$row[0];
                    $organizations=$row[1];
                    $organizationsArray=explode(",",$organizations);
                    $personal=$row[2];
                    $schoolModel=new \app\admin\model\School();
                    $school_id=$schoolModel->insertGetId(["name"=>$school]);

                    $primary_activity_id=intval($params["primary_activity_id"]);
                    $padData["primary_activity_id"]=$primary_activity_id;
                    $padData["school_id"]=$school_id;
                    $padData["personal"]=$personal;
                    $pad_id=$pad->insertGetId($padData);
                    if($organizationsArray){
                        foreach($organizationsArray as $ok=>$ov){
                            $organization_id=$organizationModel->insertGetId(["name"=>$ov]);
                            $pwoData["primary_activity_id"]=$primary_activity_id;
                            $pwoData["organization_id"]=intval($organization_id);
                            $pwoData["pad_id"]=$pad_id;
                            $result=$pwo->insertGetId($pwoData);
                        }
                    }
                    $userModel=new \app\admin\model\User();
                    $personal_id=$userModel->padUserAdd($personal);
                    $padpModel=new \app\admin\model\Padp();
                    $padpModel->insertGetId(["pad_id"=>$pad_id,"personal_id"=>$personal_id]);
                    $result=$pad_id;
                }
            }


            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }
    public function export($ids = null){
        // 创建新的Spreadsheet对象
        $spreadsheet = new Spreadsheet();
        $primaryActivityModel=new \app\admin\model\primary\Activity();
        $schoolModel=new \app\admin\model\School();
        $organizationModel=new \app\admin\model\Organization();
        $pwoModel=new \app\admin\model\Pwo();
        // 获取活动的工作表
        $sheet = $spreadsheet->getActiveSheet();
        $primaryActivity_id=intval($ids);
        $primaryActivity=$primaryActivityModel->where("id",$primaryActivity_id)->find();
        $filename=$primaryActivity["name"]."_".date("YmdHis").'.xlsx';
        // 设置工作表标题
        $sheet->setTitle($primaryActivity["name"]);

        // 设置单元格数据
        $sheet->setCellValue('A1', '學校');
        $sheet->setCellValue('B1', '組織');
        $sheet->setCellValue('C1', '個人');

        $padModel=new \app\admin\model\Pad();
        $padList=$padModel->where("primary_activity_id",$primaryActivity_id)->select();
        if($padList){
           /* $padList=$padList->toArray();*/
            $max_organization_str_length=strlen('組織');
            foreach($padList as $key=>$val){
                $schoolIndex='A'.($key+2);
                $school=$schoolModel->where("id",$val["school_id"])->find();
                $sheet->setCellValue($schoolIndex, $school['name']);

                $personalIndex='C'.($key+2);
                $sheet->setCellValue($personalIndex, $val["personal"]);

                $organizationListStr="";
                /*
               $pwoList=$pwoModel->where("pad_id",$val["id"])->select();
               if($pwoList){
                   $pwoList=$pwoList->toArray();
                  foreach($pwoList as $ok=>$ov){
                       $organization=$organizationModel->where("id",$ov["organization_id"])->find();
                       $organizationListStr.=
                   }
               }
               */
                $organization_id_list=$pwoModel->where("pad_id",$val["id"])->column("organization_id");
                $organization_name_list=$organizationModel->where("id","in",$organization_id_list)->column("name");
                if($organization_name_list){
                    $organizationListStr= implode(",",$organization_name_list);
                    if(strlen($organizationListStr)>$max_organization_str_length){
                        $max_organization_str_length=strlen($organizationListStr);
                    }
                }
                $organizationIndex='B'.($key+2);
                // 设置列宽自适应，根据内容调整列宽度
                /*$sheet->getColumnDimension('B')->setAutoSize(true);*/
                $sheet->getColumnDimension('B')->setWidth($max_organization_str_length*1);
                // 设置列宽自适应，根据内容调整A列宽度
                $sheet->getStyle('B')->getFont()->setSize(16);
                $sheet->setCellValue($organizationIndex,$organizationListStr);

            }
        }
        // 创建Excel写入器
        $writer = new Xlsx($spreadsheet);

        // 保存Excel文件到服务器的一个文件中
        $writer->save($filename);

        // 或者直接输出到浏览器下载
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filename);
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
    public function qrcodeBySvgToBase64($url,$pic_name){
        $qrcode = new QRCode();
        $qrcodeBase64 = $qrcode->render($url);
        $this->assign("qrcodeBase64",$qrcodeBase64);
        $svgData = str_replace('data:image/svg+xml;base64,', '', $qrcodeBase64);
        $decodedSvgData = base64_decode($svgData);
        $imagick = new \Imagick();
        // 读取SVG数据
        $imagick->readImageBlob($decodedSvgData);
        // 设置输出格式为PNG
        $imagick->setImageFormat('png');
        // 将SVG转换为PNG并获取转换后的数据
        $pngData = $imagick->getImageBlob();
        // 创建PNG图像资源
       $pngImage = imagecreatefromstring($pngData);
        $pic_dir="/uploads/".date("Ymd");
        $fileSystemDir=$_SERVER["DOCUMENT_ROOT"].$pic_dir;
        $filename=$fileSystemDir."/".$pic_name;
         imagepng($pngImage, $filename);
    }
    public function itemqrcode($ids=null){
        try{
            $id=intval($ids);

            $url = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"]."/index/userpadp/index.html?pad_id=".$id;



            $qrcode = new thinkQrcode();
            $qrcodePath=$qrcode->png($url)->getPath();
           /* var_dump($qrcodePath);*/

            $this->assign("qrcodePath",$qrcodePath);
            $this->assign("qrcodeLink",$url);
            return $this->view->fetch();
        }
        catch(\Exception $e){
            var_dump($e->getMessage());
        }

    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
