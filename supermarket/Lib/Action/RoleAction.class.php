<?php
/**
 * User: simplewind
 * Date: 4/20/13
 * Time: 7:40 PM
 * 修改这里的注释内容
 */

class RoleAction extends BaseAction{

    public function access(){
        $roleid = $this->_get('roleid','intval',0);
        if(!$roleid) $this->error('参数错误!');
        Vendor('Common.Tree');  //导入通用树型类

        $Tree = new Tree();
        $Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $Tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $NodeDB = D('Node');
        $node = $NodeDB->select();

        $AccessDB = D('Access');
        $access = $AccessDB->field('role_id,node_id,pid,level')->select();


        foreach ($node as $n=>$t) {
            $node[$n]['checked'] = ($AccessDB->is_checked($t,$roleid,$access))? ' checked' : '';
            $node[$n]['depth'] = $AccessDB->get_level($t['id'],$node);
            $node[$n]['pid_node'] = ($t['pid'])? ' class="tr lt child-of-node-'.$t['pid'].'"' : '';
        }
        $str  = "<tr id='node-\$id' \$pid_node>
                    <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='nodeid[]' value='\$id' class='radio' level='\$depth' \$checked onclick='javascript:checknode(this);' > \$title (\$name)</td>
                </tr>";

        $Tree->init($node);
        $html_tree = $Tree->get_tree(0, $str);
        $this->assign('html_tree',$html_tree);
        $this->display();
    }


    public function auth(){

        //获取角色信息
        $role = M("role")->where(array( "id" => $this->_get("id","intval")) )->find();
        if (empty($role['id'])) {
            $this->error("不存在该用户组", U('role/index'));
        }

        //获取角色的访问权限
        $access =  M("Access")->field("CONCAT(`node_id`,':',`level`,':',`pid`) as val")->where(array("role_id" =>$role['id']) )->select();
        $role['access'] = count($access) > 0 ? json_encode($access) : json_encode(array());
        $this->assign("info", $role);


        //获取节点信息
        $nodeModel = M("node");
        $datas = $nodeModel->where("level=1")->select();
        foreach ($datas as $k => $v) {
            $map['level'] = 2;
            $map['pid'] = $v['id'];
            $datas[$k]['data'] = $nodeModel->where($map)->select();
            foreach ($datas[$k]['data'] as $k1 => $v1) {
                $map['level'] = 3;
                $map['pid'] = $v1['id'];
                $datas[$k]['data'][$k1]['data'] = $nodeModel->where($map)->select();
            }
        }
        $this->assign("nodeList", $datas);
        $this->display();
    }

    public function updateAccess(){
        $accessModel = M("access");
        $roleId = $this->_post("id","intval");
        $data = $this->_post("data");

        $accessModel->where(array("role_id" => $roleId))->delete();

        if(0 === count($data)){
            $this->success("权限已全部清空！",U("role/index"));
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
            $this->error("权限设置失败，请重试");
        }
        $this->success("权限设置成功！",U("role/index"));
    }
}