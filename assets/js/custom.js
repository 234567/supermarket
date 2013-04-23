/**
 *
 *
 * 公共JS，负责绑定处理一些公共事件
 *
 */
$(function(){
    "use strict";

    //向导插件
    $(".wizard").bwizard();

    //返回按钮
    $('a.btn-back').on('click',function(){
        history.go(-1);
    });


});