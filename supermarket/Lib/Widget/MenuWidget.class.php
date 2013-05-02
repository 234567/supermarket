<?php

class MenuWidget extends Widget
{

    private $menuList = array(
        "后台首页"=>array(
            "Index/index" => "后台首页",
        ),
        "分店管理" => array(
            "Branch/index" => "分店列表",
            "Branch/add" => "新增分店",
        ),
        "员工管理" => array(
            "Staff/index" => "员工列表",
            "Staff/add" => "增加员工",
        ),
        "供货商管理" => array(
            "Supplier/index" => "供货商列表",
            "Supplier/add" => "增加供货商",
        ),
        "商品信息管理" => array(
            "Category/index" => "分类列表",
            "Goods/index" => "商品列表",
            "Promotions/index" => "促销商品",
            "Promotions/release" => "发布促销",
            "Goods/add" => "录入商品",
        ),
        "入库管理" => array(
            "StockRecord/index" => "入库记录",
            "StockGoods/index" => "我要入库",
        ),
        "销售管理" => array(
            "SalesRecord/index" => "销售记录",
            "SaleGoods/index" => "我要销售",
        ),
        "权限管理" => array(
            "Node/index" => "节点列表",
            "Role/index" => "角色列表",
            "Node/add" => "增加节点",
            "Role/add" => "添加角色",
        ),
    );

    /**
     * 实现render方法
     * @param mixed $data
     * @return string|void
     */
    public function render($data)
    {
        if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
            return '<div class="alert alert-error">没有权限</div>';
        }
//        //如果是管理员
//        if (session(C("ADMIN_AUTH_KEY")) === true) {
//            //直接显示全部功能
//            return $this->renderFile(THEME_PATH . "Common/left_menu.html", $data);
//        }

//        //显示菜单项
//        $menu = array();
//        if(isset($_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]])) {
//            //如果已经缓存，直接读取缓存
//            $menu  = $_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]];
//        }else {
//        //读取数据库模块列表生成菜单项
//        $node = M("Node");
//        $id = $node->getField("id");
//        $where['level'] = 2;
//        $where['status'] = 1;
//        $where['pid'] = $id;
//        $list = $node->where($where)->field('id,name,title')->order('sort asc')->select();
//        if (isset($_SESSION['_ACCESS_LIST'])) {
//            $accessList = $_SESSION['_ACCESS_LIST'];
//        } else {
//            import('@.ORG.Util.RBAC');
//            $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
//        }
//        foreach ($list as $key => $module) {
//            if (isset($accessList[strtoupper(APP_NAME)][strtoupper($module['name'])]) || $_SESSION['administrator']) {
//                //设置模块访问权限
//                $module['access'] = 1;
//                $menu[$key] = $module;
//            }
//        }
//        //缓存菜单访问
//        $_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]] = $menu;
//        }


        //显示菜单项
        $menu = array();
        if(isset($_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]])) {
            //如果已经缓存，直接读取缓存
            $menu  = $_SESSION['menu'.$_SESSION[C('USER_AUTH_KEY')]];
        }else {
            if (isset($_SESSION['_ACCESS_LIST'])) {
                $accessList = $_SESSION['_ACCESS_LIST'];
            } else {
                import('@.ORG.Util.RBAC');
                $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
            }

            foreach($this->menuList as $name => $module){
                foreach($module as $path => $title){
                    $arr = explode("/",$path);
                    //如果是管理员 或者 有权限
                    if (session( C("ADMIN_AUTH_KEY")) || isset($accessList[strtoupper(APP_NAME)][strtoupper($arr[0])][strtoupper($arr[1])])  ) {
                        $menu[$name][$path] = $title;
                    }
                }
            }
            //缓存菜单访问
            $_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]] = $menu;
        }

        $data["menu"] = $menu;
        $tplFile = THEME_PATH . "Common/menu.html";
        $content = $this->renderFile($tplFile, $data);
        return $content;
    }
}