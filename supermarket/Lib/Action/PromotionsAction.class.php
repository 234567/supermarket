<?php
/**
 * Class PromotionsAction
 *
 * 商品促销模块
 */
class PromotionsAction extends  BaseAction{


    /**
     * 发布折扣
     */
    public function release(){
        //实例化Service
        $service = D($this->getActionName(),"Service");
        $result = $service->release();
        if(false == $result){
            $this->error("该商品已存在折扣信息，不能重复添加折扣信息！",$this->getReturnUrl());
        }
        $this->branch = $result["branch"];
        $this->goods = $result["goods"];
        trace($result["goods"]);
        $this->display();
    }


    /**
     * 修改折扣信息
     */
    public function edit(){
        $id = $this->_param("id","intval");
        $branchId = $_SESSION["staff_info"]["branch_id"];
        $service = D($this->getActionName(),"Service");

        $result = $service->edit($branchId,$id);

        if(false === $result["goods"]){
            $this->error("无权限删除其他分公司折扣信息!",U("promotions/index"));
        }
        $this->promotions = $result["promotions"];
        $this->goods = $result["goods"];
        $this->display();
    }

    public function del(){
        $id = $this->_param("id","intval");
        $branchId = $_SESSION["staff_info"]["branch_id"];
        $service = D("Promotions","Service");
        try{
            $service->del($branchId,$id);
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("删除成功！");
    }

}