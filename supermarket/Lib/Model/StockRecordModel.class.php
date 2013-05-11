<?php
//入库记录模型
class StockRecordModel extends CommonModel
{
    //字段定义
    protected $fields = array(
        "id",
        "branch_id",
        "staff_id",
        "staff_name",
        "total_amount",
        "total_cost",
        "time"
    );
    protected $_auto = array();
    protected $_validate = array(
        array("branch_id", "require", "分店必须！"),
        array("staff_id", "require", "入库员必须！"),
        array("time", "time", "时间为当前时间", Model::MUST_VALIDATE, "", Model::MODEL_BOTH)


        /* array("branch_id","dbExist","不存在这个分店",Model::EXISTS_VALIDATE,"function",Model::MODEL_BOTH)*/
    );

}