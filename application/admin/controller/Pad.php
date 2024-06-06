<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

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
        var_dump($params);
        exit();
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
            $pwo=\app\admin\model\Pwo();
            foreach($params["organization"] as $key=>$val){
                $organization_id=$Organization->insertGetId(["name"=>$val]);
                $pwo->insertGetId(["primary_activity_id"=>$params["primary_activity_id"],"organization_id"=>$organization_id]);
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
