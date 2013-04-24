<?php
/**
 * User: simplewind
 * Date: 4/5/13
 * Time: 11:45 PM
 * 商品的基本信息模型类、仅仅保存商品的基本信息，对于商品在超市的各种实际情况，请参考分店商品表
 */


class GoodsModel extends Model
{
    protected $tableName = 'goods';
    //字段定义
    protected $fields = array(
        'id',
        'category_id', //商品分类
        'barcode', //商品条形码
        'name', //商品名称
        'specifications', //商品规格
        'unit', //单位
        'sales_price', //销售价格
        'alarm', //报警数量
        'brand', //商品品牌
        'keyword', //商品关键词
        'desp', //商品描述信息
        '_pk' => 'id',
        '_autoinc' => true
    );

    //自动验证
    protected $_validate = array(
        //在新增的时候必须验证条形码的唯一性
        array('barcode', 'require', '条形码必须！'),
        array('barcode', '', '条形码已经存在！', Model::MUST_VALIDATE, 'unique', Model:: MODEL_INSERT),
        array('name','require','商品名称必须！'),
    );

    //自动完成
    protected $_auto = array(
        array('alarm', 'checkAlarm',Model:: MODEL_INSERT,"callback"), // 新增的时候checkAlarm
    );

    /**
     * 检测是否设置提醒数量，如果没有，使用默认值填充
     * @return int 默认的填充值
     */
    public function checkAlarm(){
        $alarm  = $_POST['alarm'];
        if(empty($alarm)){
            //使用默认值填充
            $alarm = 10;
        }
        return $alarm;
    }
}