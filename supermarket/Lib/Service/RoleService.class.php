<?php

/**
 * Class RoleService
 *
 * 员工角色相关的业务逻辑
 *
 */
class RoleService{

    /**
     * 获取角色列表
     * @param $map  查询条件
     * @return array    包含列表和分页的数组
     */
    public function getList($map = array()){
        $model = M("Role");
        //不显示内置的超级管理员，也可以从模板中间接的禁止操作
        //$map["id"] = array("gt",1);
        $count = $model->where($map)->count('id');
        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,5);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
        }
        $result["page"] = $p->show();
        return $result;
    }

    public function insert(){
        $model = D('Role');
        $vo = $model->create();
        if(false === $vo){
            throw new ThinkException($model->getError());
        }
        //开启事务
        $model->startTrans();
        $role = $model->add();
        if(false === $role){
            $model->rollback();
            throw new ThinkException($model->getError());
        }
        $model->commit();
    }

    public function update(){
        $model = D('Role');
        $vo = $model->create();
        if(false === $vo){
            throw new ThinkException($model->getError());
        }
        //开启事务
        $model->startTrans();
        $role = $model->save();
        if(false === $role){
            $model->rollback();
            throw new ThinkException($model->getError());
        }
        $model->commit();
    }

    public function del($id){
        $model = D("Staff");

        $model->startTrans();
        //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
        $condition = array("id" => array("in" ,explode(",",$id)));
        //这里的删除并不是真正的删除操作，只是将信息标记为删除状态
        $result = $model->where($condition)->setField('status',  -1 );
        if(false == $result){
            $model->rollback();
            throw new ThinkException($model->getError());
        }
        $model->commit();
    }

    public function updateAccess($roleId,$data= array()){
        $accessModel = M("access");
        $accessModel->where(array("role_id" => $roleId))->delete();
        if(0 === count($data)){
            return true;
        }

        $accessData = array();
        foreach($data as $key => $val){
            //拆分数据
            $items = explode(":" , $val);
            $accessData[$key]['role_id'] = $roleId;
            $accessData[$key]['node_id'] = $items[0];
            $accessData[$key]['level'] = $items[1];
            $accessData[$key]['pid'] = $items[2];
        }

        $result = $accessModel->addAll($accessData);
        if(false === $result){
            throw new ThinkException("权限设置失败，请重试");
        }
    }


    /**
     * 通过角色ID获取所属角色的员工列表
     * * @param $id       角色ID
     * @param $branchId 分店ID
     * @return array    包含list和分页对象的数组
     */
    public function getStaffList($id ,$branchId){
        $where = "rs.role_id = ".$id." and rs.user_id = s.id";
        if(isset($branchId)){
            $where .= " and s.branch_id = ".$branchId;
        }

        $result = array();
        $result["role"] = M("role")->getById($id);
        $model = M()->table(array("staff"=>"s", "role_user"=>"rs" ))->where($where);
        $count =  $model->count('id');
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,5);
            $result["list"] = M()->table(array("staff"=>"s", "role_user"=>"rs" ))->where($where)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        return $result;
    }

    public function getRoleType($staffId){
        return M("RoleUser")->join("role ON role_user.role_id = role.id")->where(array("user_id"=>$staffId))->find();
    }
}