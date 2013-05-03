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

            //对商品库存量进行减少
            M("BranchHasGoods")->where(array(
                "branch_id" =>$staffInfo["branch_id"],
                "goods_id" => $goods["id"],
            ))->setDec('amount',$goods["amount"]);
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


    /**
     * 获取销售记录
     * @param array $map    查询条件
     * @return array    包含列表和分页对象的数组
     */
    public function getList($map = array()){
        $model = M("SalesRecord");

        //取出员工所属的分店信息
        $branchInfo = session("branch_info");
        $map["branch_id"] = $branchInfo["id"];
        if( session( C("ADMIN_AUTH_KEY") ) == true ){
            //如果是管理员
            unset($map["branch_id"]);
        }

        $fields = "sales_record.*,branch.name as branch_name";
        $join = "branch ON branch.id = branch_id";
        $count = $model->where($map)->count('id');
        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,10);
            $result["list"] = $model->field($fields)->join($join)->order("time desc")->
                where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }


    /**
     * 统计任意天数以内的日销售总额
     * @param int $dayAmount    天数
     * @return array    包含数据的数组
     */
    public function countRecent($dayAmount = 7){
        //获取起始天数的时间戳
        $begin = strtotime("-$dayAmount days");

        $SalesRecord = M("SalesRecord");
        //SQL是 SELECT date_format(FROM_UNIXTIME( `time`),'%Y-%m-%d') AS day,sum(`total_price`) as total
        //          FROM `sales_record` WHERE `time` >= $begin group by day
        //
        $map = array();
        $map["time"] = array("gt", $begin);
        $result = $SalesRecord->join("branch ON branch.id = branch_id")->field(array(
            "branch_id","name", //分店标识、名称
            "date_format(FROM_UNIXTIME( `time`),'%Y-%m-%d')" => "day",
            "sum(`total_price`)"=>"total",
        ))->where($map)->group("name,day")->select();


        $data = array();
        //以分店ID为基准重新进行分组
        foreach($result as $val){
            $data[ $val["branch_id"] ]["name"] = $val["name"];
            $data[ $val["branch_id"] ]["data"][] = array($val["day"] , $val["total"]);
        }

        $newData = array();
        foreach($data as $key => &$branch){

            $branchData = $branch["data"];

            //先生成日期标签，并初始化数据
            $len = $dayAmount;
            $daysLabel = array();

            $idx = 0;
            while($len--){
                $tmp = date("Y-m-d",strtotime("-$len days"));
                $daysLabel[ $tmp ] = 0;
                $daysLabel[] = array($idx++,$tmp);
            }

            //然后用真实数据替换掉
            foreach($branchData as $val){
                $daysLabel[ $val[0] ] = $val[1];
            }

            $len = $dayAmount;
            $showData = array();
            $idx = 0;
            while($len--){
                $tmp = date("Y-m-d",strtotime("-$len days"));
                $showData[] = array($idx++ ,$daysLabel[$tmp]  );
                unset($daysLabel[$tmp]);
            }


            $newData[] = array(
                //"branch_id"=>$key,
                "label" => $branch["name"],
                "ticks" => $daysLabel,
                "data" => $showData,
            );
        }


        return $newData;
    }


    public function getDetail($recordId){
        //查询入库记录详细
        $result = array();

        //获取销售记录
        $salesRecord = M("SalesRecord")->getById($recordId);
        if(false === $salesRecord){
            throw new ThinkException("找不到指定的销售记录！");
        }

        $itemModel = M("SalesItem");
        $count = $itemModel->where(array("sales_record_id"=>$recordId))->count("id");
        if($count > 0){
            $field = array(
                "sales_item.*,goods.barcode,goods.name,goods.specifications,goods.unit"
            );
            $result["items"] =$itemModel->field($field)->join("goods ON sales_item.goods_id = goods.id")->where(array("sales_record_id"=>$recordId))->select();
        }

        $result["record"] = $salesRecord;
//        dump($result);
        return $result;
    }
}