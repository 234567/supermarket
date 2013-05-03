<?php
/**
 * Class GoodsAction
 *
 * 商品信息管理模块
 */
class GoodsAction extends BaseAction{

    /**
     * 商品搜索，目前只能通过分类以及商品名称进行查找
     *
     */
    public function search(){
        $cid = $this->_param("cid","intval");
        $name = $this->_param("name");

        $map = array();
        if(!empty($cid)){
            $map["category_id"] = $cid;
        }

        if(!empty($name)){
            $map["name"] = array("like", "%".$name."%");
        }

        //通过条件获取商品列表
        $service = D("Goods","Service");
        $result = $service->getList($map);
        $this->list = $result['list'];
        $this->page = $result['page'];
        $this->display("index");
    }


    /**
     * AJAX获取商品信息接口，
     * 通过商品编号 或者 商品条形码 均可获取。
     */
    public function getInfo(){
        $id = $this->_param("id","intval");
        $barcode = $this->_param("barcode");

        if(!empty($id)){
            $info = M("Goods")->getById($id);
        }elseif(!empty($barcode)){
            $info = M("Goods")->getByBarcode($barcode);
        }else{
            $this->error("参数错误");
        }
        if(empty($info)){
            $this->error("找不到该商品的任何信息！");
        }

        $this->ajaxReturn($info,'获取商品信息成功！',1);
    }

    public function checkBarcode(){
        $barcode = $this->_param("value");
        if(empty($barcode)){
            echo json_encode(array(
                    "value" => $barcode,
                    "valid" => false,
                    "message" => "条形码错误！"
            ));
            return ;
        }

        $info = M("Goods")->getByBarcode($barcode);
        //没有该条码的商品信息
        if(empty($info)){
            echo json_encode(array(
                "value" => $barcode,
                "valid" => true,
                "message" => "条形码".$barcode."可录入，没有对应的商品信息！",
            ));
            return ;
        }else{
            echo json_encode(array(
                "value" => $barcode,
                "valid" => false,
                "message" => "条形码".$barcode."已存在，商品名称：".
                    $info["name"]." 价格￥".$info["sales_price"]."元 <a href=\"".U("Goods/edit?id=".$info["id"])."\">点击修改</a>",
            ));
            return ;
        }
    }
}