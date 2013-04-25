<?php
/**
 * User: simplewind
 * Date: 13-4-9
 * Time: 上午12:03
 * 修改这里的注释内容
 */

class BaseAction extends Action{

    function _initialize(){
        $this->waitSecond = 9999;
        //RBAC权限检测
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('@.ORG.Util.RBAC');
            if (!RBAC::AccessDecision()) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error("没有权限！");
                }
            }
        }

        //取出员工所属的分店信息
        $branchInfo = session("branch_info");
        ///如果既不是管理员，又不是员工，则发生错误！
        if( session( C("ADMIN_AUTH_KEY") ) != true && empty($branchInfo) ){
            $this->error("登陆信息出错！",U("public/logout"));
        }
    }


    protected function getReturnUrl() {
        return U(MODULE_NAME.'/'.strtolower(  C('DEFAULT_ACTION') ) );
    }

    public function index(){
        //实例化Service
        $service = D($this->getActionName(),"Service");
        //无过滤条件获取列表
        $result = $service->getList(array());
        $this->list = $result['list'];
        trace($result["list"]);
        $this->page = $result['page'];
        $this->display();
    }

    public function insert(){
        $service = D($this->getActionName(),"Service");
        try{
            $service->insert();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("新增成功！",$this->getReturnUrl());
    }

    public function edit(){
        $name = $this->getActionName();
        $model = M($name);
        $id = $this->_param( $model->getPk());
        if(empty($id)){
            $this->error("参数错误！");
        }
        $vo = $model->getById($id);
        $this->vo = $vo;
        $this->display();
    }

    public function update(){
        $service = D($this->getActionName(),"Service");
        try{
            $service->update();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("修改成功！",$this->getReturnUrl());
    }


    public function del(){
        $service = D($this->getActionName(),"Service");
        try{
            $service->del();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("删除成功！",$this->getReturnUrl());
    }


    public function forbid(){
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_param($pk);
        if(empty($id)){
            $this->error("非法参数！");
        }
        $condition = array($pk => array("in", $id));
        $list = $model->forbid($condition);
        if( false === $list){
            $this->error("状态禁用失败！");
        }

        $this->success("状态禁用成功！",$this->getReturnUrl());
    }

    public function recycle(){
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_get($pk);
        if(empty($id)){
            $this->error("非法参数！");
        }
        $condition = array($pk => array("in", $id));
        $list = $model->recycle($condition);
        if(false === $list){
            $this->error("状态还原失败！");
        }
        $this->success("状态还原成功！",$this->getReturnUrl());
    }

    function resume() {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_get( $pk );
        if(empty($id)){
            $this->error("非法参数！");
        }
        $condition = array($pk => array('in', $id));
        if (false == $model->resume($condition)) {
            $this->error('状态恢复失败！');
        }
        $this->success('状态恢复成功！',$this->getReturnUrl());
    }
}