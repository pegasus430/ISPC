/**
 * projects/overview.js 10.05.2018
 */
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
					/*$(ui.panel).html( translate('[Projects Open legend]') );*/
					window.selectedTab = "projects_open";
				break;
				
				case 1:
					/*$(ui.panel).html( translate('[Projects Prepare legend]') );*/
					window.selectedTab = "projects_prepare";
				break;
				
				case 2:
					/*$(ui.panel).html( translate('[Projects Closed legend]') );*/
					window.selectedTab = "projects_closed";
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
	
	
	var _idx = 0;
	
	if (typeof selected_tab != "undefined") {
		switch (selected_tab) {
			case "add_project_comments":
				_idx = 0;
				break;
			case "add_project_files":
				_idx = 1;
				break;
			case "add_project_work":
				_idx = 2;
				break;
			case "add_project_outside_participant":
				_idx = 3;
				break;
		}
	}
	
	//overview edit project
	$("#tabs_project").tabs({
		selected : _idx,
		show : function(event, ui) {			
			if ($.fn.DataTable.isDataTable( $('.datatable', $(ui.panel))) ) {
				$('.datatable', $(ui.panel)).DataTable().columns.adjust();
			}
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
	
	
	drawDatatableCourse();
	
	drawDatatableFiles();

	drawDatatableWork();
	
	drawDatatableOutsideParticipants();
	
	uploader_create(
		$('.qq_file_uploader_placeholder').get(0),
		['*', 'pdf', 'docx', 'doc', 'xml', 'xls', 'csv'],
		window._max_filesize,
		true
	);
	

});




function drawDatatableCourse(){
	
	$('.selector_course_table').DataTable({
		// ADD language
		 "language": {
			 "url" : appbase + "/javascript/data_tables/de_language.json"// +
				// "?-"
				// +
				// getTimestamp()
         },
 		sDom: 't',
		processing: true,
		info: false,
		filter: false,
		paginate: false,
		serverSide: false,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
		
		"columnDefs" : [
		                {"targets": 0, "bVisible": true, "searchable": false, "orderable": false},
		                {"targets": 1, "bVisible": false, "searchable": false, "orderable": true},
		                {"targets": 2, "bVisible": true, "searchable": false, "orderable": true, "iDataSort": 1},
		                {"targets": 3, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 4, "bVisible": true, "searchable": false, "orderable": false}
		                ],
		             
	
 		//order: [[ 1, "asc" ]],
 		
 		"fnDrawCallback" : function(oSettings) {
			$(this).DataTable().column(0, {})
					.column(0, {})
					.nodes()
					.each(
							function(cell, i) {
								cell.innerHTML = oSettings._iDisplayStart
										+ i + 1;
							});

			//$(this).DataTable().columns.adjust();
			
		}
	});
	
}


function drawDatatableFiles(){
	
	$('.selector_file_table').DataTable({
		// ADD language
		 "language": {
			 "url" : appbase + "/javascript/data_tables/de_language.json"// +
				// "?-"
				// +
				// getTimestamp()
         },
 		sDom: 't',
		processing: true,
		info: false,
		filter: false,
		paginate: false,
		serverSide: false,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
		columnDefs: [ 
				       	{ "width": "5%", "targets": 0, "searchable": false, "orderable": false },
				       	{ "width": "15%", "iDataSort": 2, "targets": 1, "searchable": false, "orderable": true },
				       	
				       	{ "width": "0", "bVisible": false,"targets": 2, "searchable": false, "orderable": true },
				       	
				       	{ "width": "40%", "targets": 3, "searchable": false, "orderable": true },
				       	{ "width": "15%", "targets": 4, "searchable": false, "orderable": true },
				       	{ "width": "20%", "targets": 5, "searchable": false, "orderable": true },
				       	{ "width": "5%", "targets": 6, "searchable": false, "orderable": false }
				],
				
 		order: [[ 1, "asc" ]],
 		
 		"fnDrawCallback" : function(oSettings) {
			$(this).DataTable().column(0, {})
					.column(0, {})
					.nodes()
					.each(
							function(cell, i) {
								cell.innerHTML = oSettings._iDisplayStart
										+ i + 1;
							});

			//$(this).DataTable().columns.adjust();
			
		},
	});
	
}


function drawDatatableWork(){
	
	$('.selector_work_table').DataTable({
		// ADD language
		 "language": {
			 "url" : appbase + "/javascript/data_tables/de_language.json"// +
				// "?-"
				// +
				// getTimestamp()
         },
 		sDom: 't',
		processing: true,
		info: false,
		filter: false,
		paginate: false,
		serverSide: false,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
		
		
		"columnDefs" : [
		                {"targets": 0, "bVisible": true, "searchable": false, "orderable": false},
		                {"targets": 1, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 2, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 3, "bVisible": true, "searchable": false, "orderable": true, "iDataSort": 4},
		                {"targets": 4, "bVisible": false, "searchable": false, "orderable": true},
		                {"targets": 5, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 6, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 7, "bVisible": true, "searchable": false, "orderable": true},
		                
		                {"targets": 8,
							'title' : translate('[Project actions]'),
							'data' : null,
							'name' : "actioncolumn",
							'orderable' : false,
							'className' : "",
							'width' : "100px",
			                'defaultContent': '<i class="icon-btn action action_edit" data-btnaction="work-edit" title="'+translate('details')+'"></i>'
			                	+ '<i class="icon-btn action action_delete" data-btnaction="work-delete" title="'+translate('delete')+'"></i>'
						},
						
						{"targets": 9, "bVisible": false, "searchable": false, "orderable": false},
						{"targets": 10, "bVisible": false, "searchable": false, "orderable": false},
						{"targets": 11, "bVisible": false, "searchable": false, "orderable": false},
						{"targets": 12, "bVisible": false, "searchable": false, "orderable": false},
		                
		                ],
		                
 		order: [[ 1, "asc" ]],
 		
 		"fnDrawCallback" : function(oSettings) {
			$(this).DataTable().column(0, {})
					.column(0, {})
					.nodes()
					.each(
							function(cell, i) {
								cell.innerHTML = oSettings._iDisplayStart
										+ i + 1;
							});

			//$(this).DataTable().columns.adjust();
			
		},
		
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			
			//console.log(arguments);
			$('td > i.action', nRow).on('click', function() {
				var btnAction = $(this).data('btnaction');
				switch (btnAction) {
				    case "work-edit":
				    	project_work_add($(this).closest('.ui-tabs-panel').find('.add_new_shv_patient_line'), 'project_work', aData);
				    	break;
				
				    case "work-delete":
				    	deleteProjectEntryDialog($("#project_ID").val(), 'ProjectParticipants', aData[9], 'add_project_work');
				    	break;
				}
		  	});
		}
		
		
	});
	
}

function drawDatatableOutsideParticipants(){

	//ISPC-2608 Dragos added action and id columns
	$('.selector_outside_participants_table').DataTable({
		// ADD language
		 "language": {
			 "url" : appbase + "/javascript/data_tables/de_language.json"// +
				// "?-"
				// +
				// getTimestamp()
         },
 		sDom: 't',
		processing: true,
		info: false,
		filter: false,
		paginate: false,
		serverSide: false,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
		
		
		"columnDefs" : [
		                {"targets": 0, "bVisible": true, "searchable": false, "orderable": false},
		                {"targets": 1, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 2, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 3, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 4, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 5, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 6, "bVisible": true, "searchable": false, "orderable": true},
		                {"targets": 7, "bVisible": true, "searchable": false, "orderable": true},
						{"targets": 13, "bVisible": true, "searchable": false, "orderable": false},
						{"targets": 14, "bVisible": false, "searchable": false, "orderable": false}
		                ],
		                
 		order: [[ 1, "asc" ]],
 		
 		"fnDrawCallback" : function(oSettings) {
			$(this).DataTable().column(0, {})
					.column(0, {})
					.nodes()
					.each(
							function(cell, i) {
								cell.innerHTML = oSettings._iDisplayStart
										+ i + 1;
							});

			//$(this).DataTable().columns.adjust();
			
		},
		//ISPC-2608 Dragos added action buttons
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			//console.log(arguments);
			$('td > i.action', nRow).on('click', function() {
				var btnAction = $(this).data('btnaction');
				switch (btnAction) {
					case "outsideparticipant-edit":
						project_edit_outside_participant($(this).closest('.ui-tabs-panel').find('.add_new_shv_patient_line'), 'project_outside_participants', aData);
						break;

					case "outsideparticipant-delete":
						deleteProjectEntryDialog($("#project_ID").val(), 'ProjectOutsideParticipants', aData[14], 'add_project_outside_participant');
						break;
				}
			});
		}
	});
	
}









//extensions = array[];
function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
  var _cid = window._cid || 0;
  //defaults
  var _max_filesize = 102400000;
  var _allowed_extensions = ['pdf','docx'];
  var _multiple_files = false;
  
  if ( ! $.isNumeric(max_filesize) ) {
      max_filesize = _max_filesize;
  }
  if ( ! $.isArray(allowed_extensions) ) {
      allowed_extensions = _allowed_extensions;
  }
  if (  typeof multiple_files !== "boolean"  ) {
      multiple_files = _multiple_files;
  }
  
  var holderElement, tabname, action_name;
  
  if (typeof holderId === 'object') {
      holderElement = holderId;
  } else {
      holderElement = document.getElementById(holderId);
  }
  
  
  if (holderElement == null) {
      return;//holderId not found
  }
  
  
  var sessionParams = function sessionParams() {
  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_client_files';
		
		var _project_ID = $("#project_ID").val() || 0;
	      
		_action_name += "_" + _project_ID;
	      
		var _params = {
				'_method'	: 'SESSION',
				'action'	: _action_name,
				'date'		: function() {
					return new Date();
				},
				'cid'		: _cid,
		};
		
		return _params;
  };
  
  var deleteParams = function deleteParams() {
	  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_client_files';
		
		var _project_ID = $("#project_ID").val() || 0;
	      
		_action_name += "_" + _project_ID;
	      
		var _params = {
				'_method'	: 'DELETE',
				'action'	: _action_name,
				'date'		: new Date(),
				'cid'		: _cid,
		};
		
		return _params;
  };
  
  var qq_uploader = new qq.FineUploader({
     
	  'debug': false,
	  'maxConnections' : 1,
      'multiple' : multiple_files,
      'element': holderElement,
      'template': 'qq-template',
      
      session : {
      	endpoint : appbase+'misc/clientuploadify',
      	params: sessionParams(),
      },
      
      'request': {
          'customHeaders': {},
          'endpoint': appbase+'misc/clientuploadify',
          'filenameParam': "qqfilename",
          'forceMultipart': true,
          'inputName': "qqfile",
          'method': "POST",
          'params': {
        	  // !! params are overwriten on submit this are for info
              'action'    : 'upload_client_files',
              //'id'        : window.idpd,
              //'tabname'   : holderId,
              'action_name': 0,//holderId,
              'date'      : function() {
                  return new Date();
              },
              'multiple'  : multiple_files,
              //'file_date' : '',
              'upload_and_save' : false,
          },
          'paramsInBody': true,
          'totalFileSizeName': "qqtotalfilesize",
          'uuidName': "qquuid",
      },
      
      
      deleteFile: {            
          enabled: true, // defaults to false
          method: "POST",
          endpoint: appbase+'misc/clientuploadify',
          customHeaders: {},
          params: deleteParams()
      },
      
        
      retry: {
          enableAuto: false
      },
      
      
      validation: {
          allowedExtensions: allowed_extensions,
          sizeLimit: max_filesize
      },
      
      
      messages: {
          typeError: translate('FineUploader_lang')['typeError'],
          sizeError: translate("FineUploader_lang")["sizeError"],
          minSizeError: translate("FineUploader_lang")["minSizeError"],
          emptyError: translate("FineUploader_lang")["emptyError"],
          noFilesError: translate("FineUploader_lang")["noFilesError"],
          tooManyItemsError: translate("FineUploader_lang")["tooManyItemsError"],
          maxHeightImageError: translate("FineUploader_lang")["maxHeightImageError"],
          maxWidthImageError: translate("FineUploader_lang")["maxWidthImageError"],
          minHeightImageError: translate("FineUploader_lang")["minHeightImageError"],         
          minWidthImageError: translate("FineUploader_lang")["minWidthImageError"],
          retryFailTooManyItems: translate("FineUploader_lang")["retryFailTooManyItems"],
          onLeave: translate("FineUploader_lang")["onLeave"],
          unsupportedBrowserIos8Safari: translate("FineUploader_lang")["unsupportedBrowserIos8Safari"],

      },
      
      callbacks: {
    	  
    	  onSessionRequestComplete : function(response, success, rawXhrOrXdr) {
      		if ( ! success) {
      			return;
      		}
      		
      		$.each(response, function(id, file) {
      			
      			var _filename = file.name;
      			_filename = _filename.split('.').slice(0, -1).join('.');
      			
      			$('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(file.uuid);
      			$('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_filename);
      		});
      		
  		},
    	  
        /*  
      	onDelete : function (id) {
      	},
      	
      	onSubmitDelete : function(id) {        		
      	},
      	*/
          onSubmit: function(id, name) {
        	  
        	  $('input[name=btnsubmit]').attr("disabled", true);

		      var el = this._options.element;
		      
		      var file_date = true;
		  
		      //setParams
		      tabname =  $(el).data('tabname') || null;
		      
		      action_name =  $(el).data('action_name') || 'upload_client_files';
		      
		      var _project_ID = $("#project_ID").val() || 0;
		      
		      action_name += "_" + _project_ID;
		      
		      var params = {
		  		'idcidpd'       : window.idcidpd,
		  		//'id'            : window.idpd, // this is for patient files
		  		//'cid'           : _cid,// use this if you link clients
		          'action'        : action_name,
		          'tabname'       : tabname,
		          'date'          : function() {
		              return new Date();
		          },
		          'multiple'      : multiple_files,
		          'file_date'     : file_date,
		          'upload_and_save' : false
		      };
		      
		      this.setParams(params, id);
		      
		      return true;
              
          },
          
          onComplete: function(id, fileName, responseJSON){   
                           
          	$('input[name=btnsubmit]').attr("disabled", false);

              if (responseJSON.success == true){
            	  var _filename = fileName.split('.').slice(0, -1).join('.')
        			
                  $('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
                  $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_filename);
//                  getSize (id)
              } else if ('error' in responseJSON) {
            	  var _error = $.map(responseJSON.error, function(e){
            		    return e;
            	  }).join('; ');
            	  
            	  $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_error);
              }
              
              if (responseJSON.redirect == true){
                  if (typeof responseJSON.redirect_location != 'undefined') {
                      
                  } else {
//                  window.location.reload();//redirect to self
                  }
                  
              }
              
          },
      }
      
      
  });
  
  return qq_uploader;
}




var timer_redraw = null;
var datatableObj = null;
var selectedTab = "projects_open"; //projects_open, projects_prepare, projects_closed;

function redrawDatatable(keep_page) 
{
	
	var _resetPaging = true;
	
	if (keep_page === true) {
		_resetPaging = false;
	}
	
	window.clearTimeout(timer_redraw);
		
	timer_redraw = window.setTimeout(function () {
		window.datatableObj.ajax.reload(null, _resetPaging);
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

			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];
			
			
			aoData.push({
				"name" : "step",
				"value" : "fetch_projects_list"
			});
			aoData.push({
				"name" : "project_status",
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
					'title' : "project_id",
					'data' : "project_ID",
					'visible' : false,
					'className' : "",
				},
				{
					'title' : translate('[Project name]'),
					'data' : "name",
					'orderable' : true,
					'className' : "gotoDetails",
					'width' : "300px"
				},
				{
					'title' : translate('[Project Open from]'),
					'data' : "open_from",
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					'title' : translate('[Project Open till]'),
					'data' : "open_till",
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					"bVisible": false,
					'title' : translate('[Project Prepare from]'),
					'data' : "prepare_from",
					'className' : "gotoDetails",
					'width' : "100px"
				},
				{
					"bVisible": false,
					'title' : translate('[Project Prepare till]'),
					'data' : "prepare_till",
					'className' : "gotoDetails",
					'width' : "100px"
				},
				
				{
					'title' : translate('[Project actions]'),
					'data' : null,
					'name' : "actioncolumn",
					'orderable' : false,
					'className' : "",
					'width' : "100px",
	                'defaultContent': '<i class="icon-btn action action_details" data-btnaction="project-edit" title="'+translate('edit')+'"></i>'
	                	+ '<i class="icon-btn action action_edit" data-btnaction="project-details" title="'+translate('details')+'"></i>'
	                	+ '<i class="icon-btn action action_export" data-btnaction="project-export" title="'+translate('[Export Project Details]')+'"></i>'
	                	+ '<i class="icon-btn action action_delete" data-btnaction="project-delete" title="'+translate('delete')+'"></i>'
				},
				
				

		],
		"order" : [[ 3, "asc" ]],

		"fnDrawCallback" : function(oSettings) {

			$('input:checkbox.select_all', $(this).parents('#projects_dtable_wrapper') ).prop("checked", false);
			
			$(this).DataTable().column("actioncolumn:name")
			.nodes()
			.each(function(cell, i) {
				if (window.selectedTab == "projects_closed") {
					$('i.action_delete, i.action_edit').hide();
				} else {
					$('i.action_delete, i.action_edit').show();
				}
			});
			
			//$(this).DataTable().columns.adjust();
			
		},
		
		"createdRow": function( row, data, dataIndex ) {
			var _cbx = "<div class='datatable_cb_row'><label>"
			+ Number(dataIndex + 1)
			+ "<input type='checkbox' class='row_select' value='" + data.project_ID + "' />"
			+ "</label></div>";
			
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

			var _btn = '<input disabled=true type="button" onclick="gotoProjectExport()" value="' + translate('[Export Project Details]') + '"/>';
			$('#bottom_export').append($(_btn)).addClass("clearer");
			
		},
		
		"headerCallback": function( thead, data, start, end, display ) {
			
			$("th", thead).on('change', 'input.row_select', function (event) {
			    if ($(this).is(":checked")) {
					$("#bottom_export > input:button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", false);
					$('input:checkbox.row_select', $(this).parents('#projects_dtable_wrapper') ).prop("checked", true);
				} else {
					$("#bottom_export > input:button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", true);
					$('input:checkbox.row_select', $(this).parents('#projects_dtable_wrapper') ).prop("checked", false);
				}
		    });
			
		},
		
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			
			
			$("td", nRow).on('change', 'input.row_select', function (event) {
			    if ($(this).is(":checked")) {
					$("#bottom_export > input:button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", false);
				} else {
					$('input:checkbox.select_all', $(this).parents('#projects_dtable_wrapper') ).prop("checked", false);
					if ( ! $('input.row_select:checkbox:checked', $(this).parents('#projects_dtable_wrapper') ).length) {
						$("#bottom_export > input:button", $(this).parents('#projects_dtable_wrapper')).prop("disabled", true);
					}
				}
		    });
			
			
			$('td.gotoDetails', nRow).on('click', function() {
				gotoProjectDetails(aData.project_ID);
			});
			
			$('td > i.action', nRow).on('click', function() {
				var btnAction = $(this).data('btnaction');
				switch (btnAction) {
				    case "project-edit":
				    	newProjectDialog(aData);
				    	break;
				
				    case "project-delete":
				    	deleteProjectDialog(aData);
				    	break;
				    
				    case "project-details":
				        gotoProjectDetails(aData.project_ID);
				    	break;				    	
				    	
				    case "project-export":
				    	gotoProjectExport(aData.project_ID);
				    	break;
				}
		  	});
		}
						
	});

	return projects_datatable;
}



function gotoProjectExport ( project_ID ) {
	var _project_ID = project_ID || 0;
	
	var _chkArray = [];
	
	if (_project_ID == 0) {

		$('input.row_select:checked', $('#projects_dtable_wrapper')).not('.select_all').each(function() {
			_chkArray.push($(this).val());
		});
		_project_ID = _chkArray.join(',') ;
	}

	window.open(appbase + "projects/overview?step=export_project&project_ID=" + _project_ID, '_blank');
}


function gotoProjectDetails ( project_ID ) {
	window.location.href = appbase + "projects/overview?step=view_project&project_ID=" + project_ID;	
}

function deleteProjectDialog( aData ) {
	
	var _aData =  aData || {};
	
	jConfirm(translate('[Are you sure you want to delete this project <br/> %1% ?]', "<strong>" + _aData.name + "</strong>"), translate('[Delete Project]'), function(r) {
		if (r) {
			window.location.href = appbase + "projects/overview?step=delete_project&project_ID=" + _aData.project_ID ;
		}
	});
		
}

		
function deleteProjectEntryDialog ( _project_ID, _type, _row_ID , _selected_tab) {
	
	jConfirm(translate('[Are you sure you want to delete this entry ?]'), translate('[Delete Project Entry]'), function(r) {
		if (r) {
			window.location.href = appbase + "projects/overview?step=delete_project_entry"
			+ "&project_ID=" + _project_ID
			+ "&type=" + _type
			+ "&row_ID=" + _row_ID
			+ "&selected_tab=" + _selected_tab
			;
		}
	});
}


//this fn is also for edit
function newProjectDialog( aData ) {
	
	var _aData =  aData || {};
	
	$('#new_project_dialog').dialog({

		dialogClass : "newProjectDialog",
		modal : true,
		autoOpen : true,
		closeOnEscape : true,
		title : translate('[Create New Project]'),
		minWidth : 560,
		minHeight : 300,
		
		open : function() {
			$("#name", this).val(_aData.name);
			$("#open_from", this).val(_aData.open_from);
			$("#open_till", this).val(_aData.open_till);
			/*
			$("#prepare_from", this).val(_aData.prepare_from);
			$("#prepare_till", this).val(_aData.prepare_till);
			*/
			$("#description", this).val(_aData.description);
			$("#project_ID", this).val(_aData.project_ID);
			
			$("#error_messages", this).html('');
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
								"step" : 'add_new_project',
								"name" :  $("#name", $(this).dialog()).val(),
								"open_from" :  $("#open_from", $(this).dialog()).val(),
								"open_till" :  $("#open_till", $(this).dialog()).val(),
								/*
								"prepare_from" :  $("#prepare_from", $(this).dialog()).val(),
								"prepare_till" :  $("#prepare_till", $(this).dialog()).val(),
								*/
								"description" :  $("#description", $(this).dialog()).val(),
								"project_ID" :  $("#project_ID", $(this).dialog()).val(),
							};

							$.ajax({
								"dataType" : 'json',
								"type" : "POST",
								"url" : window.location.href, //appbase + "projects/overview",
								"data" : _post_data,
								"success" : function(data) {
						            if (data.success == true) {
						            	$(_this_button).dialog("close");
						            	redrawDatatable(true);
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
										alert(translate('btmbuchhistory_lang')["only positive amount"]);
									}, 50);
						}

					},

				},
				

		],

		
	});
}



/* TODO .. this html should pe placed directly, like in the first 2 tabs */
function project_work_add (_target, parent_form , _data) {

	console.log(_target);
	$.get(appbase + 'ajax/createformprojectworkadd?parent_form='+parent_form, function(result) {

//		var newFieldset =  $(result).insertBefore($(_target).parents('tr')).addClass('add_new_shv_patient_line');
		var newFieldset =  $(result).insertBefore($(_target)).addClass('add_new_shv_patient_line');
//		var newFieldset =  $(_target).replaceWith(result).addClass('add_new_shv_patient_line');
		
		$('.date', $(_target).closest('table'))
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
		
		try {
			$('.livesearchFormEvents').livesearchUnifiedProvider({
				'limitSearchResults'	: 50,
				'limitSearchGroups'		: ['user', 'voluntaryworker']
			});
		}catch (e) {
			//console.log(e);
		}
		
		
		$('button:submit', $(_target).closest('table')).on("click", function () {
			
			var _textarea = $("textarea[name$='[work_description]']");
			
			if (_textarea.val() == '') {
				
				if ($("div.ErrorDiv", $(_target).closest('table')).length) {
					$("div.ErrorDiv").html(translate('[Please describe the work you have done]'));
				} else {
					$( "<div class='ErrorDiv'>" + translate('[Please describe the work you have done]') + "</div>" ).insertBefore( _textarea );
				}
				
				return false;
				
			} else {
				return true;
			}
			
		});
		
		
		if (typeof(_data) !== 'undefined' && typeof(_data) == 'object') {
			
			//console.log(_data);
			
			var _pid = _data[9],
			_participant_type = _data[10],
			_participant_id = _data[11],
			_participant_name = _data[12],
			_work_date = _data[3],
			_work_duration = _data[5], //dauer?
			_work_driving_distance = _data[6],
			_work_driving_time = _data[7],
			_work_description = _data[2];
			
			
			$(newFieldset).find("input[name*='project_participant_ID']").val(_pid);
			$(newFieldset).find("input[name*='participant_type']").val(_participant_type);
			$(newFieldset).find("input[name*='project_ID']").val(0);
			$(newFieldset).find("input[name*='participant_id']").val(_participant_id);
			$(newFieldset).find("input[name*='participant_name']").val(_participant_name);
			$(newFieldset).find("input[name*='work_date']").val(_work_date);
			$(newFieldset).find("input[name*='work_duration']").val(_work_duration);
			$(newFieldset).find("input[name*='work_driving_distance']").val(_work_driving_distance);
			$(newFieldset).find("input[name*='work_driving_time']").val(_work_driving_time);
			$(newFieldset).find("textarea[name*='work_description']").val(_work_description);
			
			//console.log(newFieldset);
		}
		
//		$(_target).hide();
		$(_target).remove();
		
	});
}



function project_add_outside_participant (_target, parent_form) {

	$.get(appbase + 'ajax/createformoutsideparticipant?parent_form='+parent_form, function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parents('tr'));
		
		try {
			$('.livesearchFormEvents').livesearchCityZipcode({
				'limitSearchResults'	: 50,
			});
			$('.livesearchFormEvents').livesearchUnifiedProvider({
				'limitSearchResults'	: 50,
				'limitSearchGroups'		: ['voluntaryworker', 'member']
			});
		}catch (e) {
			//console.log(e);
		}
		
		$(_target).hide();
		
	});
}

//ISPC-2608 Dragos edit function
function project_edit_outside_participant (_target, parent_form, _data) {

	$.get(appbase + 'ajax/createformoutsideparticipant?parent_form='+parent_form, function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parents('tr'));

		try {
			$('.livesearchFormEvents').livesearchCityZipcode({
				'limitSearchResults'	: 50,
			});
			$('.livesearchFormEvents').livesearchUnifiedProvider({
				'limitSearchResults'	: 50,
				'limitSearchGroups'		: ['voluntaryworker', 'member']
			});
		}catch (e) {
			//console.log(e);
		}

		if (typeof(_data) !== 'undefined' && typeof(_data) == 'object') {

			//console.log(_data);

			var _project_outside_participant_ID = _data[14],
				_first_name = _data[1],
				_last_name = _data[2],
				_title_prefix = _data[3],
				_title_suffix = _data[4],
				_salutation = _data[5],
				_street = _data[6]
				_zip = _data[7],
				_city = _data[8],
				_email = _data[9];
				_mobile = _data[10];
				_phone = _data[11];
				_comment = _data[12];



			$(newFieldset).find("input[name*='project_outside_participant_ID']").val(_project_outside_participant_ID);
			$(newFieldset).find("input[name*='first_name']").val(_first_name);
			$(newFieldset).find("input[name*='last_name']").val(_last_name);
			$(newFieldset).find("input[name*='title_prefix']").val(_title_prefix);
			$(newFieldset).find("input[name*='title_suffix']").val(_title_suffix);
			$(newFieldset).find("input[name*='salutation']").val(_salutation);
			$(newFieldset).find("input[name*='zip']").val(_zip);
			$(newFieldset).find("input[name*='city']").val(_city);
			$(newFieldset).find("input[name*='email']").val(_email);
			$(newFieldset).find("input[name*='mobile']").val(_mobile);
			$(newFieldset).find("textarea[name*='phone']").val(_phone);
			$(newFieldset).find("textarea[name*='comment']").val(_comment);

			//console.log(newFieldset);
		}

		$(_target).remove();

	});
}
