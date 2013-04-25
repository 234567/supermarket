<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-24
 * Time: 下午9:56
 */
//供货商模型
class SupplierModel extends  RelationModel{
    //字段定义
    protected $field = array(
        "id",//主键ID
        "real_name",//供货商名称,
        "phone_number",//供货商电话号码
        "mobile",//供货商手机号码
        "address"//联系地址
    );
    //自动完成
    protected $_auto = array(

    );
    //自动验证
    protected $_validate = array(
        array("real_name","require","供货商名称必须!"),
        array("phone_number","require","供货商名称必须!"),
        array("real_name","","供货商名称唯一!",Model::VALUE_VALIDATE,"unique",Model::MODEL_BOTH)

    );

    //关联模型
    protected $_link = array(
        "goods"=>array(
            "mapping_type"=>MANY_TO_MANY,
            "class_name"=>"Goods",
            "foreign_key"=>"supplier_id",
            "relation_foreign_key"=>"goods_id",
            "relation_table"=>"supplier_has_goods"
        )
    );
}