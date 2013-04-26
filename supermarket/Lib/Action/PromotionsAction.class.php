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
        $this->branch = $result["branch"];
        $this->goods = $result["goods"];
        $this->display();
    }
    public function edit(){
        $service = D($this->getActionName(),"Service");
        $result = $service->edit();
        $this->promotions = $result["promotions"];
        $this->goods = $result["goods"];
        $this->display();
    }

}