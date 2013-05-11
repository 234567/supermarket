<?php

/**
 * Class StaffService
 *
 * 员工管理相关的一些业务逻辑
 *
 */
class StaffService{

    /**
     * 获取员工列表，可指定分店标识
     * @param int $branchId 分店标志
     * @param array $map    查询条件
     * @return array    包含结果和分页对象的数组
     */
    public function getList($map=array(),$branchId=0){
        //主键ID大于1,因为ID为1的是超级管理员
        $map['id'] = array('gt',1);
        if(!empty($branchId)){
            $map["branch_id"] = $branchId;
        }
//       /*
//        * 修改：沈玉敏
//        */
//        if($branchId !=0){
//            $map["branch_id"] = $branchId;
//        }
        $model = M("Staff");
        $count = $model->where($map)->count('id');
        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,10);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }

    /**
     * 插入员工信息
     * 通过表单创建对象，然后添加员工信息
     * 其中整个过程开启事务，使用异常来进行事务处理
     * @throws ThinkException
     */
    public function insert(){
        $model = D("Staff");
   /*     $data = array(
            "branch_id"=>$_SESSION["branch_info"]["id"],
            "account"=>$
        );*/
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
            throw new ThinkException("添加员工信息出错！".$model->getError());
        }

        if(!isset( $_POST["role_id"]) ){
            $model->rollback();
            throw new ThinkException("请指定员工所属角色！");
        }

        //添加员工为对应的角色，便于权限控制
        $result = M("RoleUser")->add(array("role_id" => intval( $_POST["role_id"] ), "user_id"=> $id ));
        if ( false === $result ) {
            $model->rollback();
            throw new ThinkException("指定员工角色出错，请重试！");
        }
        //提交事务
        $model->commit();
    }

    /**
     * 更新员工信息
     *
     * @throws ThinkException
     */
    public function update(){
        $model = D("Staff");
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
            throw new ThinkException("修改员工信息出错！".$model->getError());
        }

        if(isset( $_POST["role_id"]) ){
            //添加员工为对应的角色，便于权限控制
            $role = M("RoleUser");
            //删除旧的角色信息
            $role->where(array("staff_id"=> $id ))->delete();
            //重新指定员工位新角色
            $result = $role->add(array("role_id" => intval( $_POST["role_id"] ), "user_id"=> $id ));
            if ( false === $result ) {
                $model->rollback();
                throw new ThinkException("指定员工角色出错，请重试！");
            }
        }
        //添加其他业务逻辑
        //TODO：更新相关冗余字段

        //提交事务
        $model->commit();
    }


    /**
     * 删除员工信息
     *
     * @throws ThinkException
     */
    public function del($id){
        $model = D("Staff");

        $model->startTrans();
        //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
        $condition = array("id" => array("in" ,explode(",",$id)));
        //这里的删除并不是真正的删除操作，只是将信息标记为删除状态
        $result = $model->where($condition)->setField('status',  -1 );
        if(false == $result){
            $model->rollback();
            throw new ThinkException("删除失败！");
        }
        //TODO：添加其他业务逻辑
        //比如删除员工，还需要删除与员工相关的一些其他信息


        $model->commit();
    }

}