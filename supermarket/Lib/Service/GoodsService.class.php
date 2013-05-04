<?php


class GoodsService{

    public function getList($map = array()){
        $model = M("Goods");

        $count = $model->where($map)->count('id');
        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,20);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }


    public function insert(){
        $model = D("Goods");
        $vo = $model->create();
        if(false === $vo){
            throw new ThinkException($model->getError());
        }

        //开启事务
        $model->startTrans();
        $id = $model->add();
        if( false === $id ){
            //事务回滚
            $model->rollback();
            throw new ThinkException("添加商品信息出错！".$model->getError());
        }
        //TODO:添加其他业务逻辑


        //提交事务
        $model->commit();
    }

    public function update(){
        $model = D("Goods");
        $vo = $model->create();
        if(false === $vo){
            throw new ThinkException($model->getError());
        }

        //开启事务
        $model->startTrans();
        $id = $model->save();
        if( false === $id ){
            //事务回滚
            $model->rollback();
            throw new ThinkException("修改商品信息出错！".$model->getError());
        }
        //TODO:添加其他业务逻辑

        //提交事务
        $model->commit();
    }

    public function del($id){
        $model = D("Goods");
        $model->startTrans();
        //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
        $condition = array("id" => array("in" ,explode(",",$id)));
        //这里的删除并不是真正的删除操作，只是将信息标记为删除状态
//        $result = $model->where($condition)->delete();
        //将状态设置为-1表示已删除状态
        $result = $model->where($condition)->setField("status",-1);
        if(false == $result){
            $model->rollback();
            throw new ThinkException($model->getError());
        }
        //TODO:添加其他业务逻辑

        $model->commit();

    }
}