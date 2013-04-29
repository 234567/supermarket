<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-24
 * Time: 下午10:06
 * To change this template use File | Settings | File Templates.
 */
class SupplierAction extends  BaseAction{

    public function searchGoods(){
        //实例化Service
        $service = D($this->getActionName(),"Service");
        //无过滤条件获取列表
        try{
            $result = $service->searchGoods();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->goodsList = $result["goodsList"];
        $this->supplier = $result["supplier"];
        $this->page = $result["page"];
        $this->display();
    }

    public function goodscompare(){
        $service = D($this->getActionName(),"Service");
        //无过滤条件获取列表
        try{
            $result = $service->goodscompare();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        trace($result);
        $this->current_supplier_id = $result["current_supplier_id"];
        $this->goods = $result["goods"];
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }
}