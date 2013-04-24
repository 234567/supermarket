<?php

class GoodsAction extends BaseAction{

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

        //实例化Service
        $service = D("Goods","Service");
        //无过滤条件获取列表
        $result = $service->getList($map);
        $this->list = $result['list'];
        $this->page = $result['page'];
        $this->display("index");
    }


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

        $this->ajaxReturn($info,'获取商品信息成功！',1);
    }
}