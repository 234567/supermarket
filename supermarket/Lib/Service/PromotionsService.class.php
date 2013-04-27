<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-26
 * Time: 下午1:46
 * To change this template use File | Settings | File Templates.
 */
class PromotionsService{

    public function getList($map){

        $model = D("Promotions");
        $count = $model->where($map)->count("id");
        $table = "promotions as p,goods as g,branch as b";
        $result = array();
        $fields = "p.*,g.name as goods_name,g.id as goods_id,g.sales_price,b.name as branch_name";
        if($count > 0 ){
            import("@.ORG.Util.Page");
            $page = new Page($count,5);
            $result["list"] = $model->table($table)->field($fields)->where("p.goods_id=g.id and p.branch_id=b.id")->order("p.time_end desc")->limit($page->firstRow,$page->listRows)->select();
            $result["page"] = $page->show();
        }
        return $result;
    }

    //商品列表下发布折扣
    public function release(){
        $goods_id = $_GET["goods_id"];
        $branch_id = $_SESSION["staff_info"]["branch_id"];
        if(!isset($goods_id) && !isset($branch_id)){
            throw new ThinkException("分店ID出错或商品ID出错!");
        }


        //查询该商品在此期间是否存在折扣，存在则不允许在添加折扣，否则添加
        $goods = D("Promotions")->where("goods_id=".$goods_id)->find();
        $result = array();
        if(false == $goods){
            $result["branch"] = M("Branch")->getById($branch_id);
            $result["goods"] = M("Goods")->getById($goods_id);
            if(false == $result["branch"] && false == $result["goods"]){
                throw new ThinkException("分店不存在或没有打折的商品!");
            }
        }

        return $result;
    }

    public function insert(){
        $model = D("Promotions");
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
        //提交事务
        $model->commit();
    }

    public function edit(){
        $id = $_GET["id"];
        $branch = $_SESSION["staff_info"]["branch_id"];
        $model = M("Promotions");
        if(false === $id){
            throw new ThinkException("ID出错!");
        }

        $result = array();
        $result["promotions"] = $model->getById($id);
        //无权限删除其他分公司折扣信息
        if( $result["promotions"]["branch_id"] == $branch){
            $result["goods"] = D("Goods")->getById($result["promotions"]["goods_id"]);
            if(false == $result){
                throw new ThinkException("修改商品折扣出错！".$model->getError());
            }
        }else{
            $result["goods"]=false;
        }
        return $result;
    }
    public function update(){
        $model = D("Promotions");
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
        //提交事务
        $model->commit();
    }

    public function del(){
        $id = $_GET["id"];
        $branch = $_SESSION["staff_info"]["branch_id"];
        if(false === $id){
            throw new ThinkException("ID出错!");
        }
        $model = D("Promotions");
        $result  = $model->getById($id);
        //开启事务
        $model->startTrans();
        //登录员工所属分店是否与删除折扣信息的分店标识一致，才允许删除
        if($branch == $result["branch_id"]){
            $vo = $model->delete($id);
            if( false === $vo ){
                //事务回滚
                $model->rollback();
                throw new ThinkException("删除折扣信息出错！".$model->getError());
            }
        }
        //提交事务
        $model->commit();
    }



}