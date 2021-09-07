// JavaScript Document
/** With Optional Overrides **/
$(document).ready(function() {
	/*
	 //alert($(window).width());

	 if($.browser.msie)
	 {
	 $("#topmenu").css({'margin-top':'45px'});
	 }else{

	 $("#topmenu").css({'margin-top':'35px'});
	 }



	 $("#Wrapper").css({'width':$(window).width()-20});
	 $("#MainContent").css({'width':$(window).width()-212-20});

	 $(".header").css({'width':$(window).width()-20});
	 $(".TopMenu").each(function(){
	 $(this).css({'width':$(window).width()-212-20});
	 });


	 */
	if($('#patientsearch_second').length){
		//ISPC-2561 Carmen 16.03.2020
		var search_url= appbase + 'ajax/patientsearch?q=';
		if(connected_patient_search == 1){
			var search_url=  appbase + 'ajax/patientconnectedsearch?q='; 
		}
		// --
		$('#patientsearch_second').liveSearch({
			//url: appbase + 'ajax/patientsearch?q=', //ISPC-2561 Carmen 16.03.2020
			url: search_url,
			id: 'livesearch_global_patient',
			/*aditionalWidth: '400',*/
			noResultsDelay: '1200',
			customStart: true,
			DOMelement: 'div #ap-logo_new',
			typeDelay: '1200'
		});
	}

	var SelfLocation = window.location.href.split('?');
	switch (SelfLocation[1]) {
		case "justify_right":
			jQuery(".megamenu").megamenu({
				'justify' : 'right'
			});
			break;
			
		case "justify_left":
		default:
			jQuery(".megamenu").megamenu();
	}



	$("#recording_date").mask("99.99.9999");
	$("#birthd").mask("99.99.9999");
	$("#cnt_birthd").mask("99.99.9999");
	$("#cardentry_date").mask("99.99.9999");
	$("#date_of_birth").mask("99.99.9999");
	$("#card_valid_till").mask("99.99.9999");
	$("#admission_date").mask("99.99.9999");
	$("#valid_from").mask("99.99.9999");
	$("#valid_till").mask("99.99.9999");
	$("#entry_date").mask("99.99.9999");
	$("#discharge_date").mask("99.99.9999");
	$("#death_date").mask("99.99.9999");


	$("form").submit(function () {
		disable();
	});

	var OSName = "Unknown OS";
	if (navigator.appVersion.indexOf("Win") != -1)
		OSName = "Windows";
	if (navigator.appVersion.indexOf("Mac") != -1)
		OSName = "MacOS";
	if (navigator.appVersion.indexOf("X11") != -1)
		OSName = "UNIX";
	if (navigator.appVersion.indexOf("Linux") != -1)
		OSName = "Linux";

	if (OSName == 'MacOS') {

		if ($.browser.safari) {
			/*
			 $(".LeftList01 input").css({'height':'27px' });
			 $(".RightList01 textarea").css({'padding-top':'5px','border-left':'solid 1px #e4e7e8','margin-left':'6px'});
			 */
		} else {
			/*alert("mozila");
			$(".LeftList01 input").css({'height':'24px'});
			$(".RightList01 textarea").css({'height':'33px','padding-top':'7px'});	*/
		}


	}

	shortcut.add("Alt+F", function() {
		document.getElementById('patientsearch').focus();
	});
	shortcut.add("Alt+S", function() {
		$("form").submit();
	});
/*
	if (jQuery.trim($('.err').html()) == '') {
		$('.err').addClass('ErrorDivHide');
	}
	*/
	$('.err').each(function(){
		if (jQuery.trim($(this).html()) == '') {
			$(this).addClass('ErrorDivHide');
		}
	});
	
	
	$('#doctdropdown').hide();

	$.jGrowl.defaults.closer = false;

	if (!$.browser.safari) {
		$.jGrowl.defaults.animateOpen = {
			width : 'show'
		};
		$.jGrowl.defaults.animateClose = {
			width : 'hide'
		};
	}

	initMenu();

	if (notifymessage.length > 0) {
		$.jGrowl(notifymessage, {
			sticky : true
		});
	}

	if (sysnews.length > 0) {
		eval(sysnews);

	}

	if($.fn.userSession) { //doesn't work in IE7 now
		//let's check some sessions
		$.fn.userSession();
		
		//fire session check again on forms submit, let's try to fire it when the user clicks the submit button
	
//		$(":submit, :button, #btnsubmit").not("#submitform, #apply, #create_invoice, #create_pdf, #invoice_save, #invoice_save_draft, #invoice_draft_complete,#generatepdf").click(function(event){
		$("#btnsubmit, .btnsubmit_usersessions").click(function(event){
//			console.log('merge');
			event.preventDefault(); //stop the click
			element = $(this);
			$.fn.userSession().checksession('abort', function(result) {
				if(result === false) {
					event.stopPropagation(); //stop going forward
					event.stopImmediatePropagation(); //stop going forward
				} else {
					element.unbind('click').click(); //resume
				}
			});
			return false;
		});
	}

	//When page loads...
	$(".tab_content_x").hide(); //Hide all content
	$("ul.tabs li:first").addClass("active").show(); //Activate first tab
	$(".tab_content_x:first").show(); //Show first tab content

	//On Click Event
	$("ul.tabs li").bind('click', function() {
		$("ul.tabs li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content_x").hide(); //Hide all tab content

		var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active ID content

		//some tabs have links to open (this is preventing the tabs to load data in addressbook)
		//fixed for symptomatics tabs to stop loading #tab2 url
		if(!$(this).parent().hasClass('addressb')){
			return false;
		}
	});
	
	// ISPC-2138 1c-14.02.2018
    $('.left_menu_datatable').on( 'page.dt', function () {
        $('html, body').animate({
            scrollTop: 0
        }, 300);
  	});
    
	
	
	//hide modal markup from layout
	$('#btm_actions_modal').dialog({
	    autoOpen: false,
	    modal: true
	});

	
	setTimeout(function(){
		//scroll left menu: 2017.11.29
	    var _headerHeight = 80;
	    var _footerHeight = 50;
	    if ($('.newheader > .logo > img').length) {
	    	//notice we don't wait for the image to load..
//	    	 var _logo_img_height = $('.newheader > .logo > img').height() || 76;
	    	 var _logo_img_height = $('.newheader > .logo > img').height() || 76;    	 
	    	 _headerHeight = _logo_img_height; //+ 6 = if margin-top
	    	 _headerHeight = _headerHeight < 42 ? 42 : _headerHeight;
	    	 
	    	 // TODO-2689 Ancuta 29.11.2019
	    	 if ($('.maintenance_alert_info').length) {
	    		 var _banner_block_height  = $('.maintenance_alert_info').height()+30 || 90 
	    		 _headerHeight  = _headerHeight + _banner_block_height;
	    	 }
	    	 
	    }
	    
		if ($('#menu').length) 
		{
		    $(window)
		    .scroll(function() {
		        clearTimeout($.data(this, 'scrollTimerMenu'));
		        $.data(this, 'scrollTimerMenu', setTimeout(function() {
		        	$('#menu').LeftDivMenuScroller(_headerHeight, _footerHeight);
		        }, 150));
		    })
		    .resize(function(){
		    	$('#menu').LeftDivMenuScroller(_headerHeight, _footerHeight);
		    })
		    ;
	    }
	}, 50)
	
	
	
});


(function ( $ ) {
//scroll left menu: 2017.11.29
$.fn.LeftDivMenuScroller = function(_headerHeight, _footerHeight) {
	
		var header_top = _headerHeight || 80, footer_h = _footerHeight || 50;
		
	    var elementTop = $(this).offset().top;
	    var elementBottom = elementTop + $(this).outerHeight();
	
	    var viewportTop = $(window).scrollTop();
	    var viewportBottom = viewportTop + $(window).height();
	
	    var viewportOffset = (elementTop - viewportTop);
	    
	    if (viewportOffset > 0) {
	    	//stick up
	    	var topOff = elementTop - viewportOffset;
	    	if (header_top > topOff) topOff = header_top;
	    	$(this).clearQueue();
	    	$(this).css('position', 'absolute').animate({top: topOff});
		
	    }
	    else if ( (elementBottom > viewportTop && elementTop < viewportBottom) && ($(this).outerHeight() < $(window).height())) {
	    	//TODO: not finished ifs for small menu to stay on top
	    	var topOff = header_top ;
	    	$(this).clearQueue();
	    	$(this).css('position', 'absolute').animate({top:topOff});
	    }
	    else if ( ! (elementBottom > viewportTop && elementTop < viewportBottom)) {
	    	//move up/down if outside of viewPort
	    	if ($(this).outerHeight() < $(window).height()) {
	    		var topOff = elementTop - viewportOffset;
	    	} else {
	    		var topOff = viewportBottom - $(this).outerHeight() - footer_h;
	    	}
	    	if (header_top > topOff) topOff = header_top ;
	    	
	    	$(this).clearQueue();
	    	$(this).css('position', 'absolute').animate({top:topOff});
	    	
	    }
	    return;
	};
}( jQuery ));

/**
 * cla@orw
 * if text is missing in translation returns same
 * replaces %d+% with the arguments[d+]
 */
function translate(keyword) {
	var _args = arguments;
	var _keyword = typeof(jsTranslate) != 'undefined' && typeof jsTranslate[keyword] != 'undefined' ? jsTranslate[keyword] : keyword;
	if (typeof _keyword === 'string') {
	    return _keyword.replace(/\%(\d+)\%/g, function(match, number) {
	    	return typeof _args[number] != 'undefined' ? _args[number] : match;
	    });
	} else {
		return _keyword;
	}
}

function updateContactNumber(epid, chk) {
	if (chk.checked == true) {
		ajaxCallserver({
			url : appbase + 'patient/updatecontactnumber?id=' + epid + '&rcn='
					+ encodeURIComponent(chk.value)
		});
	}
}

function checkdischargednew(formname){

	
	$.fn.userSession().checksession('abort', function(result) {
		if(result === true) {
			
			if (formname == "frmcourse"){
				$('form#' + formname + ' input[type=button]').attr('disabled', true);
			} else {
				var myTimer = setTimeout(function () {$('form#'+formname+' input[type=button]').attr('disabled', true);}, 150);
			}
			setTimeout(function () {$('form#'+formname+' input[type=button]').attr('disabled', false);}, 22000);
			
			if(isdischarged==1)
			{
				jConfirm(translate('dischargealert'), '', function(r) {
					if(r)
					{
						var submitstr = "document."+formname+".submit()";
						eval(submitstr);
					} else {
						setTimeout(function () {$('form#'+formname+' input[type=button]').attr('disabled', false);}, 10);
					}
				});
				return false;
			}
			else
			{
				//ISPC - 2129
		    	var isemergencyplan = false;
		    	$('.tag-editor-tag'). each(function(){
              	  if($(this).text() == 'Notfallplan')
              	  {
              		isemergencyplan = true;
              		var filesuploaded = $('input[name="qquuid[]"]').length;
              		
              		if(filesuploaded == 1)
              		{
              			jConfirm(translate('your are about to upload an emergency plan. shall this file be taken as latest version?'), '', function (r) {
        				    if(r)
        				    {
        				    	$('#active_version').val('1');
        						var submitstr = "document." + formname + ".submit()";
        						eval(submitstr);
        				    } else {
        				    	var submitstr = "document." + formname + ".submit()";
        						eval(submitstr);
        						setTimeout(function () { $('form#' + formname + ' input[type=button]').attr('disabled', false); }, 10);
        				    }
        				});
              		}
              		else
              		{
              			clearTimeout(myTimer);
              			jAlert(translate('With tag Notfallplan you can upload only one file at a time!'), 'Alert');
              			
              		}
              		return false;
              	  }
                });
		    	if(!isemergencyplan)
		    	{
		    		var submitstr = "document." + formname + ".submit()";
					eval(submitstr);
		    	}
			}
			
			
		}
	});
}

// check syncronus if clientid has changed (same as on callback then..) 
function checkclientchanged(formid)
{	
	var check_result = true;
	$('body').addClass('body-overlay'); 
	$.fn.userSession().checksession('abort_syncronus', function(result) {
		if(result === false) {
			$('body').removeClass('body-overlay');
			check_result = false;
            return false;
		}
	});
	if(check_result && typeof formid !== 'undefined') {
		setTimeout(function () { $('form#' + formid + ' :button').attr('disabled', true);}, 150);
		setTimeout(function () { $('form#' + formid + ' :button').attr('disabled', false);}, 10000);
	}
	$('body').removeClass('body-overlay');
	return check_result;
	
}
//add trim functionality to the string. IE<9 Safari<5 (ECMAScript 3)
if (!String.prototype.trim) {
	  (function() {
	    // Make sure we trim BOM and NBSP
	    var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
	    String.prototype.trim = function() {
	      return this.replace(rtrim, '');
	    };
	  })();
	}
if (!String.prototype.ltrim) {
	  (function() {
	    // Make sure we trim BOM and NBSP
	    var rtrim = /^[\s\uFEFF\xA0]+/g;
	    String.prototype.ltrim = function() {
	      return this.replace(rtrim, '');
	    };
	  })();
	}
if (!String.prototype.rtrim) {
	  (function() {
	    // Make sure we trim BOM and NBSP
	    var rtrim = /[\s\uFEFF\xA0]+$/g;
	    String.prototype.rtrim = function() {
	      return this.replace(rtrim, '');
	    };
	  })();
	}
//add format=sprintf functionality to the string. 
if (!String.prototype.format) {
	  (function() {
	    String.prototype.format = function() {
		    var args = arguments;
		    return this.replace(/\%(\d+)\%/g, function(match, number) { 
		      return typeof args[number] != 'undefined'
		        ? args[number]
		        : match
		      ;
		    });
		  };
	  })();
	}

//escapeValue added to be used for <input/>
if (!String.prototype.escapeValue) {
	  (function() {
		  var map, unsafe_chars;
		  map = {
		          "<": "&lt;",
		          ">": "&gt;",
		          '"': "&quot;",
		          "'": "&#x27;",
		          "`": "&#x60;"
		        };
	    String.prototype.escapeValue = function() {
	        var text = this;
	        if (typeof(text) == "undefined" || text == null || text === false) {
	          return "";
	        }
	        if (!/[\&\<\>\"\'\`]/.test(text)) {
	          return text;
	        }
	        
	        unsafe_chars = /&(?!\w+;)|[\<\>\"\'\`]/g;
	        return text.replace(unsafe_chars, function(chr) {
	          return map[chr] || "&amp;";
	        });
	      };
	  })();
	}


/*
 * delayKeyup
 * http://code.azerti.net/javascript/jquery/delaykeyup.htm
 * Inspired by CMS in this post : http://stackoverflow.com/questions/1909441/jquery-keyup-delay
 * Written by Gaten
 * Exemple : $("#input").delayKeyup(function(){ alert("5 secondes passed from the last event keyup."); }, 5000);
 */
(function ($) {
    $.fn.delayKeyup = function(callback, ms){
        var timer = 0;
        $(this).keyup(function(){                   
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        });
        return $(this);
    };
})(jQuery);