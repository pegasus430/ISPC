/**
 * orders/overview.js 18.12.2018
 * //ISPC-2464 Ancuta 31.10.2019
 */
var medication_list = {};

// ISPC-2548 TODO-2848 Lore 06.02.2020
var actual_medication_list 	= {};
var bedarfs_medication_list = {};
var krisen_medication_list 	= {};
var iv_medication_list 		= {};
var isnutrition_medication_list = {};
var intervall_medication_list 	= {};



$(document).ready(function() {
	
	//overview main page	
	$("#tabs_orders").tabs({

	    select: function(event, ui) {
	        $('#layout_result_messages').hide();
	    },
	
		show : function(event, ui) {

			var selected_tab = ui.index;

			switch (ui.index) {

				case 0:
					window.selectedTab = "own_active_orders";
				break;
				
				case 1:
					window.selectedTab = "all_active_orders";
				break;
				
				case 2:
					window.selectedTab = "closed_orders";
				break;

				default:
				break;
			}
		}
	    
	});
	
	var _idx = 0;
	
	if (typeof selected_tab != "undefined") {
		
		switch (selected_tab) {
			case "own_active_orders":
				_idx = 0;
				break;
			case "all_active_orders":
				_idx = 1;
				break;
			case "closed_orders":
				_idx = 2;
				break;
		}
	}
	
	//overview edit project
	$("#tabs_orders").tabs({
		selected : _idx,
		show : function(event, ui) {

			var url_selected_tab = ui.index;
			switch (ui.index) {

			case 0:
				url_selected_tab = "own_active_orders";
			break;
			
			case 1:
				url_selected_tab= "all_active_orders";
			break;
			
			case 2:
				url_selected_tab= "closed_orders";
			break;

			default:
			break;
		}
			
//			var sSource_url = appbase + 'orders/overview?tab='+url_selected_tab;
//			window.location.href= sSource_url
//			
			
		},
	    select: function(event, ui) {
	        $('#layout_result_messages').hide();
	    }
	
	});
	
	
	
	
	$('.date')
	.datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: '',
	});
	
	$('.todo_selectbox' , $("#todos")).chosen({
		placeholder_text_single: translate('please select'),
		placeholder_text_multiple : translate('please select'),
		multiple:1,
		width:'250px',
		"search_contains": true,
		no_results_text: translate('noresultfound')
	});
	
	
	$('.order_cell').live('click', function(e){
		e.preventDefault();
		if (isDoubleClicked($(this))) return; //     TODO-2835 ISPC : PE Bestellungen bug with double click Ancuta 21.01.2020

		var order_date = $(this).data('date'); 
		var order_datedmY = $(this).data('odmy'); 
		var order_id = $(this).data('order_id');
		var order_parent_id = $(this).data('order_parent_id');
		var order_step_parent_id = $(this).data('order_step_parent_id');
		var patient = $(this).data('patient');
		var is_parent = $(this).data('is_parent');
		var current_order_id = $(this).data('current_order_id');
		
		var interval_type = $(this).data('interval_type');
		var interval_options = $(this).data('interval_options');

		var oData = {};
		oData['patient'] = patient;
		oData['order_date'] = order_date;
		oData['order_date_dmY'] = order_datedmY;
		oData['order_id'] = order_id;
		oData['parent_id'] = order_parent_id;
		oData['step_parent_id'] = order_step_parent_id;
		oData['is_parent'] = is_parent;
		oData['current_order_id'] = current_order_id;
		
		oData['interval_type'] = interval_type;
		oData['interval_options'] = interval_options;

		/*
		$('#order_dialog').load(appbase+'orders/order?patient='+patient+'&date='+order_date+'&order_id='+order_id+'&parent_id='+order_parent_id+'&step_parent_id='+order_step_parent_id, function() {
			OrderDialog(oData)
		});
		*/
		//TODO-2872 Ancuta 24.03.2020
		// if cell is not empty - allow user to choose action ( add new or edit  order) 
		if( order_id != undefined  && (( current_order_id != "" && order_parent_id != "") || (order_id != 0 && order_id != undefined)) ){
			$('#choose_order_dialog').load(appbase+'orders/chooseorder?patient='+patient+'&date='+order_date+'&order_id='+order_id+'&parent_id='+order_parent_id+'&step_parent_id='+order_step_parent_id, function() {
				ChooseOrderDialog(oData)
			});
			
		} else{
		// if cell is empty - continue 	
			$('#order_dialog').load(appbase+'orders/order?patient='+patient+'&date='+order_date+'&order_id='+order_id+'&parent_id='+order_parent_id+'&step_parent_id='+order_step_parent_id, function() {
				OrderDialog(oData)
			});
		}		
		
	});
	
	//TODO-2872 Ancuta 24.03.2020 (start)
	$(document).off('click',".choose_order_cell").on('click', '.choose_order_cell', function(e){
		e.preventDefault();
		if (isDoubleClicked($(this))) return; //     TODO-2835 ISPC : PE Bestellungen bug with double click Ancuta 21.01.2020
		
		var order_date = $(this).data('date'); 
		var order_datedmY = $(this).data('odmy'); 
		var order_id = $(this).data('order_id');
		var order_parent_id = $(this).data('order_parent_id');
		var order_step_parent_id = $(this).data('order_step_parent_id');
		var patient = $(this).data('patient');
		var is_parent = $(this).data('is_parent');
		var current_order_id = $(this).data('current_order_id');
		
		var interval_type = $(this).data('interval_type');
		var interval_options = $(this).data('interval_options');
		
		var oData = {};
		oData['patient'] = patient;
		oData['order_date'] = order_date;
		oData['order_date_dmY'] = order_datedmY;
		oData['order_id'] = order_id;
		oData['parent_id'] = order_parent_id;
		oData['step_parent_id'] = order_step_parent_id;
		oData['is_parent'] = is_parent;
		oData['current_order_id'] = current_order_id;
		
		oData['interval_type'] = interval_type;
		oData['interval_options'] = interval_options;
		
		// close the chose dialog 
		$('#choose_order_dialog').dialog('close');
		
		$('#order_dialog').load(appbase+'orders/order?patient='+patient+'&date='+order_date+'&order_id='+order_id+'&parent_id='+order_parent_id+'&step_parent_id='+order_step_parent_id, function() {
			OrderDialog(oData)
		});
		
	});
	//TODO-2872 Ancuta 24.03.2020 (end)
 

	//################################################################
	//##################### ADD PATIENT TO GRID ######################
	//################################################################
	
	
	$('.add_patient').live('click', function(e){
		var start_date = $(this).data("start_date");
		var page_tab = $(this).data("tab");
		
		$('#add_patient')
		.data('start_date',start_date)
		.data('page_tab',page_tab)
		.dialog('open');
	});

	$('#add_patient').dialog({

		modal : true,
		autoOpen : false,
		closeOnEscape : true,
		title : translate('[Add new patient to active grid]'),
		minWidth : 560,
		minHeight : 300,
		
		open : function() {
			var start_date  = $(this).data('start_date');
			$('#period_start_date').val(start_date);
			var page_tab  = $(this).data('page_tab');
			$('#go2tab').val(page_tab);
			
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
		},

		buttons : [

					//cancel button
					{
						text : translate('cancel'),
						click : function() {
							$(this).dialog("close");
						},
					
					},
					//save button
					{
						text : translate('save'),
						click : function() {

							var _this_button = this;
							
							if (checkclientchanged()) {
								// submit with ajax the change?
								var _post_data = {
									"step" : 'add_patient2active_grid',
									"patientid" :  $("#patientid", $(this).dialog()).val(),
									"start_date" :  $("#period_start_date", $(this).dialog()).val()
								};

								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
							            if (data.success == true) {
							            	
							            	$(_this_button).dialog("close");
							            	// refresh
							            	window.location.href= window.location.href
							            	
							            } else {
							            	$("#error_messages", _this_button).html(data.errors);
							            }
									},
									"error" : function(xhr, ajaxOptions, thrownError) {
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											//console.log(xhr, ajaxOptions, thrownError);
											$("#error_messages", this).html(thrownError);
										}
									}
								});
								

							} else {
								setTimeout(
										function() {
//											alert(translate('btmbuchhistory_lang')["only positive amount"]);
										}, 50);
							}

						},

					}
					],

		
	});
	
	$('#patientsearch_share').liveSearch({
		url: 'ajax/patientsearchorders?field_value=',
		id: 'livesearch_share_patient',
		aditionalWidth: '62',
		noResultsDelay: '1200',
		typeDelay: '1200'
	});
	
	//################################################################
	//##################### REMOVE PATIENT FROM GRID ######################
	//################################################################
		
	$('._remove').live('click', function(e){
		var patient_id = $(this).data("patient");
		var tab = $(this).data("tab");
		
		
		$('#remove_patient')
		.data('patient_id',patient_id)
		.data('tab',tab)
		.dialog('open');
	});
	$('#remove_patient').dialog({

		modal : true,
		autoOpen : false,
		closeOnEscape : true,
		title : translate('[Remove Patient from order Grid]'),
		minWidth : 560,
		minHeight : 100,
		
		open : function() {
			var patient_id  = $(this).data('patient_id');
			$('#remove_patientid').val(patient_id);
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
		},

		buttons : [

					//cancel button
					{
						text : translate('cancel'),
						click : function() {
							$(this).dialog("close");
						},
					
					},
					//save button
					{
						text : translate('[Remove]'),
						click : function() {

							var _this_button = this;
							
							if (checkclientchanged()) {
								// submit with ajax the change?

								var _post_data = {
									"step" : 'remove_patient_from_grid',
									"patientid" :  $("#remove_patientid", $(this).dialog()).val(),
								};

								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
//										console.log(data);
							            if (data.success == true) {
							            	$(_this_button).dialog("close");

							            	window.location.href = window.location.href;
							            	// refresh
							            } else {
							            	$("#error_messages", _this_button).html(data.errors);
							            }
									},
									"error" : function(xhr, ajaxOptions, thrownError) {
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											//console.log(xhr, ajaxOptions, thrownError);
											$("#error_messages", this).html(thrownError);
										}
									}
								});
								

							} else {
								setTimeout(
										function() {
//											alert(translate('btmbuchhistory_lang')["only positive amount"]);
										}, 50);
							}

						},

					}
					],

		
	});
	
	
	$('._play').live('click', function(e){
		var patient_id = $(this).data("patient");
		var patient_current_status = $(this).data("patient_status");
		
		// get last order of clicked patient 
		
		  if (checkclientchanged()) {
			   // submit with ajax the change?
			   
			   var _post_data = {
					   "step" : 'last_patient_order',
					   "patientid" : patient_id,
					   "patient_status" : patient_current_status,
			   };
			   
			   $.ajax({
				   "dataType" : 'json',
				   "type" : "POST",
				   "url" : window.location.href, //appbase + "orsers/ordersoverview",
				   "data" : _post_data,
				   "success" : function(data) {
//					   console.log(data);
					   if (data.success == true) {

							var order_date =data.order_date; 
							var order_id = data.order_id;
							var order_parent_id =data.parent_id;
							var order_step_parent_id = data.step_parent_id;
							var patient = data.patient_id;
							var is_parent = "";
							var current_order_id = "";

//							alert(patient_current_status);
							var oData = {};
							oData['patient'] = data.patient_id;
							oData['order_date'] = data.order_date;
							oData['order_id'] = data.order_id;
							oData['parent_id'] = data.parent_id;
							oData['step_parent_id'] = data.step_parent_id;
							oData['is_parent'] = "";
							oData['current_order_id'] = "";
							
							$('#order_dialog').load(appbase+'orders/order?patient='+patient+'&date='+order_date+'&order_id='+order_id+'&parent_id='+order_parent_id+'&step_parent_id='+order_step_parent_id+'&patient_status='+patient_current_status, function() {
								OrderDialog(oData)
							});
						   // refresh
					   } else {
						   $("#error_messages_play", _this_button).html(data.errors);
					   }
				   },
				   "error" : function(xhr, ajaxOptions, thrownError) {
					   if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
						   //console.log(xhr, ajaxOptions, thrownError);
						   $("#error_messages_pause", this).html(thrownError);
					   }
				   }
			   });
			   
			   
		   } else {
			   setTimeout(
					   function() {
//							alert(translate('btmbuchhistory_lang')["only positive amount"]);
					   }, 50);
		   }
		  
		  
		  

		
		
	});
	
	
	$('._pause').live('click', function(e){
		var patient_id = $(this).data("patient");
		
		
		$('#pause_patient_orders')
		.data('patient_id',patient_id)
		.dialog('open');
	});
	$('#pause_patient_orders').dialog({
		
		modal : true,
		autoOpen : false,
		closeOnEscape : true,
		title : translate('[Pause active orders]'),
		minWidth : 560,
		minHeight : 150,
		
		open : function() {
			var patient_id  = $(this).data('patient_id');
			$('#pause_patientid').val(patient_id);
			$("#pause_start_date").val();
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
			
			$("#error_messages_pause").html('');
			$("#pause_start_date").val();
		},
		
		buttons : [
		           
		           //cancel button
		           {
		        	   text : translate('cancel'),
		        	   click : function() {
		        		   $(this).dialog("close");
		        	   },
		        	   
		           },
		           //save button
		           {
		        	   text : translate('[Save]'),
		        	   click : function() {
		        		   
		        		   var _this_button = this;
		        		   
		        		   if (checkclientchanged()) {
		        			   // submit with ajax the change?
		        			   
		        			   var _post_data = {
		        					   "step" : 'pause_patient_orders',
		        					   "patientid" :  $("#pause_patientid", $(this).dialog()).val(),
		        					   "start_date" :  $("#pause_start_date", $(this).dialog()).val(),
		        			   };
		        			   
		        			   $.ajax({
		        				   "dataType" : 'json',
		        				   "type" : "POST",
		        				   "url" : window.location.href, //appbase + "orsers/ordersoverview",
		        				   "data" : _post_data,
		        				   "success" : function(data) {
//		        					   console.log(data);
		        					   if (data.success == true) {
		        						   $(_this_button).dialog("close");
		        						   
		        						   window.location.href = window.location.href;
		        						   // refresh
		        					   } else {
		        						   $("#error_messages_pause", _this_button).html(data.errors);
		        					   }
		        				   },
		        				   "error" : function(xhr, ajaxOptions, thrownError) {
		        					   if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
		        						   //console.log(xhr, ajaxOptions, thrownError);
		        						   $("#error_messages_pause", this).html(thrownError);
		        					   }
		        				   }
		        			   });
		        			   
		        			   
		        		   } else {
		        			   setTimeout(
		        					   function() {
//											alert(translate('btmbuchhistory_lang')["only positive amount"]);
		        					   }, 50);
		        		   }
		        		   
		        	   },
		        	   
		           }
		           ],
		           
		           
	});
	
	
	
	$('._stop').live('click', function(e){
		var patient_id = $(this).data("patient");
		
		
		$('#stop_patient_orders')
		.data('patient_id',patient_id)
		.dialog('open');
	});
	$('#stop_patient_orders').dialog({
		
		modal : true,
		autoOpen : false,
		closeOnEscape : true,
		title : translate('[stop active orders]'),
		minWidth : 560,
		minHeight : 150,
		
		open : function() {
			var patient_id  = $(this).data('patient_id');
			$('#stop_patientid').val(patient_id);
			$("#stop_start_date").val();
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
			
			$("#error_messages_stop").html('');
			$("#stop_start_date").val();
		},
		
		buttons : [
		           
		           //cancel button
		           {
		        	   text : translate('cancel'),
		        	   click : function() {
		        		   $(this).dialog("close");
		        	   },
		        	   
		           },
		           //save button
		           {
		        	   text : translate('[Save]'),
		        	   click : function() {
		        		   
		        		   var _this_button = this;
		        		   
		        		   if (checkclientchanged()) {
		        			   // submit with ajax the change?
		        			   
		        			   var _post_data = {
		        					   "step" : 'stop_patient_orders',
		        					   "patientid" :  $("#stop_patientid", $(this).dialog()).val(),
		        					   "start_date" :  $("#stop_start_date", $(this).dialog()).val(),
		        			   };
		        			   
		        			   $.ajax({
		        				   "dataType" : 'json',
		        				   "type" : "POST",
		        				   "url" : window.location.href, //appbase + "orsers/ordersoverview",
		        				   "data" : _post_data,
		        				   "success" : function(data) {
//		        					   console.log(data);
		        					   if (data.success == true) {
		        						   $(_this_button).dialog("close");
		        						   
		        						   window.location.href = window.location.href;
		        						   // refresh
		        					   } else {
		        						   $("#error_messages_stop", _this_button).html(data.errors);
		        					   }
		        				   },
		        				   "error" : function(xhr, ajaxOptions, thrownError) {
		        					   if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
		        						   //console.log(xhr, ajaxOptions, thrownError);
		        						   $("#error_messages_stop", this).html(thrownError);
		        					   }
		        				   }
		        			   });
		        			   
		        			   
		        		   } else {
		        			   setTimeout(
		        					   function() {
//											alert(translate('btmbuchhistory_lang')["only positive amount"]);
		        					   }, 50);
		        		   }
		        		   
		        	   },
		        	   
		           }
		           ],
		           
		           
	});
	
	
	
	
	
	
	// reload medication datatable
	
	
//	$('.refresh_med').live('click', function() {
//		alert("ssss");
////		redrawDatatable(true);
//	});
//	
	
	
	
$('#place_order_options').dialog({
		
		modal : true,
		autoOpen : false,
		closeOnEscape : true,
		title : translate('[save order options]'),
		minWidth : 700,
		minHeight : 200,
		
		open : function() {
			var patient_id  = $(this).data('patient_id');
			
			
			
			
			var _post_data = {
					"step" : 'get_following_saved_orders',
					"order_data" : $('#save_order').serialize(),
			};
			
			$.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : window.location.href, //appbase + "orsers/ordersoverview",
				"data" : _post_data,
				"success" : function(data) {
					if (data.following_saved == 1) {
						$('.following_orders').html(data.following_saved_orders);
					} else {
//						$('.following_orders').html("ONLY PLANNED ORDERS");
						$('.following_orders').html("");
					}
				}
			});
			
		},
		
		beforeClose : function() {
			// return false; // don't allow to close
		},
		
		close : function(event, ui) {
			// dialog was closed
		},
		
		buttons : [
		           // Apply changes to current ONLY
		           {
		        	   text : translate('[Yes, change current order]'),
		        	   "class": 'yes_current' ,//TODO-2872 
		        	   click : function() {
		        		   
		        		   //var _this_button = this;
		        		   var _this_button = $(this).data(_this_button);
		        		   
		        		   //TODO-2872 Ancuta 25.03.2020
		        		   $('.yes_current').attr("disabled", true);
		        		   $('.yes_current').addClass("btnDisabledLoading");
		        		   //-- 
		        		   if (checkclientchanged()) {
		        			   // submit with ajax the change?

		        				
								var _post_data = {
										"step" : 'current_order_dialog',
										"order_data" : $('#save_order').serialize(),
								};
								
								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
										if (data.success == 1) {
//											$(_this_button).dialog("close");
											$('#place_order_options').dialog("close");
											$(this).dialog("close");
											// reload 
											var sSource_url = window.location.href;
											window.location.href= sSource_url
										} else {
											$('#place_order_options').dialog("close");
											
											//TODO-2872 Ancuta 25.03.2020
											$('.yes_current').attr("disabled", false);
							        		$('.yes_current').removeClass("btnDisabledLoading");
							        		//-- 
							        		
											$(".ErrorDiv").show();
											$(".ErrorDiv").html(data.errors);
										}
									},
									"error" : function(data, xhr, ajaxOptions, thrownError) {
										$('#place_order_options').dialog("close");
										
										//TODO-2872 Ancuta 25.03.2020
										$('.yes_current').attr("disabled", false);
						        		$('.yes_current').removeClass("btnDisabledLoading");
						        		// --
						        		
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											$(".ErrorDiv").show();
											$(".ErrorDiv", this).html(thrownError);
										}
									}
								});
		        			   
		        			   
		        		   } else {
		        			   setTimeout(
		        					   function() {
		        					   }, 50);
		        		   }
		        		   
		        	   },
		        	   
		           },
		           //save button
		           {
		        	   text : translate('[Yes, change current and all following]'),
		        	   "class": 'yes_current_and_following' ,//TODO-2872 Ancuta 25.03.2020
		        	   click : function() {
		        		   
		        		   var _this_button = $(this).data(_this_button);
		        		   
		        		   //TODO-2872 Ancuta 25.03.2020
		        		   $('.yes_current_and_following').attr("disabled", true);
		        		   $('.yes_current_and_following').addClass("btnDisabledLoading");
		        		   // --
		        		   if (checkclientchanged()) {
								var _post_data = {
										"step" : 'order_dialog',
										"order_data" : $('#save_order').serialize(),
								};
								
								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
										if (data.success == 1) {
											$(_this_button).dialog("close");
											$('#place_order_options').dialog("close");
											
											// reload 
											var sSource_url = window.location.href;
											window.location.href= sSource_url
										} else {
											//TODO-2872 Ancuta 25.03.2020
										   $('.yes_current_and_following').attr("disabled", false);
						        		   $('.yes_current_and_following').removeClass("btnDisabledLoading");
						        		   //--
											$('#place_order_options').dialog("close");
											$(".ErrorDiv").show();
											$(".ErrorDiv").html(data.errors);
										}
									},
									"error" : function(data, xhr, ajaxOptions, thrownError) {
												//TODO-2872 Ancuta 25.03.2020
											   $('.yes_current_and_following').attr("disabled", false);
							        		   $('.yes_current_and_following').removeClass("btnDisabledLoading");
							        		   //-- 
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											$(".ErrorDiv").show();
											$(".ErrorDiv", this).html(thrownError);
										}
									}
								});
		        			   
		        			   
		        		   } else {
		        			   setTimeout(
		        					   function() {
		        					   }, 50);
		        		   }
		        		   
		        	   },
		        	   
		           },
		           {
		        	   text : translate('cancel'),
		        	   click : function() {
		        		   $(this).dialog("close");
		        	   },
		           }
		           ],
		           
		           
	});
	
	
});

function selectOrderPatient(pid, epid, firstname, lastname, admission, dob)
{
	$('#searchdropdown_share').slideUp('slow');

	var selected_list = '<table class="datatable" style="width:100%"><thead><tr class="BlueBg" ><td class="first" width="233">'+translate('epid')+'</td><td class="first" width="233">'+translate('firstname')+'</td><td width="200">'+translate('lastname')+' </td><td width="200">'+translate('admissiondate')+' </td><td width="200">'+translate('dateofbirth')+' </td></tr></thead><tbody>';
	selected_list+='<tr class="row">';
	selected_list+='<td class="first" width="233">'+epid +'</td>';
	selected_list+='<td class="first" width="233">'+unescape(firstname)+'</td>';
	selected_list+='<td width="200">'+unescape(lastname) +'</td>';
	selected_list+='<td width="200">'+admission +'</td>';
	selected_list+='<td width="200">'+dob +'</td>';
	selected_list+='</tr></tbody></table>';
	$('#patientid').val(pid);

	$('#searchdropdown_share_selected').html(selected_list);

	$('#searchdropdown_share_selected').slideDown('slow');
	$('#patientsearch_share').val(epid);
}


function OrderDialog( aData) {
	var _aData =  aData || {};	
	
	$('#order_dialog').dialog({
	
		dialogClass : "newProjectDialog",
		modal : true,
		autoOpen : true,
		closeOnEscape : true,
		title : translate('[Order modal]'),
		minWidth : 900,
		minHeight : 300,
		
		open : function() {
			
			//ISPC-2639 Carmen 16.07.2020
			if(_aData.order_id == '0')
			{
				$('.generatepdf_order_button').hide();
			}
			else
			{
				$('.generatepdf_order_button').show();
			}
			//--
			var start_date  = $(this).data('start_date');
//			var is_parent = $(this).data('is_parent');
			$('#is_parent').val(_aData.is_parent);

			$('#period_start_date').val(start_date);
			$("#error_messages", this).html('');
			
			$('.date')
			.datepicker({
				dateFormat: 'dd.mm.yy',
				showOn: "both",
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: '',
			});
			
			
			$('.user_selectbox').chosen({
				placeholder_text_single: translate('please select'),
				placeholder_text_multiple : translate('please select'),
				multiple:1,
				width:'100%',
				"search_contains": true,
				no_results_text: translate('noresultfound')
			});

			//ISPC-2639 Ancuta 21.07.2020
			$('.materials_selectbox').chosen({
				placeholder_text_single: translate('please select'),
				placeholder_text_multiple : translate('please select'),
				multiple:1,
				width:'100%',
				"search_contains": true,
				allow_duplicates: false,
				no_results_text: translate('noresultfound')
			});
			//-- 
			
			
			medication_list = drawDatatableIntubatedMedication(_aData);
			//ISPC-2548 TODO-2848 Lore 06.02.2020
			actual_medication_list 		= drawDatatableActualMedication(_aData);
			bedarfs_medication_list 	= drawDatatableBedarfsMedication(_aData);
			krisen_medication_list 		= drawDatatableKrisenMedication(_aData);
			iv_medication_list 			= drawDatatableIVMedication(_aData);
			isnutrition_medication_list = drawDatatableIsnutritionMedication(_aData);
			intervall_medication_list 	= drawDatatableIntervallMedication(_aData);
			
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
		},
	
		buttons : [
		           
				//close button
				{
					text : translate('[close modal]'),
					"class": "close_button",
					click : function() {
						$(this).dialog("close");
					},
				
				},
				//ISPC-2639 Carmen 16.07.2020
				//generate pdf button
				{
					text : translate('[GENERATE PDF ORDER]'),
					"class":"generatepdf_order_button",
					click : function() {
						var _post_data = {
								"step" : 'generate_from_order_pdf',
								"order_data" : $('#save_order').serialize(),
								"patient": _aData.patient,
						};
						$.ajax({
							"dataType" : 'json',
							"type" : "POST",
							"url" : window.location.href, //appbase + "orsers/ordersoverview",
							"data" : _post_data,
							"success" : function(data) {
								var win = window.open('', '_blank');	
								var link = document.createElement('a');
								link.href = 'orders/pdforderdownload?pdfname='+data.pdfname;
							    win.location.href = link.href;
							    
							    win.setTimeout(function(){win.top.close();}, 1000);
							},
							"error" : function(data, xhr, ajaxOptions, thrownError) {
								
							}
						});
					},
				},				
				//--
				//save button
				{
					text : translate('[PLACE ORDER]'),
					"class":"place_order_button",
					click : function() {
	
						var _this_button = this;
						if (checkclientchanged()) {
							

							$('#order_status').val('active');
							
							// submit with ajax the change?
							if(_aData.order_id != 0 || _aData.parent_id != 0 ){
								// -------------
								// ORDER NOT NEW
								// -------------
								

								// get "dates"  details
								var post_interval_type ="";
								$('.add_action_interval').each(function(){
									if($(this).is(":checked")){
										  post_interval_type = $(this).val(); 
									}
								});
								
								var post_interval_options = "";
								$('.add_action_interval').each(function(){
									if($(this).is(":checked")){
										  post_interval_type = $(this).val(); 
									}
								});
								

								var post_interval_options_str = "";
								if(post_interval_type == 'selected_days_of_the_week'){
									$('.selected_days_of_the_week').each(function(){
										if($(this).is(":checked")){
											post_interval_options_str = post_interval_options_str+','+Number($(this).val());
										}
									});
									post_interval_options_str = post_interval_options_str.substring(1);
								} else if(post_interval_type == 'every_x_days'){
									post_interval_options_str = $('input.every_x_days').val();
								}
								
								
								
								// ------------------------------
								// Check if "dates" were changed
								// ------------------------------
								post_order_date = $('#order_date').val(); 
								if( (_aData.interval_type != post_interval_type || _aData.interval_options != post_interval_options_str || _aData.order_date_dmY != post_order_date)
										|| (_aData.order_id != 0 && _aData.parent_id == _aData.order_id  && _aData.is_parent == "yes")
								){
									
									// If "dates"  were changed - ALL furute orders will be changed! - leave in place the OLD functionality
									jConfirm(translate('[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified]'), translate('atention'), function(r) {
									if(r)
									{
									 
										var _post_data = {
												"step" : 'order_dialog',
												"order_data" : $('#save_order').serialize(),
										};
										$.ajax({
											"dataType" : 'json',
											"type" : "POST",
											"url" : window.location.href, //appbase + "orsers/ordersoverview",
											"data" : _post_data,
											"success" : function(data) {
									            if (data.success == 1) {
									            	$(_this_button).dialog("close");
									            	
									            	// reload 
									            	var sSource_url = window.location.href;
													window.location.href= sSource_url
									            } else {
									            	$(".ErrorDiv").show();
									            	$(".ErrorDiv", _this_button).html(data.errors);
									            }
											},
											"error" : function(data, xhr, ajaxOptions, thrownError) {
												if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
													$(".ErrorDiv").show();
									            	$(".ErrorDiv", this).html(thrownError);
												}
											}
										});
									}
								}); 
									
								} else {
									
									$('#place_order_options')
									.data('_this_button',_this_button)
									.dialog('open');
								}
							} else{
								
								// -------------
								// ORDER IS NEW
								// -------------
								var _post_data = {
										"step" : 'order_dialog',
										"order_data" : $('#save_order').serialize(),
								};
				 
								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
							            if (data.success == 1) {
							            	$(_this_button).dialog("close");
							            	
							            	// reload 
							            	var sSource_url = window.location.href;
											window.location.href= sSource_url
							            } else {
							            	$(".ErrorDiv").show();
							            	$(".ErrorDiv", _this_button).html(data.errors);
							            }
									},
									"error" : function(data, xhr, ajaxOptions, thrownError) {
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											$(".ErrorDiv").show();
							            	$(".ErrorDiv", this).html(thrownError);
										}
									}
								});
							}
	
						} else {
							setTimeout(
									function() {
										alert(translate('Client has changed'));
									}, 50);
						}
	
					},
	
				},
				//save button
				{
					text : translate('[VERIFY ORDER]'),
					"class":"verify_order_button",
					click : function() {
						
						var _this_button = this;
						
						if (checkclientchanged()) {
							
							if(_aData.order_id != 0 || _aData.parent_id != 0 ){
								// -------------
								// ORDER NOT NEW
								// -------------
							
								$('#order_status').val('verified');
	
	
								// get "dates"  details
								var post_interval_type ="";
								$('.add_action_interval').each(function(){
									if($(this).is(":checked")){
										  post_interval_type = $(this).val(); 
									}
								});
								
								var post_interval_options = "";
								$('.add_action_interval').each(function(){
									if($(this).is(":checked")){
										  post_interval_type = $(this).val(); 
									}
								});
								
	
								var post_interval_options_str = "";
								if(post_interval_type == 'selected_days_of_the_week'){
									$('.selected_days_of_the_week').each(function(){
										if($(this).is(":checked")){
											post_interval_options_str = post_interval_options_str+','+Number($(this).val());
										}
									});
									post_interval_options_str = post_interval_options_str.substring(1);
								} else if(post_interval_type == 'every_x_days'){
									post_interval_options_str = $('input.every_x_days').val();
								}
								
								
								
								// ------------------------------
								// Check if "dates" were changed
								// ------------------------------
								post_order_date = $('#order_date').val(); 
								if( (_aData.interval_type != post_interval_type || _aData.interval_options != post_interval_options_str || _aData.order_date_dmY != post_order_date)
										|| (_aData.order_id != 0 && _aData.parent_id == _aData.order_id  && _aData.is_parent == "yes")
								){
									
									// If "dates"  were changed - ALL furute orders will be changed! - leave in place the OLD functionality
									jConfirm(translate('[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified]'), translate('atention'), function(r) {
									if(r)
									{
										var _post_data = {
												"step" : 'order_dialog',
												"order_data" : $('#save_order').serialize(),
										};
										$.ajax({
											"dataType" : 'json',
											"type" : "POST",
											"url" : window.location.href, //appbase + "orsers/ordersoverview",
											"data" : _post_data,
											"success" : function(data) {
									            if (data.success == 1) {
									            	$(_this_button).dialog("close");
									            	
									            	// reload 
									            	var sSource_url = window.location.href;
													window.location.href= sSource_url
									            } else {
									            	$(".ErrorDiv").show();
									            	$(".ErrorDiv", _this_button).html(data.errors);
									            }
											},
											"error" : function(data, xhr, ajaxOptions, thrownError) {
												if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
													$(".ErrorDiv").show();
									            	$(".ErrorDiv", this).html(thrownError);
												}
											}
										});
									}
								}); 
									
								} else {
									
									$('#place_order_options')
									.data('_this_button',_this_button)
									.dialog('open');
								}
							
							} else{
								
								// -------------
								// ORDER IS NEW
								// -------------
								$('#order_status').val('verified');
								var _post_data = {
										"step" : 'order_dialog',
										"order_data" : $('#save_order').serialize(),
								};
				 
								$.ajax({
									"dataType" : 'json',
									"type" : "POST",
									"url" : window.location.href, //appbase + "orsers/ordersoverview",
									"data" : _post_data,
									"success" : function(data) {
							            if (data.success == 1) {
							            	$(_this_button).dialog("close");
							            	
							            	// reload 
							            	var sSource_url = window.location.href;
											window.location.href= sSource_url
							            } else {
							            	$(".ErrorDiv").show();
							            	$(".ErrorDiv", _this_button).html(data.errors);
							            }
									},
									"error" : function(data, xhr, ajaxOptions, thrownError) {
										if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
											$(".ErrorDiv").show();
							            	$(".ErrorDiv", this).html(thrownError);
										}
									}
								});
							}
							
						} else {
							setTimeout(
									function() {
										alert(translate('Client has changed'));
									}, 50);
						}
						
					},
					
				},
				
				//cancel order button
				{
					text : translate('[CANCEL ORDER]'),
					"class":"cancel_order_button",
					click : function() {
						var _this_button = this;
						
						var _this_button = this;
						
						if (checkclientchanged()) {
							
							
							
							$('#order_status').val('canceled');


							// get "dates"  details
							var post_interval_type ="";
							$('.add_action_interval').each(function(){
								if($(this).is(":checked")){
									  post_interval_type = $(this).val(); 
								}
							});
							
							var post_interval_options = "";
							$('.add_action_interval').each(function(){
								if($(this).is(":checked")){
									  post_interval_type = $(this).val(); 
								}
							});
							

							var post_interval_options_str = "";
							if(post_interval_type == 'selected_days_of_the_week'){
								$('.selected_days_of_the_week').each(function(){
									if($(this).is(":checked")){
										post_interval_options_str = post_interval_options_str+','+Number($(this).val());
									}
								});
								post_interval_options_str = post_interval_options_str.substring(1);
							} else if(post_interval_type == 'every_x_days'){
								post_interval_options_str = $('input.every_x_days').val();
							}
							
							
							
							post_order_date = $('#order_date').val(); 
							if( (_aData.interval_type != post_interval_type || _aData.interval_options != post_interval_options_str || _aData.order_date_dmY != post_order_date) ){
								// If "dates"  were changed - ALL furute orders will be changed! - leave in place the OLD functionality
								jConfirm(translate('are you sure you want to cancel the order and all the following orders '), translate('atention'), function(r) {
								if(r)
								{
									var _post_data = {
											"step" : 'order_dialog',
											"order_data" : $('#save_order').serialize(),
									};
									$.ajax({
										"dataType" : 'json',
										"type" : "POST",
										"url" : window.location.href, //appbase + "orsers/ordersoverview",
										"data" : _post_data,
										"success" : function(data) {
								            if (data.success == 1) {
								            	$(_this_button).dialog("close");
								            	
								            	// reload 
								            	var sSource_url = window.location.href;
												window.location.href= sSource_url
								            } else {
								            	$(".ErrorDiv").show();
								            	$(".ErrorDiv", _this_button).html(data.errors);
								            }
										},
										"error" : function(data, xhr, ajaxOptions, thrownError) {
											if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
												$(".ErrorDiv").show();
								            	$(".ErrorDiv", this).html(thrownError);
											}
										}
									});
								}
							}); 
								
							} else {
								
								$('#place_order_options')
								.data('_this_button',_this_button)
								.dialog('open');
								
								
							}
							
							
							
							
							
						/*	jConfirm(translate('are you sure you want to cancel the order and all the following orders ?'), translate('atention'), function(r) {
								if(r)
								{
									// submit with ajax the change?
									$('#order_status').val('canceled');
									var _post_data = {
										"step" : 'order_dialog',
										"order_data" : $('#save_order').serialize(),
									};
									
									$.ajax({
										"dataType" : 'json',
										"type" : "POST",
										"url" : window.location.href, //appbase + "orsers/ordersoverview",
										"data" : _post_data,
										"success" : function(data) {
								            if (data.success == 1) {
								            	$(_this_button).dialog("close");
								            	
								            	// reload 
								            	var sSource_url = window.location.href;
												window.location.href= sSource_url
								            } else {
								            	$(".ErrorDiv").show();
								            	$(".ErrorDiv", _this_button).html(data.errors);
								            }
										},
										"error" : function(data, xhr, ajaxOptions, thrownError) {
											if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
												$(".ErrorDiv").show();
								            	$(".ErrorDiv", this).html(thrownError);
											}
										}
									});
								}
							});*/
							
							
						} else {
							setTimeout(
									function() {
										alert(translate('Client has changed'));
									}, 50);
						}
						
//						$(this).dialog("close");
					},
				
				},
				
	
		],
	
		
});


}

//TODO-2872 Ancuta 24.03.2020 (start)
function ChooseOrderDialog( aData) {
	var _aData =  aData || {};	
	$('#choose_order_dialog').dialog({
		
		dialogClass : "newProjectDialog",
		modal : true,
		autoOpen : true,
		closeOnEscape : true,
		title : translate('[Choose Order modal]'),
		minWidth : 600,
		minHeight : 300,
		
		open : function() {
		},
		beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			// dialog was closed
		},
		
		buttons : [
			
			//close button
			{
				text : translate('[close modal]'),
				"class": "close_button",
				click : function() {
					$(this).dialog("close");
				},
				
			},
			],
			
			
	});
	
	
}
//TODO-2872 Ancuta 24.03.2020 (end)

//onclick radio button
function onclick_set_delivery_date() 
{
	// get order date
	var order_date = $('#order_date').val()

	// set delivery date
	$('#delivery_date').val(order_date);

}

//onclick radio button
function onclick_action_interval(_this) 
{
	var interval = $(_this).val();
	
	$(".ioptions").attr("disabled", true);

	$(".interval_options").hide();
	$("."+interval).attr("disabled", false);
	$(".interval_options."+interval).show();
}



var timer_redraw = null;
var datatableObj = null;
//var selectedTab = "own_active_orders"; //projects_open, projects_prepare, projects_closed;


function onclick_refresh_medication(_this) 
{
	var patient =  $(_this).data('patient');
	
	var oData = {};
	oData['patient'] = patient;
	oData['redraw'] = 1;
	
	medication_list.ajax.reload();
	//ISPC-2548 TODO-2848 Lore 06.02.2020
	actual_medication_list.ajax.reload();
	bedarfs_medication_list.ajax.reload();
	krisen_medication_list.ajax.reload();
	iv_medication_list.ajax.reload();
	isnutrition_medication_list.ajax.reload();
	intervall_medication_list.ajax.reload();

}

//ISPC-2639 pct.1 Lore 08.07.2020
function onclick_refresh_medication_dosage() 
{
   	  
	$("#buton_refresh_dosage").val('1');
	    
	medication_list.ajax.reload();
	//ISPC-2548 TODO-2848 Lore 06.02.2020
	actual_medication_list.ajax.reload();
	bedarfs_medication_list.ajax.reload();
	krisen_medication_list.ajax.reload();
	iv_medication_list.ajax.reload();
	isnutrition_medication_list.ajax.reload();
	intervall_medication_list.ajax.reload();	

}

function onclick_changeMonth(_this) 
{
	
	var year =  $(_this).data('year');
	var month =  $(_this).data('month');
	var tab =  $(_this).data('tab');
//	var source_var = window.location.href;
//	var sSource_url = "";
//	if(source_var.indexOf("?") == "-1"){
//		var sSource_url = source_var+'?year='+year+'&month='+month;
//	}  else {
		var sSource_url = appbase + 'orders/overview?year='+year+'&month='+month+'&tab='+tab;
//}
	window.location.href= sSource_url
	
 
}


function drawDatatableIntubatedMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable = $('#intubated_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		// "bLengthChange": false,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "intubated_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				
				
				{
					'title' : translate('[medication]'),
					'data' : "order_medication_name",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "300px"
				},
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : translate('[dosage]'),
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
						
/*					'title' : translate('[dosage]'),
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"*/
				},
				{
					'title' : translate('[packaging]'),
					'data' : "order_medication_packaging",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "150px"
				},
				{
					'title' : translate('[volume]'),
					'data' : "order_medication_volume",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : translate('[kcal]'),
					'data' : "order_medication_kcal",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {

//			$('input:checkbox.select_all', $(this).parents('#intubated_medication_list_wrapper') ).prop("checked", false);
			
		},
		
		"createdRow": function( row, data, dataIndex ) {

			var _cbx = "<div class='datatable_cb_row'><label>"
			//+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
			+ "</label>"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   value='" + data.order_medication_dosage + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][packaging]'   value='" + data.order_medication_packaging + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][volume]'   value='" + data.order_medication_volume + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][kcal]'   value='" + data.order_medication_kcal + "' />"
			+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
			if(buton_refresh_dsg == 1){
				var _cbx = "<div class='datatable_cb_row'><label>"
					//+ Number(dataIndex + 1)
					+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
					+ "</label>"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   value='" + data.order_medication_dosage_refr + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][packaging]'   value='" + data.order_medication_packaging + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][volume]'   value='" + data.order_medication_volume + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][kcal]'   value='" + data.order_medication_kcal + "' />"
					+ "</div>";
			} 
			
			$("td", row).eq(0).html(_cbx);
			
		/*	
			var medication_name ="" 
				+ "<b>"
				+ data.order_medication_name
				+ "</b>"
				+ "<div class='datatable_med_row patient_med'>" 
				+ data.patient_medication_name
				+ "</div>"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
				; 
			$("td", row).eq(1).html(medication_name);
			
			
			var medication_dosage = "" 
				+ data.order_medication_dosage
				+ "<div class='datatable_med_row'><b>" 
				+ data.patient_medication_dosage
				+ "</b>"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   value='" + data.order_medication_dosage + "' />"
				+ "</div>"
				; 
			$("td", row).eq(2).html(medication_dosage);
			*/
			
			
		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable.ajax.reload();
	}
	
	return projects_datatable;
}
/**
 *  TODO-2835 ISPC : PE Bestellungen bug with double click Ancuta 21.01.2020
 *  https://stackoverflow.com/questions/11621652/how-to-prevent-a-double-click-using-jquery
 * @param element
 * @returns 
 * ancuta Jan 21, 2020
 */

function isDoubleClicked(element) {
    //if already clicked return TRUE to indicate this click is not allowed
    if (element.data("isclicked")) return true;

    //mark as clicked for 1 second
    element.data("isclicked", true);
    setTimeout(function () {
        element.removeData("isclicked");
    }, 1000);

    //return FALSE to indicate this click was allowed
    return false;
}



//ISPC-2548 TODO-2848 Lore 06.02.2020
function drawDatatableActualMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_actual = $('#actual_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "actual_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				

				{
					'title' : "Name / Wirkstoff ",
					'data' : "order_medication_name",
				     'render': function (data, type, row) {
				          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "370px"
				},			
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : "Dosierung",
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"
						
/*					'title' : "Dosierung",
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"*/
				},
				{
					'title' : "Kommentar",
					'data' : "order_medication_comments",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "100px"
				},
				{
					'title' : "Konzentration",
					'data' : "order_medication_concentration",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : "Darreichungsform",
					'data' : "order_medication_dosage_form",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : "Einheit",
					'data' : "order_medication_unit",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "60px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+1000;
			
			var _cbx = "<div class='datatable_cb_row'><label>"
				//+ Number(dataIndex + 1)
				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
				+ "</label>"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'   		   value='" + data.order_medication_unit + "' />"
				+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
			if(buton_refresh_dsg == 1){
				var _cbx = "<div class='datatable_cb_row'><label>"
					//+ Number(dataIndex + 1)
					+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
					+ "</label>"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage_refr + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
					+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'   		   value='" + data.order_medication_unit + "' />"
					+ "</div>";
			}
			

			
			
			$("td", row).eq(0).html(_cbx);
	
			
		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_actual.ajax.reload();
	}
	
	return projects_datatable_actual;
}

function drawDatatableBedarfsMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_bedarfs = $('#bedarfs_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "bedarfs_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
	"columns" :  [
			{
				'title' : "",
				'data' : null,
				'name' : "auto-counter",
				'orderable' : false,
				'className' : "",
				'width' : "15px",
			},
			{
				'title' : "debug",
				'data' : "debug",
				'name' : "debugcolumn",
				'visible' : false,
				'orderable' : false,
				'className' : "",
			},
			
			{
				'title' : "patient_drugplan_id",
				'data' : "patient_drugplan_id",
				'visible' : false,
				'className' : "",
			},
			
			
			{
				'title' : "Name / Wirkstoff",
				'data' : "order_medication_name",
			     'render': function (data, type, row) {
			          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
			        },
			    'orderable' : true,
				'className' : "gotoDetails",
				'width' : "380px"
			},			
			{
				//ISPC-2639 pct.1 Lore 23.07.2020
				'title' : "Dosierung",
				'data' : "order_medication_dosage",
			     'render': function (data, type, row) {
						if($("#buton_refresh_dosage").val() == 1){
							return row.order_medication_dosage_refr ;
						} else {
							return row.order_medication_dosage;
						}
			        },
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "70px"
					
/*				'title' : "Dosierung",
				'data' : "order_medication_dosage",
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "70px"*/
			},
			{
				'title' : "Intervall",
				'data' : "order_medication_dosage_interval",
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "50px"
			},
			{
				'title' : "Kommentar",
				'data' : "order_medication_comments",
				'className' : "gotoDetails",
				'orderable' : true,
				'width' : "100px"
			},
			{
				'title' : "Konzentration",
				'data' : "order_medication_concentration",
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "80px"
			},
			{
				'title' : "Darreichungsform",
				'data' : "order_medication_dosage_form",
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "100px"
			},
			{
				'title' : "Einheit",
				'data' : "order_medication_unit",
				'orderable' : true,
				'className' : "gotoDetails",
				'width' : "60px"
			}

	],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+2000;
			
			var _cbx = "<div class='datatable_cb_row'><label>"
			//+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
			+ "</label>"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_interval]'   value='" + data.order_medication_dosage_interval + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
			+ "</div>";
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
            if(buton_refresh_dsg == 1){
    			var _cbx = "<div class='datatable_cb_row'><label>"
    				//+ Number(dataIndex + 1)
    				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
    				+ "</label>"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
        			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   	   	   value='" + data.order_medication_dosage_refr + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_interval]'   value='" + data.order_medication_dosage_interval + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
    				+ "</div>";
			} 
            
			$("td", row).eq(0).html(_cbx);

		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_bedarfs.ajax.reload();
	}
	
	return projects_datatable_bedarfs;
}

function drawDatatableKrisenMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_krisen = $('#krisen_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "krisen_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				
				
				{
					'title' : "Name / Wirkstoff",
					'data' : "order_medication_name",
				     'render': function (data, type, row) {
				          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
				        },
				    'orderable' : true,
					'className' : "gotoDetails",
					'width' : "380px"
				},			
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : "Dosierung",
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"
						
/*					'title' : "Dosierung",
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"*/
				},
				{
					'title' : "Intervall",
					'data' : "order_medication_dosage_interval",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "50px"
				},
				{
					'title' : "Kommentar",
					'data' : "order_medication_comments",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "100px"
				},
				{
					'title' : "Konzentration",
					'data' : "order_medication_concentration",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "80px"
				},
				{
					'title' : "Darreichungsform",
					'data' : "order_medication_dosage_form",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : "Einheit",
					'data' : "order_medication_unit",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "60px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+3000;
			
			var _cbx = "<div class='datatable_cb_row'><label>"
			//+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
			+ "</label>"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_interval]'   value='" + data.order_medication_dosage_interval + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
			+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
            if(buton_refresh_dsg == 1){
    			var _cbx = "<div class='datatable_cb_row'><label>"
    				//+ Number(dataIndex + 1)
    				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
    				+ "</label>"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   	       value='" + data.order_medication_dosage_refr + "' />"    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_interval]'   value='" + data.order_medication_dosage_interval + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
    				+ "</div>";
            	
			} 
            
			$("td", row).eq(0).html(_cbx);

		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_krisen.ajax.reload();
	}
	
	return projects_datatable_krisen;
}

function drawDatatableIVMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_iv = $('#iv_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "iv_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				
				
				{
					'title' : "Name / Wirkstoff",
					'data' : "order_medication_name",
				     'render': function (data, type, row) {
				          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
				        },
				    'orderable' : true,
					'className' : "gotoDetails",
					'width' : "380px"
				},			
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : "Dosierung",
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"
						
					/*					'title' : "Dosierung",
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"*/	
				},
				{
					'title' : "Kommentar",
					'data' : "order_medication_comments",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "100px"
				},
				{
					'title' : "Konzentration",
					'data' : "order_medication_concentration",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "80px"
				},
				{
					'title' : "Darreichungsform",
					'data' : "order_medication_dosage_form",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : "Einheit",
					'data' : "order_medication_unit",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "60px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+4000;

			var _cbx = "<div class='datatable_cb_row'><label>"
				//+ Number(dataIndex + 1)
				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
				+ "</label>"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
				+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
            if(buton_refresh_dsg == 1){
    			var _cbx = "<div class='datatable_cb_row'><label>"
    				//+ Number(dataIndex + 1)
    				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
    				+ "</label>"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
                	+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage_refr + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
    				+ "</div>";
			} 
            
			$("td", row).eq(0).html(_cbx);
			
		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_iv.ajax.reload();
	}
	
	return projects_datatable_iv;
}

function drawDatatableIsnutritionMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_isnutrition = $('#isnutrition_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "isnutrition_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				
				
				{
					'title' : "Name / Wirkstoff",
					'data' : "order_medication_name",
				     'render': function (data, type, row) {
				          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
				        },
				    'orderable' : true,
					'className' : "gotoDetails",
					'width' : "380px"
				},			
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : "Dosierung",
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"
						
					/*					'title' : "Dosierung",
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"*/	
				},
				{
					'title' : "Kommentar",
					'data' : "order_medication_comments",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "100px"
				},
				{
					'title' : "Konzentration",
					'data' : "order_medication_concentration",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "80px"
				},
				{
					'title' : "Darreichungsform",
					'data' : "order_medication_dosage_form",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : "Einheit",
					'data' : "order_medication_unit",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "60px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+5000;

			var _cbx = "<div class='datatable_cb_row'><label>"
			//+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
			+ "</label>"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
			+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
            if(buton_refresh_dsg == 1){
    			var _cbx = "<div class='datatable_cb_row'><label>"
    				//+ Number(dataIndex + 1)
    				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
    				+ "</label>"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'   value='" + data.order_medication_name + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'   value='" + data.order_medication_drug + "' />"
        			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage_refr + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	   value='" + data.order_medication_comments + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][concentration]'     value='" + data.order_medication_concentration + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage_form]'       value='" + data.order_medication_dosage_form + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][unit]'              value='" + data.order_medication_unit + "' />"
    				+ "</div>";
			} 
			
			$("td", row).eq(0).html(_cbx);
						
			
		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_isnutrition.ajax.reload();
	}
	
	return projects_datatable_isnutrition;
}

function drawDatatableIntervallMedication(aData) 
{
	
	var _aData =  aData || {};	
	
	var projects_datatable_intervall = $('#intervall_medication_list').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : 't',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 50,

		"bFilter" : false,
		"bSort" : true,

		"autoWidth" : true,

		"pagingType" : "full_numbers",

		"scrollX" : true,
		"scrollCollapse" : true,

		'serverSide' : true,
		"bProcessing" : true,

		"stateSave" : false,
		
		'processing' : true,
		"bServerSide" : true,

		"sAjaxSource" : window.location.href,
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			var source_var = sSource;
			var sSource_url = "";
			
			var order_id_str ="";
			if(_aData.order_id){
				order_id_str ="&order_id="+_aData.order_id;
			}
			else if(_aData.step_parent_id){
				order_id_str ="&order_id="+_aData.step_parent_id;
			}
			else if(_aData.parent_id){
				order_id_str ="&order_id="+_aData.parent_id;
			}
			
			
			if(source_var.indexOf("?") == "-1"){
				var sSource_url = sSource+'?patient='+_aData.patient+order_id_str;
			} else {
				var sSource_url = sSource+'&patient='+_aData.patient+order_id_str;
			}
			
			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "intervall_medication_list"
			});
			
			aoData.push({
				"name" : "length",
				"value" : oSettings._iDisplayLength
			});
			aoData.push({
				"name" : "start",
				"value" : oSettings._iDisplayStart
			});

			oSettings.jqXHR = $.ajax({
				"dataType" : 'json',
				"type" : "POST",
				"url" : sSource_url,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		"columns" :  [
				{
					'title' : "",
					'data' : null,
					'name' : "auto-counter",
					'orderable' : false,
					'className' : "",
					'width' : "15px",
				},
				{
					'title' : "debug",
					'data' : "debug",
					'name' : "debugcolumn",
					'visible' : false,
					'orderable' : false,
					'className' : "",
				},
				
				{
					'title' : "patient_drugplan_id",
					'data' : "patient_drugplan_id",
					'visible' : false,
					'className' : "",
				},
				
				
				{
					'title' : "Name / Wirkstoff",
					'data' : "order_medication_name",
				     'render': function (data, type, row) {
				          return row.order_medication_name + '<br>' + row.order_medication_drug + '';
				        },
				    'orderable' : true,
					'className' : "gotoDetails",
					'width' : "300px"
				},			
				{
					//ISPC-2639 pct.1 Lore 23.07.2020
					'title' : "Dosierung",
					'data' : "order_medication_dosage",
				     'render': function (data, type, row) {
							if($("#buton_refresh_dosage").val() == 1){
								return row.order_medication_dosage_refr ;
							} else {
								return row.order_medication_dosage;
							}
				        },
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"
						
					/*					'title' : "Dosierung",
					'data' : "order_medication_dosage",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "70px"*/	
				},
				{
					'title' : "Indikation",
					'data' : "order_medication_indication",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "50px"
				},
				{
					'title' : "Kommentar",
					'data' : "order_medication_comments",
					'className' : "gotoDetails",
					'orderable' : true,
					'width' : "100px"
				},
				{
					'title' : "Intervall (Tage)",
					'data' : "order_medication_days_interval",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "120px"
				},
				{
					'title' : "Intervall zuletzt gestartet",
					'data' : "order_medication_administration_date",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "150px"
				}

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var dataIndex = dataIndex+6000;

			var _cbx = "<div class='datatable_cb_row'><label>"
			//+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
			+ "</label>"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'     value='" + data.order_medication_name + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'     value='" + data.order_medication_drug + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		     value='" + data.order_medication_dosage + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][indication]'   	     value='" + data.order_medication_indication + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	     value='" + data.order_medication_comments + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][days_interval]'       value='" + data.order_medication_days_interval + "' />"
			+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][administration_date]' value='" + data.order_medication_administration_date + "' />"
			+ "</div>";
			
			//ISPC-2639 pct.1 Lore 23.07.2020
			var buton_refresh_dsg = $("#buton_refresh_dosage").val();
            if(buton_refresh_dsg == 1){
    			var _cbx = "<div class='datatable_cb_row'><label>"
    				//+ Number(dataIndex + 1)
    				+ "<input type='checkbox' class='row_select'  name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][patient_drugplan_id]' "+data.selected+"  value='" + data.patient_drugplan_id + "' />"
    				+ "</label>"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_name]'     value='" + data.order_medication_name + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][medication_drug]'     value='" + data.order_medication_drug + "' />"
                	+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][dosage]'   		   value='" + data.order_medication_dosage_refr + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][indication]'   	     value='" + data.order_medication_indication + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][comments]'   	     value='" + data.order_medication_comments + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][days_interval]'       value='" + data.order_medication_days_interval + "' />"
    				+ "<input type='hidden'    name='data[PatientsOrdersMedication]["+Number(dataIndex)+"][administration_date]' value='" + data.order_medication_administration_date + "' />"
    				+ "</div>";
			} 
			
			$("td", row).eq(0).html(_cbx);
						
			
		},
	});

	
	if(_aData.redraw == "1"){
		projects_datatable_intervall.ajax.reload();
	}
	
	return projects_datatable_intervall;
}

