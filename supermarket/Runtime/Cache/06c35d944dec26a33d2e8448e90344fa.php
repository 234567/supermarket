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
            <h4>权限列表</h4>
        </div>
        <div class="box-content">

                <form class="form-horizontal" method="post" action="<?php echo U('role/updateAccess');?>">
                    <fieldset>
                        <legend>详细权限信息</legend>
                <table class="table table-striped table-bordered table-hover table-condensed">
                    <tbody>
                    <input type="hidden" name="id" value="<?php echo ($info["id"]); ?>"/>
                    <?php if(is_array($nodeList)): $i = 0; $__LIST__ = $nodeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$level1): $mod = ($i % 2 );++$i;?><tr>
                            <td><label><input name="data[]" level="1" type="checkbox" obj="node_<?php echo ($level1["id"]); ?>" value="<?php echo ($level1["id"]); ?>:1:0"/> <b>[项目]</b> <?php echo ($level1["title"]); ?></label></td>
                        </tr>
                        <?php if(is_array($level1['data'])): $i = 0; $__LIST__ = $level1['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$level2): $mod = ($i % 2 );++$i;?><tr>
                                <td style="padding-left: 30px; ">
                                    <label>
                                        <input name="data[]" level="2" type="checkbox" obj="node_<?php echo ($level1["id"]); ?>_<?php echo ($level2["id"]); ?>" value="<?php echo ($level2["id"]); ?>:2:<?php echo ($level2["pid"]); ?>"/>
                                        <b>[模块]</b> <?php echo ($level2["title"]); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left: 60px;">
                                    <?php if(is_array($level2['data'])): $i = 0; $__LIST__ = $level2['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$level3): $mod = ($i % 2 );++$i;?><label>
                                            <input name="data[]" level="3" type="checkbox" obj="node_<?php echo ($level1["id"]); ?>_<?php echo ($level2["id"]); ?>_<?php echo ($level3["id"]); ?>" value="<?php echo ($level3["id"]); ?>:3:<?php echo ($level3["pid"]); ?>"/>
                                            <b>[操作]</b> <?php echo ($level3["title"]); ?>
                                        </label><?php endforeach; endif; else: echo "" ;endif; ?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">修改</button>
                            <button type="reset" class="btn">重置</button>
                        </div>
                    </fieldset>
                </form>

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



    <script type="text/javascript">
        //初始化数据
        function setAccess(){
            //清空所有已选中的
            $("input[type='checkbox']").prop("checked",false);
            //数据格式：
            //节点ID：node_id；1，项目；2，模块；3，操作
            //节点级别：level；
            //父级节点ID：pid
            var access=$.parseJSON('<?php echo ($info["access"]); ?>');
            var access_length=access.length;
            if(access_length>0){
                for(var i=0;i<access_length;i++){
                    //$("input[type='checkbox'][value='"+access[i]['val']+"']").attr("checked","checked");
                    $("input[type='checkbox'][value='"+access[i]['val']+"']").parent().addClass("checked");
                }
            }
        }
        $(function(){
            //执行初始化数据操作
            setAccess();
            //为项目时候全选本项目所有操作
            $("input[level='1']").click(function(){
                var obj=$(this).attr("obj")+"_";
                $("input[obj^='"+obj+"']").prop("checked",$(this).prop("checked"));
            });
            //为模块时候全选本模块所有操作
            $("input[level='2']").click(function(){
                var obj=$(this).attr("obj")+"_";
                $("input[obj^='"+obj+"']").prop("checked",$(this).prop("checked"));
                //分隔obj为数组
                var tem=obj.split("_");
                //将当前模块父级选中
                if($(this).prop('checked')){
                    $("input[obj='node_"+tem[1]+"']").prop("checked","checked");
                }
            });
            //为操作时只要有勾选就选中所属模块和所属项目
            $("input[level='3']").click(function(){
                var tem=$(this).attr("obj").split("_");
                if($(this).prop('checked')){
                    //所属项目
                    $("input[obj='node_"+tem[1]+"']").prop("checked","checked");
                    //所属模块
                    $("input[obj='node_"+tem[1]+"_"+tem[2]+"']").prop("checked","checked");
                }
            });
            //重置初始状态，勾选错误时恢复
            $(".reset").click(function(){
                setAccess();
            });
            //清空当前已经选中的
            $(".empty").click(function(){
                $("input[type='checkbox']").prop("checked",false);
            });
            $(".submit").click(function(){
                commonAjaxSubmit();
            });
        });
    </script>

</body>
</html>