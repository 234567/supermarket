<?php
/**
 * Class StaffAction
 * 后台用户模块
 */
class StaffAction extends BaseAction {

    public function edit(){
        $id = $this->_param( "id" );
        if(empty($id)){
            $this->error("参数错误！");
        }
        $model = M("Staff");
        $vo = $model->getById($id);
        $this->vo = $vo;

        $branchId = $vo["branch_id"];
        $branchInfo = M("Branch")->getById($branchId);
        if(empty($branchInfo)){
            $this->error("系统错误，找不到员工对应的分店信息！");
        }
        $this->branchInfo =$branchInfo;
        $this->display();
    }


    // 检查帐号
    public function checkAccount() {
        $name  =  $this->_param("account");

        if(!preg_match('/^[a-z]\w{4,}$/i',$name)) {
            $this->error( '帐号必须是字母，且5位以上！');
        }
        // 检测用户名是否冲突

        $result  =   M("Staff")->getByAccount($name);
        if($result) {
            $this->error('该用户名已经存在！');
        }else {
            $this->success('该用户名可以使用！');
        }
    }


    /**
     * 重置密码
     */
    public function resetPwd() {
        $id  =  $_POST['id'];
        $password = $_POST['password'];
        if(''=== trim($password)) {
            $this->error('密码不能为空！');
        }
        $Staff = M('Staff');
        $Staff->password	=	md5($password);
        $Staff->id			=	$id;
        $result	=	$Staff->save();
        if(false !== $result) {
            $this->success("密码修改成功，新密码为：$password");
        }else {
            $this->error('重置密码失败！');
        }
    }
}