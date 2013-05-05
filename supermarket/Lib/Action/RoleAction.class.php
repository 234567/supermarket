<?php

/**
 * Class RoleAction
 *
 * 用户角色相关模块
 * 包括基本的角色管理，查看角色下的用户列表、对角色的详细权限控制等
 */
class RoleAction extends BaseAction{

    public function auth(){
        $id = $this->_param("id","intval");
        if(empty($id)){
            $this->error("非法参数！");
        }

        //获取角色信息
        $role = M("role")->getById($id);
        if (empty($role)) {
            $this->error("不存在该用户组", U('role/index'));
        }

        //获取角色的访问权限
        $access =  M("Access")->field("CONCAT(`node_id`,':',`level`,':',`pid`) as val")->where(array("role_id" =>$role['id']) )->select();
        $role['access'] = count($access) > 0 ? json_encode($access) : json_encode(array());

        //获取节点信息
        $nodeModel = M("node");
        $datas = $nodeModel->where("level=1")->select();
        foreach ($datas as $k => $v) {
            $map['level'] = 2;
            $map['pid'] = $v['id'];
            $map['status'] = 1;
            $datas[$k]['data'] = $nodeModel->where($map)->select();
            foreach ($datas[$k]['data'] as $k1 => $v1) {
                $map['level'] = 3;
                $map['pid'] = $v1['id'];
                $datas[$k]['data'][$k1]['data'] = $nodeModel->where($map)->select();
            }
        }

        $this->role = $role;
        $this->nodeList = $datas;
        $this->display();
    }

    /**
     * 更新角色的权限访问信息
     *
     */
    public function updateAccess(){
        $roleId = $this->_post("id","intval");
        $data = $this->_post("data");
        $service = D("Role","Service");
        try{
            $service->updateAccess($roleId,$data);
        }catch (Exception $e){
            $this->error($e->getMessage());
        }

        $this->success("权限设置成功！",U("role/index"));
    }



    public function staff(){
        $id = $this->_param("id");
        if(empty($id)){
            $this->error("参数错误！");
        }

        $service = D("Role","Service");
        if(session( C("ADMIN_AUTH_KEY") ) === true){
            $result = $service->getStaffList($id);
        }else{
            //获取分店标识
            $branchInfo = session("branch_info");
            $result = $service->getStaffList($id,$branchInfo["id"]);
        }

        $this->role = $result["role"];
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }
}