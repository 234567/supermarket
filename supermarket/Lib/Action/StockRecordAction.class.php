<?php
/**
 * Class StockRecordAction
 *
 *
 */
class StockRecordAction extends BaseAction{

    public function index(){
        //实例化Service
        $service = D("StockRecord","Service");
        try{
            $result = $service->adminList();
        }catch (Exception $e){
            $this->error($service->getError());
        }
        $this->list = $result;
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