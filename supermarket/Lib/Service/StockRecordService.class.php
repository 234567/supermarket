<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-25
 * Time: 下午5:51
 * To change this template use File | Settings | File Templates.
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
}