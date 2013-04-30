<?php
//入库记录模型
class StockItemModel extends CommonModel{
    //字段定义
    protected $fields = array(
        "id",//id
        "stock_record_id",//入库记录
        "goods_id",//商品ID
        "actual_cost",//成本价
        "amount",//数量
        "remark"//备注
    );
    protected $_auto = array(

    );
    protected  $_validate = array(

    );
}
