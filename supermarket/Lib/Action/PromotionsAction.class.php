<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-26
 * Time: 下午1:48
 * To change this template use File | Settings | File Templates.
 */

class PromotionsAction extends  BaseAction{
    //发布折扣
    public function release(){
        //实例化Service
        $service = D($this->getActionName(),"Service");
        $result = $service->release();
        if(false == $result){
            $this->error("该商品已存在折扣信息，不能重复添加折扣信息！",$this->getReturnUrl());
        }
        $this->branch = $result["branch"];
        $this->goods = $result["goods"];
        $this->display();
    }
    public function edit(){
        $service = D($this->getActionName(),"Service");
        $result = $service->edit();
        trace($result);
        if(false === $result["goods"]){
            $this->error("无权限删除其他分公司折扣信息!",U("promotions/index"));
        }
        $this->promotions = $result["promotions"];
        $this->goods = $result["goods"];
        $this->display();
    }

}