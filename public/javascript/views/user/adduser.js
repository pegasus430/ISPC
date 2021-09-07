;
/*
 * adduser.js
 * @cla on 20.09.2018
 */

$(document).ready(function(){
	
	var _active_tab = null; 
	
	if(window.location.hash) {
		var _hash = window.location.hash.substring(1);		
		var _tabs = $(".other_settings_accordion").find("> fieldset").each(function(i){
			if ( $(this).attr('id') == _hash) {
				_active_tab = i;
				return false;
			}
		});
	}
	
	//if (window.logininfo_usertype == 'SA') {
    	//i've added accordion only for admin
    	$(".other_settings_accordion").find("> fieldset").each(function(){
    		$(this).wrapInner( "<div class='accordion_body'></div>");
    		var _legend = $(this).find(">.accordion_body > legend").addClass('accordion_head');
    		$(this).prepend(_legend);
    	});
    	$(".other_settings_accordion").find("> fieldset").css({
    		'border': 0,
    		'padding': 0,
    		'margin': 0,
    	})
    	
    	$(".other_settings_accordion").find("> fieldset > legend").css({
            'padding': "10px",
            'margin': 0,
            'width': 'calc(100% - 20px)',
            'text-indent': '20px',
            'display': 'block'
        })
    	
    	$(".other_settings_accordion").accordion({
            active: _active_tab,
            collapsible: true,
            autoHeight: false,
            header: '> fieldset > legend',
            
            change: function (event, ui) {
                $("#tab_container_provider #box-PatientAcp").data('accordion_active', $(event.target).accordion("option", 'active'));
            },
        
        });
	//}
	
	
	
	$("input[name^='user_settings\[topmenu\]'][type='checkbox']").change(function(event){		
		if ($("input[name^='user_settings\[topmenu\]'][type='checkbox']:checked").length > window.topmenu_max_checked) {
			this.checked = false; // reset first
			event.preventDefault();
			
			setTimeout(function(){alert(translate("[Max topmenu links is %1%]").format('', window.topmenu_max_checked));}, 50);
		}
	});
	
	
	
});


function elvi_username_show(_this)
{
	if ($(_this).val() == 'connect') {
		$("div.selector_elvi_username").show();
	} else {
		$("div.selector_elvi_username").hide().find('#elvi_username').val('');
	}	
}