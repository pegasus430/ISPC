;
/* dgpfullpatientlist.js */

$(document).ready(function() {
	
	//overview main page	
	$("#tabs_projects").tabs({

	    select: function(event, ui) {
	        $('#layout_result_messages').hide();
	    },
	
		show : function(event, ui) {

			var selected_tab = ui.index;

			switch (ui.index) {

				case 0:
					window.selectedTab = "submited";
				break;
				
				case 1:
					window.selectedTab = "not-completed";
				break;
				
				case 2:
					window.selectedTab = "ready_to_send";//completed but not send
				break;

				default:
				break;

			}
			

			if ( ! $.fn.DataTable.isDataTable( $('.datatable', $(ui.panel))) && window.datatableObj === null) {
				window.datatableObj = drawDatatableProjects();
				//$('.datatable', $(ui.panel)).DataTable().columns.adjust();
			} else {
				redrawDatatable();
			}
				
		}
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
				"value" : "fetch_patients_list"
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
					'title' : "<input type='checkbox' class='row_select select_all'/>",
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
					'title' : translate('admission_date') + " - " +translate('discharge_date'),
					'data' : "admission_discharge",
					'className' : "",
					'orderable' : false,
					'width' : "200px"
				},
				{
					'title' : translate('register_status'),
					'data' : null,
					'name' : "actioncolumn",
					'orderable' : false,
					'className' : "editDgpKern",
					'width' : "100px",
	                'defaultContent': '<i class="icon-btn block_32x32 " data-btnaction="project-edit" title="'+translate('edit')+'"></i>'
				},
				
				

		],
		"order" : [[ 3, "asc" ]],

		
		
		"fnDrawCallback" : function(oSettings) {

			var _api = this.api();
			
			if (window.selectedTab == 'ready_to_send') {
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
				
			} else {
//				_api.column("auto-counter:name").visible(false);
				
				$(_api.table().header())
				.find('input:checkbox.select_all')
				.prop("checked", false)
				.hide();
				
				$(_api.table().container())
				.find('#bottom_export')
				.hide();
				
			}
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			
			var _start = $(this).DataTable().settings()[0]._iDisplayStart;
			var _cbx = "<div class='datatable_cb_row'><label>"
				+ Number(_start + dataIndex + 1)
				+ "&nbsp;";
			
			if (window.selectedTab == 'ready_to_send') {
				_cbx += "<input type='checkbox' class='row_select' value='" + data.idpd + "' />";
			}
			
			_cbx += "</label></div>";
			
			$("td", row).eq(0).html(_cbx);	
		},

		"initComplete" : function(settings, json) {
			// debug column
//			if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
//				var _column = $(this).DataTable().column("debugcolumn:name");
//				if (_column) {
//					_column.visible(true);
//				}	
//			}
			var _btn = '<button disabled="true" type="submit" class="ui-state-default ui-state-active ui-corner-all" style="padding:5px"  type="button" onclick="gotoDgpExport()" value="dgp_send"> ' + translate('export_dgp_xml') +'</button>';
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
				gotoDgpKernDetails(aData.idpd);
				
		  	});
		}
						
	});

	return projects_datatable;
}


function gotoDgpKernDetails ( patient_id ) {
	//'<a href="' . APP_BASE . 'patientnew/hospizregisterv3?id=' . $vw_data['encrypt_id'] . '" class="register_icon '.$status.' " title="'.$this->view->translate('register status '.$status).'" ></a>';

	window.location.href = appbase + "patientnew/hospizregisterv3?id=" + patient_id;	
}


function gotoDgpExport () {
	
	var _chkArray = [];

	$('input.row_select:checked', $('#projects_dtable_wrapper')).not('.select_all').each(function() {
		_chkArray.push($(this).val());
	});

	$.ajax({
		"dataType" : 'json',
		"type" : "POST",
		"url" : appbase + "dgp/dgpfullpatientlist",
		"data" : {
			step : 'dgp_send',
			idpd : _chkArray
		},
		"success" : function (data) {
			
			if (data.success) {
				
				redrawDatatable();
				
			} else {
				setTimeout(function (){alert(data.message);}, 50);
			}
		}
	});
	
}
