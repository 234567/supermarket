<?php
/**
 * User: simplewind
 * Date: 13-4-9
 * Time: 上午12:03
 * 修改这里的注释内容
 */

class BaseAction extends Action{

    function _initialize(){
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
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
    }


    public function index(){
        //列表过滤器，生成查询Map对象
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $name = $this->getActionName();
        $model = D($name);
        if(!empty($model)){
            $this->_list($model,$map);
        }
        $this->display();
    }


    function getReturnUrl() {
        return U(MODULE_NAME.'/'.strtolower(  C('DEFAULT_ACTION') ) );
    }

    protected function _search($name=""){
        if(empty($name)){
            $name = $this->getActionName();
        }
        $name = $this->getActionName();
        $model = D($name);
        $map = array();
        foreach($model->getDbFields() as $key => $val ){
//            if( isset( $this->_param( $val ) ) && ( $this->_param( $val ) !== "" ) ){
//                $map[ $val ] = $this->_param( $val );
//            }
            if (isset($_REQUEST [$val]) && $_REQUEST [$val] != '') {
                $map [$val] = $_REQUEST [$val];
            }
        }
        return $map;
    }



    protected function _list($model, $map, $param=array()) {
        //数据量统计
        $count = $model->where($map)->count('id');

        if($count > 0){
            import("@.ORG.Page");
            //分页数
            $listRows = $param['listRows']? $param['listRows'] : 10;
            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $parameter .= "$key=" . urlencode($val) . "&";
                }
            }


            if( !empty($param['target']) && !empty($param['pagesId'])) {
                $p = new Page($count, $listRows, $parameter, $param['url'],$param['target'], $param['pagesId']);
            }else{
                $p = new Page($count, $listRows, $parameter , $param['url']);
            }

            $voList = $model->where($map)->limit($p->firstRow.','.$p->listRows)->select();
            $voList = $model->parseFieldsMap($voList);

            $pages = C('PAGE');//要ajax分页配置PAGE中必须theme带%ajax%，其他字符串替换统一在配置文件中设置，
            //可以使用该方法前用C临时改变配置
            foreach ($pages as $key => $value) {
                $p->setConfig($key, $value); // 'theme'=>'%upPage% %linkPage% %downPage% %ajax%'; 要带 %ajax%
            }

            //分页显示
            $page = $p->show();

            //模板赋值
            $this->assign('list', $voList);
            $this->assign("page", $page);
//            if ($this->isAjax()) {//判断ajax请求
//                layout(false);
//                $param['template'] = (!$template) ? 'ajaxlist' : $template;
//                exit($this->fetch($template));
//            }
        }
    }


    public function insert(){
        $name = $this->getActionName();
        $model = D($name);
        $vo = $model->create();
        if(false === $vo){
            $this->error($model->getError());
        }
        $list = $model->add();
        if(false === $list){
            $this->error("新增失败！");
        }

        $this->success("新增成功！",$this->getReturnUrl());
    }

//    public function read(){
//        $this->edit();
//    }

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
        $name = $this->getActionName();
        $model  = D($name);

        if(false === $model->create()){
            $this->error($model->getError());
        }

        $list = $model->save();

        if(false === $list){
            $this->error("编辑失败！");
        }

        $this->success("编辑成功！",$this->getReturnUrl());
    }


    public function del(){
        $name = $this->getActionName();
        $model = M($name);
        if(!empty($model)){
            $pk = $model->getPk();
            $id = $this->_param( $pk );
            if( !isset($id) ){
                $this->error("非法操作！");
            }
            //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
            $condition = array($pk => array("in" ,explode(",",$id)));
            $list = $model->where($condition)->setField('status',  -1 );
            if( false === $list ){
                $this->error("删除失败！");
            }
            $this->success("删除成功！",$this->getReturnUrl());
        }
    }


    public function foreverdelete(){
        $name = $this->getActionName();
        $model = M($name);
        if(!empty($model)){
            $pk =$model->getPk();
            $id = $this->_param( $pk );
            if( !isset($id) ){
                $this->error("非法操作！");
            }

            //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
            $condition = array($pk => array("in" ,explode(",",$id)));
            if( false === $model->where($condition)->delete() ){
                $this->error("删除失败！");
            }
            $this->success("删除成功！",$this->getReturnUrl());
        }
    }


    public function clear(){
        $name = $this->getActionName();
        $model = D($name);

        if(!empty($model)){
            $list = $model->where('status=1')->delete();
            if(false === $list){
                $this->error(L('_DELETE_FAIL_'));
            }

            $this->success(L('_DELETE_SUCCESS_'),$this->getReturnUrl());
        }
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

    public function checkPass(){
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_get($pk);
        if(empty($id)){
            $this->error("非法参数！");
        }
        $condition = array($pk  =>  array("in" ,$id));
        $list = $model->checkPass($condition);
        if(false === $list){
            $this->error("状态批准失败！");
        }
        $this->success("状态批准成功！",$this->getReturnUrl());
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

    public function recycleBin(){
        $map = $this->_search();
        $map ['status'] = - 1;
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
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