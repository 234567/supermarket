<?php
return array(
	//'配置项'=>'配置值'

    //数据库配置
    'DB_TYPE'               => 'mysql',     // 数据库类型
    'DB_HOST'               => '192.168.1.2', // 服务器地址
    'DB_NAME'               => 'supermarket',          // 数据库名
    'DB_USER'               => 'root',      // 用户名
    'DB_PWD'                => 'root',          // 密码
    'DB_PORT'               => '3306',        // 端口
    'DB_PREFIX'             => '',    // 数据库表前缀

    //启用主题
    'DEFAULT_THEME' => 'charisma',      //默认主题
    'THEME_LIST' => 'charisma',            //主题列表
    'TMPL_DETECT_THEME' => 	true, // 自动侦测模板主题
    //模板替换
    'TMPL_PARSE_STRING'  =>array(
        //增加ASSETS替换
        '__ASSETS__' => __ROOT__.'/assets',//静态资源目录
        '__PUBLIC__' => __ROOT__.'/assets', // 更改默认的/Public 替换规则
    ),



    //关闭URL大小写敏感
    'URL_CASE_INSENSITIVE' =>true,
    //启用REWRITE
    'URL_MODEL' => 2,

    //开启参数过滤
    'DEFAULT_FILTER' =>'htmlspecialchars,strip_tags',
    'VAR_FILTERS'=>'htmlspecialchars,strip_tags',
    'DB_FIELDTYPE_CHECK'=>true,
    //更改分页的样式，这里改掉以配合Bootstrap的分页样式
//    'PAGE'=>array(
//        //'theme'=>'%upPage% %linkPage% %downPage% %ajax%'
//        'theme' =>'<ul><li><a>%totalRow% %header% %nowPage%/%totalPage% 页 </a></li>'.
//            '%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end% </ul>',
//    ),

    //自动开启SESSION
    'SESSION_AUTO_START'        =>  true,

    //以下配置与RBAC权限认证有关
    'USER_AUTH_ON'              =>  true,
    'USER_AUTH_TYPE'			=>  2,		// 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             =>  'supermarket@baidu.com',	// 用户认证SESSION标记
    'ADMIN_AUTH_KEY'			=>  'administrator',
    //本系统用户即是超市员工，所以这里以员工表为验证模型，没有另设User模型
    'USER_AUTH_MODEL'           =>  'Staff',	// 默认验证数据表模型
    'AUTH_PWD_ENCODER'          =>  'md5',	// 用户认证密码加密方式
    'USER_AUTH_GATEWAY'         =>  '/public/login',// 默认认证网关
    'NOT_AUTH_MODULE'           =>  'Public',	// 默认无需认证模块
    'REQUIRE_AUTH_MODULE'       =>  '',		// 默认需要认证模块
    'NOT_AUTH_ACTION'           =>  '',		   // 默认无需认证操作
    'REQUIRE_AUTH_ACTION'       =>  '',		// 默认需要认证操作
    'GUEST_AUTH_ON'             =>  false,      // 是否开启游客授权访问
    'GUEST_AUTH_ID'             =>  0,              // 游客的用户ID
    //角色表、角色用户表、访问权限表、节点信息表
    'RBAC_ROLE_TABLE'           =>  'role',
    'RBAC_USER_TABLE'           =>  'role_user',
    'RBAC_ACCESS_TABLE'         =>  'access',
    'RBAC_NODE_TABLE'           =>  'node',
    'DB_LIKE_FIELDS'            =>  'title|remark',



    //自动加载、预加载标签库
    'APP_AUTOLOAD_PATH'         =>  '@.TagLib',
    'TAGLIB_PRE_LOAD' => 'Front' ,
    'SHOW_PAGE_TRACE' => 1,
);
?>