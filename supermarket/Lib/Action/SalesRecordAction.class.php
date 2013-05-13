<?php

/**
 * Class SalesRecordAction
 *
 * 销售管理模块，
 * 功能如下：
 * 查看超市所有员工的销售记录、
 * 查看特定员工的销售记录
 * 查看指定分店的销售记录
 * 图表显示销售统计信息
 */
class SalesRecordAction extends BaseAction
{

    /**
     * 以图表形式显示最近的销售记录
     * 并且可以在各个分店之间进行数据比较
     */
    public function showChart()
    {
        $day = $this->_param("recent", "intval", 7);
        $data = $service = D("SalesRecord", "Service")->countRecent($day);
        $this->data = json_encode($data);
        $this->display();
    }

    /**
     * 后台管理员查看销售记录
     *
     */
    public function index()
    {
        try {
            $service = D("SalesRecord", "Service");
            //如果不是管理员，限定分店ID为自己的管理的分店。
            if ($_SESSION[C("ADMIN_AUTH_KEY")] !== true) {
                $branchId = $_SESSION["staff_info"]["branch_id"];
            }
            $result = $service->getList(array(), $branchId);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->list = $result['list'];
        $this->page = isset($result['page']) ? $result['page'] : null;
        $this->display();
    }

    /**
     * 多条件搜索销售记录（区分管理员与负责人）
     */
    public function search()
    {
        //获取过滤参数
        $branchId = $this->_param("branchId", "intval", 0);
        //查看的员工ID
        $staffId = $this->_param("staffId", "intval", 0);
        $startTime = $this->_param("starttime", "strtotime", 0);
        $endTime = $this->_param("endtime", "strtotime");
        $map = array();
        //限定开始时间
//        $map["sales_record.time"] = array();
        if (!empty($startTime)) {
            $map["sales_record.time"][] = array("gt", $startTime);
        }
        if (!empty($endTime)) {
            $map["sales_record.time"][] = array("lt", $endTime);
        }

        try {
            $service = D("SalesRecord", "Service");
            //如果不是管理员，限定分店ID为自己的管理的分店。
            if ($_SESSION[C("ADMIN_AUTH_KEY")] !== true) {
                $branchId = $_SESSION["staff_info"]["branch_id"];
            }
            $result = $service->getList($map, $branchId, $staffId);
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

    /**
     * 查看销售记录详细，如果是负责人，限定分店。
     */
    public function showDetail()
    {
        $recordId = $this->_param("recordId");

        $service = D("SalesRecord", "Service");
        try {
            if ($_SESSION[C("ADMIN_AUTH_KEY")] === true) {
                $result = $service->getDetail($recordId);
            } else {
                $result = $service->getDetail($recordId, $_SESSION["staff_info"]["branch_id"]);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->items = $result["items"];
        $this->record = $result["record"];
        $this->display();
    }

}