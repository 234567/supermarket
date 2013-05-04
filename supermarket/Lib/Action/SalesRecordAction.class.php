<?php

/**
 * Class SalesRecordAction
 *
 * 销售管理模块，
 * 功能如下：
 * 查看超市所有员工的销售记录、
 * 查看特定员工的销售记录
 * 查看指定分店的销售记录
 * 图表显示以上销售记录
 */
class SalesRecordAction extends BaseAction{

    /**
     * 以图表形式显示最近的销售记录
     * 并且可以在各个分店之间进行数据比较
     */
    public function showchart(){
        $day = $this->_param("recent","intval",7);
        $data = $service = D("SalesRecord","Service")->countRecent($day);
        $this->data = json_encode($data);
        $this->display();
    }

}