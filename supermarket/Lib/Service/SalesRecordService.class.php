<?php

/**
 * Class SalesRecordService
 *
 * 前台销售的业务逻辑类
 */
class SalesRecordService {


    /**
     * @param $staffInfo        员工信息
     * @param $goodsList    商品列表，包含商品数量
     * @return array               返回一些销售统计信息
     * @throws ThinkException
     */
    public function doSale($staffInfo,$goodsList){
        //商品总额
        $totalPrice = 0.0;
        //商品总数量
        $totalAmount = 0;
        //总共节省
        $totalSave = 0.0;

        $record = D("SalesRecord");
        //开启事务
        $record->startTrans();
        //先将销售记录总项数据进行插入
        $recordData = array(
            "branch_id"=>intval($staffInfo["branch_id"]),
            "staff_id" => intval($staffInfo["id"]),
            "staff_name" => $staffInfo["name"],
            "time"=>time(),
            "total_amount" => $totalAmount,
            "total_price" => $totalPrice,
            "total_saving" => $totalSave,
            "pay_type" => 0, //默认为现金支付，暂不支持会员支付
        );
        $result = $record->create($recordData);
        if(false === $result){
            throw new ThinkException($record->getError());
        }

        $recordId = $record->add($result);
        if(false === $recordId){
            $record->rollback();
            throw new ThinkException($record->getError());
        }


        $items = D("SalesItem");
        foreach($goodsList as &$goods){
            //统计商品总量
            $totalAmount += $goods["amount"];
            //统计交易总额
            $totalPrice  += $goods["amount"]*$goods["real_price"];
            $itemData = array(
                "sales_record_id" => $recordId,
                "goods_id" => $goods["id"],
                "goods_name" => $goods["name"],
                "price_sales" =>    $goods["sales_price"],
                "price_real" => $goods["real_price"],
                "amount" => $goods["amount"],
            );

            //计算节省金额
            $totalSave += ($goods["sales_price"] - $goods["real_price"]) *$goods["amount"];
            $result = $items->create($itemData);
            if(false === $result){
                throw new ThinkException($items->getError());
            }
            $result = $items->add($result);
            if(false === $result){
                $record->rollback();
                throw new ThinkException($items->getError());
            }
        }

        $recordData["id"] = $recordId;
        $recordData["total_amount"] = $totalAmount;
        $recordData["total_price"] = $totalPrice;
        $recordData["total_saving"] = $totalSave;

        //重新更新销售记录
        $result = $record->save($recordData);
        if(false === $result){
            $record->rollback();
            throw new ThinkException($record->getError());
        }

        //添加其他业务逻辑


        //一切成功
        //提交事务
        $record->commit();
        return $recordData;
    }
}