/**
 * modalevents.js

 * #ISPC-2512PatientCharts
 * @date 15.04.2020
 * @author @Ancuta
 * ISPC-2517
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

(function($) {

	$(document).off('click',".ui-widget-overlay").on('click',".ui-widget-overlay", function(){
//		 $(".ui-dialog-content").dialog("close");
		 
//		$('#patient-main-add-modal').dialog("close");

	});
	
	
//	modalevents
	$.fn.modalevents = function(options) {

		var eventsDialog;

		var debugmode = false;

		var patid = window.idpd;

		var loginfo = function(_msg) {
			if (debugmode) {
				console.info(_msg);
			}
		}

		var defaults_post = {
//			post_url: window.appbase + datamatrixurl,
			id: window.idpd,
//			action: "datamatrix",
		};

		
		var defaults = {

			dialog_id : "#patient-main-add-modal",
			step: 1, // open on step
		
			btn_close: translate('eventsModal_lang')['btn_close'],
			btn_continue: translate('eventsModal_lang')['btn_continue'],

			step_in_progress_title: translate('eventsModal_lang')['step_in_progress_title'],
			step_in_progress_infotext: translate('eventsModal_lang')['step_in_progress_infotext'],

			step1_title: translate('eventsModal_lang')['step1_title'],
			step1_label: translate('eventsModal_lang')['step1_label'],
			step1_infotext: translate('eventsModal_lang')['step1_infotext'],

			step2_title: translate('eventsModal_lang')['step2_title'],
			step2_label: translate('eventsModal_lang')['step2_label'],
			step2_infotext: translate('eventsModal_lang')['step2_infotext'],

			general_error: translate('eventsModal_lang')['general error'],

		};
		//append custom options
		var _opts = $.extend(defaults, options);

		var create_dialog = function(_step) {

			eventsDialog = $(_opts.dialog_id).dialog({

				autoOpen: true,
				modal: true,
				maxWidth: 800,
				maxHeight: 800,
				width: 600,
				height: 800,
				
				create: function (event, ui) { // this is more reliable
					$("textarea", this).val("");
	
				},

				close: function(ev, ui) {
					//		            $(this).dialog("destroy");
					//		            $(this).remove();
					_opts.step = 1; //reset step
				},
				open: function() {
					$('.modal_loading_div', this).show();
					
					var url = appbase + 'patientevents/modal?id=' + idpd ;

					xhr = $.ajax({
						url : url,
						cache: false ,
						success : function(response) {
							$('#patient-main-add-modal').html(response);
						}
					});
					
					 jQuery('.ui-widget-overlay').on('click', function () {
						 $('#patient-main-add-modal').dialog('close');
			            });
 
				},

				buttons: [{
						text: _opts.btn_close,
						click: function() {
							$(this).dialog("close");
						}
					},
				]
			});
		};

		create_dialog(_opts.step); //this.start = create_dialog; //make this callable directly

 
		return;
	};
	
})(jQuery);

	$(document).ready(function() {
		
		$(document).off('click',".open_event_modal").on('click',".open_event_modal", function(){
			//$('#awake_sleeping_modal').data('recid', '25').dialog('open'); //to edit use data to send the id
			var event_name =  $(this).data('event_name');
			
			switch(event_name) {
			
			case 'vital_signs':
				$('#vital_signs_modal').data('vs_source', 'charts').data('recid', '').dialog('open');
				break;
			
			 case 'awake_sleep_status':
	  		     //$('#awake_sleeping_modal').data('recid', '25').dialog('open'); //to edit use data to send the id
				$('#awake_sleeping_modal').data('recid', '').dialog('open');
			  break;
			  
			 case 'organic_entries_exits':
				 //$('#organicentriesexits_modal').data('recid', '5').dialog('open');
				$('#organicentriesexits_modal').data('recid', '').dialog('open');
				 break;
				 
			 case 'custom_events':
					//$('#custom_event_modal').data('recid', '8').dialog('open');
					$('#custom_event_modal').data('recid', '').dialog('open');
				 break;
				 
				 
			 case 'positioning':
					//$('#positioning_modal').data('recid', '1').dialog('open');
					$('#positioning_modal').data('recid', '').dialog('open');
				 break;
				 
			 case 'suckoff':
					//$('#suckoff_modal').data('recid', '1').dialog('open');
					$('#suckoff_modal').data('recid', '').dialog('open');
				 break;
				 
			 case 'contact_form_main':
					window.location.href = 'patientform/contactform?id='+idpd;
				 break;
				 
			 case 'contact_form_items':
				 var form_type = $(this).data('event_type');
				 window.location.href = 'patientform/contactform?id='+idpd+'&ftid='+form_type;
				 break;
				 
			 case 'artificial_entries_exits':
				 	//$('#artificial_entries_exits_modal').data('recid', '').data('action', 'add').dialog('open');
				
					$('#artificial_entries_exits_main_modal').dialog('open');
				 break;
				 
			 case 'symptomatology':
				 	$('#symptomatology_modal').data('recid', '').dialog('open');
		
				 break;
			//ISPC-2516 Carmen 15.07.2020	 
			 case 'symptomatologyII':
				 	$('#symptomatologyII_modal').data('recid', '').dialog('open');
		
				 break;
				 
			 case 'medication_dosage_interaction':
				 	$('#medication_dosage_view_modal').dialog('open');
		

				 break; 
			 default:
			 // code to be executed if n is different from case 1 and 2
			}    
		});
		
		
		//ISPC-2516 Carmen 09.04.2020
		$('#awake_sleeping_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 620,
		   	height: 300,
		   	//title:  "Verhaltensbeobachtung",
		   	title:  "Wach-Schlaf-Rhythmus",
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   	//ISPC-2517
			   	if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
			   		$('#awake_save_go_back').show();
			   	}
			   	else
			   	{
			   		$('#awake_save_go_back').hide();
			   	}
			   	//--
		   	 jQuery('.ui-widget-overlay').on('click', function () {
				 //$('#awake_sleeping_modal').dialog('close');
	            });
		   	 
		   	 	$('.modal_loading_div', this).show();
		   		if($(this).data('recid'))
		   		{
		   			var url = 'patientevents/events?action=show_form&form=awakesleepingadd&recid='+$(this).data('recid');
		   			if($(this).parent().find('.delbutton').is(":hidden"))
					{
		   				$(this).parent().find('.delbutton').show();
					}
		   		}
		   		else
		   		{
		   			var url = 'patientevents/events?action=show_form&form=awakesleepingadd';
		   			$(this).parent().find('.delbutton').hide();
		   		}
		   			   		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#awake_sleeping_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
				//delete button
				{
					'class' : "delbutton leftButton",
					text : 'Eintrag löschen',
					click : function() {
					var recid = $(this).data('recid');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
					    if(r) {	
					    	var setdata = {
									 id: recid,
					    	};
					    	
							$.ajax({
								 type: 'POST',
								 url: 'patientevents/events?action=save_form&form=awakesleepingsave&subaction=delete&patid='+pid,
								 data: setdata,
								 success:function(data){
									 $('#awake_sleeping_modal').dialog("close");
									 if (typeof loadPage == 'function') { 
											loadPage(); 
										}
											/* $('#awake_sleeping_modal').unblock();
											$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
				
											var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
				
											if(action_name == 'patientcourse') {
												window.location.reload(true);
											} */
								 }
							 });
					    }
					});
						
					},
					
				},
		    	
			{
				click: function(){
					 
					var data = $('.create_form_block_awake_sleeping_status tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					 var setdata = {
							 id: data[0].id,
							 awake_sleep_status: data[1].awake_sleep_status,
							 status_date: data[2].status_date,
							 status_time: data[2].status_time,
					 };
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=awakesleepingsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#awake_sleeping_modal').dialog("close");
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
							}
							 
						 }
					 });					
				},
				text: translate('save')
			},
			
			//ISPC-2517 Carmen 05.06.2020
			{
				click: function(){
					 
					var data = $('.create_form_block_awake_sleeping_status tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					 var setdata = {
							 id: data[0].id,
							 awake_sleep_status: data[1].awake_sleep_status,
							 status_date: data[2].status_date,
							 status_time: data[2].status_time,
					 };
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=awakesleepingsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#awake_sleeping_modal').dialog("close");
							 if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
								 $('#patient-main-add-modal').dialog("close");
							 }
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage();
								setTimeout(function(){$('html, body').animate({ scrollTop: $('#awake_sleep_status_chart').offset().top}, 1000); }, 9000);
								//$('html, body').animate({ scrollTop: $('#awake_sleep_status_chart').offset().top}, 1000);
							} else {
								// go to charts Ancuta 05.06.2020
								window.location.href = appbase + 'charts/overview?id='+pid+'#ch_awake_sleep_status_chart';
							}
							 
						 }
					 });					
				},
				text: translate('save and go back to chart'),
				'id' : 'awake_save_go_back'
			},
			//--

			{
				click: function(){
			
					$('#awake_sleeping_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
	//--
		
		//ISPC-2522 Carmen 10.04.2020
		$('.positioning_chart_button').live('click', function() {
			//$('#positioning_modal').data('recid', '1').dialog('open');
			$('#positioning_modal').dialog('open');
		});
		
		$('#positioning_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 620,
		   	height: 350,
		   	title:  translate('positioning'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
			   		$('#positioning_save_go_back').show();
			   	}
		   		else
		   		{
		   			$('#positioning_save_go_back').hide();
		   		}
			   	 jQuery('.ui-widget-overlay').on('click', function () {
					 //$('#positioning_modal').dialog('close');
		            });
		   		
			   	$('.modal_loading_div', this).show();
		   		if($(this).data('recid'))
		   		{
		   			var url = 'patientevents/events?action=show_form&form=positioningadd&recid='+$(this).data('recid');
		   			if($(this).parent().find('.delbutton').is(":hidden"))
					{
		   				$(this).parent().find('.delbutton').show();
					}
		   		}
		   		else
		   		{
		   			var url = 'patientevents/events?action=show_form&form=positioningadd';
		   			$(this).parent().find('.delbutton').hide();
		   		}
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#positioning_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
				   		
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
				//delete button
				{
					'class' : "delbutton leftButton",
					text : 'Eintrag löschen',
					click : function() {
					var recid = $(this).data('recid');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
					    if(r) {	
					    	var setdata = {
									 id: recid,
					    	};
					    	
							$.ajax({
								 type: 'POST',
								 url: 'patientevents/events?action=save_form&form=positioningsave&subaction=delete&patid='+pid,
								 data: setdata,
								 success:function(data){
									 $('#positioning_modal').dialog("close");
									 if (typeof loadPage == 'function') { 
											loadPage(); 
										}
											/* $('#awake_sleeping_modal').unblock();
											$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
				
											var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
				
											if(action_name == 'patientcourse') {
												window.location.reload(true);
											} */
								 }
							 });
					    }
					});
						
					},
					
				},
		    	
			{
				click: function(){


					if($('#positioning_type option:selected').val() != '')
					{
					
					//ISPC-2662 Carmen 31.08.2020
					/*var data = $('.create_form_block_positioning tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
							
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();
				 	 
					 var setdata = {
							 id: data[0].id,
							 positioning_type: data[1].positioning_type,
							 positioning_additional_info: data[2].positioning_additional_info,
							 positioning_date: data[3].positioning_date,
							 positioning_time: data[3].positioning_time,
					 };*/
				
					var setdata = $("#positioning_modal form").serialize();
					//--
					
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=positioningsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#positioning_modal').dialog("close");
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
							}
						 }
					 });	
					}
					else
					{
						$('#positioning_type_error').show();
					}
				},
				text: translate('save')
			},
			
			//ISPC-2517 Carmen 05.06.2020
			{
				click: function(){


					if($('#positioning_type option:selected').val() != '')
					{
					
					
					var data = $('.create_form_block_positioning tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
							
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();
					 
					 var setdata = {
							 id: data[0].id,
							 positioning_type: data[1].positioning_type,
							 positioning_additional_info: data[2].positioning_additional_info,
							 positioning_date: data[3].positioning_date,
							 positioning_time: data[3].positioning_time,
					 };
					
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=positioningsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#positioning_modal').dialog("close");
							 if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
								 $('#patient-main-add-modal').dialog("close");
							 }
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage();
								 setTimeout(function(){  $('html, body').animate({ scrollTop: $('#positioning_chart').offset().top}, 1000); }, 9000);
							    // $('html, body').animate({ scrollTop: $('#positioning_chart').offset().top}, 1000);

							}else {
								// go to charts Ancuta 05.06.2020
								window.location.href = appbase + 'charts/overview?id='+pid+'#ch_positioning_chart';
							}
						 }
					 });	
					}
					else
					{
						$('#positioning_type_error').show();
					}
				},
				text: translate('save and go back to chart'),
				'id': 'positioning_save_go_back'
			},
			//--

			{
				click: function(){
			
					$('#positioning_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		

		$(document).on('change', '#positioning_type', function() {
			$('#positioning_type_error').hide();
		});

		
	//--
		//ISPC-2523 Carmen 13.04.2020
		$('.suckoff_chart_button').live('click', function() {
			//$('#suckoff_modal').data('recid', '1').dialog('open');
			$('#suckoff_modal').dialog('open');
		});
		
		$('#suckoff_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 620,
		   	height: 400,
		   	title:  translate('suckoff'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
		   			$('#suckoff_save_go_back').show();
		   		}
		   		else
		   		{
		   			$('#suckoff_save_go_back').hide();
		   		}
			   	 jQuery('.ui-widget-overlay').on('click', function () {
					 //$('#suckoff_modal').dialog('close');
		            });
			   	 
			   	$('.modal_loading_div', this).show();
		   		if($(this).data('recid'))
		   		{
		   			var url = 'patientevents/events?action=show_form&form=suckoffadd&recid='+$(this).data('recid');
		   			if($(this).parent().find('.delbutton').is(":hidden"))
					{
		   				$(this).parent().find('.delbutton').show();
					}
		   		}
		   		else
		   		{
		   			var url = 'patientevents/events?action=show_form&form=suckoffadd';
		   			$(this).parent().find('.delbutton').hide();
		   		}
		   		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#suckoff_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
				   		
				   		$('#suckoff_secretion').val($('#suckoff_secretion').val().replace(/\./g, ','));
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
				//delete button
				{
					'class' : "delbutton leftButton",
					text : 'Eintrag löschen',
					click : function() {
					var recid = $(this).data('recid');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
					    if(r) {	
					    	var setdata = {
									 id: recid,
					    	};
					    	
							$.ajax({
								 type: 'POST',
								 url: 'patientevents/events?action=save_form&form=suckoffsave&subaction=delete&patid='+pid,
								 data: setdata,
								 success:function(data){
									 $('#suckoff_modal').dialog("close");
									 if (typeof loadPage == 'function') { 
											loadPage(); 
										}
											/* $('#awake_sleeping_modal').unblock();
											$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
				
											var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
				
											if(action_name == 'patientcourse') {
												window.location.reload(true);
											} */
								 }
							 });
					    }
					});
						
					},
					
				},
				
			{
				click: function(){

					var data = $('.create_form_block_suckoff tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							//ISPC-2523 Lore 14.05.2020
							 else if(this.type == 'checkbox' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio' && this.type != 'checkbox')
							 {
								 formset[this.name] = $(this).val();
							 }
							 
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();
					
					
					//ISPC-2523 Lore 14.05.2020
					//console.log(data);
					if(data[1].suckoff_secretion == '' && data[2].suckoff_consistency == '' && data[3].suckoff_consistency_text == '' && data[4].suckoff_color == ''){
						alert(translate('[Please fill something !!!]'));
						return false;
					}
					
					 if(data[2].suckoff_consistency == '4')
					 {
						 var setdata = {
								 id: data[0].id,
								 suckoff_secretion: data[1].suckoff_secretion.replace(/\,/g, '.'),
								 suckoff_consistency: data[2].suckoff_consistency,
								 suckoff_consistency_text: data[3].suckoff_consistency_text,
								 suckoff_color: data[4].suckoff_color,
								 suckoff_date: data[5].suckoff_date,
								 suckoff_time: data[5].suckoff_time,
								 
								 suckoff_soothing: data[6].suckoff_soothing,
								 suckoff_possible: data[7].suckoff_possible

						 }; 
					 }
					 else
					 {
						 var setdata = {
								 id: data[0].id,
								 suckoff_secretion: data[1].suckoff_secretion.replace(/\,/g, '.'),
								 suckoff_consistency: data[2].suckoff_consistency,
								 suckoff_consistency_text: '',
								 suckoff_color: data[4].suckoff_color,
								 suckoff_date: data[5].suckoff_date,
								 suckoff_time: data[5].suckoff_time,
								 
								 suckoff_soothing: data[6].suckoff_soothing,
								 suckoff_possible: data[7].suckoff_possible
						 };
					 }
					
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=suckoffsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#suckoff_modal').dialog("close");
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
							}
						 }
					 });	
					
				},
				text: translate('save')
			},

			//ISPC-2517 Carmen 05.06.2020
			{
				click: function(){

					var data = $('.create_form_block_suckoff tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							//ISPC-2523 Lore 14.05.2020
							 else if(this.type == 'checkbox' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio' && this.type != 'checkbox')
							 {
								 formset[this.name] = $(this).val();
							 }
							 
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();
					
					
					//ISPC-2523 Lore 14.05.2020
					//console.log(data);
					if(data[1].suckoff_secretion == '' && data[2].suckoff_consistency == '' && data[3].suckoff_consistency_text == '' && data[4].suckoff_color == ''){
						alert(translate('[Please fill something !!!]'));
						return false;
					}
					
					 if(data[2].suckoff_consistency == '4')
					 {
						 var setdata = {
								 id: data[0].id,
								 suckoff_secretion: data[1].suckoff_secretion.replace(/\,/g, '.'),
								 suckoff_consistency: data[2].suckoff_consistency,
								 suckoff_consistency_text: data[3].suckoff_consistency_text,
								 suckoff_color: data[4].suckoff_color,
								 suckoff_date: data[5].suckoff_date,
								 suckoff_time: data[5].suckoff_time,
								 
								 suckoff_soothing: data[6].suckoff_soothing,
								 suckoff_possible: data[7].suckoff_possible

						 }; 
					 }
					 else
					 {
						 var setdata = {
								 id: data[0].id,
								 suckoff_secretion: data[1].suckoff_secretion.replace(/\,/g, '.'),
								 suckoff_consistency: data[2].suckoff_consistency,
								 suckoff_consistency_text: '',
								 suckoff_color: data[4].suckoff_color,
								 suckoff_date: data[5].suckoff_date,
								 suckoff_time: data[5].suckoff_time,
								 
								 suckoff_soothing: data[6].suckoff_soothing,
								 suckoff_possible: data[7].suckoff_possible
						 };
					 }
					
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=suckoffsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#suckoff_modal').dialog("close");
							 if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
								 $('#patient-main-add-modal').dialog("close");
							 }
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage();
								setTimeout(function(){ $('html, body').animate({ scrollTop: $('#suckoff_chart').offset().top}, 1000); }, 9000);
								//$('html, body').animate({ scrollTop: $('#suckoff_chart').offset().top}, 1000);
							}else {
								// go to charts Ancuta 05.06.2020
								window.location.href = appbase + 'charts/overview?id='+pid+'#ch_suckoff_chart';
							}
						 }
					 });	
					
				},
				text: translate('save and go back to chart'),
				'id': 'suckoff_save_go_back'
			},
			//--
			
			{
				click: function(){
			
					$('#suckoff_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
			//ISPC-2523 Carmen 13.04.2020  allow a decimal value (no limitation - just decimal like 888,44)
			$(document).on('click', "#suckoff_secretion", function (){
				if($(this).val() == "0.00")$(this).val("");
				//$(this).parent().parent().addClass("yellow_bg");


			});
			
			$(document).on('keydown', "#suckoff_secretion", function (e) {
				// Allow: backspace, delete, tab, escape, enter and .
				if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 188]) !== -1 ||
					// Allow: home, end, left, right
					(e.keyCode >= 35 && e.keyCode <= 39)) 
				{
					// let it happen, don't do anything
					return;
				}

				
				// Ensure that it is a number and stop the keypress
				if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
					e.preventDefault();
				}
			});
	//--
			
			//ISPC-2518+ISPC-2520 Carmen 14.04.2020
			$('.organicentriesexits_chart_button').live('click', function() {
				//$('#organicentriesexits_modal').data('recid', '5').dialog('open');
				$('#organicentriesexits_modal').dialog('open');
			});
			
			$('#organicentriesexits_modal').dialog({
			    autoOpen: false,
			    modal: true,
			    width: 620,
			   	height: 400,
			   	title:  translate('organicentriesexits'),
			   	dialogClass: "charts_modal",
			   	
			   	open: function(){
			   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
			   			$('#organic_save_go_back').show();
			   		}
			   		else
			   		{
			   			$('#organic_save_go_back').hide();
			   		}
			      	 jQuery('.ui-widget-overlay').on('click', function () {
						 //$('#organicentriesexits_modal').dialog('close');
			            });
			      	$('.modal_loading_div', this).show();
			   		if($(this).data('recid'))
			   		{
			   			var url = 'patientevents/events?action=show_form&form=organicentriesexitsadd&recid='+$(this).data('recid');
			   			if($(this).parent().find('.delbutton').is(":hidden"))
						{
			   				$(this).parent().find('.delbutton').show();
						}
			   		}
			   		else
			   		{
			   			var url = 'patientevents/events?action=show_form&form=organicentriesexitsadd';
			   			$(this).parent().find('.delbutton').hide();
			   		}

			   		var recid = $(this).data('recid');
			   		$.ajax({
						type: 'POST',
						url: url,

						success:function(data){
						
							$('#organicentriesexits_modal').html(data);

							$('.time')
			        		.timepicker({
			        			minutes: {
			        				interval: 5
			        			},
			        			showPeriodLabels: false,
			        			rows: 4,
			        			hourText: 'Stunde',
			        			minuteText: 'Minute'
			        		})
			        		.mask("99:99");
					   		
					   		$( ".date" ).datepicker({
								dateFormat: 'dd.mm.yy',
								showOn: "button",
								buttonImage: $('#calImg').attr('src'),
								buttonImageOnly: true,
								changeMonth: true,
								changeYear: true,
								nextText: '',
								prevText: '',
								maxDate: '0',
							}).mask("99.99.9999");
					   		
					   		$('#organic_amount').val($('#organic_amount').val().replace(/\./g, ','));
					   		show_extrafields($('#organic_id').val(), recid);
						},
						error:function(){
							
						}
					});
			   	},
			    buttons:[
			             
				//delete button
				{
					'class' : "delbutton leftButton",
					text : 'Eintrag löschen',
					click : function() {
					var recid = $(this).data('recid');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
					    if(r) {	
					    	var setdata = {
									 id: recid,
					    	};
					    	
							$.ajax({
								 type: 'POST',
								 url: 'patientevents/events?action=save_form&form=organicentriesexitssave&subaction=delete&patid='+pid,
								 data: setdata,
								 success:function(data){
									 $('#organicentriesexits_modal').dialog("close");
									 if (typeof loadPage == 'function') { 
											loadPage(); 
										}
											/* $('#awake_sleeping_modal').unblock();
											$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
				
											var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
				
											if(action_name == 'patientcourse') {
												window.location.reload(true);
											} */
								 }
							 });
					    }
					});
						
					},
					
				},
			    

				{
					click: function(){

						var data = $('.create_form_block_organic_entries_exits tr').map(function() {
							 
							 var formset = {};
							 $(this).find(':input').each(function() {				    		
						    	
								 if(this.type == 'radio' && $(this).is(":checked"))
								 {
									 formset[this.name] = $(this).val();
								 }
								 else if(this.type != 'radio')
								 {
									 formset[this.name] = $(this).val();
								 }
								
							 });
							 if(!$.isEmptyObject(formset))
							{
						        return formset;
							}
						 }).get();
						 
						 var setdata = {};
						 
						 for (index = 0; index < data.length; ++index) {
							 
							 for(var key in data[index])
							 {
								 if (data[index].hasOwnProperty(key)) {
									 setdata[key] = data[index][key];
								 }
								 
								 if(key == 'organic_amount')
								 {
									 setdata[key] = setdata[key].replace(/\,/g, '.');
								 }
							 }  
						 }
						
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=organicentriesexitssave&patid='+pid,
							 data: setdata,
							 success:function(data){
								 $('#organicentriesexits_modal').dialog("close");
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage(); 
								}
							 }
						 });	
						
					},
					text: translate('save')
				},

				//ISPC-2517 Carmen 05.06.2020
				{
					click: function(){

						var data = $('.create_form_block_organic_entries_exits tr').map(function() {
							 
							 var formset = {};
							 $(this).find(':input').each(function() {				    		
						    	
								 if(this.type == 'radio' && $(this).is(":checked"))
								 {
									 formset[this.name] = $(this).val();
								 }
								 else if(this.type != 'radio')
								 {
									 formset[this.name] = $(this).val();
								 }
								
							 });
							 if(!$.isEmptyObject(formset))
							{
						        return formset;
							}
						 }).get();
						 
						 var setdata = {};
						 
						 for (index = 0; index < data.length; ++index) {
							 
							 for(var key in data[index])
							 {
								 if (data[index].hasOwnProperty(key)) {
									 setdata[key] = data[index][key];
								 }
								 
								 if(key == 'organic_amount')
								 {
									 setdata[key] = setdata[key].replace(/\,/g, '.');
								 }
							 }  
						 }
						
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=organicentriesexitssave&patid='+pid,
							 data: setdata,
							 success:function(data){
								 $('#organicentriesexits_modal').dialog("close");
								 if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
									 $('#patient-main-add-modal').dialog("close");
								 }
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage(); 
									 setTimeout(function(){ $('html, body').animate({ scrollTop: $('#organic_entries_exits_chart').offset().top}, 1000); }, 9000);
									//$('html, body').animate({ scrollTop: $('#organic_entries_exits_chart').offset().top}, 1000);
								}else {
									// go to charts Ancuta 05.06.2020
									window.location.href = appbase + 'charts/overview?id='+pid+'#ch_organic_entries_exits_chart';
								}
							 }
						 });	
						
					},
					text: translate('save and go back to chart'),
					'id': 'organic_save_go_back'
				},
				//--

				{
					click: function(){
				
						$('#organicentriesexits_modal').dialog('close');
					},
					text: translate('cancel'),
				}
			    ]
			});
			
				//allow a decimal value (no limitation - just decimal like 888,44)
				$(document).on('click', "#organic_amount", function (){
					if($(this).val() == "0.00")$(this).val("");
					//$(this).parent().parent().addClass("yellow_bg");


				});
				
				$(document).on('keydown', "#organic_amount", function (e) {
					// Allow: backspace, delete, tab, escape, enter and .
					if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 188]) !== -1 ||
						// Allow: home, end, left, right
						(e.keyCode >= 35 && e.keyCode <= 39)) 
					{
						// let it happen, don't do anything
						return;
					}

					
					// Ensure that it is a number and stop the keypress
					if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
						e.preventDefault();
					}
				});
			
				
		//--
				
			//ISPC-2519 Carmen 15.04.2020
			$('.custom_event_chart_button').live('click', function() {
				//$('#custom_event_modal').data('recid', '8').dialog('open');
				$('#custom_event_modal').dialog('open');
			});

			$('#custom_event_modal').dialog({
			    autoOpen: false,
			    modal: true,
			    width: 620,
			   	height: 380,
			   	title:  translate('custom_event_modal'),		//ISPC-2519 Lore 14.05.2020
			   	dialogClass: "charts_modal",
			   	
			   	open: function(){
			   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
			   			$('#custom_save_go_back').show();
			   		}
			   		else
			   		{
			   			$('#custom_save_go_back').hide();
			   		}
			      	jQuery('.ui-widget-overlay').on('click', function () {
						 //$('#custom_event_modal').dialog('close');
			        });
			      	 
			      	$('.modal_loading_div', this).show();
			   		if($(this).data('recid'))
			   		{
			   			var url = 'patientevents/events?action=show_form&form=customeventadd&recid='+$(this).data('recid');
			   			if($(this).parent().find('.delbutton').is(":hidden"))
						{
			   				$(this).parent().find('.delbutton').show();
						}
			   		}
			   		else
			   		{
			   			var url = 'patientevents/events?action=show_form&form=customeventadd';
			   			$(this).parent().find('.delbutton').hide();
			   		}
			   			   		
			   		$.ajax({
						type: 'POST',
						url: url,

						success:function(data){
						
							$('#custom_event_modal').html(data);

							$('.time')
			        		.timepicker({
			        			minutes: {
			        				interval: 5
			        			},
			        			showPeriodLabels: false,
			        			rows: 4,
			        			hourText: 'Stunde',
			        			minuteText: 'Minute'
			        		})
			        		.mask("99:99");
					   		
					   		$( ".date" ).datepicker({
								dateFormat: 'dd.mm.yy',
								showOn: "button",
								buttonImage: $('#calImg').attr('src'),
								buttonImageOnly: true,
								changeMonth: true,
								changeYear: true,
								nextText: '',
								prevText: '',
								maxDate: '0',
							}).mask("99.99.9999");
						},
						error:function(){
							
						}
					});
			   	},
			    buttons:[
			             
				//delete button
				{
					'class' : "delbutton leftButton",
					text : 'Eintrag löschen',
					click : function() {
					var recid = $(this).data('recid');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
					    if(r) {	
					    	var setdata = {
									 id: recid,
					    	};
					    	
							$.ajax({
								 type: 'POST',
								 url: 'patientevents/events?action=save_form&form=customeventsave&subaction=delete&patid='+pid,
								 data: setdata,
								 success:function(data){
									 $('#custom_event_modal').dialog("close");
									 if (typeof loadPage == 'function') { 
											loadPage(); 
										}
											/* $('#awake_sleeping_modal').unblock();
											$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
				
											var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
				
											if(action_name == 'patientcourse') {
												window.location.reload(true);
											} */
								 }
							 });
					    }
					});
						
					},
					
				},
			    	
				{
					click: function(){
						 
						if($('#custom_event_name').val() != '' && $('#custom_event_date').val() != '' && $('#custom_event_time').val() != '')
						{
						var data = $('.create_form_block_custom_event tr').map(function() {
							 
							 var formset = {};
							 $(this).find(':input').each(function() {				    		
						    	
								 if(this.type == 'radio' && $(this).is(":checked"))
								 {
									 formset[this.name] = $(this).val();
								 }
								 else if(this.type != 'radio')
								 {
									 formset[this.name] = $(this).val();
								 }
							 });
						        return formset;
						 }).get();
						 
						 var setdata = {
								 id: data[0].id,
								 custom_event_name: data[1].custom_event_name,
								 custom_event_description: data[3].custom_event_description,
								 custom_event_date: data[4].custom_event_date,
								 custom_event_time: data[4].custom_event_time,
						 };
						 
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=customeventsave&patid='+pid,
							 data: setdata,
							 success:function(data){
								 $('#custom_event_modal').dialog("close");
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage(); 
								}
							 }
						 });
						}
						else
						{
							if($('#custom_event_name').val() == '')
							{
								$('#custom_name_type_error').show();
							}
							if($('#custom_event_date').val() == '' || $('#custom_event_time').val() == '')
							{
								$('#custom_date_time_type_error').show();
							}
						}
					},
					text: translate('save')
				},
				
				//ISPC-2517 Carmen 05.06.2020
				{
					click: function(){
						 
						if($('#custom_event_name').val() != '' && $('#custom_event_date').val() != '' && $('#custom_event_time').val() != '')
						{
						var data = $('.create_form_block_custom_event tr').map(function() {
							 
							 var formset = {};
							 $(this).find(':input').each(function() {				    		
						    	
								 if(this.type == 'radio' && $(this).is(":checked"))
								 {
									 formset[this.name] = $(this).val();
								 }
								 else if(this.type != 'radio')
								 {
									 formset[this.name] = $(this).val();
								 }
							 });
						        return formset;
						 }).get();
						 
						 var setdata = {
								 id: data[0].id,
								 custom_event_name: data[1].custom_event_name,
								 custom_event_description: data[3].custom_event_description,
								 custom_event_date: data[4].custom_event_date,
								 custom_event_time: data[4].custom_event_time,
						 };
						 
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=customeventsave&patid='+pid,
							 data: setdata,
							 success:function(data){
								 $('#custom_event_modal').dialog("close");
								 if ($('#patient-main-add-modal').is(":visible")){
									 $('#patient-main-add-modal').dialog("close");
								 }
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage();
									setTimeout(function(){$('html, body').animate({ scrollTop: $('#custom_events_chart').offset().top}, 1000); }, 9000);
									//$('html, body').animate({ scrollTop: $('#custom_events_chart').offset().top}, 1000);
								}else {
									// go to charts Ancuta 05.06.2020
									window.location.href = appbase + 'charts/overview?id='+pid+'custom_events_chart';
								}
							 }
						 });
						}
						else
						{
							if($('#custom_event_name').val() == '')
							{
								$('#custom_name_type_error').show();
							}
							if($('#custom_event_date').val() == '' || $('#custom_event_time').val() == '')
							{
								$('#custom_date_time_type_error').show();
							}
						}
					},
					text: translate('save and go back to chart'),
					'id': 'custom_save_go_back'
				},
				//--

				{
					click: function(){
				
						$('#custom_event_modal').dialog('close');
					},
					text: translate('cancel'),
				}
			    ]
			});
			
			$(document).on('keyup keydown change paste', '#custom_event_name', function() {
				if($('#custom_name_type_error').is(":visible"))
				{
					$('#custom_name_type_error').hide();
				}
			});
			
			$(document).on('keyup keydown change paste', '#custom_event_date, #custom_event_time', function() {
				if($('#custom_date_time_type_error').is(":visible"))
				{
					$('#custom_date_time_type_error').hide();
				}
			});
		//--
			
		//ISPC-2508 Carmen 19.05.2020 new design
		$('#patient_charts_actions_modal').dialog({
			autoOpen: false,
			modal: true,
			maxWidth: 400,
			maxHeight: 400,
			width: 300,
			height: 350,
			
			close: function() {
				$('#patient_charts_actions_modal').html('');
			},
			open: function() {

				$('.modal_loading_div', this).show();
				
				var openfrom = $(this).data('openfrom');
				var entrydata_ID = $(this).data('recid');
				var url = appbase + 'ajax/modalactions?recid='+entrydata_ID+'&openfrom='+openfrom;

				$.get(url, function(result) {
					var newFieldset =  $('#patient_charts_actions_modal').append(result);
					
			});
				
				 jQuery('.ui-widget-overlay').on('click', function () {
					 $('#patient_charts_actions_modal').dialog('close');
		            });
				
				 $(document).off('click', '.artificial_action').on('click', '.artificial_action', function(){

						/*if($(this).val() != 'edit' && $(this).val() != 'delete')
						{
							$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).dialog('open');
						}*/
			        	//else if($(this).val() == 'delete')
					 	if($(this).val() == 'delete')
			        	{
		        			$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).data('openfrom', openfrom).dialog('open');
		    	        	var buttons = $('#artificial_entries_exits_modal').dialog('option', 'buttons');
		    	        	$('#artificial_entries_exits_modal').dialog('close');
		    	        	buttons[0].click.apply($('#artificial_entries_exits_modal'));

			        	}
			        	else
			        	{
			        		$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).data('openfrom', openfrom).dialog('open');
			        		
			        	}
			        	
			        	$('#patient_charts_actions_modal').dialog('close');
			    	});
			},

			buttons: [{
					text: translate('cancel'),
					click: function() {
						$(this).dialog("close");
					}
				},
			]
		});
		//--
			
		//ISPC-2508 Carmen 21.04.2020
		$('.add_event_artificial').live('click', function() {
			//$('#artificial_entries_exits_modal').data('recid', '235').dialog('open');
			$('#artificial_entries_exits_modal').data('recid', '').data('action', 'add').data('openfrom', 'addmodal').dialog('open');
		});
		
		$('#artificial_entries_exits_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 860,
		   	height: 320,
		   	title:  translate('Artificial entries exits:'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   		var openfrom = $(this).data('openfrom');
		   		if(openfrom != 'addmodal')
		   		{
		   			$('#artificial_save_go_back').hide();
		   		}
		   		else
		   		{
		   			$('#artificial_save_go_back').show();
		   		}
		      	jQuery('.ui-widget-overlay').on('click', function () {
					 //$('#artificial_entries_exits_modal').dialog('close');
		        });
		   		
		   		var that = $(this).parent();		   		
		      	
		   		$('.modal_loading_div', this).show();		   		
		   		if($(this).data('recid'))
		   		{
		   			var url = 'patientevents/events?action=show_form&form=artificialentriesexitsadd&recid='+$(this).data('recid')+'&artaction='+$(this).data('action');
		   			/*if($(this).parent().find('.refreshbutton').is(":hidden"))
					{
		   				$(this).parent().find('.refreshbutton').show();
					}
		   			if($(this).parent().find('.removebutton').is(":hidden"))
					{
		   				$(this).parent().find('.removebutton').show();
					}
		   			if($(this).parent().find('.artdelbutton').is(":hidden"))
					{
		   				$(this).parent().find('.artdelbutton').show();
					}*/
		   			$(this).parent().find('.refreshbutton').hide();
	        		$(this).parent().find('.removebutton').hide();
	        		$(this).parent().find('.artdelbutton').hide();
		   			
		   		}
		   		else
		   		{
		   			var url = 'patientevents/events?action=show_form&form=artificialentriesexitsadd&artaction='+$(this).data('action');
		   			$(this).parent().find('.refreshbutton').hide();
	        		$(this).parent().find('.removebutton').hide();
	        		$(this).parent().find('.artdelbutton').hide();
		   		}
		   		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#artificial_entries_exits_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
				   		
				   		$("#artificial_entries_exits_modal table tr").first().css("border-bottom", "1px solid #DDD");
				   		if ( $( "#remove_date" ).length ) {
				   			that.parent().find('.refreshbutton').hide();
			        		that.parent().find('.removebutton').hide();
			        		that.parent().find('.artdelbutton').hide();
			        		
					   		$('#remove_date').on('change', function(){
					   			var date_arr = $('#option_date').val().split('.');
							    var end_date_arr = $('#remove_date').val().split('.');
							    
							    var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], '0', '0', '0', '0');
							    var end = new Date(end_date_arr[2], end_date_arr[1]-1, end_date_arr[0], '0', '0', '0', '0');
							    if((end-start) < 0)
							    {
							    	alert(translate('End date cant be before start date'));
							    }	
					   		});
				   		}
				   		/*else if($( "#id" ).val() != '')
				   		{
				   			that.parent().find('.refreshbutton').show();
			        		that.parent().find('.removebutton').show();
			        		that.parent().find('.artdelbutton').show();
				   		}*/	
				   		
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
		//delete button
		{
			'class' : "artdelbutton leftButton",
			text : 'Eintrag löschen',
			click : function() {
			var recid = $(this).data('recid');
			var openfrom = $(this).data('openfrom');
			var action = $(this).data('action');
			jConfirm(translate('[Are you sure you want to delete this artificial entry/exit ?]'), translate('confirmdeletetitle'), function(r) {
			    if(r) {
			    	if(openfrom != 'contactform')
					{
				    	var setdata = {
								 id: recid,
				    	};
				    	
						$.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=artificialentriesexitssave&artaction=delete&patid='+pid+'&openfrom='+openfrom,
							 data: setdata,
							 success:function(data){
								 $('#artificial_entries_exits_modal').dialog("close");
								 if(openfrom == 'icon')
									{
									setTimeout(function () {
										$('#part_'+setdata['id']).remove();
									}, 800);
											
									//console.log(response);
									var response_obj = jQuery.parseJSON(data);
									
									if (response_obj['remove_icon'] == '1') {
										$('#sys_icon-72').remove();
										$('#content_sys_icon-72').remove();
									}
									}
								 else if(openfrom == 'addmodal')
									{
									 var url = 'patientevents/events?action=show_form&form=artificialentriesexitsactions&patid='+idpd;
								   		$.ajax({
											type: 'POST',
											url: url,

											success:function(data){
												
												$('#artificial_entries_exits_main_modal').empty();
												if (data.hasOwnProperty('entries')) {
												var _arttitle = $('<div>', {
						        					'class'	: 'artificial_title',
						        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
						        					html 	: [translate('artificial_entries')]
						            			});
												$(_arttitle).prependTo('#artificial_entries_exits_main_modal');
												
												$(data['entries']).appendTo('#artificial_entries_exits_main_modal');
												}
												if (data.hasOwnProperty('exits')) {
												var _arttitle = $('<div>', {
						        					'class'	: 'artificial_title',
						        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
						        					html 	: [translate('artificial_exits')]
						            			});
												$(_arttitle).appendTo('#artificial_entries_exits_main_modal');
												
												$(data['exits']).appendTo('#artificial_entries_exits_main_modal');
												}
												var _addbutton = $('<div>', {
						        					'class'	: 'add_event_artificial',
						        					'style' : 'margin-top: 10px;',
						        					html 	: [$("<img>", {"src" : appbase + 'images/btttt_plus.png'}), " " + translate('block_artificial_entries_exits_add')]
						            			});
												$(_addbutton).appendTo('#artificial_entries_exits_main_modal');
											}
								   		});
									}
								 if (typeof loadPage == 'function') { 
										loadPage(); 
									}
										/* $('true#awake_sleeping_modal').unblock();
										$('.ui-dialog-buttonpane button:first').removeAttr('disabled');
		
										var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';
		
										if(action_name == 'patientcourse') {
											window.location.reload(true);
										} */
							 }
						 });
					}
			    	else
			    	{
			    		$('#action_'+recid).val(action);
				    	
						setTimeout(function () {
							$('#fpart_'+recid).hide();
						}, 800);
			    	}
			    }
			});
				
			},
			
		},
		
		//refresh button
		/*{
			'class' : "refreshbutton leftButton",
			text : 'Eintrag ausgewechselt',
			click : function() {
			var recid = $(this).data('recid');
			jConfirm(translate('[Are you sure you want to refresh this artificial entry/exit ?]'), translate('confirmdeletetitle'), function(r) {
			    if(r) {
			    	var setdata = {
							 id: recid,
			    	};
					$.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=artificialentriesexitssave&subaction=refresh&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#artificial_entries_exits_modal').dialog("close");
							 if (typeof loadPage == 'function') { 
									loadPage(); 
								}
									 $('#awake_sleeping_modal').unblock();
									$('.ui-dialog-buttonpane button:first').removeAttr('disabled');

									var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';

									if(action_name == 'patientcourse') {
										window.location.reload(true);
									} 
						 }
					 });
			    }
			});
				
			},
			
		},
		
		//remove button
			{
				'class' : "removebutton leftButton",
				text : 'Eintrag entfernen',
				click : function() {
				var recid = $(this).data('recid');
				jConfirm(translate('[Are you sure you want to remove this artificial entry/exit ?]'), translate('confirmdeletetitle'), function(r) {
				    if(r) {
				    	var setdata = {
								 id: recid,
				    	};
						$.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=artificialentriesexitssave&subaction=remove&patid='+pid,
							 data: setdata,
							 success:function(data){
								 $('#artificial_entries_exits_modal').dialog("close");
								 if (typeof loadPage == 'function') { 
										loadPage(); 
									}
										 $('#awake_sleeping_modal').unblock();
										$('.ui-dialog-buttonpane button:first').removeAttr('disabled');

										var action_name = '<?php echo Zend_Controller_Front::getInstance()->getRequest()->getActionName(); ?>';

										if(action_name == 'patientcourse') {
											window.location.reload(true);
										} 
							 }
						 });
				    }
				});
					
				},
				
			},*/
	
			{
				
				click: function(){

					var openfrom = $(this).data('openfrom');
					var data = $('.create_form_artificial_entries_exits tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
							
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();					 
					if(openfrom == 'contactform')
					{
						var artset_id = $(this).data("recid");
						var action = $(this).data('action');
						contactformartificialblockupdate(artset_id, action, data);
					}
					else
					{
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 var aerr = '0';
					 if (setdata.hasOwnProperty('remove_date')) {
						
						 if(setdata['remove_date'] != "")
						 {
							 var date_arr = setdata['option_date'].split('.');
						     var end_date_arr = setdata['remove_date'].split('.');
						    
						     var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], '0', '0', '0', '0');
						     var end = new Date(end_date_arr[2], end_date_arr[1]-1, end_date_arr[0], '0', '0', '0', '0');
						     if((end-start) < 0)
						     {
						    	 aerr = '1';
						     }
						 }
						 else
						 {
							 aerr = '1';
						 }
					 }
					 
				     if(aerr == '1')
				     {
				    	 alert(translate('End date cant be before start date'));
				     }
				     else
				     {
					    
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=artificialentriesexitssave&patid='+pid+'&artaction='+$(this).data('action')+'&openfrom='+$(this).data('openfrom'),
							 data: setdata,
							 success:function(data){
								 //ISPC-2508 Carmen 21.05.2020 new design
								 /*$('#artificial_entries_exits_modal').dialog("close");*/
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage(); 
								}
								
								 if(openfrom == 'icon')
									{
									setTimeout(function () {
										$('#part_'+setdata['id']).remove();
									}, 800);
											
									//console.log(response);
									var response_obj = jQuery.parseJSON(data);
									
									if (response_obj['remove_icon'] == '1') {
										$('#sys_icon-72').remove();
										$('#content_sys_icon-72').remove();
									}
									}
									else if(openfrom == 'addmodal')
									{
										var url = 'patientevents/events?action=show_form&form=artificialentriesexitsactions&patid='+idpd;
								   		$.ajax({
											type: 'POST',
											url: url,

											success:function(data){
												
												$('#artificial_entries_exits_main_modal').empty();
												if (data.hasOwnProperty('entries')) {
												var _arttitle = $('<div>', {
						        					'class'	: 'artificial_title',
						        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
						        					html 	: [translate('artificial_entries')]
						            			});
												$(_arttitle).prependTo('#artificial_entries_exits_main_modal');
												
												$(data['entries']).appendTo('#artificial_entries_exits_main_modal');
												}
												if (data.hasOwnProperty('exits')) {
												var _arttitle = $('<div>', {
						        					'class'	: 'artificial_title',
						        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
						        					html 	: [translate('artificial_exits')]
						            			});
												$(_arttitle).appendTo('#artificial_entries_exits_main_modal');
												
												$(data['exits']).appendTo('#artificial_entries_exits_main_modal');
												}
												var _addbutton = $('<div>', {
						        					'class'	: 'add_event_artificial',
						        					'style' : 'margin-top: 10px;',
						        					html 	: [$("<img>", {"src" : appbase + 'images/btttt_plus.png'}), " " + translate('block_artificial_entries_exits_add')]
						            			});
												$(_addbutton).appendTo('#artificial_entries_exits_main_modal');
											}
								   		});
									}
									else
									{	
								 if(data !== '')
								 {
									 
									 $('#artificial_entries_exits_modal').html(data); 
								 }
								 else
								 {
									
									 //$('#artificial_entries_exits_modal').dialog("close");
									/* $('.modal_success_msg').show();
										setTimeout(function () {
											$('.modal_success_msg').hide();
											},1000);
									if (typeof loadPage == 'function') { 
										loadPage(); 
									}*/
								 }
							 }
								 
								 $('.time')
					        		.timepicker({
					        			minutes: {
					        				interval: 5
					        			},
					        			showPeriodLabels: false,
					        			rows: 4,
					        			hourText: 'Stunde',
					        			minuteText: 'Minute'
					        		})
					        		.mask("99:99");
							   		
							   		$( ".date" ).datepicker({
										dateFormat: 'dd.mm.yy',
										showOn: "button",
										buttonImage: $('#calImg').attr('src'),
										buttonImageOnly: true,
										changeMonth: true,
										changeYear: true,
										nextText: '',
										prevText: '',
										maxDate: '0',
									}).mask("99.99.9999");
							 }
						
						 //--
						 });	
						 $('#artificial_entries_exits_modal').dialog("close"); 
				     }
				    
					}
					// $('#artificial_entries_exits_modal').dialog("close");
					
				},
				'class' : "rightButton",
				text: translate('save')
			},
			//ISPC-2517 Carmen 05.06.2020
			{
				
				click: function(){

					var openfrom = $(this).data('openfrom');
					var data = $('.create_form_artificial_entries_exits tr').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
							
						 });
						 if(!$.isEmptyObject(formset))
						{
					        return formset;
						}
					 }).get();					 
					if(openfrom == 'contactform')
					{
						var artset_id = $(this).data("recid");
						var action = $(this).data('action');
						contactformartificialblockupdate(artset_id, action, data);
					}
					else
					{
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 var aerr = '0';
					 if (setdata.hasOwnProperty('remove_date')) {
						 var date_arr = setdata['option_date'].split('.');
					     var end_date_arr = setdata['remove_date'].split('.');
					    
					     var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], '0', '0', '0', '0');
					     var end = new Date(end_date_arr[2], end_date_arr[1]-1, end_date_arr[0], '0', '0', '0', '0');
					     if((end-start) < 0)
					     {
					    	 aerr = '1';
					     }
					 }
					 
				     if(aerr == '1')
				     {
				    	 alert(translate('End date cant be before start date'));
				     }
				     else
				     {
					    
						 $.ajax({
							 type: 'POST',
							 url: 'patientevents/events?action=save_form&form=artificialentriesexitssave&patid='+pid+'&artaction='+$(this).data('action')+'&openfrom='+$(this).data('openfrom'),
							 data: setdata,
							 success:function(data){
								 //ISPC-2508 Carmen 21.05.2020 new design
								 /*$('#artificial_entries_exits_modal').dialog("close");*/
								 $('.modal_success_msg').show();
									setTimeout(function () {
										$('.modal_success_msg').hide();
										},1000);
								if (typeof loadPage == 'function') { 
									loadPage(); 
								}
								
								 if(openfrom == 'icon')
									{
									setTimeout(function () {
										$('#part_'+setdata['id']).remove();
									}, 800);
											
									//console.log(response);
									var response_obj = jQuery.parseJSON(data);
									
									if (response_obj['remove_icon'] == '1') {
										$('#sys_icon-72').remove();
										$('#content_sys_icon-72').remove();
									}
									}
									else if(openfrom == 'addmodal')
									{
										$('#artificial_entries_exits_main_modal').dialog('close');
										$('#patient-main-add-modal').dialog('close');
										
									}
									else
									{	
								 if(data !== '')
								 {
									 
									 $('#artificial_entries_exits_modal').html(data); 
								 }
								 else
								 {
									
									 //$('#artificial_entries_exits_modal').dialog("close");
									/* $('.modal_success_msg').show();
										setTimeout(function () {
											$('.modal_success_msg').hide();
											},1000);
									if (typeof loadPage == 'function') { 
										loadPage(); 
									}*/
								 }
							 }
								 
								 $('.time')
					        		.timepicker({
					        			minutes: {
					        				interval: 5
					        			},
					        			showPeriodLabels: false,
					        			rows: 4,
					        			hourText: 'Stunde',
					        			minuteText: 'Minute'
					        		})
					        		.mask("99:99");
							   		
							   		$( ".date" ).datepicker({
										dateFormat: 'dd.mm.yy',
										showOn: "button",
										buttonImage: $('#calImg').attr('src'),
										buttonImageOnly: true,
										changeMonth: true,
										changeYear: true,
										nextText: '',
										prevText: '',
										maxDate: '0',
									}).mask("99.99.9999");
							 }
						
						 //--
						 });	
						 
				     }
				    
					}
					 $('#artificial_entries_exits_modal').dialog("close");
					
				},
				'class' : "rightButton",
				text: translate('save and go back to chart'),
				'id': 'artificial_save_go_back'
			},
			//--
			{
				click: function(){
			
					$('#artificial_entries_exits_modal').data('action', '').dialog('close');
				},
				'class' : "rightButton", 
				text: translate('cancel'),
			}
		    ]
		});
	//--
		
		$('#symptomatology_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 620,
		   	height: 700,
		   	title:  translate('symptomatology_label'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
		   			$('#symptomatology_save_go_back').show();
		   		}
		   		else
		   		{
		   			$('#symptomatology_save_go_back').hide();
		   		}
		      	jQuery('.ui-widget-overlay').on('click', function () {
					 //$('#symptomatology_modal').dialog('close');
		        });
		      	
		   		$('.modal_loading_div', this).show();
		   		
		   		var url = 'patientevents/events?action=show_form&form=symptomatologyadd';
  		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#symptomatology_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
			{
				click: function(){
					
					var data = $('#symptomatology_modal').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=symptomatologysave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#symptomatology_modal').dialog("close");
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
							}
						 }
					 });
					
				},
				text: translate('save')
			},
			
			//ISPC-2517 Carmen 05.06.2020
			{
				click: function(){
					
					var data = $('#symptomatology_modal').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=symptomatologysave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#symptomatology_modal').dialog("close");
							 if ($('#patient-main-add-modal').is(":visible")){
								 $('#patient-main-add-modal').dialog("close");
							 }
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
								setTimeout(function(){ $('html, body').animate({ scrollTop: $('#symptomatology_chart').offset().top}, 1000); }, 9000);
								//$('html, body').animate({ scrollTop: $('#symptomatology_chart').offset().top}, 1000);
							}else {
								// go to charts Ancuta 05.06.2020
								window.location.href = appbase + 'charts/overview?id='+pid+'#ch_symptomatology_chart';
							}
						 }
					 });
					
				},
				text: translate('save and go back to chart'),
				'id': 'symptomatology_save_go_back'
			},
			//--

			{
				click: function(){
			
					$('#symptomatology_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
				
		$('#medication_dosage_view_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 850,
		   	height: 600,
		   	title:  translate('Medikation'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		      	jQuery('.ui-widget-overlay').on('click', function () {
					 //$(this).dialog('close');
		        });
		      	
		      	$('.modal_loading_div', this).show();
//		   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + idpd ,{},);
		   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + idpd );
		   	},
		    buttons:[
			{
				click: function(){
			
					$(this).dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
		
		$('#medication_dosage_interaction_bulk_modal').dialog({
			autoOpen: false,
			modal: true,
			width: 700,
			height: 650,
			title:  "Medication",
			dialogClass: "charts_modal",
			open: function(){
				
//				jQuery('.ui-widget-overlay').on('click', function () {
//					$(this).dialog('close');
//				});
//				$('#loading_mdim', this).show();
				$('.modal_loading_div', this).show();
	           var dosageData = {
	        		   medication_type: $(this).data('medication_type'),
	        		   dosage_time_interval: $(this).data('dosage_time_interval'),
				};
	           
		   		$(this).parent().find('.delbutton').hide();
	           
				var url = 'patientevents/events?patid='+pid+'&action=show_form&form=dosage_interaction_bulk';
				
				
				$.ajax({
					type: 'POST',
					data: dosageData,
					url: url,
					
					success:function(data){
						
						$('#medication_dosage_interaction_bulk_modal').html(data);
						
						$('.time')
						.timepicker({
							minutes: {
								interval: 5
							},
							showPeriodLabels: false,
							rows: 4,
							hourText: 'Stunde',
							minuteText: 'Minute'
						})
						.mask("99:99");
						
						$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "both",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
					},
					error:function(){
						
					}
				});
			},
			buttons:[
						 
					
				{
					click: function(){
						
						var md_source = $(this).data('md_source');
						
						var that = $(this).data('that');
						
						var time_sched = $(this).data('time_schedule');
		 
						var dataString = $("#bulk_interaction").serialize();
							$.ajax({
								type: 'POST',
								url: 'patientevents/events?action=save_form&form=dosage_interaction_bulk_save&patid='+pid,
								data: dataString,
								success:function(data){
					 
									$('#medication_dosage_interaction_bulk_modal').dialog("close");
									
									//ISPC-2517 Ancuta 23.05.2020
									// RELOADDD to show all new statuses
//									$('#medication_icon_block').html();
//							   		$('#medication_icon_block').load(appbase + 'patientevents/medicationicon?id=' + pid ,{},);
							   		$('#medication_icon_block').load(appbase + 'patientevents/medicationicon?id=' + pid );
									//
//							   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + pid ,{},);
							   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + pid );
									
									if (typeof loadPage == 'function' ) { 
										loadPage();
									}
									else
									{
										//window.location.reload(true);
									}
								}
							});
					},
					text: translate('save')
				},
				//ISPC-2517 Carmen 05.06.2020
				{
					click: function(){
						
						var md_source = $(this).data('md_source');
						
						var that = $(this).data('that');
						
						var time_sched = $(this).data('time_schedule');
		 
						var dataString = $("#bulk_interaction").serialize();
							$.ajax({
								type: 'POST',
								url: 'patientevents/events?action=save_form&form=dosage_interaction_bulk_save&patid='+pid,
								data: dataString,
								success:function(data){
					 
									$('#medication_dosage_interaction_bulk_modal').dialog("close");
									$('#medication_dosage_view_modal').dialog("close");
									$('#patient-main-add-modal').dialog("close");
									//ISPC-2517 Ancuta 23.05.2020
									// RELOADDD to show all new statuses
//									$('#medication_icon_block').html();
//							   		$('#medication_icon_block').load(appbase + 'patientevents/medicationicon?id=' + pid ,{},);
							   		$('#medication_icon_block').load(appbase + 'patientevents/medicationicon?id=' + pid );
									//
//							   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + pid ,{},);
							   		$('#medication_dosage_view_modal').load(appbase + 'patientevents/medicationicon?id=' + pid );
									
									if (typeof loadPage == 'function' ) { 
										loadPage();
									}
									else {
										// go to charts Ancuta 05.06.2020
										window.location.href = appbase + 'charts/overview?id='+pid;
									}
								}
							});
					},
					text: translate('save and go back to chart')
				},
				//--
				{
					click: function(){
						
						$(this).dialog("close");
					},
					text: translate('cancel'),
				}
			]
		});		
		
		
		$('#artificial_entries_exits_main_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 850,
		   	height: 600,
		   	title:  translate('Artificial entries exits:'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		      	jQuery('.ui-widget-overlay').on('click', function () {
					 //$(this).dialog('close');
		        });
		      	
		      	$('.modal_loading_div', this).show();
		   		var url = 'patientevents/events?action=show_form&form=artificialentriesexitsactions&patid='+idpd;
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
						
						$('#artificial_entries_exits_main_modal').empty();
						if ($( "#calImg" ).length == '0')
						{
							$('<style id="artificialstyle" type="text/css">').text("#ui-timepicker-div {z-index: 1033 !important;} #ui-datepicker-div {z-index: 1025 !important;}").appendTo(document.head);
							
							var _calImg = $('<div>', {
	        					'style' : "margin: 7px; display: none;",
	        					html 	: [$("<img>", {"src" : appbase + 'images/calendar.png', "id" : 'calImg', "class" : 'trigger'}), ""]
	            			});
							$(_calImg).appendTo('#artificial_entries_exits_main_modal');
						}
						
						if (data.hasOwnProperty('entries')) {
						var _arttitle = $('<div>', {
        					'class'	: 'artificial_title',
        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
        					html 	: [translate('artificial_entries')]
            			});
						$(_arttitle).appendTo('#artificial_entries_exits_main_modal');
						
						$(data['entries']).appendTo('#artificial_entries_exits_main_modal');
						}
						if (data.hasOwnProperty('exits')) {
						var _arttitle = $('<div>', {
        					'class'	: 'artificial_title',
        					'style' : 'margin-top: 10px; margin-bottom: 10px;',
        					html 	: [translate('artificial_exits')]
            			});
						$(_arttitle).appendTo('#artificial_entries_exits_main_modal');
						
						$(data['exits']).appendTo('#artificial_entries_exits_main_modal');
						}
						var _addbutton = $('<div>', {
        					'class'	: 'add_event_artificial',
        					'style' : 'margin-top: 10px;',
        					html 	: [$("<img>", {"src" : appbase + 'images/btttt_plus.png'}), " " + translate('block_artificial_entries_exits_add')]
            			});
						$(_addbutton).appendTo('#artificial_entries_exits_main_modal');
					}
		   		});
				   	 
				   		
		   	},
		   	close: function() {
		   		$('#artificialstyle').remove();
			},
		    buttons:[
			{
				click: function(){
			
					$('#artificialstyle').remove();
					$(this).dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
		//ISPC-2516 Carmen 15.07.2020
		$('#symptomatologyII_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 760,
		   	height: 700,
		   	title:  translate('symptomatologyII_label'),
		   	dialogClass: "charts_modal",
		   	
		   	open: function(){
		   		if ($('#patient-main-add-modal').length && $('#patient-main-add-modal').hasClass('ui-dialog-content') && $('#patient-main-add-modal').dialog('isOpen')){
		   			$('#symptomatologyII_save_go_back').show();
		   		}
		   		else
		   		{
		   			$('#symptomatologyII_save_go_back').hide();
		   		}
		      	jQuery('.ui-widget-overlay').on('click', function () {
					 //$('#symptomatology_modal').dialog('close');
		        });
		      	
		   		$('.modal_loading_div', this).show();
		   		
		   		var url = 'patientevents/events?action=show_form&form=symptomatologyIIadd';
  		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#symptomatologyII_modal').html(data);

						$('.time')
		        		.timepicker({
		        			minutes: {
		        				interval: 5
		        			},
		        			showPeriodLabels: false,
		        			rows: 4,
		        			hourText: 'Stunde',
		        			minuteText: 'Minute'
		        		})
		        		.mask("99:99");
				   		
				   		$( ".date" ).datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "button",
							buttonImage: $('#calImg').attr('src'),
							buttonImageOnly: true,
							changeMonth: true,
							changeYear: true,
							nextText: '',
							prevText: '',
							maxDate: '0',
						}).mask("99.99.9999");
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
		             
			{
				click: function(){
					
					var data = $('#symptomatologyII_modal').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=symptomatologyIIsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#symptomatologyII_modal').dialog("close");
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
							}
						 }
					 });
					
				},
				text: translate('save')
			},
			
			//ISPC-2517 Carmen 05.06.2020
			{
				click: function(){
					
					var data = $('#symptomatologyII_modal').map(function() {
						 
						 var formset = {};
						 $(this).find(':input').each(function() {				    		
					    	
							 if(this.type == 'radio' && $(this).is(":checked"))
							 {
								 formset[this.name] = $(this).val();
							 }
							 else if(this.type != 'radio')
							 {
								 formset[this.name] = $(this).val();
							 }
						 });
					        return formset;
					 }).get();
					 
					var setdata = {};
					 
					 for (index = 0; index < data.length; ++index) {
						 
						 for(var key in data[index])
						 {
							 if (data[index].hasOwnProperty(key)) {
								 setdata[key] = data[index][key];
							 }
						 }  
					 }
					 
					 $.ajax({
						 type: 'POST',
						 url: 'patientevents/events?action=save_form&form=symptomatologyIIsave&patid='+pid,
						 data: setdata,
						 success:function(data){
							 $('#symptomatologyII_modal').dialog("close");
							 if ($('#patient-main-add-modal').is(":visible")){
								 $('#patient-main-add-modal').dialog("close");
							 }
							 $('.modal_success_msg').show();
								setTimeout(function () {
									$('.modal_success_msg').hide();
									},1000);
							if (typeof loadPage == 'function') { 
								loadPage(); 
								setTimeout(function(){ $('html, body').animate({ scrollTop: $('#symptomatologyII_chart').offset().top}, 1000); }, 9000);
								//$('html, body').animate({ scrollTop: $('#symptomatology_chart').offset().top}, 1000);
							}else {
								// go to charts Ancuta 05.06.2020
								window.location.href = appbase + 'charts/overview?id='+pid+'#symptomatology_chart';
							}
						 }
					 });
					
				},
				text: translate('save and go back to chart'),
				'id': 'symptomatologyII_save_go_back'
			},
			//--

			{
				click: function(){
			
					$('#symptomatologyII_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
		$(document).on('click', '.add_symptom', function(){
			$.get(appbase + 'patientevents/createformblocksymptomatologyrow', function(result) {
				var newFieldset =  $(result).appendTo($("#clientsymptomstable"));
			});
		});
		
		
		
		
		
	});
 	
 //$(document).ready END
				

		   		function show_extrafields(orgid, recid)
		   		{
		   			$.get(appbase + 'charts/createformblockorganicextrafields?orgid='+orgid+'&recid='+recid, function(result) {
		   					$('.extrafieldsrow').remove();
		   					var newFieldset =  $(result).insertAfter($('#organic_id').closest('tr'));
		   					
						});
		   			
		   		}
		   		
		   	//ISPC-2508 Carmen 21.04.2020
		   		function clientSettings(opt_id, client_options)
		   		{
		   			var cl_set;
		   			$.each(client_options, function(key, option){
		   				cl_set = {};
		   				if(option.id == opt_id)
		   				{
		   					cl_set['localization_available'] = option.localization_available;
		   					cl_set['name'] = option.name;
		   					cl_set['days_availability'] = option.days_availability;
		   					return false;
		   				}
		   			});
		   			return cl_set;
		   		}
		   	//ISPC-2508 Carmen 21.04.2020
		   		

		   		(function($) {
		   			$.fn.assertKeydownNumber = function(e) {
		   				
		   				try {
		   					// Allow: backspace, delete, tab, escape, enter, dot, comma 
		   					if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 188]) !== -1 ||
		   							// Allow: Ctrl+A
		   							(e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
		   							// Allow: Ctrl+C
		   							(e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
		   							// Allow: Ctrl+X
		   							(e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
		   							// Allow: home, end, left, right
		   							(e.keyCode >= 35 && e.keyCode <= 39) ||
		   							//Allow numbers 
		   							((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105))) {
		   						// let it happen, don't do anything
		   						return true;
		   					}
		   					// Ensure that it is a number and stop the keypress
		   					if ((!e.shiftKey && (e.keyCode < 48 || e.keyCode > 57)) || (e.keyCode < 96 || e.keyCode > 105)) {
//		   						e.preventDefault();
		   						return false;
		   					}
		   				} catch (e) {
		   					return true;
		   				}
		   			};	
		   		})(jQuery);

		   		(function($) {
		   			$.fn.validate0to10 = function(options) {
		   				try {
		   					var val = parseInt($(this).val(), 10);
		   					if (isNaN(val) || Number(val) < 0 || Number(val)>10) {
		   						////ISPC-2517 Carmen 05.06.2020
		   						$(this).css('background-color', '#ff0000');
		   						alert(translate('Symptom values are from 0 to 10, you entered: ')+' '+val);
		   						val = '';
		   						var _that = $(this);
		   						setTimeout(function () {
		   							_that.css('background-color', '#f2f2f2').fadeIn();
								}, 300);
		   						//--
		   					}
		   					
		   					$(this).val(val);
		   					
		   				} catch (e) {
		   					
		   					return true;
		   				}
		   			};	
		   		})(jQuery);		
		   		
		   		var data = $('.create_form_artificial_entries_exits tr').map(function() {
					 
					 var formset = {};
					 $(this).find(':input').each(function() {				    		
				    	
						 if(this.type == 'radio' && $(this).is(":checked"))
						 {
							 formset[this.name] = $(this).val();
						 }
						 else if(this.type != 'radio')
						 {
							 formset[this.name] = $(this).val();
						 }
						
					 });
					 if(!$.isEmptyObject(formset))
					{
				        return formset;
					}
				 }).get();					 

		   		//ISPC-2508 Carmen 26.05.2020 new design
				function contactformartificialblockupdate(artset_id, action, data)
				{
					if(artset_id == "")
				    {
				  		if($("#no_entries").length > 0)
				  		{
				  			 $("#artificial_content").find("tr:eq(0)").remove();
				  		}
				    	var parent_form = 'FormBlockArtificialEntriesExits[artificial_content]';
						
						$.get(appbase + 'ajax/createformblockartificialentryexitrow?parent_form='+parent_form, function(result) {
						//var newFieldset =  $(result).insertAfter($('#artificial_content tr:last'));
							var newFieldset =  $('#artificial_content tr:last').before(result);
							if(data[2].option_name != '')
						    {
								var new_row = $(result).attr('id').substring(6);
								
							    $('#option_id_'+new_row).val(data[1].option_id);
							    $('#action_'+new_row).val(action);
							    $('#option_date_'+new_row).val(data[3].option_date + ' ' + data[3].option_time);
							    $('#option_localization_'+new_row).val(data[4].option_localization);
							    $("#fpart_"+new_row).find("td:eq(0) span").text(data[2].option_name);
							    $("#fpart_"+new_row).find("td:eq(1)").text(data[3].option_date);
							    
							    //calculate option age
							    var date_arr = data[3].option_date.split('.');
							    var time_arr = data[3].option_time.split(':');
							    
							    var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], time_arr[0], time_arr[1], '0', '0');
							    var end = new Date();
							    var days = (end - start) / 1000 / 60 / 60 / 24;
							    //console.log(end);
							    
							    // which you need to offset as below because of daylight savings
							    days = Math.round(days - (end.getTimezoneOffset() - start.getTimezoneOffset()) / (60 * 24));
							    //console.log(days);
							    if(days == 0)
							    {
							    	$("#fpart_"+new_row).find("td:eq(2)").text(translate('today new'));
							    }
							    else if(days < 0)
							    {
							    	$("#fpart_"+data[0].id).find("td:eq(2)").text('');
							    }
							    else
							    {
							    	$("#fpart_"+new_row).find("td:eq(2)").text(days + ' ' + translate('days'));
							    }
							    
						    }
						});
				    }
				  	else
				  	{
					    if(data[2].option_name != '')
					    {
					    	
						    $('#option_id_'+data[0].id).val(data[1].option_id);
						    $('#action_'+data[0].id).val(action);
						    $('#option_date_'+data[0].id).val(data[3].option_date + ' ' + data[3].option_time);
						    $('#option_localization_'+data[0].id).val(data[4].option_localization);
						    $("#fpart_"+data[0].id).find("td:eq(0) span").text(data[2].option_name);
						    $("#fpart_"+data[0].id).find("td:eq(1)").text(data[3].option_date);
						    var days_val = parseInt($("#option_availability_"+artset_id).val(), 10);
						    //calculate option age
						    var date_arr = data[3].option_date.split('.');
						    var time_arr = data[3].option_time.split(':');
						    
						    var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], time_arr[0], time_arr[1], '0', '0');
						    var end = new Date();
						    var days = (end - start) / 1000 / 60 / 60 / 24;
						    //console.log(end);
						    // which you need to offset as below because of daylight savings
						    days = Math.round(days - (end.getTimezoneOffset() - start.getTimezoneOffset()) / (60 * 24));
						    //console.log(days);
						    if(days == 0)
						    {
						    	$("#fpart_"+data[0].id).find("td:eq(2)").text(translate('today new'));
						    }
						    else if(days < 0)
						    {
						    	$("#fpart_"+data[0].id).find("td:eq(2)").text('');
						    }
						    else
						    {
						    	if(days > days_val && days_val != 0)
						    	{
						    		$("#fpart_"+data[0].id).find("td:eq(2)").html('<font style="color:red;">!</font>'+days + ' ' + translate('days'));
						    	}
						    	else
						    	{
						    		$("#fpart_"+data[0].id).find("td:eq(2)").text(days + ' ' + translate('days'));
						    	}
						    }
					    }
					    else
					    {
					    	if(action == 'edit')
							{
					    		
					    	var days_val = parseInt($("#option_availability_"+artset_id).val(), 10);
					    	var date_arr = data[3].option_date.split('.');
						    var time_arr = data[3].option_time.split(':');
						    
						    var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], time_arr[0], time_arr[1], '0', '0');
						    var end = new Date();
						    var days = (end - start) / 1000 / 60 / 60 / 24;
						    //console.log(end);
						    
						    // which you need to offset as below because of daylight savings
						    days = Math.round(days - (end.getTimezoneOffset() - start.getTimezoneOffset()) / (60 * 24));
						    
						    //console.log(days);
						    $('#option_date_'+data[0].id).val(data[3].option_date + ' ' + data[3].option_time);
						    $('#action_'+data[0].id).val(action);
						    $('#option_localization_'+data[0].id).val(data[4].option_localization);
						    $("#fpart_"+data[0].id).find("td:eq(1)").text(data[3].option_date);
						    if(days == 0)
						    {
						    	$("#fpart_"+data[0].id).find("td:eq(2)").text(translate('today new'));
						    }
						    else if(days < 0)
						    {
						    	$("#fpart_"+data[0].id).find("td:eq(2)").text('');
						    }
						    else
						    {
						    	if(days > days_val && days_val != 0)
						    	{
						    		$("#fpart_"+data[0].id).find("td:eq(2)").html('<font style="color:red;">!</font>'+days + ' ' + translate('days'));
						    	}
						    	else
						    	{
						    		$("#fpart_"+data[0].id).find("td:eq(2)").text(days + ' ' + translate('days'));
						    		}
						    	
						    }
							}
					    	else if(action == 'refresh')
							{
								$('#action_'+artset_id).val(action);
								var option_date = new Date($("#option_date_"+artset_id).val()),
						        days = parseInt($("#option_availability_"+artset_id).val(), 10);
								var days_val = parseInt($("#option_availability_"+artset_id).val(), 10);
								var option_date = new Date();
								
								var remove_arr = data[4].remove_date.split('.');
							    var remove_time_arr = data[4].remove_time.split(':');
								var remove = new Date(remove_arr[2], remove_arr[1]-1, remove_arr[0], remove_time_arr[0], remove_time_arr[1], '0', '0');
							    var end = new Date();
							    var days = (end - start) / 1000 / 60 / 60 / 24;
							    
							    //console.log(days);
							    $('#option_date_'+data[0].id).val(data[3].option_date + ' ' + data[3].option_time);
							    $('#remove_date_'+data[0].id).val(data[4].remove_date + ' ' + data[4].remove_time);
								
						        if(!isNaN(option_date.getTime())){				            
						            var yyyy = option_date.getFullYear().toString();
						            var mm = (option_date.getMonth()+1).toString(); // getMonth() is zero-based
						            var dd  = option_date.getDate().toString();
						            option_date = (dd[1]?dd:"0"+dd[0]) +  "."  + (mm[1]?mm:"0"+mm[0]) +  "."  + yyyy;
						            
						            $("#fpart_"+artset_id).find("td:eq(1)").text(option_date);
						            
						            //calculate age
						            var date_arr = option_date.split('.');
								    //var time_arr = data[3].option_time.split(':');
								    
								    var start = new Date();
								    var end = new Date();
								    var days = (end - start) / 1000 / 60 / 60 / 24;
								    
								    //console.log(end);
								    
								    // which you need to offset as below because of daylight savings
								    days = Math.round(days - (end.getTimezoneOffset() - start.getTimezoneOffset()) / (60 * 24));
								    $('#option_date_'+artset_id).val(option_date);
								    if(days == 0)
								    {
								    	$("#fpart_"+artset_id).find("td:eq(2)").text(translate('today new'));
								    }
								    else if(days < 0)
								    {
								    	$("#fpart_"+artset_id).find("td:eq(2)").text('');
								    }
								    else
								    {
								    	if(days > days_val && days_val != 0)
								    	{
								    		$("#fpart_"+artset_id).find("td:eq(2)").html('<font style="color:red;">!</font>'+days + ' ' + translate('days'));
								    	}
								    	else
								    	{
								    		$("#fpart_"+artset_id).find("td:eq(2)").text(days + ' ' + translate('days'));
								    	}
								    	
								    }
								   
						        } else {
						            alert("Invalid Date");  
						        }
							}
					    	else if(action == 'remove')
							{
					    		var remove_arr = data[4].remove_date.split('.');
							    var remove_time_arr = data[4].remove_time.split(':');
								var remove = new Date(remove_arr[2], remove_arr[1]-1, remove_arr[0], remove_time_arr[0], remove_time_arr[1], '0', '0');
							   
							    $('#remove_date_'+data[0].id).val(data[4].remove_date + ' ' + data[4].remove_time);
								$('#action_'+artset_id).val(action);
						    	
								setTimeout(function () {
									$('#fpart_'+artset_id).hide();
								}, 800);
							}
						    
					    }
				  	}
				}
				//ISPC-2516 Carmen 13.07.2020
				function remove_sym_line(link) {
					link.closest('tr').next().remove(); 
					link.closest('tr').remove();
					//cl_sym_count--;
				}
				//--
				
				