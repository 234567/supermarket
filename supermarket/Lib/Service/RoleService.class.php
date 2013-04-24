<?php


class RoleService{

    public function getList($map){
        $model = M("Role");
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


}