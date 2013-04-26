<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-26
 * Time: 下午1:45
 * To change this template use File | Settings | File Templates.
 */
class PromotionsModel extends CommonModel{
    //字段定义
    protected $fields = array(
        "id",//主键ID
        "branch_id",//分店ID
        "goods_id",//商品ID
        "discount",//具体折扣 如0.8标识打8折
        "time_start",//商品打折开始时间
        "time_end"//商品打折结束时间
    );
    //自动完成
    protected $_auto = array(

    );
    //自动验证
    protected $_validate = array(
       array("time_start","require","开始时间必须!"),
       array("time_end","require","结束时间必须!"),

    );
}