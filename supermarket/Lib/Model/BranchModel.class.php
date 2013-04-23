<?php
/**
 * User: corn-s
 */
//分店模型
class BranchModel extends CommonModel{
    //字段定义
    protected $fields = array(
        'id',//ID
        'name',//分店名
        'address',//地址
        'phone',//分店联系电话
        'director_staff_id',//负责人ID（根据id ，获取负责人名字，联系电话）
        'photo',//分店照片
        '_pk' => 'id',
        '_autoinc' => true
    );

    //自动完成
    protected $_auto = array(

    );

    //自动验证 待完善
    protected $_validate = array(
        array('name','require','分店名必须！',Model::EXISTS_VALIDATE,'',Model:: MODEL_BOTH ),//
        array('address','require','分店地址必须！',Model::EXISTS_VALIDATE,'',Model:: MODEL_BOTH ),//
        array('name','','分店名唯一！',Model::VALUE_VALIDATE,'unique',Model::MODEL_BOTH),
    );
}