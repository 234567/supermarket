<?php

/**
 * Class PublicAction
 *
 * 公共模块，此Action不受权限控制
 * 负责完成一些公共的操作，比如登陆，检测登陆，退出登陆、生成验证码
 * 以及对所有用户通用的一些功能，比如修改个人信息，修改登陆密码，等等
 */
class PublicAction extends Action{

    /**
     * 用户登录页面
     */
    public function login() {
        //如果没有登陆，显示登陆页面
        if(!isset($_SESSION[C("USER_AUTH_KEY")])) {
            $this->display();
        }else{
            //跳转回后台首页
            $this->redirect("Index/index");
        }
    }

    public function index() {
        //如果通过认证跳转到首页
        redirect(__APP__);
    }

    /**
     * 退出登陆
     */
    public function logout() {
        if(isset($_SESSION[C("USER_AUTH_KEY")])) {
            unset($_SESSION[C("USER_AUTH_KEY")]);
            unset($_SESSION);
            session_destroy();
            $this->success("成功退出系统！",U("public/login"));
        }else {
            $this->error("请勿重复退出！");
        }
    }

    /**
     * 登录检测
     * 执行登陆操作
     */
    public function checkLogin() {
        $account =  $this->_post("account");
        $password =  $this->_post("password");
        $verify =  $this->_post("verify");

        //输入验证
        if( empty( $account ) || empty( $password ) || empty( $verify ) ) {
            $this->error( "请输入正确的登陆信息！" );
        }

        //检测验证码
        if( session("verify") != md5( $verify )) {
            $this->error("验证码错误！");
        }

        //生成认证条件
        $map            =   array();
        $map["account"]	= $account;
        $map["status"]	=	array("egt",0);

        import ( "@.ORG.Util.RBAC" );
        $authInfo = RBAC::authenticate($map);


        //使用用户名、密码和状态的方式进行认证
        if(false === $authInfo || $authInfo === null) {
            $this->error("帐号不存在!!");
        }elseif($authInfo["status"] == 0 ) {
            $this->error("帐号已被禁用，请联系管理员！");
        }else{
            if($authInfo["password"] != md5($password)) {
                $this->error("密码错误！");
            }

            session( C("USER_AUTH_KEY") ,$authInfo["id"]);
            //保存员工姓名
            session("staff_info", $authInfo);

            //获取分店信息
            $branchInfo = M("Branch")->where( array("id"=>$authInfo["branch_id"]) )->find();
            if(false === $branchInfo || null === $branchInfo){
                //不存在的分店信息
                $this->error("不存在的分店信息，你肯定不是本超市的员工！");
            }
            //保存分店信息
            session("branch_info" , $branchInfo );

            //如果是超级管理员
            if($authInfo["account"] === "admin") {
                session( C("ADMIN_AUTH_KEY") , true);
            }

            //保存登录信息
            $Staff	=	M("Staff");
            $ip		=	get_client_ip();
            $time	=	time();
            $data = array();
            $data["id"]	=	$authInfo["id"];
            $data["last_login_time"]	=	$time;
            $data["login_count"]	=	array("exp","login_count+1");
            $data["last_login_ip"]	=	$ip;
            $Staff->save($data);


            // 缓存访问权限
            RBAC::saveAccessList();
            $this->success("登录成功！",U("index/index"));
        }
    }

    /**
     * 生成验证码
     */
    public function verify() {
        $type	 =	 isset($_GET["type"])?$_GET["type"]:"gif";
        import("@.ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }


    /**
     * 检测用户是否登陆
     */
    public function checkUser(){
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->error('请先登陆',U('public/login'));
        }
    }
    /**
     * 修改个人信息
     */
    public function profile(){
        $this->checkUser();
        $this->vo = session("staff_info");
        $this->display();
    }

    /**
     * 执行个人信息的更新操作
     */
    public function updateProfile(){
        $this->checkUser();
        $staffInfo = session("staff_info");
        $model = D("Staff");
        $result = $model->create();
        if(false ===$result){
            $this->error("请输入正确的信息！");
        }
        //数组合并
        $result = array_merge($staffInfo,$result);
        //填充更新时间字段
        $result["update_time"] = time();

        if(false === $model->save($result)){
            $this->error("修改失败！请检查");
        }

        //重置SESSION中的员工信息
        session("staff_info",$result);
        $this->success("修改个人信息成功！");
    }

    /**
     * 修改登陆密码
     */
    public function changepw(){
        $this->checkUser();
        $this->display();
    }

    /**
     * 执行密码修改的操作
     */
    public function changePass(){
        $this->checkUser();

        if(!isset($_POST["oldpass"]) || !isset($_POST["newpass"]) || !isset($_POST["renewpass"]) ||
            $_POST["renewpass"] != $_POST["newpass"]){
            $this->error("请输入新旧密码或者重复密码与新密码不一致！");
        }

        $staffInfo = session("staff_info");
        if(md5( $_POST["oldpass"]) != $staffInfo["password"]){
            $this->error("旧密码错误！");
        }

        $result = D("Staff")->where(array("id"=>$staffInfo["id"]))->setField("password",md5($_POST["newpass"]));
        if(false === $result){
            $this->error("密码修改失败！");
        }

        $staffInfo["password"] = md5( $_POST["newpass"] );
        session("staff_info",$staffInfo);

        $this->success("密码修改成功！",U("index/index"));
    }
}