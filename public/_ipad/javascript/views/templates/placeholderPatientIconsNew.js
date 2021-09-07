;
/**
 * placeholderPatientIconsNew.js
 * @date 01.11.2018
 * @author @cla
 * 
 * context $("#placeholder\\.patient\\.icons\\.new")
 * events binded here, idealy should only need to be performed in this context
 * .. please allways set the context so you don't have selector collisions
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}



var placeholder_new_icons_Click_CustomFn = {};
/*
 * see vitalsigns = 49.js as example .. 4 usage
 */


jQuery(document).ready(function ($) {
	
	
	//patient new_icons click action will show the details box
    $('#new_icons', $("#placeholder\\.patient\\.icons\\.new")).on('click', 'div.patient_icon_cell', function(e){
        
        var _box = $(this).data('details_box') || '_missingdetails';
        
        /**
         * trigger a custom function for this specific icon details box..... 
         * all functions are named the same
         * and after call the toggle on the details
         */
        if (placeholder_new_icons_Click_CustomFn.hasOwnProperty(_box + "_before") && typeof placeholder_new_icons_Click_CustomFn[_box + "_before"] == 'function') {
    		var _params = {};
    		placeholder_new_icons_Click_CustomFn[_box + "_before"](_params);
        }
        
        if ($(".selector_new_icons_details", $("#placeholder\\.patient\\.icons\\.new")).find("#content_sys_icon-"+_box).is(":visible")) {
            //just hide
        	$(".selector_new_icons_details", $("#placeholder\\.patient\\.icons\\.new")).find("#content_sys_icon-"+_box).slideToggle();
        } else {
            //hideall and shown our own
            $(".selector_new_icons_details", $("#placeholder\\.patient\\.icons\\.new"))
            .find('.tab-content:visible').slideToggle('fast', 'swing', function(){
            	//oncomplete show the other one..
            }).end()
            .find("#content_sys_icon-"+_box).slideToggle()
            ;
        }
        ;
        
        
        /**
         * trigger a custom function for this specific icon details box after.....
         * this should be moved to animation onComplete callback
         * all functions are named the same
         * and after call the toggle on the details
         */
        if (placeholder_new_icons_Click_CustomFn.hasOwnProperty(_box + "_after") && typeof placeholder_new_icons_Click_CustomFn[_box + "_after"] == 'function') {
    		var _params = {};
    		placeholder_new_icons_Click_CustomFn[_box + "_after"](_params);
        }
    });
    
    
	
	
	
	//custom icons
	check_icons();
	
	
	
	$('#assign_custom_icon', $("#placeholder\\.patient\\.icons\\.new")).click(function() {
		check_icons();
		$('#available_custom_icons', $("#placeholder\\.patient\\.icons\\.new")).toggle();
	});
	

	//assign
	$('div#available_custom_icons div.available', $("#placeholder\\.patient\\.icons\\.new")).live('click', function() {
		var that = $(this);
		
		$(that).hide();

		//do assign code here! some ajax stuffs
		xhr = $.ajax({
			url: appbase + 'ajax/assignpatienticon?id=' + window.idpd + '&iconid=' + $(this).attr('rel'),
			success: function(response) {
				var response_obj = jQuery.parseJSON(response);
				if (response_obj['status'] == 'ok') {
					//clone that element and append it to the icon bar with id changed
					$(that).clone()
					.insertBefore('#assign_custom_icon')
					.attr('id', 'assigned_custom_icon-' + $(that).attr('rel'))
					;

					console.log(that);
					//remove old class and add new class for iconbar entity
					$('#assigned_custom_icon-' + $(that).attr('rel'))
					.removeClass('available')
					.addClass('custom_icon_assigned')
					.show();

					//hide cloned icon
					$(that).hide();
					
					check_icons();
				}
			}
		});
	});
	//(un)assign
	$('#confirm_unassign', $("#placeholder\\.patient\\.icons\\.new")).dialog({autoOpen: false});
	
	$('.custom_icon_assigned', $("#placeholder\\.patient\\.icons\\.new")).live('click', function() {
		
		var icon_id = parseInt($(this).attr('id').substr(('assigned_custom_icon-').length));


		$('<div>', {
				html : '<p>' + translate('confirm_unassign_icon_text') + '</p>'
			}
		)
		.appendTo('body')
		.dialog({
			autoOpen: true,
			modal: true,
			title: translate('unassign_icon'),
			    
			close: function(event, ui) {
	            $(this).remove();
	        } ,
			
			buttons: [
			          {
						text : translate('unassign_icon_btn'),
						click: function() {
							//do (un)assign code here! some ajax stuffs
							xhr = $.ajax({
								
								url: appbase + 'ajax/removepatienticon?id=' + window.idpd + '&iconid=' + icon_id,
								
								success: function(response) {
									var response_obj = jQuery.parseJSON(response);
		
									if (response_obj['status'] == 'ok') {
										//remove curent icon element
										$('#assigned_custom_icon-' + icon_id).remove();
		
										//unhide available icon
										$('#available_custom_icon-' + icon_id).show();
		
										check_icons();
									}
								}
							});
							$(this).dialog("close");
						}
			          },
			          {
			        	  text : translate('cancel'),
			        	  click: function() {
			        		  $(this).dialog("ajax/loadweightchartclose");
			        	  }
			          }
				]
		})
		
		;
		
	});
	
	
});




function check_icons() {
	
	var count_asigned = $('div.custom_icon_assigned', $("#placeholder\\.patient\\.icons\\.new")).length;
	var count_available = $('div.available', $("#placeholder\\.patient\\.icons\\.new")).length;

	if (count_asigned == count_available) {
		$('#available_custom_icons', $("#placeholder\\.patient\\.icons\\.new")).hide();
		$('#assign_custom_icon', $("#placeholder\\.patient\\.icons\\.new")).hide();
	} else {
		$('#assign_custom_icon', $("#placeholder\\.patient\\.icons\\.new")).show();
	}
}