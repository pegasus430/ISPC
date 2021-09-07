;
/**
 * placeholderPatientIconsSystem.js
 * @date 01.11.2018
 * @author @cla
 * 
 * context $("#placeholder\\.patient\\.icons\\.system")
 * events binded here, idealy should only need to be performed in this context
 * .. please allways set the context so you don't have selector collisions
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

jQuery(document).ready(function ($) {

	   $('div#status_icon', $("#placeholder\\.patient\\.icons\\.system")).click(function() {
		   
			if ($('div#sys_icon-6', $("#placeholder\\.patient\\.icons\\.system")).hasClass('traffic_light')) {
								
				//single icon
				$('.traffic_light', $("#placeholder\\.patient\\.icons\\.system"))
				.find(' > img').toggle()
				.end()
				.find('div.btnClose').toggle()
				;
				
				//multiple icons
				$('#other_lights', $("#placeholder\\.patient\\.icons\\.system")).toggle();
				
			} else {
				var _box = $('.patient_icon_cell', this).data('details_box') || '_missingdetails';
				
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
		        	
			}	
		});

		$('div#other_lights .patient_icon_cell', $("#placeholder\\.patient\\.icons\\.system")).live('click', function() {
			var initial_status = $('#current_traffic_status', $("#placeholder\\.patient\\.icons\\.system")).val();
			var selected_status = $(this).find('img').attr('rel');
			if (initial_status != selected_status) {
				save_status(selected_status, $(this));
			}
			$('#current_traffic_status', $("#placeholder\\.patient\\.icons\\.system")).val(selected_status);
		});

		function save_status(new_status, replacement)
		{
			if (new_status) {
				var stat = new Array();

				stat[1] = "normal, keine Krise";
				stat[2] = "Achtung, instabil";
				stat[3] = "Krise";
				jConfirm('Sind Sie sicher, dass Sie den Status des Patienten auf "' + stat[new_status] + '" ändern wollen?', '', function(r) {
					if (new_status && r)
					{
						ajaxCallserver({url: appbase + 'patient/changetraffic?status_id=' + new_status + '&patienttrid='+window.idpd});
						$('div.traffic_light img', $("#placeholder\\.patient\\.icons\\.system")).replaceWith($(replacement).html());
						$('div.traffic_light img', $("#placeholder\\.patient\\.icons\\.system")).show();
					}
				});
			}
		}

});