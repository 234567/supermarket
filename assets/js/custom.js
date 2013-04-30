/**
 *
 *
 * 公共JS，负责绑定处理一些公共事件
 *
 */

$(document).ready(function(){


    //highlight current / active link
    $('ul.main-menu li a').each(function(){
        if($(this).attr("href")===String(window.location)){
            $(this).parent().addClass('active');
            $("ul.main-menu a.menu-first").removeClass("collapsed");
            $(this).parents("ul").addClass("in collapse");
            //$('a[href="#'+id+'"]').click();
        }
    });

    //animating menus on hover
    $('ul.main-menu li:not(.nav-header)').hover(function(){
            $(this).animate({'margin-left':'+=5'},400);
        },
        function(){
            $(this).animate({'margin-left':'-=5'},400);
        });



    //prevent # links from moving to top
    $('a[href="#"][data-top!=true]').click(function(e){
        e.preventDefault();
    });


    //uniform - styler for checkbox, radio and file input
    $("input:checkbox, input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

    //chosen - improves select
    $('[data-rel="chosen"],[rel="chosen"]').data("placeholder","请选择").chosen();

    //tabs
    $('#myTab a:first').tab('show');
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    //makes elements soratble, elements that sort need to have id attribute to save the result
    $('.sortable').sortable({
        revert:true,
        cancel:'.btn,.box-content,.nav-header',
        update:function(event,ui){
            //line below gives the ids of elements, you can make ajax call here to save it to the database
            //console.log($(this).sortable('toArray'));
        }
    });

    //tooltip
    $('[rel="tooltip"],[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});

    //popover
    $('[rel="popover"],[data-rel="popover"]').popover();

    //iOS / iPhone style toggle switch
    //$('.iphone-toggle').iphoneStyle();

    $('.btn-close').click(function(e){
        e.preventDefault();
        $(this).parent().parent().parent().fadeOut();
    });

    $('.btn-minimize').click(function(e){
        e.preventDefault();
        var $target = $(this).parent().parent().next('.box-content');
        if($target.is(':visible')){
            $('i',$(this)).removeClass('icon-chevron-up').addClass('icon-chevron-down');
        }else{
            $('i',$(this)).removeClass('icon-chevron-down').addClass('icon-chevron-up');
        }
        $target.slideToggle();
    });
});



$(function(){
    "use strict";

//    window._originalAlert = window.alert;
//    window.alert = function(text) {
//        function bootStrapAlert(){
//            if(!$.fn.dialog) return false;
//            if($("#windowAlertModal").length ==1) return true;
//            $("body").append('<div id="windowAlertModal" title="注意"></div>');
//            $("#windowAlertModal").text(text);
//            $("#windowAlertModal").dialog({
//                zIndex:9999,
//                autoOpen: false,
//                modal: true,
//                buttons:{
//                    "确定":function(){
//                        $("#windowAlertModal").dialog('close');
//                    }
//                }
//            });
//            return true;
//        }
//        if ( bootStrapAlert() ){
//            $('#windowAlertModal').text(text);
//            $('#windowAlertModal').dialog('open');
//        }  else {
//            console.log('未找到');
//            window._originalAlert(text);
//        }
//    };
//
//    window._originalConfirm = window.confirm;
//    window.confirm = function(text, cb) {
//
//    }



    //表格排序插件
    $("table").addClass("tablesorter").tablesorter();

    /**
     * 日期选择控件,来自JQuery UI Bootstrap
     */
    $(".datepicker").datepicker();

    $.fn.datetimepicker.dates['zh-CN'] = {
        days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
        daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
        daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
        months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
        monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
        today: "今日"
    };

    /**
     * 日期时间选择控件
     */
    $("div.datetimepicker").datetimepicker({
        language: 'zh-CN'
    });

    /**
     * 表单验证插件
     */
    $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();

    $("select[name*='category'] option").each(function(){
        if( parseInt($(this).val() ,10) < 10000 ){
            $(this).attr('disabled',true);
        }
    });
});