<?php
/**
 * User: corn-s
 * Date: 13-4-24
 * Time: 上午10:59
 */
//分店业务逻辑
class BranchService {
    public function getList($map){
        //status 状态：1表示正常，0表示不可用，-1表示已删除
        $map = array("status"=>array("eq",1));

        //取出员工所属的分店信息
        $branchInfo = session("branch_info");
        $map["branch_id"] = $branchInfo["id"];
        if( session( C("ADMIN_AUTH_KEY") ) == true ){
            //如果是管理员
            unset($map["branch_id"]);
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
//更新
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
            throw new ThinkException("修改员工信息出错！".$model->getError());
        }
        //提交事务
        $model->commit();
    }

    //插入
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
        $model->commit();
    }

    //删除
    public function del(){
        $model = D('Branch');
        $id = $_GET['id'];
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

