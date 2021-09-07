;
/*
 * elviroom.js
 * @cla on 10.09.2018
 */

var _debug = typeof(DEBUG) !== 'undefined' && window.DEBUG === true ? true : false;

function elviroom_user_login(_this) {
	
	var _action = 'user.login';
	
	if (_debug && $(_this).attr('id') == 'DEV_perform_action') {
		_action	= $('select#action').val();
	}
	
	var _data = {
        'action'            : _action,
        'perform_action'    : 'SUBMIT'
    };

	
	$.ajax({
        dataType    : "json",
        type        : "POST",
        //url         : appbase + 'user/elviroom',
        url         : appbase + 'user/editprofile?pseudo_action=elviroom',
        data        : _data,
        
        beforeSend: function (jqXHR) {  
        	
        	var dlist = $('<div style="width: 50%;margin: 0 auto; padding-top:10px" class="loading_wait"><img src="images/loading.gif"></div>');
        	dlist.insertAfter(_this)
        	$(_this).hide();
        	
        },
        complete: function (jqXHR) {
        	$(_this).show().parent().find('.loading_wait').hide();
        },
        
        success: function (response, request) {

        	if (response.hasOwnProperty("success") && response.success) {
            	if (response.hasOwnProperty("__ispc") && response.__ispc.hasOwnProperty("iframe_url")) {
            		//as_iframe(response.iframe_url); //Load denied by X-Frame-Options: https://dev.elvi.de/ does not permit framing by http://10.0.0.36/ispc2017_08/public/user/elviroom.
            		as_new_window(response.__ispc.iframe_url);	
            	}
            	            	
            } else if (response.hasOwnProperty("__ispc")  && response.__ispc.hasOwnProperty("create_new_elviUser")) {
            	
            	//redirect this user to user/pare
            	window.location = appbase + "user/editprofile#fieldset_elvi_settings";
            	
            } else if (response.hasOwnProperty("__ispc")  && response.__ispc.hasOwnProperty("message")) {
            
            	setTimeout(function(){ alert("ISPC Message:\n" + translate(response.__ispc.message).replace(/<br\s*\/?>/mg,"\n")); }, 200);
            
            } else if (response.hasOwnProperty("messages")) {
	        	
            	setTimeout(function(){ alert("elVi Message:\n" + translate(response.messages[0]).replace(/<br\s*\/?>/mg,"\n")); }, 200);
	        }
        	
        	if (_debug) {
        		$('textarea#debug_elvi').val(JSON.stringify(response, undefined, 4));
        	}
        	
        	
        }, 
        error : function (jqXHR, textStatus, errorThrown)
        {
        	if (_debug) {
        		$('textarea#debug_elvi').val(JSON.stringify(jqXHR, undefined, 4) + "\ntextStatus: " + textStatus + "\nerrorThrown: " + errorThrown);
        	}
        }
        
	});
	
	return false;
}


function as_new_window(url) {

    setTimeout(function(){ 
    	window.open(url, '_blank');
    	alert(translate("Popup must be enabled for this to work. Goto Settings->Safari->Block Pop-ups and allow."));
    }, 200);
}


function as_iframe(url) {
	$('<iframe>', {
        src: url,
        id:  'elvi_user_as_iframe',
        frameborder: 1,
        scrolling: 'yes',
        }
    )
    .appendTo('#elvi_user_placeholder');
    
}


