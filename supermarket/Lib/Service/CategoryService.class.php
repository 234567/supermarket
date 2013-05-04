<?php
/**
 * Created by JetBrains PhpStorm.
 * User: corn-s
 * Date: 13-4-24
 * Time: 下午8:49
 * To change this template use File | Settings | File Templates.
 */
//商品分类业务逻辑
class CategoryService{

    public function getList($map){
        $map['pid']=array('eq',0);
        $category = D('Category');
        $result = array();
        $result['list'] = $category->parseFieldsMap($category->where($map)->select());
        return  $result;
    }
    //添加
    public function insert(){
        $category = D('Category');
        $vo = $category->create();
        if(false == $vo){
            // 字段验证错误
            throw new ThinkException("商品分类字段验证错误!");
        }
        //填充主键
        $pid = $vo['pid'];
        $count = $category->where(array('pid' =>$pid ))->count();
        $count++;
        $id = $pid.'';
        //组装分类ID
        if($count >0 && $count <= 9){
            $id = $id.'0'.$count;
        }else{
            $id = $id.$count;
        }
        $vo['id']= $id;
        //开启事务
        $category->startTrans();
        $id = $category->add($vo);
        if (false == $id ) {
            //事务回滚
            $category->rollback();
            throw new ThinkException("商品分类数据写入失败!");
        }
        //提交事务
        $category->commit();

    }
    //更新
    public function update(){
        $category = D('Category');
        $vo = $category->create();
        if(false == $vo){
            throw new ThinkException("表单验证失败!");
        }
        //开启事务
        $category->startTrans();
        $id = $category->save();
        if(false == $id ){
            $category->rollback();
            throw new ThinkException("数据修改失败!");
        }
        $category->commit();
    }

    //删除
    public function del($id){
        $category = D('Category');
        //如果当前分类下有子分类，则不能删除
        if($category->where(array('pid'=>$id))->count()  >  0){
            throw new ThinkException('当前分类下还有子分类，不能进行删除！');
        }

        $goods = D('Goods');
        if($goods->where(array('category_id'=>$id))->count()  >  0){
            throw new ThinkException('当前分类下还有商品信息，不能进行删除！');
        }

        //开启事务
        $category->startTrans();
       /* $result = $category->where(array('id'=>$id))->setField('status',-1);*/
        $result =  $category->delete($id);
        if (false == $result) {
            //回滚
            $category->rollback();
            throw new ThinkException('删除失败！');
        }
        //提交
        $category->commit();
    }
}