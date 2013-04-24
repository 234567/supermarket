<?php


class RoleService{

    public function getList($map){
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

    }

    public function update(){

    }

    public function del(){

    }


    /**
     * 通过角色ID获取所属角色的员工列表
     * @param $branchId
     * @param $id       角色ID
     * @return array    包含list和分页对象的数组
     */
    public function getStaffList($id ,$branchId=""){
//        $map = array("branch_id"=>$branchId,"role_id"=>$id);)
        $where = "rs.role_id = ".$id." and rs.staff_id = s.id";
        if(isset($branchId)){
            $where .= " and s.branch_id = ".$branchId;
        }

        $result = array();
        $model = M()->table(array("staff"=>"s", "role_staff"=>"rs" ))->where($where);
        $count =  $model->count('id');
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,5);
            $result["list"] = M()->table(array("staff"=>"s", "role_staff"=>"rs" ))->where($where)->limit($p->firstRow.','.$p->listRows)->select();
            $result["page"] = $p->show();
        }
        trace($result);
        return $result;
    }
}