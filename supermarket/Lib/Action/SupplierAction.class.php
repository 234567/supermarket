<?php
/**
 * Class SupplierAction
 *
 * 供货商管理模块
 * 负责处理供货商相关功能
 */
class SupplierAction extends BaseAction
{

    /**
     * 搜索供货商能够提供的商品列表
     */
    public function searchGoods()
    {
        //实例化Service
        $service = D($this->getActionName(), "Service");
        //无过滤条件获取列表
        try {
            $result = $service->searchGoods();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->goodsList = $result["goodsList"];
        $this->supplier = $result["supplier"];
        $this->page = $result["page"];
        $this->display();
    }

    /**
     * 供货商之间进行价格比较
     */
    public function goodscompare()
    {
        $service = D($this->getActionName(), "Service");
        //无过滤条件获取列表
        try {
            $result = $service->goodscompare();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->current_supplier_id = $result["current_supplier_id"];
        $this->goods = $result["goods"];
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }

    //检查供货商名称是否重复
    public function checkName()
    {
        $service = D("Supplier", "Service");
        $name = $this->_param("value");
        $id = $this->_param("id");
        $service->checkName($name, $id);
    }

    //查询出status为0（不可用），-1（已删除）的供货商
    public function trash()
    {
        $service = D("Supplier", "Service");
        $map = array("status" => array("neq", 1));
        try {
            $result = $service->trash($map);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }

    //根据不用操作，对status状态进行操作
    public function operate()
    {
        $service = D("Supplier", "Service");
        $id = $this->_param("id");
        $op = $this->_param("op");
        try {
            $result = $service->operate($id, $op);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("操作成功");
    }

}