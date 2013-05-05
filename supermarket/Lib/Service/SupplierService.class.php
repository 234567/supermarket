<?php

/**
 * Class SupplierService
 *
 * 供货商业务逻辑处理类
 */
class SupplierService{

    /**
     * 通过指定条件获取供货商列表
     * @param $map  条件
     * @return array    列表和分页对象
     */
    public function getList($map=array()){
        //status 状态：1表示正常，0表示不可用，-1表示已删除
        $map = array("status"=>array("eq",1));
        $model = D("Supplier");
        $result = array();
        $totalRows = $model->where($map)->count();
        if($totalRows>0){
            import("@.ORG.Util.Page");
            $page= new Page($totalRows,5);
            $result["list"] = $model->where($map)->limit($page->firstRow,$page->listRows)->select();
            $result["page"] = $page->show();
        }
        return $result;
    }


    /**
     * 增加供货商信息
     * @throws ThinkException
     */
    public function insert(){
        $model = D("Supplier");
        $vo = $model->create();
        if(false === $vo){
            throw new ThinkException("表单验证失败!");
        }
        //开启事务
        $model->startTrans();
        $result = $model->add();
        if(false == $result){
            $model->rollback();
            throw new ThinkException("数据插入失败!");
        }
        $model->commit();
    }

    /**
     * 更新供货商信息
     * @throws ThinkException
     */
    public function update(){
        $model = D("Supplier");
        $vo = $model->create();
        if(false == $vo){
            throw new ThinkException($model->getError());
        }
        //开启事务
        $model->startTrans();
        $result = $model->save();
        if(false == $result){
            $model->rollback();
            throw new ThinkException($model->getError());
        }
        $model->commit();
    }

    /**
     * 删除指定ID的供货商信息
     * @param $id       供货商编号
     * @throws ThinkException
     */
    public function del($id){
        $model = D("Supplier");
        //查询供货商中是否存在商品
        $goodsCount = M("Supplier_has_goods")->where("supplier_id=".$id)->count();
        if($goodsCount > 0){
            throw new ThinkException("该供货商有提供商品，不能删除该供货商!");
        }
        //开启事务
        $model->startTrans();
        $condition = array("id"=>array("eq",$id));
        //不是真正的删除，而是将其隐藏,status 状态：1表示正常，0表示不可用，-1表示已删除
         $del = $model->where($condition)->setField("status",-1);
        if(false == $del){
            $model->rollback();
            throw new ThinkException("删除失败!");
        }
        $model->commit();
    }

    public function searchGoods(){
        //从页面获取供货商id
        $id = $_GET["id"];
        if(!isset($id)){
            throw new ThinkException("ID错误!");
        }
        $result = array();
        //获取供货商信息
        $result["supplier"] = M("supplier")->getById($id);
        //链表查询条件
        $condition = "g.id = shg.goods_id and shg.supplier_id='".$id."'";
        //统计商品条数
        $model = M()->table("goods as g,supplier_has_goods as shg")->where($condition);
        $count = $model->count("id");
        //判断是否存在商品信息
        if($count > 0){
            import("@.ORG.Util.Page");
            $page = new Page($count,5);
            //分页查询商品列表
            $result["goodsList"] = M()->table("goods as g,supplier_has_goods as shg")->where($condition)->limit($page->firstRow,$page->listRows)->select();
            $result["page"] = $page->show();
        }
       return $result;
    }

    public function goodscompare(){
        $goods_id = $_GET["goods_id"];
        $supplier_id = $_GET["supplier_id"];
        if(!isset($goods_id)){
            throw new ThinkException("商品id错误!");
        }
        $result = array();
        //更加商品id获取拥有该商品的供货商列表
       $result["current_supplier_id"]=$supplier_id;
        $count = M("Goods")->where("id=".$goods_id)->count("id");
        //存在该商品信息
        if($count>0){
            //列举提供该商品的供货商列表，
            import("@.ORG.Util.Page");
            //获取商品信息
            $result["goods"] = M("Goods")->getById($goods_id);
            //获提供该商品的供货商列表，并对其价格进行对比
            $page = new Page($count,5);
            //链表查询条件
            $condition = "shg.supplier_id = s.id and shg.goods_id='".$goods_id."'";
/*            $fields = "s.id as s_id,s.real_name,shg.last_price";*/
            //分页查询商品列表
            $result["list"] = M()->table("supplier_has_goods as shg,supplier as s")->where($condition)->order("shg.last_price asc")->limit($page->firstRow,$page->listRows)->select();
            $result["page"] = $page->show();
        }
        return $result;
    }


}
