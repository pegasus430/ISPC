;
/**
 * 49.js = Vital signs icon
 * @date 01.04.2019
 * @author @cla
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


jQuery(document).ready(function ($) {
	
	//add fn for before and after icon click
	if (typeof window.placeholder_new_icons_Click_CustomFn === 'undefined') {
		placeholder_new_icons_Click_CustomFn =  {};
	}
	
	var ba_49 = {
			
			"49_before" : function(_params) {
				var _highcart_isLoaded = $('#content_sys_icon-49').data("highcart_isLoaded") || false;
				
				if ( ! _highcart_isLoaded) {
					refresh_weight_chart_block();
					$('#content_sys_icon-49').data("highcart_isLoaded", true);
				}
				
				$('#content_sys_icon-49').css({'visibility': 'visible',"height":"40px"});
				$('#content_sys_icon-49 > #weight_chart_block').show();
				if ($('#content_sys_icon-49 #weight_container').length) {
					$('#content_sys_icon-49 svg').attr('visibility', 'visible');
					$('#content_sys_icon-49').css({"height":"280px"});
				}
			},
			
			"49_after" : function(_params) {
			}
			
	};
	
	
	$.extend(placeholder_new_icons_Click_CustomFn , ba_49);
	
});




function vital_signs_modal() {
	
	$('<div>', {
		html : '<p align="center">' + translate('loading please wait....') + '<br><img src="images/pageloading.gif"></p>',
		id : "vital_signs_modal"
		}
	)
	.appendTo('body')
	.dialog({
		autoOpen: true,
		modal: true,
		title: translate('vital_signs_modal_title'),
		width: '450',
		height: '520',
		resizable: false,
		draggable: true,
		
		buttons : [
           {
        	   text : translate('save'),
        	   click : function() {
        		   $.ajax({
        			   type: 'POST',
        			   url: 'patientform/vitalsigns?id=' + window.idpd,
					   data: $('#vital_signs_icon_form').serialize(),
					   success:function(data){
								$('#vital_signs_modal').dialog("close");
								$('#vital_signs_modal').unblock();
								$('.ui-dialog-buttonpane button:first').removeAttr('disabled');

								var action_name = 'patientcourse';

								if(action_name == 'patientcourse') {
									window.location.reload(true);
								}
							}
						});
				}
           },
           {
        	   text : translate('cancel'),
        	   click : function() {
        		   $(this).dialog("close");
			}}
		],
		open: function(ui) {
			$('#vital_signs_modal').load(appbase + "ajax/loadvitalsignsform?id=" + window.idpd,
					{}, 
					function(){
						var _that = this;
						$.map($('input[type="text"]', _that), function( val, i ) {
							if ($(val).val() == '') {	
								$(val).attr('pattern', '[0-9]*');
							}
						});
					}
			);
		},
		
		close: function(event, ui) {
            $(this).remove();
        } ,
	});

}




function refresh_weight_chart_block() {
	
	$('#weight_chart_block', $("#placeholder\\.patient\\.icons\\.new")).load(
			appbase + 'ajax/loadweightchart?id=' + window.idpd + '&use_icon_settings=1',
			{},	
			function() {
				
				$('#content_sys_icon-49', $("#placeholder\\.patient\\.icons\\.new")).css({'visibility': 'visible',"height":"40px"});
				$('#content_sys_icon-49 > #weight_chart_block', $("#placeholder\\.patient\\.icons\\.new")).show();
				if ($('#content_sys_icon-49 #weight_container').length) {
					$('#content_sys_icon-49 svg').attr('visibility', 'visible');
					$('#content_sys_icon-49').css({"height":"280px"});
				}

				$(".vital_signs_icon_button", $("#placeholder\\.patient\\.icons\\.new")).on('click', function(){
					vital_signs_modal();
				}); 
				
			}
	);
}