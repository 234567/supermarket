<?php
/**
 * User: corn-s
 * Date: 13-4-24
 * Time: 下午10:06
 */
//供货商业务逻辑
class SupplierService{
    public function getList($map){
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

    public function insert(){
        $model = D("Supplier");
        $vo = $model->create();
        if(false == $vo){
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

    public function update(){
        $model = D("Supplier");
        $vo = $model->create();
        if(false == $vo){
            throw new ThinkException("表单验证失败!");
        }
        //开启事务
        $model->startTrans();
        $result = $model->save();
        if(false == $result){
            $model->rollback();
            throw new ThinkException("数据更新失败!");
        }
        $model->commit();
    }

    public function del(){
        $id = $_GET["id"];
        dump($id);
        if(!isset($id)){
          throw new ThinkException("ID错误!");
        }
        $model = D("Supplier");

        $condition = array("id"=>array("eq",$id));
        $result = $model->where($condition)->relation(true)->select();
        if($result["goods"] == null){
            throw new ThinkException("该供货商有提供商品，不能删除该供货商!");
        }
        //开启事务
        $model->startTrans();
        //不是真正的删除，而是将其隐藏
       /* $del = $model->where($condition)->setField("status",-1);*/
        $del = $model->delete($id);
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

}
