<?php
/**
 * User: simplewind
 * Date: 4/21/13
 * Time: 9:30 PM
 * 修改这里的注释内容
 */

class PublicAction extends Action{

   // 用户登录页面
    public function login() {
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        }else{
            $this->redirect('Index/index');
        }
    }

    public function index() {
        //如果通过认证跳转到首页
        redirect(__APP__);
    }

    // 用户登出
    public function logout() {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            $this->success('登出成功！',U('public/login'));
        }else {
            $this->error('已经登出！');
        }
    }

    // 登录检测
    public function checkLogin() {
        $account =  $this->_post('account');
        $password =  $this->_post('password');
        $verify =  $this->_post('verify');
        if( empty( $account ) || empty( $password ) || empty( $verify ) ) {
            $this->error( '请输入完整的登陆信息！' );
        }

        if( session('verify') != md5( $verify )) {
            $this->error('验证码错误！');
        }

        //生成认证条件
        $map            =   array();
        // 支持使用绑定帐号登录
        $map['account']	= $account;
        $map["status"]	=	array('egt',0);

        import ( '@.ORG.Util.RBAC' );
        $authInfo = RBAC::authenticate($map);
        //使用用户名、密码和状态的方式进行认证
        if(false === $authInfo || $authInfo === null) {
            $this->error('帐号不存在!!');
        }elseif($authInfo['status'] == 0 ) {
            $this->error("帐号已被禁用，请联系管理员！");
        }else{
            if($authInfo['password'] != md5($password)) {
                $this->error('密码错误！');
            }

            session( C('USER_AUTH_KEY') ,$authInfo['id']);
            //保存员工姓名
            session("staff_info", $authInfo);

            //如果是超级管理员
            if($authInfo['account'] === 'admin') {
                session( C('ADMIN_AUTH_KEY') , true);
            }else{
                //获取分店信息
                $branchInfo = M("Branch")->where( array("id"=>$authInfo["branch_id"]) )->find();
                if(false === $branchInfo || null === $branchInfo){
                    //不存在的分店信息
                    $this->error("员工信息不正确！不存在的分店信息！");
                }
                //保存分店信息
                session("branch_info" , M("Branch")->where( array("id"=>$authInfo["branch_id"]) )->find() );
            }

            //保存登录信息
            $Staff	=	M('Staff');
            $ip		=	get_client_ip();
            $time	=	time();
            $data = array();
            $data['id']	=	$authInfo['id'];
            $data['last_login_time']	=	$time;
            $data['login_count']	=	array('exp','login_count+1');
            $data['last_login_ip']	=	$ip;
            $Staff->save($data);


            // 缓存访问权限
            RBAC::saveAccessList();
            $this->success('登录成功！',U("index/index"));

        }
    }

    public function verify() {
        $type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
        import("@.ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }

}