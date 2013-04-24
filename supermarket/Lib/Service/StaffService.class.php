<?php


class StaffService{

    public function getList($map){
        //主键ID大于1,因为ID为1的是超级管理员
        $map['id'] = array('gt',1);
        $model = M("Staff");

        //取出员工所属的分店信息
        $branchInfo = session("branch_info");
        if(isset($branchInfo)){
            //如果是分店负责人，则只能管理分店所属的员工
            $map["branch_id"] = $branchInfo["id"];
        }

        $count = $model->where($map)->count('id');
        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,5);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }

    public function insert(){
        $model = D("Staff");
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
        $result = M("RoleStaff")->add(array("role_id" => intval( $_POST["role_id"] ), "staff_id"=> $id ));
        if ( false === $result ) {
            $model->rollback();
            throw new ThinkException("指定员工角色出错，请重试！");
        }
        //提交事务
        $model->commit();
    }

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
            $role = M("RoleStaff");
            //删除旧的角色信息
            $role->where(array("staff_id"=> $id ))->delete();
            //重新指定员工位新角色
            $result = $role->add(array("role_id" => intval( $_POST["role_id"] ), "staff_id"=> $id ));
            if ( false === $result ) {
                $model->rollback();
                throw new ThinkException("指定员工角色出错，请重试！");
            }
        }
        //添加其他业务逻辑

        //提交事务
        $model->commit();
    }

    public function del(){
        $model = D("Staff");
        $id = $_GET["id"];
        if(!isset($id)){
            throw new ThinkException("请提供要删除的员工ID！");
        }

        $model->startTrans();
        //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
        $condition = array("id" => array("in" ,explode(",",$id)));
        //这里的删除并不是真正的删除操作，只是将信息标记为删除状态
        $result = $model->where($condition)->setField('status',  -1 );
        if(false == $result){
            $model->rollback();
            throw new ThinkException("删除失败！");
        }

        //添加其他业务逻辑
        //比如删除员工，还需要删除与员工相关的一些其他信息
        $model->commit();
    }


}