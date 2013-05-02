<?php

/**
 * Class SaleGoodsAction
 *
 * 商品销售Action，用于完成超市前台的商品销售功能
 * 包括、商品的销售，查询、折扣、退货等
 */
class SaleGoodsAction extends BaseAction{

    public function index(){
        //清空SESSION
        unset($_SESSION["goods_list"]);

        $this->display();
    }

    /**
     * 目前只是用于显示扫描条码页面，以后再添加其他业务逻辑
     */
    public function scanbarcode(){
        $this->display();
    }

    /**
     * 根据商品条码获取商品信息
     */
    public function getInfo(){
        $barcode = $this->_param("barcode");
        if(empty($barcode)){
            $this->error("参数错误");
        }
        $info = M("Goods")->getByBarcode($barcode);
        if(empty($info)){
            $this->error("不存在对应的商品信息！");
        }

        $map["branch_id"] = $_SESSION["staff_info"]["branch_id"];
        $now = time();
        //应该处于有效期内
        $map["time_start"] = array("elt",$now);
        $map["time_end"] = array("egt",$now);
        $map["goods_id"] = intval($info["id"]);
        $promotion = M("Promotions")->where($map)->find();
        //增加商品的促销信息
        if(!empty($promotion)){
            $info["promotions"] = $promotion;
        }

        $this->ajaxReturn($info,'获取商品信息成功！',1);
    }


    /**
     * 显示购物清单，
     * 同时获取商品的基本信息、出销信息（如果有的话），并计算商品总量，总额等用于页面展示
     * 同时页面自动处理表单输入，然后完成订单确认之后提交整个购物信息
     */
    public function showform(){
        $staffInfo = session("staff_info");
        $goodsList = session("goods_list");
        //商品出销信息的查询条件
        $map = array();
        $map["branch_id"] = $staffInfo["branch_id"];
        $now = time();
        //应该处于有效期内
        $map["time_start"] = array("elt",$now);
        $map["time_end"] = array("egt",$now);
        $promotions = M("promotions");

        $totalPrice = 0.0;
        $totalAmount = 0;
        //获取商品的促销信息
        foreach($goodsList as &$goods){
            //加入商品ID查询促销
            $map["goods_id"] = intval($goods["id"]);
            //获取商品的折扣信息
            $goods["promotions"] = $promotions->where($map)->find();

            //如果没有折扣
            if(empty($goods["promotions"])){
                $goods["real_price"] = $goods["sales_price"];
            }else{
                //有折扣就进行折扣
                $goods["real_price"] = $goods["sales_price"] * $goods["promotions"]["discount"];
            }

            $goods["total_price"] = $goods["real_price"] * $goods["amount"];
            $totalPrice += $goods["total_price"];
            $totalAmount += $goods["amount"];
        }
        //更新SESSION的值
        session("goods_list", $goodsList);

        //模板变量
        $this->list = $goodsList;
        $this->totalPrice = $totalPrice;
        $this->totalAmount = $totalAmount;
        $this->display();
    }


    /**
     * 处理商品销售
     * --------------------------------------------------------------------------------
     * 将SESSION中存储的商品列表以及员工信息取出，
     * 并从表单提交的数量中获取对应的商品数量，
     * 然后统一将数据传给SERVICE进行处理
     * -------------------------------------------------------------------------------
     */
    public function doSale(){

        //获取员工信息
        $staffInfo = session("staff_info");
        //获取商品列表
        $goodsList = &$_SESSION["goods_list"];
        $service = D("SalesRecord","Service");

        //获取各商品的数量
        foreach($goodsList as &$goods){
            $goods["amount"] = $this->_param("goods".$goods["id"],"intval",1);
        }

        try{
            $result = $service->doSale($staffInfo,$goodsList);
            //保存其他记录信息
            session("record_info",$result);
        }catch (Exception $e){
            $this->error("交易发生错误！".$e->getMessage());
        }

        //$this->success("销售成功！",U("SaleGoods/showticket"));
        //跳过提示成功页面直接重定向到打印小票
        $this->redirect("SaleGoods/showticket");
    }

    /**
     * 显示并打印小票信息
     */
    public function showticket(){
        $this->recordInfo =  session("record_info");
        $this->list = session("goods_list");
        $this->display();
    }
}