<?php

namespace app\admin\model\primary\activity;

use think\Model;


class Data extends Model
{

    

    

    // 表名
    protected $name = 'primary_activity_data';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
