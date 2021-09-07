 //test for "beforeunload" support (not supposrted on iOs)
    (function ($, global) {
	var field = 'beforeunloadSupported';
	if(global.localStorage &&
		global.localStorage.getItem &&
		global.localStorage.setItem &&
		!global.localStorage.getItem(field)) {
	    $(window).on('beforeunload', function () {
		global.localStorage.setItem(field, 'yes');
	    });
	    $(window).on('unload', function () {
		// If unload fires, and beforeunload hasn't set the field,
		// then beforeunload didn't fire and is therefore not
		// supported (cough * iPad * cough)
		if(!global.localStorage.getItem(field)) {
		    global.localStorage.setItem(field, 'no');
		}
	    });
	}
	global.isBeforeunloadSupported = function () {
	    if(global.localStorage &&
		    global.localStorage.getItem &&
		    global.localStorage.getItem(field) &&
		    global.localStorage.getItem(field) == "yes") {
		return true;
	    } else {
		return false;
	    }
	}
    })(jQuery, window);

    var beforeunloadCalled = false; //this works on non iOs devices

    $(window).on('beforeunload', function () {
	beforeunloadCalled = true;
    });

    (function ($) {
	var checkstarted; //global js var to avoid multiple requests

	$.fn.userSession = function (options) {

	    var defaults = {
		frequency: 120000, //when to run
		nosession_freq: 10000, //run checks faster when the session has expired
		click_reset: true, //if clicking elements refreshes session
		alive_url: appbase + 'user/refreshsession',
		check_url: appbase + 'usersessions/checknew',
		redirect_url: appbase + 'index/logout',
		login_url: appbase,
		logout_url: appbase + 'index/logout',
		warning_text: translate('inactivemsg'),
		warning_title: translate('inactivewintitle'),
		expired_title: translate('expiredwintitle'),
		expired_text: translate('expiredmsg'),
		conn_title: translate('connwintitle'),
		conn_text: translate('connmsg'),
		client_changed_title: translate('client_changed_title'),
		client_changed_text: translate('client_changed_text') ,
		client_changed_close: translate('client_changed_close') ,
		client_changed : false
	    };

	    //##############################
	    //## Private Variables
	    //##############################
	    var opts = $.extend(defaults, options);
	    var liveTimeout, confTimeout, sessionTimeout;
	    var freq = opts.frequency;

	    var checkstart = setInterval(firecheck, freq); //set check interval

	    function firecheck() {
		//console.log(freq);
		clearInterval(checkstart); //clear the interval

		checksession('wait'); //sesssion check might change the interval

		checkstart = setInterval(firecheck, freq); //set the interval again 
	    }

	    function enablebuttons() { //re-enable all submit buttons and remove classes
		$("input[type='submit'], input[type='button']").attr('disabled', false);
		$('input#submitform').removeClass('loading_button');
		$('input#pdfexport').removeClass('loading_button');
	    }

	    function cookies_delete() {
			var cookies = document.cookie.split(";");
	
			for(var i = 0; i < cookies.length; i++) {
			    var cookie = cookies[i];
			    var eqPos = cookie.indexOf("=");
			    var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
			    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}
	    }

	    //window.idcidpd holds the cleintid from the pageload
	    function get_idcidpd() {
	    	var arr;	    	
	    	if (typeof(window.idcidpd) !== 'undefined' ) {
		    	arr = {"idcidpd": window.idcidpd};
		    } else {
		    	arr = {};
		    }
	    	return arr;
	    }
	    
	    var checksession = function (behaviour, callback) {
	    	

	    	
	    	var async =  true;
			if (behaviour == 'abort_syncronus') {
				behaviour = 'abort';
				async = false;
			}
			
		if(!$.isFunction(callback)) {
		    callback = function () {
			//justfallback to keep the code tidy
		    }
		}

		if(behaviour == 'abort') { //abort all previous checks, this is more important
		    if(checkstarted && checkstarted.readyState != 4) {
			checkstarted.abort();
		    }
		}

		if(behaviour == 'wait' && checkstarted && checkstarted.readyState != 4) { //wait for other checks to finish
		    return;
		}

		checkstarted = $.ajax({
		    dataType: "json",
		    url: opts.check_url,
		    cache: false,
		    timeout: (600 * 1000), //10 minutes is more than enough
		    "async" : async,
		    data : get_idcidpd()
		    
		})
			.done(function (check) {
			    if(check && check.result) {
				result = check.result;
			    } else {
				result = 'POTATO';
			    }

		    if(result == 'ACTIVE') {
			freq = opts.frequency; //set the frequency to "normal"

			//close all the modals
			$('#session_expired').dialog('close');
			$('#warning_expire').dialog('close');
			$('#connection_lost').dialog('close');

				//check if client has changed 
				if( typeof(check.client_changed) !== 'undefined' && check.client_changed =='1') {
					if( typeof(check.client_changed_text) !== 'undefined' ){
						opts.client_changed_text =  check.client_changed_text;
					}
					callback(false);
					logout("CLIENT_CHANGED");	
					opts.client_changed = true;
					return;
				} else if( opts.client_changed ) {
					//auto-hide the dialog if client changed back
					/*
					opts.client_changed = false;
					$('#usersession_client_changed').dialog('close');
					enablebuttons();
					*/
				}
			
			callback(true);
		    } else {
			//console.log('Response unreadable');
			callback(false);
			logout(result);
		    }
		})
		.fail(function (xhr, textStatus, errorThrown) {
		    if(isBeforeunloadSupported()) { //we have window.beforeunload support
			if(xhr.statusText != 'abort' && !beforeunloadCalled) { //request was not aborted and user didn't navigate away
			    if(textStatus == 'parsererror') { //jQuery triggers fail on JSON parsing error
				result = 'POTATO';
			    } else {
				result = 'FAIL';
			    }
			    callback(false);
			    logout(result);
			}
		    } else {
			if(xhr.statusText != 'abort') {
			    setTimeout(function () { //timeout workaround for iOS devices that don't support window.beforeunload
				if(textStatus == 'parsererror') { //jQuery triggers fail on JSON parsing error
				    result = 'POTATO';
				} else {
				    result = 'FAIL';
				}
				callback(false);
				logout(result);
			    }, 1000);
			}
		    }
		});
	    };

	    this.checksession = checksession; //make this callable directly

	    var logout = function (result)
	    {
		freq = opts.nosession_freq; // set the frequency for no session
		$('#session_expired').dialog({
		    autoOpen: false,
		    buttons: {
			"Wieder einloggen": function () {
			    //open window for re-log
			    window.open(opts.logout_url, '_blank');
			},
			"Abmelden": function () {
			    window.location.href = opts.logout_url;
			    $(this).dialog("close");
			}

		    },
		    closeOnEscape: false,
		    open: function (event, ui) {
			$(this).html(opts.expired_text);
			$(".ui-dialog-titlebar-close", ui.dialog || ui).hide();
		    },
		    close: function (event, ui) {

			enablebuttons();

			if(event.originalEvent && $(event.originalEvent.target).closest(".ui-dialog-titlebar-close").length) {
			    stay_logged_in();
			}
		    },
		    modal: true,
		    width: 500,
		    resizable: false,
		    title: opts.expired_title
		});

		$('#warning_expire').dialog({
		    autoOpen: false,
		    closeOnEscape: false,
		    open: function () {
			$(this).html(opts.warning_text);
		    },
		    close: function (event, ui) {

			enablebuttons();

			if(event.originalEvent && $(event.originalEvent.target).closest(".ui-dialog-titlebar-close").length) {
			    stay_logged_in();
			}
		    },
		    buttons: {
			"Angemeldet bleiben": function () {
			    stay_logged_in();
			    $(this).dialog("close");
			}

		    },
		    modal: true,
		    title: opts.warning_title
		});

		$('#connection_lost').dialog({
		    autoOpen: false,
		    buttons: {
			"Wieder einloggen": function () {
			    //open window for re-log
			    window.open(opts.logout_url, '_blank');
			},
			"Popup schließen": function () {
			    $(this).dialog("close");
			}
		    },
		    closeOnEscape: false,
		    open: function () {
			$(this).html(opts.conn_text);
		    },
		    close: function (event, ui) {
			enablebuttons();
		    },
		    modal: true,
		    width: 600,
		    resizable: false,
		    title: opts.conn_title
		});
		
		if ( ! $("#usersession_client_changed").hasClass('ui-dialog-content')) {
			$('#usersession_client_changed').dialog({
				dialogClass: "usersession_client_changed",
			    autoOpen: false,
			    closeOnEscape: false,
			    open: function () {
			    	$(".usersession_client_changed .ui-dialog-titlebar-close").hide();
			    	$(this).html(opts.client_changed_text);
			    },
			    beforeClose: function () {
			        //return false;
			    },
			    close: function (event, ui) {
					enablebuttons();
			    },
	
			    buttons: [{
			        text: opts.client_changed_close,
			        click: function () {
			        	 $(this).dialog("close");
			        	 enablebuttons();
			        	 opts.client_changed = false;
			        },
	
			    }],
			        
			    modal: true,
			    title: opts.client_changed_title,
			    minWidth: 550,
			    minHeight: 150,
			});
		
		}
		if (result == 'CLIENT_CHANGED') {
			freq = opts.frequency;
			if($('#usersession_client_changed').dialog('isOpen') !== true) {
				
				$('#usersession_client_changed').html(opts.client_changed_text);
				$('#usersession_client_changed').parent().css({position: "fixed"}).end().dialog('open').dialog("option", "position", "center");
			}
		} else if(result == 'BEFOREINACTIVE') { //we have warning now
		    if($('#warning_expire').dialog('isOpen') !== true) {
			$('#session_expired').dialog('close');
			$('#connection_lost').dialog('close');
			$('#usersession_client_changed').dialog('close');
			$('#warning_expire').parent().css({position: "fixed"}).end().dialog('open');
		    }
		} else if(result == 'INACTIVE' || result != 'FAIL') { //if session is inactive or the server response isn't what we expect (e.g. "potato")
		    if($('#session_expired').dialog('isOpen') !== true) {

			//let's log the user out now
			session_close();

			$('#warning_expire').dialog('close');
			$('#connection_lost').dialog('close');
			$('#usersession_client_changed').dialog('close');
			$('#session_expired').parent().css({position: "fixed"}).end().dialog('open');
		    }
		} else {
		    //no known result, probably connection failure
		    if($('#connection_lost').dialog('isOpen') !== true) {
			$('#warning_expire').dialog('close');
			$('#session_expired').dialog('close');
			$('#usersession_client_changed').dialog('close');
			$('#connection_lost').parent().css({position: "fixed"}).end().dialog('open');
		    }
		}
	    };

	    var stay_logged_in = function (el)
	    {
		confTimeout = '';
		if(opts.alive_url)
		{
		    $.get(opts.alive_url);
		}
	    };

	    var session_close = function (el)
	    {
		//cookies_delete();
		if(opts.logout_url)
		{
		    $.get(opts.logout_url);
		}
	    };

	    /*return {
	     checksession: function(behaviour) {
	     return checksession(behaviour);
	     }
	     }*/

	    return this.each(function () {
		obj = $(this);
		if(opts.click_reset)
		{
		    if($('#connection_lost').dialog('isOpen') !== true && $('#warning_expire').dialog('isOpen') !== true && $('#session_expired').dialog('isOpen') !== true) {
			//$(document).live('click', stay_logged_in);
		    }
		}
	    });
	};
    })(jQuery);
