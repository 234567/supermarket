<?php
/**
 * User: corn-s
 * Date: 13-4-23
 * Time: 下午6:23
 */
//商品分类模型
class CategoryModel extends CommonModel{
    //字段定义
    protected  $fields = array(
        'id',
        'pid', //父分类ID
        'name', //分类名称
        '_pk' => 'id',
        '_autoinc' => false,
    );
    protected $_auto = array(
       /* array('pid','0',Model:: MODEL_INSERT)*/
    );
    //自动验证 待完善
    protected $_validate = array(
        array('pid','require','分店名必须！',Model::EXISTS_VALIDATE,'',Model:: MODEL_BOTH )
    );
}
