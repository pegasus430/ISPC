;
/*
 * @cla on 05.10.2018
 * add here fn that will NOT be used in production
 */


$(document).ready(function() {
	
	var _development_notranslation = $.cookie("development_notranslation");
    var _development_noselect = $.cookie("development_noselect");
	
    if (_development_noselect == '1') {
    	return;
    }
	/**
	 * top-right select client
	 */
	$("#connectedclients option").each(function(){
		if ($(this).val() == '0') {
			 $(this).text('<b>This select is only when testing</b>');
			 $(this).attr("data-3", "NoClient");
		} else {
			 $(this).attr("data-3", $(this).text());
		}
		$(this).attr("data-1", $(this).val());
		$(this).attr("data-2", $(this).text());
	});
	
    $("#connectedclients")
    .chosen({
        
        placeholder_text_single     : ' ',
        inherit_select_classes      : false,
        allow_single_deselect       : false,
        display_selected_options    : false,
        width   : '140px',
        //multiple: false,
        "search_contains": false,
        no_results_text: translate('noresultfound'),
        
        
        "data-choice_vsprintf"  : '%3%' ,
        "data-row_vsprintf"     : '%2% <span style="float: right; font-weight:bold">(cid: %1%)</span>' ,
        "data-search_vsprintf"  : '%1% %2%',
        
    }).on("chosen:container_mousedown_open", function (evt, data) {
    	setTimeout(function(){
    		$("#adminclientid_chosen.chosen-container-active a.chosen-single").css({
    			'background':"none"
    		});
    	}, 10);
    	
	 }); 
    
    $("#connectedclients_chosen a.chosen-single").css({
    	'background-color':"transparent",
    	'height':"42px",
	    'border':"0",
	    'font-size':"14px",
	    'color':"#d6dae1",
	    'position':"relative",
	    'width':"140px",
	    'margin':"0",
	    'padding':"0",
	    'padding-top':"14px",
	    'padding-left':"12px",
	    'z-index':"3",
	    '-webkit-appearance': "none", 
	    '-moz-appearance': "none",
	    'appearance': "none;",
	    'transition':"background 0.3s"
    });
    $("#connectedclients_chosen div.chosen-drop").css({
    	 'width' : '340px',
    	 'position': 'absolute'
    });
    
    
    $("#connectedclients_chosen a.chosen-single div b").css({
    	'background-image': "url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSI5IiB2aWV3Qm94PSIwIDAgMTggOSI+CiAgPG1ldGFkYXRhPjw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4KICAgPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIvPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz48L21ldGFkYXRhPgo8ZGVmcz4KICAgIDxzdHlsZT4KICAgICAgLmNscy0xIHsKICAgICAgICBmaWxsOiAjZmZmOwogICAgICAgIGZpbGwtcnVsZTogZXZlbm9kZDsKICAgICAgfQogICAgPC9zdHlsZT4KICA8L2RlZnM+CiAgPHBhdGggaWQ9ImFycm93LWRvd24iIGNsYXNzPSJjbHMtMSIgZD0iTTg2MC44MTYsMTkuMDFhMC41NjQsMC41NjQsMCwwLDAsMC0uODM3LDAuNjY3LDAuNjY3LDAsMCwwLS45LDBMODUyLDI1LjU2NmwtNy45Mi03LjM5M2EwLjY2NiwwLjY2NiwwLDAsMC0uOSwwLDAuNTY0LDAuNTY0LDAsMCwwLDAsLjgzN2w4LjM2OCw3LjgxMmEwLjY2NywwLjY2NywwLDAsMCwuOSwwWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTg0MyAtMTgpIi8+Cjwvc3ZnPgo=')",
    	'background-position': '0px 15px',
        'background-repeat' : 'no-repeat',
    });

   
    
    var _selectLogout = $(
    "<ol id='topmenu_profile_links' onClick='topmenu_profile_select(event)' style='display:none; position:absolute; background-color:#fff; border:2px solid lime; padding: 4px;font-weight:bold'>"
    + "<li style='padding: 2px;border:1px solid gray' value='#'>This select is only when testing</li>"
    + "<li style='padding: 2px;' value='" + window.appbase + "user/editprofile'>&raquo; Original Action</li>"
    + "<li style='padding: 2px;' value='" + window.appbase + "index/logout'>&raquo; Logout</li>"
    + "<li style='padding: 2px;' value='development_notranslation'>&raquo; Turn-Translation " + (_development_notranslation == "1" ? "ON" : "OFF") + "</li>"
    + "<li style='padding: 2px;' value='development_noselect'>&raquo; Turn 2 Debug Selects " + (_development_noselect == "1" ? "ON" : "OFF") + "</li>"
    + "</ol>"
    );
    
    $("div.myaccount").append(_selectLogout);
    $("div.myaccount > a.username").click(function(event){
    	topmenu_profile_open(this);
    	event.preventDefault();
    	return false;
    });
    
    
});


function topmenu_profile_open(_this) {
	
	
	if($(_this).hasClass('dev_opened')){
		$(_this)
		.removeClass('dev_opened')
		.parent()
		.find("#topmenu_profile_links").hide().blur();
		
//		$(_this)
//		.removeClass('dev_opened')
//		.parent().find("select")
//		.attr("size", 0).hide();
	} else {
		$(_this)
		.addClass('dev_opened')
		.parent()
		.find("#topmenu_profile_links").show().focus();
//		.attr("size", 10).show().css({"height" : "180px", "min-width" : "140px"}).find("option").css({"padding": "10px"});
	}
	return false;
	//$('#topmenu_profile_links').attr('size',22); 
};
function topmenu_profile_select(event) {

	var _this = event.target.getAttribute('value');

    
    if (_this == 'development_notranslation') {
    	
    	var _development_notranslation = $.cookie("development_notranslation");

    	if (_development_notranslation == '1') {
    		$.cookie('development_notranslation', '0', { path: '/' });
    	} else {
	    	var _date = new Date();
	        _date.setTime(_date.getTime() + (1*60*60*1000));
	    	$.cookie('development_notranslation', '1', { expires: _date, path: '/' });
    	}
        window.location.reload(); 
        
    } else if (_this == 'development_noselect') {
    	
        var _development_noselect = $.cookie("development_noselect");

    	if (_development_noselect == '1') {
    		$.cookie('development_noselect', '0', { path: '/' });
    	} else {
	    	var _date = new Date();
	        _date.setTime(_date.getTime() + (10*60*1000));
	    	$.cookie('development_noselect', '1', { expires: _date, path: '/' });
    	}
        window.location.reload(); 

    } else {
    	window.location.href = _this;    	
    }
    
	return false;
}

