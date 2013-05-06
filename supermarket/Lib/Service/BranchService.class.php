<?php
/**
 * Class BranchService
 *
 * 分店业务逻辑
 * 包括：
 * 获取分店列表（区分员工）
 *
 *
 */
class BranchService {

    /**
     * 获取连锁分店列表
     * @param $map  查询条件
     * @return array
     */
    public function getList($map=array()){

        //status 状态：1表示正常，0表示不可用，-1表示已删除
        $map["status"] = array("eq",1);

        //取出员工所属的分店信息
        $map["id"] = $_SESSION["staff_info"]["branch_id"];
        if( session( C("ADMIN_AUTH_KEY") ) == true ){
            //如果是管理员
            unset($map["id"]);
        }

        $model = D('Branch');
        $totalRows = $model->where($map)->count('id');
        $result = array();
        if($totalRows>0){
            import("@.ORG.Util.Page");
            $p = new Page($totalRows,5);
            $result["list"] = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            //遍历list 获取分店联系人信息
            foreach($result["list"] as $key => $Branch){
                //分店负责人
                $staff = M('Staff')->field("name,mobile")->where(array("id"=>array("eq",$Branch["director_staff_id"])))->find();
                $result["list"][$key]["director_name"] = $staff["name"];
                $result["list"][$key]["director_mobile"] = $staff["mobile"];
            }
            $result["page"] = $p->show();
        }
        return $result;
    }


    /**
     * 更新分店信息
     * @throws ThinkException
     */
    public function update(){
        $model = D('Branch');
        $vo = $model->create();
        if(false == $vo){
            throw new ThinkException($model->getError());
        }
        //开启事务
        $model->startTrans();
        $branch = $model->save();
        if(false == $branch){
            //回滚事务
            $model->rollback();
            throw new ThinkException($model->getError());
        }

        /**
         * 如果指定了分店负责人
         */
        if($vo["director_staff_id"] !=0){
            //指定选择的员工为负责人
            $role = M("RoleUser");
            //先删除旧的角色
            $role->where(array("user_id"=>$vo["director_staff_id"]))->delete();
            //升职为店长～
            $result = $role->data(array("role_id"=>C('ROLE_TYPE_DIRECTOR'),"user_id"=>$vo["director_staff_id"]))->add();
            if(false === $result){
                $model->rollback();
                throw new ThinkException("更改负责人出错！".$model->getError());
            }
        }

        //提交事务
        $model->commit();
    }

    /**
     * 插入分店信息
     * @throws ThinkException
     */
    public function insert(){
        $model = D('Branch');
        $vo = $model->create();
        if(false == $vo){
            throw new ThinkException($model->getError());
        }
        //开启事务
        $model->startTrans();
        $branch = $model->add();
        if(false == $branch){
            $model->rollback();
            throw new ThinkException($model->getError());
        }

        /**
         * 如果指定了分店负责人
         */
        if($vo["director_staff_id"] !=0){
            //指定选择的员工为负责人
            $role = M("RoleUser");
            //先删除旧的角色
            $role->where(array("user_id"=>$vo["director_staff_id"]))->delete();
            //升职为店长～
            $result = $role->data(array("role_id"=>C('ROLE_TYPE_DIRECTOR'),"user_id"=>$vo["director_staff_id"]))->add();
            if(false === $result){
                $model->rollback();
                throw new ThinkException("指定负责人出错！".$model->getError());
            }
        }

        $model->commit();
    }

    /**
     * 删除指定分店的信息
     * @param $id   分店标识
     * @throws ThinkException
     */
    public function del($id){
        $model = D('Branch');

        //id为1标识总公司 ，总公司 是不允许删除的
        if(!isset($id) || $id == 1){
            throw new ThinkException("请指定删除分店ID");
        }
        //开启事务
        $model->startTrans();
         //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
        $staffCondition = array("branch_id"=>array("in",explode(',',$id)));
        $branchCondition = array("id"=>array("in",explode(',',$id)));

        //删除分店前先处理分店中的员工，
        $staff = D('Staff');
        $count = $staff->where($staffCondition)->count();

        if($count > 0){
            //分店中存在员工，将该分店中的员工转移到总公司去（目前暂时）
           //查询出要删除分店的所有员工
            $staffList = $staff->where($staffCondition)->select();

            //将分店员工移动到总公司中
            foreach($staffList as $value){
              $staff->where("id=".$value["id"])->setField("branch_id",1);
            }
        }
        //根据条件删除分店，这里的分店是将status设置为-1:status 状态：1表示正常，0表示不可用，-1表示已删除
       $result = $model->where($branchCondition)->setField('status',-1);
      /*  $result = $model->where($branchCondition)->delete();*/
        if(false == $result){
            $model->rollback();
            throw new ThinkException("分店删除失败！");
       }
        $model->commit();

    }


    /**
     * 获取指定分店的商品库存信息
     * @param $branchId     分店标识
     * @param array $map   过滤条件
     * @return array    包含商品列表以及分页对象的数组
     */
    public function getGoodsStock($branchId,$map = array()){
        $Goods  = M("Goods");

        //使用右连结查询分店的商品库存信息
        $joinStr = "JOIN branch_has_goods bhg ON goods.id = bhg.goods_id and bhg.branch_id = ".$branchId;

        $count = $Goods->join($joinStr)->where($map)->count();

        $result = array();
        if($count > 0){
            import("@.ORG.Util.Page");
            $p = new Page($count,15);
            //添加以下这个SQL，防止报错
            //问题解决：http://tech.it168.com/a2012/0808/1382/000001382732.shtml
            
            $Goods->query("SET sql_mode='NO_UNSIGNED_SUBTRACTION';");
            $result["list"] = $Goods->join($joinStr)->where($map)->limit($p->firstRow.','.$p->listRows)->order("bhg.amount - goods.alarm")->select();
            $result["page"] = $p->show();
        }
        return $result;
    }

}

