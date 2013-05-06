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
    //新增字段配置
    protected $insertFields  = array(
        "branch_id",
        "goods_id",
        "discount",
        "time_start",
        "time_end"
    );
    //修改时字段配置
    protected $updateFields  = array(
        "id","discount","time_start","time_end"
    );

    //自动完成
    protected $_auto = array(
        array("time_start","strtotime",Model::MODEL_BOTH,"function"),
        array("time_end","strtotime",Model::MODEL_BOTH,"function"),
    );
    //自动验证
    protected $_validate = array(
       array("branch_id","require","分店id不能为空!"),
       array("goods_id","require","商品id不能为空!"),
       array("discount","require","折扣不能为空!"),
       array("time_start","require","开始时间必须!"),
       array("time_end","require","结束时间必须!"),
       array("goods_id","checkHasPromotion","该商品此期间已经有促销信息了!",Model::MUST_VALIDATE ,'callback',Model:: MODEL_INSERT ),
    );

    /**
     *
     */
    protected function checkHasPromotion(){
        //获取商品ID
        $goodsId = $_POST['goods_id'];
        $map = array();
        $now = time();
        $map["goods_id"] = $goodsId;
        $map["time_start"][] = array("elt",$now);
        $map["time_end"][] = array("egt",$now);
        $promotions = M("Promotions")->where($map)->find();
        if(!empty($promotions)){
            return false;
        }
        return true;
    }
}