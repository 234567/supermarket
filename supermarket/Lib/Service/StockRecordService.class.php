<?php
/**
 * Class StockRecordService
 *
 * 商品入库记录业务逻辑
 */
class StockRecordService
{

    public function getList($map = array())
    {
        $model = D("StockRecord");
        $result = array();
        //是分店负责人，带上分店ID查询、及入库员工id
        $map["branch_id"] = $_SESSION["staff_info"]["branch_id"];
        //如果是管理员，可惜查询所有入库记录
        if ($_SESSION[C("ADMIN_AUTH_KEY")] === true) {
            //如果是管理员
            unset($map["branch_id"]);
        }
        $count = $model->where($map)->count("id");
        //查询入库记录
        if ($count > 0) {
            import("@.ORG.Util.Page");
            $p = new Page($count, 5);
            $map["supplier.status"] = 1;
            $field = array(
                "stock_record.id", "total_amount", "total_cost",
                "time", "supplier.id" => "supplier_id", "supplier.real_name" => "supplier_name",
                "branch.name" => "branch_name", "branch.id" => "branch_id"
            );
            $join = array("supplier on stock_record.supplier_id = supplier.id", "branch on stock_record.branch_id = branch.id");
            $result["list"] = $model->join($join)
                ->where($map)->field($field)->order("time desc")->limit($p->firstRow . ',' . $p->listRows)->select();
            $result["staff"] = D("Staff")->getById($_SESSION["staff_info"]["id"]);
            $result["page"] = $p->show();
        }
        return $result;
    }

    //后台管理人员查看入库记录
//    public function adminList($map=array()){
//        $model = D("StockRecord");
//        //存放数据的数组
//        $result =array();
//        $staff_info = $_SESSION["staff_info"];
//        if(false === $staff_info){
//            throw new ThinkException("请先登录！");
//        }
//
//        //是分店负责人，带上分店ID查询
//        $map["branch.id"] = $_SESSION["branch_info"]["id"];
//        //如果是管理员，可惜查询所有入库记录
//        if( session( C("ADMIN_AUTH_KEY") ) == true ){
//            //如果是管理员
//            unset($map["branch.id"]);
//        }
//        $staffCount = $model->count();
//        //存在记录
//        if($staffCount >0){
//            //分组查询入库记录的员工id
//            $staffList = $model->field("staff_id")->distinct(true)->order("staff_id")->select();
//            //循环变量
//            $i =0;
//            //根据入库员工ID，分别查询该入库员的入库记录
//            foreach($staffList as $staff){
//                /**
//                 * 1.根据入库员工ID在员工表中查询员工信息,并存放到result["staff"]中
//                 * 2.根据入库员工ID在入库记录中查询该员工的入库记录，并 存放到result["record"]中
//                 */
//                $staff_id = $staff["staff_id"];
//                $result[$i]["staff"] = D("Staff")->getById($staff_id);
//                //员工所属分店信息
//                $result[$i]["staff"]["branch"] = D("Branch")->getById($result[$i]["staff"]["branch_id"]);
//                $map = array("staff_id"=>array("eq",$staff_id));
//                $count = $model->where($map)->count("id");
//                //存在记录
//                if($count > 0){
//                    import("@.ORG.Util.Page");
//                    $p = new Page($count,5);
//                    $field = array(
//                        "stock_record.id","total_amount","total_cost","time","supplier.id"=>"sid","supplier.real_name","phone_number","mobile","address"
//                    );
//                    $result[$i]["record"] = $model->join("supplier on stock_record.supplier_id = supplier.id ")->where($map)->field($field)->order("time desc")->limit($p->firstRow.','.$p->listRows)->select();
//                    $result[$i]["page"] = $p->show();
//                }
//                $i++;
//            }
//        }
//        return $result;
//    }

    //查看入库记录详细
    public function detail($recordId, $supplierId)
    {
        //从页面获取入库记录id
        $model = D("StockItem");
        if (false === $recordId) {
            throw new ThinkException("入库记录ID为空！");
        }
        //查询入库记录详细
        $result = array();
        $count = $model->where("stock_record_id=" . $recordId)->count("id");
        if ($count > 0) {
            $field = array(
                "stock_item.id as itemId", "actual_cost", "amount", "remark ", "goods.*"
            );
            $result["list"] = $model->join("goods on stock_item.goods_id = goods.id")->field($field)->where("stock_record_id=" . $recordId)->select();
        }
        $result["supplier"] = M("Supplier")->getById($supplierId);
        return $result;
    }


    /**
     * 处理商品入库
     * @param $staffInfo
     * @param $stockList
     */
    public function doStock($staffInfo, $stockList)
    {
        $supplier_id = $_POST["supplier_id"];
        $total_amount = $_POST["total_amount"];
        $total_cost = $_POST["total_cost"];

        $record = M("StockRecord");
        //开启事务
        $record->startTrans();
        //处理商品入库记录数据
        $stockRecordData = array(
            "branch_id" => intval($staffInfo["branch_id"]), //分店id
            "staff_id" => intval($staffInfo["id"]), //员工id
            "supplier_id" => intval($supplier_id), //供货商id
            "total_amount" => $total_amount, //商品总数量
            "total_cost" => $total_cost, //商品总金额s
            "time" => time() //入库时间
        );
        $result = $record->create($stockRecordData);
        if (false == $result) {
            //插入失败，事务回滚
            $record->rollback();
            throw new ThinkException("record->create事务回滚" . $record->getError());
        }
        //插入数据
        $record_id = $record->add($result);
        if (false == $record_id) {
            //插入失败，事务回滚
            $record->rollback();
            throw new ThinkException("record->add数据插入失败" . $record->getError());
        }

        //处理入库记录的每项记录
        $item = M("StockItem");
        //处理每项入库项
        foreach ($stockList as $stockItem) {
            $itemData = array(
                "stock_record_id" => intval($record_id),
                "goods_id" => intval($stockItem["id"]),
                "actual_cost" => $stockItem["actual_cost"],
                "amount" => $stockItem["amount"],
                "remark" => $stockItem["remark"]
            );
            $result = $item->create($itemData);
            if (false === $result) {
                //插入失败，事务回滚
                $record->rollback();
                throw new ThinkException("create 每项入库错误" . $item->getError());
            }
            $item_id = $item->add($result);
            if (false == $item_id) {
                $record->rollback();
                throw new ThinkException("每项入库记录插入失败" . $item->getError());
            }
            /**
             * 根据入库项，修改分店商品信息
             * 1:根据商品id，查看该分店是否存在该商品信息
             * => 1)存在，修改该分店的商品数量
             * => 2)不存在，向分店添加该商品信息（即向branch_has_goods 中插入数据）
             */
            $branchGoods = M("branch_has_goods");
            $goods = $branchGoods->where("goods_id=" . $stockItem["id"] . " and branch_id=" . $staffInfo["branch_id"])->find();
            //分店下不存在该商品信息，将该商品加入到给分店中
            if (false == $goods) {
                $branchGoodsData = array(
                    "branch_id" => $staffInfo["branch_id"], //分店ID
                    "goods_id" => $stockItem["id"], //商品ID
                    "amount" => $stockItem["amount"] //商品数量
                );
                $result = $branchGoods->create($branchGoodsData);
                if (false == $result) {
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 分店添加出错" . $branchGoods->getError());
                }
                //向分店中添加商品信息
                $branchGoods_id = $branchGoods->add($result);
                if (false == $branchGoods_id) {
                    $record->rollback();
                    throw new ThinkException("分店添加商品失败" . $branchGoods->getError());
                }
            } else {
                //修改分店商品信息
                $branchGoodsData = array(
                    "branch_id" => $goods["branch_id"], //分店ID
                    "goods_id" => $goods["goods_id"], //商品ID
                    "amount" => $goods["amount"] + $stockItem["amount"] //商品数量
                );
                $result = $branchGoods->create($branchGoodsData);
                if (false == $result) {
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 分店添加出错" . $branchGoods->getError());
                }
                //向分店中添加商品信息
                $branchGoods_id = $branchGoods->save($result);
                if (false == $branchGoods_id) {
                    $record->rollback();
                    throw new ThinkException("分店修改商品失败" . $branchGoods->getError());
                }
            }
            //分店商品处理结束
            /**
             * 根据入库项，修改供货商商品信息
             * 1:根据供货商id，查看该供货商是否存在该商品信息
             * => 1)存在，修改最新货源价格
             * => 2)不存在，向供货商添加该商品信息（即向supplier_has_goods 中插入数据）
             */
            $suppler = M("supplier_has_goods");
            $supplierGoods = $suppler->where("goods_id=" . $stockItem["id"] . " and supplier_id=" . $supplier_id)->find();
            //供货商下不存在该商品信息，将该商品加入到给分店中
            if (false == $supplierGoods) {
                $supplierGoodsData = array(
                    "supplier_id" => $supplier_id, //供货商ID
                    "goods_id" => $stockItem["id"], //商品ID
                    "last_price" => $stockItem["actual_cost"] //最新货源价格
                );
                $result = $suppler->create($supplierGoodsData);
                if (false == $result) {
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 供货商商品添加出错" . $suppler->getError());
                }
                //向分店中添加商品信息
                $supplierGoods_id = $suppler->add($result);
                if (false == $supplierGoods_id) {
                    $record->rollback();
                    throw new ThinkException("供货商商品添加失败" . $suppler->getError());
                }
            } else {
                //否则修改分店商品信息
                $supplierGoodsData = array(
                    "supplier_id" => $supplierGoods["supplier_id"], //供货商ID
                    "goods_id" => $supplierGoods["goods_id"], //商品ID
                    "last_price" => $stockItem["actual_cost"] //最新货源价格
                );
                $result = $suppler->create($supplierGoodsData);
                if (false == $result) {
                    //插入失败，事务回滚
                    $record->rollback();
                    throw new ThinkException("create 供货商商品修改出错" . $suppler->getError());
                }
                //向分店中添加商品信息
                $supplierGoods_id = $suppler->save($result);
                if (false == $supplierGoods_id) {
                    $record->rollback();
                    throw new ThinkException("供货商商品修改失败" . $suppler->getError());
                }
            }
            //供货商商品处理结束

        }
        //入库记录提交
        $record->commit();
        /**
         * 入库项及入库记录插入成功
         * 清空入库记录 $stockList
         */
        unset($_SESSION["stock_list"]);
    }

}