<?php
/**
 * Class IndexAction
 *
 * 后台首页模块
 *
 */
class IndexAction extends BaseAction
{
    /**
     * 一些基本的统计信息，暂时比较简单
     * TODO:　增加商品库存不足提示，增加其他信息
     */
    public function index()
    {
        $this->staffAmount = M("Staff")->count("id");
        $map = array();
        $map["time"][] = array("gt",strtotime("-1 day"));
        $map["time"][] = array("lt",strtotime(date("Y-m-d")));
        $this->yesterdayIncoming = M("SalesRecord")->where($map)->sum("total_price");
        $this->branchAmount = M("Branch")->count("id");
        $this->display();
    }

}