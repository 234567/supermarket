<?php

/**
 * Class StockGoodsAction
 *
 * 商品入库模块
 *
 */
class StockGoodsAction extends BaseAction{

    /**
     * 入库人员首页
     */
    public function index(){

        $this->display();
    }

    public function begin(){

        $this->display();
    }

    public function pause(){
        //TODO：实现商品入库记录的暂停

        $this->success("<h1>尚未实现!</h1>");
    }


    /**
     * 取消本次入库操作
     *
     */
    public function cancel(){
        //清空入库商品列表
        session("stock_list",null);
        $this->redirect("StockGoods/index");
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
        $this->ajaxReturn($info,'获取商品信息成功！',1);
    }


    public function addToList(){
        $barcode = $this->_param("barcode");
        $actual_cost = $this->_param("actual_cost");
        $amount = $this->_param("amount");
        $remark = $this->_param("remark");
        if(empty($barcode) || empty($actual_cost) || empty($amount)){
            $this->error("请填写完整的商品信息");
        }

        $goods = M("Goods")->getByBarcode($barcode);
        $goods["actual_cost"] = floatval( $actual_cost );
        $goods["amount"] = intval( $amount );
        $goods["remark"] =  $remark;

        //加入入库清单
        $_SESSION["stock_list"][] = $goods;
        $this->success("添加成功");
    }

    /**
     * 显示待入库操作的商品列表
     */
    public function showlist(){
        $stock_list = session("stock_list");
        $totalPrice = 0.0;
        $totalAmount = 0;
        foreach($stock_list as $stock){
            $totalAmount += $stock["amount"];
            $totalPrice += $stock["amount"] * $stock["actual_cost"];
        }

        $this->list = $stock_list;
        $this->totalPrice = $totalPrice;
        $this->totalAmount = $totalAmount;
        $this->display();
    }

    /**
     * 修改列表中指定商品的入库信息
     */
    public function modify(){
        $goodsId = $this->_param("goodsId");
        $cost = $this->_param("cost","floatval");
        $amount = $this->_param("amount","intval");

        if(empty($goodsId) || empty($cost) || empty($amount)){
            $this->error("请输入正确的修改信息！");
        }

        $stockList = session("stock_list");
        foreach($stockList as &$stock){
            if($stock["id"] == $goodsId){
                $stock["actual_cost"] = $cost;
                $stock["amount"] = $amount;
            }
        }
        session("stock_list",$stockList);
        $this->success("修改成功！");
    }

    /**
     * 从入库列表中删除指定商品
     */
    public function del(){
        $goodsId = $this->_param("goodsId");
        $stockList = session("stock_list");
        $len = count($stockList);
        while($len--){
            if($stockList[$len]["id"] == $goodsId){
                array_splice($stockList, $len, 1);
//                unset($stockList[$len]);
            }
        }
        session("stock_list",$stockList);
        $this->success("删除成功！");
    }

    /**
     * 处理整个商品入库
     */
    public function doStock(){
        //获取员工信息
        $staffInfo = session("staff_info");
        //获取商品列表
        $stock_list = &$_SESSION["stock_list"];
        $service = D("StockRecord","Service");

        try{
           $service->doStock($staffInfo,$stock_list);
        }catch (Exception $e){
            $this->error("入库发生错误！".$e->getMessage());
        }
        $this->success("入库成功！",U("StockGoods/index"));
    }

    /**
     * 员工查看自己的入库记录
     */
    public function history(){
        //获取员工信息
        $staffInfo = session("staff_info");
        $map = array();
        $map["staff_id"] = $staffInfo["id"];
        $map["branch_id"] = $staffInfo["branch_id"];
        $result = D("StockRecord","Service")->getList($map);
        $this->list = $result["list"];
        $this->staff = $result["staff"];
        $this->page = $result["page"];
        $this->display();
    }

    //查看入库记录详细
    public function detail(){
        $service = D("StockRecord","Service");
        try{
            $result = $service->detail();
        }catch (Exception $e){
            $this->error("查看入库详细出差".$e->getMessage());
        }
       $this->list = $result["list"];
       $this->supplier = $result["supplier"];
        trace($result);
       $this->display();
    }
}
