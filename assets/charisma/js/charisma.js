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