<?php
/**
 * Class StockRecordAction
 *
 *
 */
class StockRecordAction extends BaseAction
{

    public function index()
    {
        //实例化Service
        $service = D("StockRecord", "Service");
        try {
            $result = $service->getList();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }

    //查看入库记录详细
    public function detail()
    {
        $recordId = $this->_param("recordId","intval");
        if(empty($recordId)){
            $this->error("参数错误！");
        }

        try {
            $service = D("StockRecord", "Service");
            if($_SESSION[C("ADMIN_AUTH_KEY")] === true){
                $result = $service->detail($recordId);
            }else{
                $result = $service->detail($recordId,$_SESSION["staff_info"]["branch_id"]);
            }
        } catch (Exception $e) {
            $this->error("查看入库详细出错" . $e->getMessage());
        }
        $this->list = $result["list"];
        $this->supplier = $result["supplier"];
        $this->display();
    }

    /**
     * 多条件搜索入库记录（区分管理员与负责人）
     */
    public function search()
    {
        //获取过滤参数
        $branchId = $this->_param("branchId", "intval", 0); //分店id
        $supplierId = $this->_param("supplierId", "intval", 0); //分店id
        $startTime = $this->_param("starttime", "strtotime", 0); //开始时间
        $endTime = $this->_param("endtime", "strtotime"); //结束时间
        $map = array();
        if (!empty($startTime)) {
            $map["stock_record.time"][] = array("gt", $startTime);
        }
        if (!empty($endTime)) {
            $map["stock_record.time"][] = array("lt", $endTime);
        }
        if (!empty($supplierId)) {
            $map["stock_record.supplier_id"] = array("eq", $supplierId);
        }
        if($_SESSION[C("ADMIN_AUTH_KEY")] === true && !empty($branchId)){
            $map["stock_record.branch_id"] =  $branchId;
        }else{
            //限定只能查看自己所在分店
            $map["stock_record.branch_id"] =  $_SESSION["staff_info"]["branch_id"];
        }

        try {
            $service = D("StockRecord", "Service");
            $result = $service->getList($map);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->branchId = $branchId;
        $this->starttime = $startTime;
        $this->endtime = $endTime;
        $this->list = $result['list'];
        $this->page = isset($result['page']) ? $result['page'] : null;
        $this->display();
    }
}