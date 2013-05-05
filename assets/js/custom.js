/**
 *
 *
 * 公共JS，负责绑定处理一些公共事件
 *
 */

$(document).ready(function(){

    //highlight current / active link
    $('ul.main-menu li a').each(function(){
        if($($(this))[0].href==String(window.location))
            $(this).parent().addClass('active');
    });

    //animating menus on hover
    $('ul.main-menu li:not(.nav-header)').hover(function(){
            $(this).animate({'margin-left':'+=5'},300);
        },
        function(){
            $(this).animate({'margin-left':'-=5'},300);
        });

    //ajax menu checkbox
    $('#is-ajax').on("click",function(e){
        $.cookie('is-ajax',$(this).prop('checked'),{expires:365});
    });

    $('#is-ajax').prop('checked',$.cookie('is-ajax')==='true' ? true : false);

    //disbaling some functions for Internet Explorer
    if($.browser.msie)
    {
        $('#is-ajax').prop('checked',false);
        $('#for-is-ajax').hide();
    }

    //establish history variables
    var History = window.History, // Note: We are using a capital H instead of a lower h
        State = History.getState(),
        $log = $('#log');

    //bind to State Change
    History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        var State = History.getState(); // Note: We are using History.getState() instead of event.state
        $.ajax({
            url:State.url,
            success:function(msg){
                //替换内容
                $('#content').html($(msg).find('#content').html());
                //替换标题
                $('title').text($(msg)[1].innerText);
                //移除提示框
                $('#loading').remove();
                //显示新内容
                $('#content').fadeIn("slow");

                //处理页面各种插件绑定
                docReady();

                if(!!$('#is-ajax').prop('checked') && State.url.search(/category\/index/) !== -1 ){
                    $("table").removeClass("tablesorter");
                    $("table tr:odd").addClass("odd");
                    $("table tr:not(.odd)").hide();
                    $("table tr:first-child").show();
                    $('a.expand').live('click',function(e){e.preventDefault();});
                    $("table").tablesorter({
                        headers: {
                            0: {sorter: false},
                            1: {sorter: false},
                            2: {sorter: false},
                            3: {sorter: false}
                        }
                    });
                    //为了处理重复绑定事件的BUG。。先取消
                    $("table tr.odd").die('click');
                    $("table tr.odd").live('click',function(){
                        var inner = $(this).next('tr').find('.table-inner');
                        if(inner.html() === ''){
                            var url = $(this).find('td:first a').attr('href');
                            inner.load(url,function(){
                                $("table tr:odd",inner).addClass("odd");
                                $("table tr:not(.odd)",inner).hide();
                                $("table tr:first-child",inner).show();
                            });
                        }
                        $(this).next("tr").toggle('slideDown');
                        $(this).find("i:first").toggleClass("icon-minus");
                    });
                }
            }
        });
    });

    //ajaxify menus
    $('.main-menu a.ajax-link').on("click",function(e){
        if($.browser.msie) e.which=1;
        if(e.which!=1 || !$('#is-ajax').prop('checked') || $(this).parent().hasClass('active')) return;
        e.preventDefault();
        if($('.btn-navbar').is(':visible')){
            $('.btn-navbar').click();
        }
        $('#loading').remove();
        $('#content').fadeOut().parent().append('<div id="loading" class="center">正在努力加载中...<div class="center"></div></div>');
        $('#loading').css({
            top:$(window).height()/2,
            left:$(window).width()/2-70
        });
//        $('#content').fadeOut().parent().append('<div  id="loading" class="center progress progress-striped progress-success active" style="margin:150px 50px;"><div class="bar" style="width: 50%;"></div></div>');
        var $clink=$(this);
        History.pushState(null, null, $clink.attr('href'));
        $('ul.main-menu li.active').removeClass('active');
        $clink.parent('li').addClass('active');
    });

    docReady();
});


function docReady(){

    //uniform - styler for checkbox, radio and file input
    $("input:checkbox, input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

    //chosen - improves select
    $('[data-rel="chosen"],[rel="chosen"]').data("placeholder","请选择").chosen({
        allow_single_deselect:true,
        no_results_text:"没有匹配的结果！"
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
    $("input,select,textarea").not("[type=submit]").jqBootstrapValidation({});

}


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


//    $("select[name*='category'] option").each(function(){
//        if( parseInt($(this).val() ,10) < 10000 ){
//            $(this).attr('disabled',true);
//        }
//    });
});