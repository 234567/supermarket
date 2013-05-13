<?php

class MenuWidget extends Widget
{

    private $menuList = array(
        array(
            "url" => "Index/index",
            "icon" => "icon-home",
            "title" => "后台首页"
        ), array(
            "url" => "Branch/index",
            "icon" => "icon-flag",
            "title" => "分店管理"
        ), array(
            "url" => "Staff/index",
            "icon" => "icon-user",
            "title" => "员工管理"
        ), array(
            "url" => "Supplier/index",
            "icon" => "icon-truck",
            "title" => "供货商管理"
        ), array(
            "title" => "商品相关"
        ), array(
            "url" => "Category/index",
            "icon" => "icon-sitemap",
            "title" => "商品分类管理"
        ), array(
            "url" => "Goods/index",
            "icon" => "icon-barcode",
            "title" => "商品信息管理",
        ), array(
            "url" => "Promotions/index",
            "icon" => "icon-hand-down",
            "title" => "商品促销管理"
        ), array(
            "url" => "StockRecord/index",
            "icon" => "icon-forward",
            "title" => "入库管理"
        ), /*array(
            "url" => "StockGoods/index",
            "icon"=>"icon-cloud-download",
            "title"=>"商品入库"
        ),*/
        array(
            "url" => "SalesRecord/index",
            "icon" => "icon-bar-chart",
            "title" => "销售管理",
        ), /*array(
            "url" => "SaleGoods/index",
            "icon"=>"icon-cloud-upload",
            "title"=>"商品销售"
        ),*/
        array(
            "title" => "权限管理相关"
        ), array(
            "url" => "Node/index",
            "icon" => "icon-list-ol",
            "title" => "节点管理"
        ), array(
            "url" => "Role/index",
            "icon" => "icon-group",
            "title" => "角色管理"
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

        //显示菜单项
        $menu = array();
        if (isset($_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]])) {
            //如果已经缓存，直接读取缓存
            $menu = $_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]];
        } else {
            //        //读取数据库模块列表生成菜单项
            //        $node = M("Node");
            //        $id = $node->getField("id");
            //        $where['level'] = 2;
            //        $where['status'] = 1;
            //        $where['pid'] = $id;
            //        $list = $node->where($where)->field('id,name,title')->order('sort asc')->select();
            if (isset($_SESSION['_ACCESS_LIST'])) {
                $accessList = $_SESSION['_ACCESS_LIST'];
            } else {
                import('@.ORG.Util.RBAC');
                $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
            }

            foreach ($this->menuList as $key => $module) {
                if (!isset($module['url'])) {
                    $menu[$key] = $module;
                    continue;
                }
                $url = explode("/", $module['url']);
                if (isset($accessList[strtoupper(APP_NAME)][strtoupper($url[0])]) || $_SESSION[C("ADMIN_AUTH_KEY")] === true) {
                    //设置模块访问权限
                    $module['access'] = 1;
                    $menu[$key] = $module;
                }
            }
            //缓存菜单访问
            //$_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]] = $menu;
        }


        /*//显示菜单项
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
        }*/
        $data["menu"] = $menu;
        $tplFile = THEME_PATH . "Common/menu.html";
        $content = $this->renderFile($tplFile, $data);
        return $content;
    }
}