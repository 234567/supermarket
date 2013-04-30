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
            $field = array(
             "stock_record.id","total_amount","total_cost","time","supplier.id"=>"sid","supplier.real_name","phone_number","mobile","address"
            );
            $result["list"] = $model->join("supplier on stock_record.supplier_id = supplier.id ")->where($map)->field($field)->order("time desc")->limit($p->firstRow.','.$p->listRows)->select();
            $result["staff"]= D("Staff")->getById($_SESSION["staff_info"]["id"]);
            $result["page"] = $p->show();
        }
        return $result;
    }

    //查看入库记录详细
    public function detail(){
        //从页面获取入库记录id
        $model = D("StockItem");
        $recordId = $_GET["recordId"];
        $supplierId = $_GET["supplierId"];

        if(false === $recordId){
            throw new ThinkException("入库记录ID为空！");
        }
        //查询入库记录详细
        $result = array();
        $count = $model->where("stock_record_id=".$recordId)->count("id");
        if($count > 0){
            $field = array(
                "stock_item.id as itemId","actual_cost","amount","remark ","goods.*"
            );
            $result["list"] =$model->join("goods on stock_item.goods_id = goods.id")->field($field)->where("stock_record_id=".$recordId)->select();
        }
        $result["supplier"] = M("Supplier")->getById($supplierId);
        return $result;
    }



    /**
     * 处理商品入库
     * @param $staffInfo
     * @param $stockList
     */
    public function doStock($staffInfo, $stockList){
        $supplier_id = $_POST["supplier_id"];
        $total_amount = $_POST["total_amount"];
        $total_cost = $_POST["total_cost"];

        $record = M("StockRecord");
        //开启事务
        $record->startTrans();
        //处理商品入库记录数据
        $stockRecordData = array(
            "branch_id" => intval($staffInfo["branch_id"]),//分店id
            "staff_id" => intval( $staffInfo["id"]),//员工id
            "supplier_id" => intval($supplier_id),//供货商id
            "total_amount" => $total_amount,//商品总数量
            "total_cost" => $total_cost,//商品总金额s
            "time" => time()//入库时间
        );
        trace($stockRecordData);
        $result = $record->create($stockRecordData);
        if(false == $result){
            //插入失败，事务回滚
            $record->rollback();
            throw new ThinkException("record->create事务回滚".$record->getError());
        }
        //插入数据
        $record_id =  $record->add($result);
        if(false == $record_id){
            //插入失败，事务回滚
            $record->rollback();
            throw new ThinkException("record->add数据插入失败".$record->getError());
        }

        //处理入库记录的每项记录
        $item = M("StockItem");
        //处理每项入库项
        foreach($stockList as $stockItem){
            $itemData = array(
                "stock_record_id" =>intval($record_id),
                "goods_id"=>intval($stockItem["id"]),
                "actual_cost"=>$stockItem["actual_cost"],
                "amount" => $stockItem["amount"],
                "remark" =>$stockItem["remark"]
            );
            $result= $item->create($itemData);
            if(false === $result){
                //插入失败，事务回滚
                $record->rollback();
                throw new ThinkException("create 每项入库错误".$item->getError());
            }
           $item_id =  $item->add($result);
           if(false == $item_id){
               $record->rollback();
               throw new ThinkException("每项入库记录插入失败".$item->getError());
           }
            /**
             * 根据入库项，修改分店商品信息
             * 1:根据商品id，查看该分店是否存在该商品信息
             * => 1)存在，修改该分店的商品数量
             * => 2)不存在，向分店添加该商品信息（即向branch_has_goods 中插入数据）
             */
            $branchGoods = M("branch_has_goods");
            $goods = $branchGoods->where("goods_id=".$stockItem["id"]." and branch_id=".$staffInfo["branch_id"])->find();
            //分店下不存在该商品信息，将该商品加入到给分店中
            if(false == $goods){
                $branchGoodsData = array(
                    "branch_id"=>$staffInfo["branch_id"],//分店ID
                    "goods_id"=>$stockItem["id"],//商品ID
                    "amount"=>$stockItem["amount"]//商品数量
                );
                $result= $branchGoods->create($branchGoodsData);
                if(false == $result){
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 分店添加出错".$item->getError());
                }
                //向分店中添加商品信息
                $branchGoods_id = $branchGoods->add($result);
                if(false == $branchGoods_id){
                    $record->rollback();
                    throw new ThinkException("分店添加商品失败".$branchGoods->getError());
                }
            }else{
                //修改分店商品信息
                $branchGoodsData = array(
                    "branch_id"=>$goods["branch_id"],//分店ID
                    "goods_id"=>$goods["goods_id"],//商品ID
                    "amount"=>$goods["amount"]+$stockItem["amount"]//商品数量
                );
                $result= $item->create($branchGoodsData);
                if(false == $result){
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 分店添加出错".$item->getError());
                }
                //向分店中添加商品信息
                trace($branchGoodsData);
                $branchGoods_id = $branchGoods->save($result);
                if(false == $branchGoods_id){
                    $record->rollback();
                    throw new ThinkException("分店修改商品失败".$branchGoods->getError());
                }
            }
            //提交事务
            $branchGoods->commit();
            /**
             * 分店商品处理结束
             */
        }
        //每项入库记录 事务提交
        $item->commit();
        //入库记录提交
        $record->commit();
        /**
         * 入库项及入库记录插入成功
         * 清空入库记录 $stockList
         */
        session("stock_list",null);
    }

}