<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    
    <title>角色组列表</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    
    

<link rel="stylesheet" type="text/css" href="__ASSETS__/charisma/css/bootstrap-cerulean.css" />


<link rel="stylesheet" type="text/css" href="__ASSETS__/jquery-ui-bootstrap/css/jquery-ui-1.10.0.custom.css" />


<link rel="stylesheet" type="text/css" href="__ASSETS__/charisma/css/charisma-app.css" />


<link rel="stylesheet" type="text/css" href="__ASSETS__/charisma/css/chosen.css" />
<link rel="stylesheet" type="text/css" href="__ASSETS__/charisma/css/uniform.default.css" />
<link rel="stylesheet" type="text/css" href="__ASSETS__/charisma/css/opa-icons.css" />
<link rel="stylesheet" type="text/css" href="__ASSETS__/plugins/bwizard/bwizard.css" />


<link rel="stylesheet" type="text/css" href="__ASSETS__/css/custom.css" />
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!-- jQuery -->
    <script type="text/javascript" src="__ASSETS__/js/jquery-1.8.3.min.js"></script>
</head>
<body>



<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".top-nav.nav-collapse,.sidebar-nav.nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand"> <img alt="Logo" src="__ASSETS__/charisma/img/logo20.png" /> <span>超市管理系统</span></a>

            <!-- user dropdown starts -->
            <div class="btn-group pull-right" >
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="icon-user"></i><span class="hidden-phone">用户组：用户名</span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a >修改个人资料</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo U('/index/logout');?>">退出登陆</a></li>
                </ul>
            </div>
            <!-- user dropdown ends -->


            <!--
            <div class="top-nav nav-collapse">
                <ul class="nav">
                    <li>
                        <form class="navbar-search pull-left">
                            <input placeholder="Search" class="search-query span2" name="query" type="text">
                        </form>
                    </li>
                </ul>
            </div>
            -->
            <!--/.nav-collapse -->
        </div>
    </div>
</div>




<div class="well nav-collapse sidebar-nav affix span2">
    <ul class="nav nav-tabs nav-stacked main-menu">
        <li class="nav-header hidden-tablet">功能列表</li>
        <li>
            <a href="<?php echo U('/index');?>">
                <i class="icon-home"></i>
                <span class="hidden-tablet">仪表盘</span>
            </a>
        </li>
        <li>
            <a href="#authority" class="nav-header menu-first collapsed" data-toggle="collapse">
                <i class="icon-lock"></i>权限管理</a>
            <ul id="authority" class="nav nav-list collapse in menu-second">
                <li><a href="<?php echo U('node/index');?>"><i class="icon-list"></i>节点列表</a></li>
                <li><a href="<?php echo U('role/index');?>"><i class="icon-list"></i>角色列表</a></li>
                <li><a href="<?php echo U('staff/index');?>"><i class="icon-list"></i>员工列表</a></li>
                <li><a href="<?php echo U('node/add');?>"><i class="icon-user"></i>增加节点</a></li>
                <li><a href="<?php echo U('staff/add');?>"><i class="icon-user"></i>新增员工</a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="container">
    <div class="row">

        <div class="span11 offset1" style="min-height: 500px;">
            
            <!--
            <ul class="breadcrumb">
            
                <li>
                    <a href="<?php echo U('/index');?>">后台首页</a>
                    <span class="divider">/</span>
                </li>
            
            </ul>
            -->

            
            

    <div class="box">
        <div class="box-header well-small">
            <h4>角色组列表</h4>
        </div>
        <div class="box-content">
            <a id="btn-add" href="<?php echo U('role/add');?>" class="btn btn-round btn-info ">
                <i class="icon-plus"></i>新增角色
            </a>
            <div id="dialog"></div>
            <hr />
            <div id="branch_list">

                <table class="table table-striped table-bordered table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>编号</th>
                        <th>组名</th>
                        <th>状态</th>
                        <th>描述</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($vo["id"]); ?></td>
                            <td><a href="<?php echo U('role/edit');?>?id=<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></a></td>
                            <td>
                                <?php if(($vo["status"] == 1)): ?><span class="label label-success">正常</span>
                                    <?php elseif(($vo["status"] == 0)): ?>
                                    <span class="label label-danger">已禁用</span>
                                    <?php else: ?>
                                    <span class="label label-inverse">已删除</span><?php endif; ?>
                            </td>
                            <td><?php echo ($vo["remark"]); ?></td>
                            <td>
                                <?php if(($vo["status"] == 1)): ?><a class="btn btn-danger btn-mini" href="<?php echo U('role/forbid');?>?id=<?php echo ($vo["id"]); ?>">禁用</a>
                                    <a class="btn btn-mini" href="<?php echo U('role/del');?>?id=<?php echo ($vo["id"]); ?>">删除</a>
                                    <?php elseif(($vo["status"] == 0 )): ?>
                                    <a class="btn btn-success btn-mini" href="<?php echo U('role/resume');?>?id=<?php echo ($vo["id"]); ?>">恢复</a>
                                    <a class="btn btn-mini" href="<?php echo U('role/del');?>?id=<?php echo ($vo["id"]); ?>">删除</a>
                                    <?php else: ?>
                                    <a class="btn btn-inverse btn-mini" href="<?php echo U('role/recycle');?>?id=<?php echo ($vo["id"]); ?>">还原</a><?php endif; ?>
                                <a class="btn btn-mini" href="#">用户列表</a>
                                <a class="btn btn-mini" href="<?php echo U('role/auth');?>?id=<?php echo ($vo["id"]); ?>">权限控制</a>
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <div id="page" class="pagination pagination-small"><?php echo ($page); ?></div>
            </div>
        </div>
    </div>

        </div>
    </div>
</div>


<div class="container">
    <div class="row">
        <hr/>
        <div class="span12">
            <span class="label label-info offset4"><a href="http://supermarket.f-tm.net">超市管理系统</a> @GoogleRobot 版权所有  </span>
        </div>
    </div>
</div>




<script type="text/javascript" src="__ASSETS__/bootstrap/js/bootstrap.js"></script>


<script type="text/javascript" src="__ASSETS__/jquery-ui-bootstrap/jquery-ui-1.9.2.custom.min.js"></script>


<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.chosen.min.js"></script>


<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.flot.min.js"></script>
<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.flot.pie.min.js"></script>
<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.flot.stack.js"></script>


<script type="text/javascript" src="__ASSETS__/charisma/js/jquery.uniform.min.js"></script>


<script type="text/javascript" src="__ASSETS__/charisma/js/charisma.js"></script>


<script type="text/javascript" src="__ASSETS__/plugins/jquery-form/jquery.form.js"></script>


<script type="text/javascript" src="__ASSETS__/plugins/bwizard/bwizard.js"></script>


<script type="text/javascript" src="__ASSETS__/js/custom.js"></script>



</body>
</html>