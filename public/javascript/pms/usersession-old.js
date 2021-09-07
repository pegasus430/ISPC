(function($){

	$.fn.userSession = function(options) {
	
		var defaults = {
			frequency: 60000, //run checks every minute
			noconfirm: 60000, //1 Min
			sessionAlive: 600000, //10 Minutes
			//redirect_url: '/js_sandbox/',
			click_reset: true,
			alive_url: appbase + 'user/refreshsession',
			check_url: appbase + 'usersessions/check',
			redirect_url: appbase + 'Index/logout',
			logout_url: appbase + 'Index/logout'
		}
    
		//##############################
		//## Private Variables
		//##############################
		var opts = $.extend(defaults, options);
		var liveTimeout, confTimeout, sessionTimeout;
		var modal = "<div id='automatic_logout'><p>"+dialogMessage+"</p></div>";
		//##############################
		//## Private Functions
		//##############################
		
//		var start_liveTimeout = function()
//		{
//			clearTimeout(liveTimeout);
//			clearTimeout(confTimeout);
//			liveTimeout = setTimeout(logout, opts.inactivity);
//      
//			if(opts.sessionAlive)
//			{
//				clearTimeout(sessionTimeout);
//				sessionTimeout = setTimeout(keep_session, opts.sessionAlive);
//			}
//		}
		
		var checkstart = function() {
			setInterval(function() {
				checksession();
			}, opts.frequency); 
		}
		
		var checksession = function() {
			if(!confTimeout) { //if no confirmation dialog
				$.getJSON(opts.check_url, function(check){
					if(check.result == 'ACTIVE') {
						//for debugging purposes only
						//alert('Still active');
					} else {
						logout();
					}
				});
			} else {
				//alert('confdiag');
			}
		}
    
		var logout = function()
		{
      
			confTimeout = setTimeout(redirect, opts.noconfirm);
			var button = {};
			button[inactivebtn] =  function(){
				stay_logged_in();
				$(this).dialog('close');
			};
			
			//close if previous opened
			$('#automatic_logout').dialog('close');
			
			$(modal).dialog({
				buttons: button,
				closeOnEscape: false,
				close: function(event, ui) {
					//check if close is fired by "x", if so, refresh session
					  if ( event.originalEvent && $(event.originalEvent.target).closest(".ui-dialog-titlebar-close").length ) {
						  stay_logged_in();
					  }
				},
				modal: true,
				title: dialogTitle
			});
      
		}
    
		var redirect = function()
		{
//			//check if session refreshed in antoher window
//			$.getJSON(opts.check_url, function(check){
//				//close modal if it's open
//				$('#modal_pop').dialog('close');
//				if(check.result == 'ACTIVE') {
//					//refreshed elsewhere, do nothing
//				} else {
//					window.location.href = opts.redirect_url;
//				}
//			});
			
			
			$.ajax({
				type: "GET",
				url: opts.check_url,
				processData: true,
				data: {},
				dataType: "json",
				success: function(check) {
					//close modal if it's open
					$('#automatic_logout').dialog('close');
					if(check.result == 'ACTIVE') {
						//refreshed elsewhere, restart checks
						stay_logged_in();
					} else {
						window.location.href = opts.redirect_url;
					}
				},
				error: function() {
						//close modal if it's open
						$('#automatic_logout').dialog('close');
						window.location.href = opts.redirect_url;
				}
			});
			
		}
    
		var stay_logged_in = function(el)
		{
			confTimeout = '';
			if(opts.alive_url)
			{
				$.get(opts.alive_url);
			}
		}
    
		
		return this.each(function() {
			obj = $(this);
			checkstart();
			if(opts.click_reset)
			{
				if(!$(modal).dialog('isOpen')){
					$(document).live('click', stay_logged_in);
				}
			}
		});
	};
})(jQuery);
