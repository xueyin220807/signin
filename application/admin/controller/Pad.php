<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

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
            $Organization=new \app\admin\model\Organization();
            $pwo=new \app\admin\model\Pwo();
            $pad=new \app\admin\model\Pad();
            $primary_activity_id=intval($params["primary_activity_id"]);
            $school_id=intval($params["school_id"]);


            $padData["primary_activity_id"]=$primary_activity_id;
            $padData["school_id"]=$school_id;
            $padData["personal"]=$params["personal"];

            $pad_id=$pad->insertGetId($padData);
            foreach($params["organization"] as $key=>$val){
                $organization_id=$Organization->insertGetId(["name"=>$val]);
                $pwoData["primary_activity_id"]=$primary_activity_id;
                $pwoData["organization_id"]=intval($organization_id);
                $pwoData["pad_id"]=$pad_id;
                $result=$pwo->insertGetId($pwoData);
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

            $Organization=new \app\admin\model\Organization();
            $pwo=new \app\admin\model\Pwo();
            $pad=new \app\admin\model\Pad();

            //读取xls
            $xlsPath=$_SERVER["DOCUMENT_ROOT"].$params["xls"];
           /* $spreadsheet = IOFactory::load($xlsPath);*/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsPath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            foreach ($sheetData as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . $cell . "</td>";
                }
                echo "</tr>";
            }
exit();
            $primary_activity_id=intval($params["primary_activity_id"]);
            $school_id=intval($params["school_id"]);


            $padData["primary_activity_id"]=$primary_activity_id;
            $padData["school_id"]=$school_id;
            $padData["personal"]=$params["personal"];

            $pad_id=$pad->insertGetId($padData);
            foreach($params["organization"] as $key=>$val){
                $organization_id=$Organization->insertGetId(["name"=>$val]);
                $pwoData["primary_activity_id"]=$primary_activity_id;
                $pwoData["organization_id"]=intval($organization_id);
                $pwoData["pad_id"]=$pad_id;
                $result=$pwo->insertGetId($pwoData);
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

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
