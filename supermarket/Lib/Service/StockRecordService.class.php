<?php
/**
 * Class StockRecordService
 *
 * 商品入库记录业务逻辑
 */
class StockRecordService{

    public function getList($map){
        $model = D("StockRecord");
        $result =array();
        $count = $model->where($map)->count();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,5);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }


    /**
     * 处理商品入库
     * @param $staffInfo
     * @param $stockList
     */
    public function doStock($staffInfo, $stockList){


    }

}