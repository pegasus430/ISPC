;
/* dgpfullpatientlist.js */
//ISPC-2474 Ancuta 23.10.2020

$(document).ready(function() {
	//overview main page	
	$("#tabs_projects").tabs({

	    select: function(event, ui) {
	        $('#layout_result_messages').hide();
	    },
	
		show : function(event, ui) {

			var selected_tab = ui.index;

			window.selectedTab = "ready_to_send";//completed but not send

			if ( ! $.fn.DataTable.isDataTable( $('.datatable', $(ui.panel))) && window.datatableObj === null) {
				window.datatableObj = drawDatatableProjects();
				//$('.datatable', $(ui.panel)).DataTable().columns.adjust();
			} else {
				redrawDatatable();
			}
				
		}
	});	
	
	
	$("#user_pass_validation").dialog({
		autoOpen: false,
		resizable: false,
		height: 200,
		scroll: true,
		width: 350,
		modal: true,
		
		buttons: [
			{
				text: translate('save'),
				click: function() {
					// validate pass 
					
					
					
					$.ajax({
						"dataType" : 'json',
						"type" : "POST",
						"url" : appbase + "client/patientpermanentdeletion",
						"data" : {
							step : 'validate_user_pass',
							user_password : $("#user_password").val()
						},
						"success" : function (data) {
							
							if (data.success == "1") {
								
								gotoScheduleDelete();
								
							} else {
								setTimeout(function (){alert(data.message);}, 50);
							}
						}
					});
					
//				
//				
//				
//				var val_data = $("#validate_pass form").serialize();
//				 $.ajax({
//					 type: 'POST',
//					 url: 'client/events?action=validate_form&form=customeventsave&patid='+pid,
//					 data: val_data,
//					 success:function(data){
//						 var response = jQuery.parseJSON(data);
//						 if(response.success == false){
//							 alert(response.error);
//							 return;
//							 
//						 } else if(response.success == true){
//							 
//						 }
//					 }
//				 })
					
					
					
//				$(this).dialog("close");
				}
			},
			
			{
			text: translate('cancel'),
			click: function() {
				$(this).dialog("close");
			}
		}


	]
 
	});
	
	
});




var timer_redraw = null;
var datatableObj = null;
var selectedTab = "submited"; //submited, notsubmited, ready_to_send;

function redrawDatatable(keep_page) 
{
	
	var _resetPaging = true;
	
	if (keep_page === true) {
		_resetPaging = false;
	}
	
	window.clearTimeout(timer_redraw);
		
	timer_redraw = window.setTimeout(function () {
		window.datatableObj.search('').ajax.reload(null, _resetPaging);
	}, 100);
	
}


function drawDatatableProjects() 
{
	var projects_datatable = $('#projects_dtable').DataTable({
		// ADD language
		"language" : {
			"url" : appbase + "/javascript/data_tables/de_language.json"// +
																// "?-"
																// +
																// getTimestamp()
		},
		sDom : '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'
				+ 't'
				+ '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"ip<"#bottom_export">>',

		"lengthMenu" : [ [ 10, 50, 100 ], [ 10, 50, 100 ] ],

		"pageLength" : 10,

		// "bLengthChange": false,

		"bFilter" : true,
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

			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "_fetch_patients_list"
			});
			aoData.push({
				"name" : "patient_dgp_status",
				"value" : window.selectedTab
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
				"url" : sSource,
				"data" : aoData,
				"success" : fnCallback
			});
		},
		
		
		
		columns : [
				{
					'title' : "#",
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
					'title' : translate('epid'),
					'data' : "epid",
					'orderable' : true,
					'className' : "",
					'width' : "40px"
				},
				{
					'title' : translate('last_name'),
					'data' : "last_name",
					'orderable' : true,
					'className' : "",
					'width' : "100px"
				},
				{
					'title' : translate('first_name'),
					'data' : "first_name",
					'className' : "",
					'width' : "100px"
				},
				{
					'title' : translate('discharge_date'),
					'data' : "discharge_date",
					'className' : "",
					'orderable' : false,
					'width' : "200px"
				},
				{
					'title' : translate('last_action_date'),
					'data' : "last_action_date",
					'className' : "",
					'orderable' : false,
					'width' : "200px"
				},
				{
					'title' : "<input type='checkbox' class='row_select select_all'/> "+translate('scheduled_deletion'),
					'data' : null,
					'name' : "scheduled_deletion",
					'orderable' : false,
					'className' : "editDgpKern",
					'width' : "100px",
				},
 
				{
					'title' : translate('scheduled_date'),
					'data' : "scheduled_date",
					'className' : "",
					'orderable' : false,
					'width' : "200px"
				},
				
				{
					'title' : translate('scheduled_user'),
					'data' : "scheduled_user",
					'className' : "",
					'orderable' : false,
					'width' : "200px"
				},
				
				

		],
		"order" : [[ 3, "asc" ]],

		
		
		"fnDrawCallback" : function(oSettings) {

			var _api = this.api();
			
//				_api.column("auto-counter:name").visible(true);
				
				$(_api.table().header())
				.find('input:checkbox.select_all')
				.prop("checked", false)
				.show();
				
				$(_api.table().container())
				.find('#bottom_export')
				.show();
				
				$('input:checkbox.select_all', $(_api.table().header())).prop("checked", false).show();
				
				$('#bottom_export', $(_api.table().footer())).show();
 
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			
			var  checked = "";
			if(data.scheduled === true){
				checked = 'checked="checked"';
			}
			
			var _cbx = "<div class='datatable_cb_row'><label>"
				+ "&nbsp;";
				_cbx += "<input type='checkbox' class='row_select' value='" + data.idpd + "'  "+checked +" />";
			
			_cbx += "</label></div>";
			$("td", row).eq(6).html(_cbx);	
			
			
			
			
			var _start = $(this).DataTable().settings()[0]._iDisplayStart;
			var _nrx = "<div class='datatable_cb_row'><label>"
				+ Number(_start + dataIndex + 1)
				+ "&nbsp;";
 
			
			_nrx += "</label></div>";
			
			$("td", row).eq(0).html(_nrx);	
			
			
			
			
		},

		"initComplete" : function(settings, json) {
			// debug column
//			if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
//				var _column = $(this).DataTable().column("debugcolumn:name");
//				if (_column) {
//					_column.visible(true);
//				}	
//			}
			var _btn = '<button disabled="true" type="submit" class="ui-state-default ui-state-active ui-corner-all" style="padding:5px"  type="button" onclick="checkScheduleDelete()" value="schedule_delete"> ' + translate('schedule patients for delete') +'</button>';
			$('#bottom_export').append($(_btn)).addClass("clearer");
			
		},
		
		"headerCallback": function( thead, data, start, end, display ) {
			
			$("th", thead).on('change', 'input.row_select', function (event) {
			    if ($(this).is(":checked")) {
					$("#bottom_export > button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", false);
					$('input:checkbox.row_select', $(this).parents('#projects_dtable_wrapper') ).prop("checked", true);
				} else {
					$("#bottom_export > button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", true);
					$('input:checkbox.row_select', $(this).parents('#projects_dtable_wrapper') ).prop("checked", false);
				}
		    });
			
		},
		
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			
			
			$("td", nRow).on('change', 'input.row_select', function (event) {
			    if ($(this).is(":checked")) {
					$("#bottom_export > button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", false);
				} else {
					$('input:checkbox.select_all', $(this).parents('#projects_dtable_wrapper') ).prop("checked", false);
					if ( ! $('input.row_select:checkbox:checked', $(this).parents('#projects_dtable_wrapper') ).length) {
						$("#bottom_export > button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", true);
					}
				}
		    });
			
			$('td > i.icon-btn', nRow)
			.addClass(aData.register_status)
			.on('click', function() {
				//aData.register_status
				gotoPatientDetails(aData.idpd);
				
		  	});
		}
						
	});

	return projects_datatable;
}


function gotoPatientDetails ( patient_id ) {
	//'<a href="' . APP_BASE . 'patientnew/hospizregisterv3?id=' . $vw_data['encrypt_id'] . '" class="register_icon '.$status.' " title="'.$this->view->translate('register status '.$status).'" ></a>';

//	window.location.href = appbase + "patient/hospizregisterv3?id=" + patient_id;	
}


function checkScheduleDelete () {
	var proceed =  false;
	
	var confdel = translate('Are you sure you want to schedule patient for deletion!???');
	var  conftitle =translate('pd_attention');
	jConfirm(confdel, conftitle, function(r) {
		if(r)
		{	
			$('#user_pass_validation').dialog('open');
			
		} else{
			
			proceed = false;
			return;
		}
	});
	
}


function gotoScheduleDelete () {
 
	var _chkArray = [];
	var _unchkArray = [];
//
//	$('input.row_select:checked', $('#projects_dtable_wrapper')).not('.select_all').each(function() {
//		_chkArray.push($(this).val());
//	});
	
	$('input.row_select', $('#projects_dtable_wrapper')).not('.select_all').each(function() {
		if($(this).is(':checked')){
			_chkArray.push($(this).val());
		} else {
			_unchkArray.push($(this).val());
		}
	});
	

	$.ajax({
		"dataType" : 'json',
		"type" : "POST",
		"url" : appbase + "client/patientpermanentdeletion",
		"data" : {
			step : 'schedule_delete',
			idpd : _chkArray,
			idpd_uncheck : _unchkArray
		},
		"success" : function (data) {
			
			if (data.success) {
				
				$('#user_pass_validation').dialog('close');
				redrawDatatable();
				
			} else {
				setTimeout(function (){alert(data.message);}, 50);
			}
		}
	});
}
