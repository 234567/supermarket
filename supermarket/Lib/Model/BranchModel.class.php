<?php
/**
 * User: corn-s
 */
//分店模型
class BranchModel extends RelationModel{
    //字段定义
    protected $fields = array(
        'id',//ID
        'name',//分店名
        'address',//地址
        'phone',//分店联系电话
        'director_staff_id',//负责人ID（根据id ，获取负责人名字，联系电话）
        'photo',//分店照片
        'status',//status 状态：1表示正常，0表示不可用，-1表示已删除
        '_pk' => 'id',
        '_autoinc' => true
    );

    //自动完成
    protected $_auto = array(
        array("status","1",Model::MODEL_BOTH)
    );
    //自动验证 待完善
    protected $_validate = array(
        array('name','require','分店名必须！',Model::EXISTS_VALIDATE,'',Model:: MODEL_BOTH ),//
        array('address','require','分店地址必须！',Model::EXISTS_VALIDATE,'',Model:: MODEL_BOTH ),//
        array('name','','分店名唯一！',Model::VALUE_VALIDATE,'unique',Model::MODEL_BOTH),
//        array('director_staff_id','','分店负责人不能重复！',Model::VALUE_VALIDATE,'unique',Model::MODEL_BOTH),
    );

    //关联模型
    protected $_link = array(
        'director'=>array(
            'mapping_type' => HAS_MANY,//一个分店对应一个负责人（员工）
            'class_name'=> 'Staff',
            'foreign_key' => 'branch_id',
//            "condition"=>"id = director_staff_id",
            'mapping_fields' => array('name','mobile'),

        )
    );
}