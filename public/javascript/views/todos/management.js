// ISPC-2160


var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'suppliers/deletesuppliers?id=';
var ajaxurl =appbase + 'todos/management';
var left_menu_list; // this will be the datatable object


var vwlist = {};
var tabsid2string = new Array("user_undone", "client_undone","completed");
var searchWait = 0;
var searchWaitInterval;
	
function dTable(){
	var tabsid2string = new Array("user_undone", "client_undone","completed");
	var colOrder = {};
	var $clone = $('#todos_table').clone();
	$($clone)
			.append( "<input type='hidden' value='"+$(this).attr('rel')+"' name='custom_icons["+$(this).attr('rel')+"]'/>" )
			.attr('id', 'todos_table_clone')
			.insertAfter('#todos_table');
	($clone).show();
	
	var vwlist = $('#todos_table_clone').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
        
 		sDom: 
			//'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"<"#top_filters">C>' +
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"Clfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
			
		
//		"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
		"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
			
		processing: true,
 		
		"fnDrawCallback": function ( oSettings ) {
			$(oSettings.nTHead).show();
		},

		info: false,
		filter: true,
		paginate: true,

		serverSide: true,
		'responsive': false,
		
		"autoWidth": true,

		"stateSave": true,

		"scrollX": true,
		"scrollCollapse": true,
		
		"stateLoadCallback": function (settings)
		{
			o = false;
			var tab =  $("#todos_tabs").tabs('option', 'selected');
			if (tab === null || typeof(tab)==='object') {tab=0;}
			 $.ajax( {
				 url: appbase+'user/loadtablepref',
				method: "POST",
			    "async": false,
			    data: { page: "todos" ,  'tab' :  tab},
			    "dataType": "json",
			    "success": function (json) {
					if (typeof(json) === 'object' && !$.isEmptyObject(json)){
						colOrder = json.colOrder;
					}
			        o = json;
			    }
			 });
			 
			 return o;
		},
		

 		colVis: {
			buttonText: "Spalten ein-/ausblenden",
			//"showAll": "Show all",
			stateChange: function ( iColumn, bVisible )
			{
				var new_col_idx = -1;
				try{
					var oSettings = vwlist.settings()[0];
					new_col_idx = oSettings.aoColumns[iColumn]._ColReorder_iOrigCol;		
				}
				finally{
					if (new_col_idx == -1){
						new_col_idx = iColumn;
					}
				}
				
				var tab =  $("#todos_tabs").tabs('option', 'selected');
				
				$.ajax({
					url: appbase+'user/savetablesettings',
					method: "POST",					
					dataType: "json",
					data: { page: "todos", column: new_col_idx, visible: bVisible, 'tab' :  tab}
					
				});
			},
			
			sAlign : 'left',
		}, 
		
 		
		
		
 		colReorder: {
 			realtime : false,

			fixedColumnsLeft: 1,
			fixedColumnsRight: 1,
			
 			reorderCallback: function ()
			{
				var cols_order = $.fn.dataTable.ColReorder( vwlist ).fnOrder();
							
				var columns_arr = new Array();
				$(cols_order).each(function(column_order,column_id) {
					columns_arr.push({
						c: column_id, 
						o: column_order 
			        });
				});
				var tabsid2string = new Array("user_undone", "client_undone","completed");
				var tab =  $("#todos_tabs").tabs('option', 'selected');
				
				$.ajax({
					url: appbase+'user/savetablesettingsorder',
					method: "POST",					
					dataType: "json",
					data: { 'tab': tab , page: "todos", columns: JSON.stringify(columns_arr) }
					
				});
			}
			
		}, 
		
		columns: [
		          { data: "action",  className: "select_todo", "name":"select", "searchable": false, "orderable": false },
		          { data: "patient_data", className: "", "orderable": false},
		          { data: "todo_text",  className: "","orderable": false},
		          { data: "assigneduser",  className: ""},
		          { data: "assignedgroup",  className: ""},
		          { data: "due_date", className: ""},
		          { data: "complete_date",  className: ""},
		          { data: "complete_user",  className: ""},
		          { data: "complete_comment",  className: ""},
			],
			
			
		columnDefs: [ 
		//moved into columns the searchable and orderable for 1, -1, -2
				       	//{ "targets": "select_todos", "searchable": false, "orderable": false },
				       //	{ "targets": 1, "searchable": true, "orderable": false },
				       //	{ "targets": 4,  "visible": false, "searchable": false, "orderable": false },
				       //	{ "targets": 4,  "visible": false, "searchable": false, "orderable": false },
				      	
				      	//{ "targets": -2, "searchable": false, "orderable": false },
				       	//{ "targets": -1, "searchable": false, "orderable": false }
				],
				
 		order: [[ 5, "desc" ]],
		
		
 		
 		
 		
 		
 		ajax: {
			url: ajaxurl,
			type: 'POST',
		    "data": function ( d ) {
				var tab =  $("#todos_tabs").tabs('option', 'selected');
				d.tab = tabsid2string[tab];
				
				if($('#exclude_discharged').prop( "checked" )){
					d.exclude_discharged= "1";
				} else{
					d.exclude_discharged= "0";
				}
				if($('#exclude_dead').prop( "checked" )){
					d.exclude_dead= "1";
				} else{
					d.exclude_dead= "0";
				}
		    },
			complete: function (data, text) {
         		$('#todos_table_clone').show();
				$("body").removeClass("loading");
				$("body").removeClass("body-overlay");
     		},
			error : function(e){
				
			}
			
			
		}, 
		searchDelay: 500,
			
		initComplete: function(oSettings)
		{
			
			// @TODO: move this reorder so it dosen't get re-called every time table is reloaded 
			if (typeof(colOrder) === 'object' && !$.isEmptyObject(colOrder)){
				try{
					$(this).DataTable().colReorder.order( colOrder );
				}catch(e){}
			}

			var export_buttons = $('.export_buttons').clone();
			export_buttons.appendTo("#bottom_export").show();
			
			$('.dataTables_filter input')
			.unbind() 
			.bind("input", function (e) {
				var item = $(this);
				searchWait = 0;
				if (this.value.length >= 2 || this.value == "") {
					if(!searchWaitInterval) searchWaitInterval = setInterval(function(){
						if(searchWait>=3){
							clearInterval(searchWaitInterval);
							searchWaitInterval = '';
							searchTerm = $(item).val();
							$("body").addClass("loading");
							vwlist.search(searchTerm).draw();						
							searchWait = 0;
						}
						searchWait++;;
					},100);
				}
				
			})
			.bind("keydown", function (e) {
				if (e.keyCode == 13){
					//Search when user presses Enter
					clearInterval(searchWaitInterval);
					$("body").addClass("loading");
					vwlist.search($(this).val()).draw();
				}
			
			});
			
		}
		
	});
	

	// @TODO: move this event inside the dataTable initialization
	$('#todos_table_clone').on( 'length.dt', function ( e, settings, len ) {
		var tab =  $("#todos_tabs").tabs('option', 'selected');
		$.ajax({
			url: appbase+'user/savetablesettingslength',
			method: "POST",					
			dataType: "json",
			data: { 'tab': tab , page: "todos", length: len }			
		});
	});

	
	return vwlist;
}






function mark_event_done(event_id, event_tabname, event_rowid, event_done_date, filter, event_extra, event_comment)
{
	var url =appbase + 'todos/todosactions';
	var url_str = "";
	
	if(event_extra){
		url_str = url + '?mode=done&eventid=' + event_id + '&tabname=' + event_tabname + '&donedate=' + event_done_date+ '&extra=' + event_extra+ '&event_comment=' + event_comment;
	} else{
		url_str = url + '?mode=done&eventid=' + event_id + '&tabname=' + event_tabname + '&donedate=' + event_done_date+ '&event_comment=' + event_comment;
	}
	
	xhr = $.ajax({
		url: url_str,
		success: function(response) {
			if($("#done_event_"+event_rowid).length != 0)
			{
			$("#done_event_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();
			}
			else if($("#med_approv_"+event_rowid).length != 0)
			{
				$("#med_approv_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();
			}
			else if($("#pump_approv_"+event_rowid).length != 0)
			{
			$("#pump_approv_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();	
			}
			else if($("#med_decl_"+event_rowid).length != 0)
			{
				$("#med_decl_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();
			}
			else if($("#pump_decl_"+event_rowid).length != 0)
			{
			$("#pump_decl_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();	
			}
			//$('tr#d_row_' + event_rowid).stop().effect("highlight", {}, 2000).stop().hide();
		}
	});
}

function mark_event_undone(event_id, event_rowid)
{
	var tabname = $('#tabname_' + event_rowid).val()

	var url =appbase + 'todos/todosactions';
	
	xhr = $.ajax({
		url: url + '?mode=undone&eventid=' + event_id + '&tabname=' + tabname,
		success: function(response) {
			
			$("#undone_event_"+event_rowid).closest('tr').stop().effect("highlight", {}, 2000).stop().hide();
			//$('tr#hist_row_' + event_rowid).stop().effect("highlight", {}, 2000).stop().hide();
			$('#loading_div_'+ event_rowid).show();
		}
	});
}
 






vwlist = dTable();


var table = {}; 


//DATATABLE
function drawDatatable() { }

var timer_redraw;

function redrawDatatable( keep_page ){
	$("body").addClass("loading");
	var resetPaging = true;
	
	if (keep_page === true) {
		resetPaging = false;
	}
	
	window.clearTimeout(timer_redraw);
		
	timer_redraw = window.setTimeout(function () {
		window.vwlist.ajax.reload(null, resetPaging);
	},800);
	
	
}



$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	// set default tab on pageload
	if(!Array.prototype.indexOf){
		Array.prototype.indexOf = function(val){
			var i = this.length;
			while (i--) {
				if (this[i] == val) return i;
			}
			return -1;
		} 
	}
	var tabs_index = 0;
//	<?php
//		if (isset($_GET['tab'])) :
//			echo "tabs_index = tabsid2string.indexOf('".htmlspecialchars($_GET['tab'])."');";
//		endif;
//	?>
	if (tabs_index == -1){
		tabs_index = 0;
	}
	var $tabs = $( "#todos_tabs" ).tabs({
		selected: tabs_index,		
	});
 
	
	vwlist = dTable();

	

	
	$("#paneln").hide();
 
	
	$('#todos_tabs').bind('tabsshow', function(event, ui) { 

		vwlist.clear();
		vwlist.destroy();
		$('#todos_table_clone').remove();

		vwlist = dTable();

	});
 
 
	
	
	
	
	$('.med_approve_rights').removeAttr('disabled','disabled');
	
	$('.med_approve_rights').live('click',function(e){
		e.preventDefault();
		
		var action = $(this).data('action');
		var recordid = $(this).data('recordid');
		var alt_id = $(this).data('alt_id');
		var patid = $(this).data('patid');
		var todoid = $(this).data('todoid');
		var row_id = $(this).data('row_id');
		
		$('.med_approve_rights').attr('disabled','disabled');
		
	 	if(action && recordid && patid){
			$.ajax({
				type: 'POST',
				url: 'ajax/applymedicationchanges?id='+patid+'',
				data: {
					todoid: todoid,
					action: action,
					recordid: recordid,
					alt_id: alt_id
				},
				success:function(data){
					var event_id = $('#event_done_' + row_id).val();
					var event_tabname = $('#tabname_' + row_id).val();
					var event_done_date = $('#done_date_' + row_id).val();
					var filter = $('#label_filter').val();
					$('#loading_div_'+ row_id).show();
					//$('tr#d_row_' + row_id).effect("highlight", {}, 5000);
					mark_event_done(event_id, event_tabname, row_id, event_done_date, filter);
					$('.med_approve_rights').removeAttr('disabled','disabled');
				},
				error:function(){
					ajax_done = 1;
					// failed request; give feedback to user
				}
			});
		} 
	});

	
	
	$('.pump_med_approve_rights').removeAttr('disabled','disabled');
	
	$('.pump_med_approve_rights').live('click',function(e){
		e.preventDefault();
		
		var action = $(this).data('action');
		var recordid = $(this).data('recordid');
		var alt_id = $(this).data('alt_id');
		var patid = $(this).data('patid');
		var todoid = $(this).data('todoid');
		var row_id = $(this).data('row_id');
		
		$('.med_approve_rights').attr('disabled','disabled');
		
	 	if(action && recordid && patid){
			$.ajax({
				type: 'POST',
				url: 'ajax/applypumpmedicationchanges?id='+patid+'',
				data: {
					todoid: todoid,
					action: action,
					recordid: recordid,
					alt_id: alt_id
				},
				success:function(data){
					var event_id = $('#event_done_' + row_id).val();
					var event_tabname = $('#tabname_' + row_id).val();
					var event_done_date = $('#done_date_' + row_id).val();
					var filter = $('#label_filter').val();
					$('#loading_div_'+ row_id).show();
					//$('tr#d_row_' + row_id).effect("highlight", {}, 5000);
					mark_event_done(event_id, event_tabname, row_id, event_done_date, filter);
					$('.med_approve_rights').removeAttr('disabled','disabled');
				},
				error:function(){
					ajax_done = 1;
					// failed request; give feedback to user
				}
			});
		} 
	});
	
	
	
	$('.done_event').live('click', function() {
		if ($(this).is(':checked')) {
			var row_id = $(this).attr('rel');
			//ISPC - 2368
			$('#contactArea').html('<iframe id="add_family_doc" frameborder="0" src="" scrolling="no" style="margin:0 auto;"></iframe>');


			centerPopup({sr:'about:blank',ht:250,wt:450});

			$('#contactArea').html('<div>'+translate("completecomment")+'<div align="right"><a id="popupContactClose" style="cursor:pointer;" onclick="uncheckbox(\'done_event_'+row_id+'\');closepopup()">x</a></div></div><div><textarea name="completecomment" id="completecomment"></textarea></div><div><button name="" onClick="saveCompleteComment(\''+row_id+'\');">'+translate("submit")+'</button></div>');

			loadPopup();
			
			/*var event_id = $('#event_done_' + row_id).val();
			var event_tabname = $('#tabname_' + row_id).val();
			var event_done_date = $('#done_date_' + row_id).val();
			var event_extra = $('#event_extra_' + row_id).val();
			var filter = $('#label_filter').val();
			$('#loading_div_'+ row_id).show();
			//$('tr#d_row_' + row_id).effect("highlight", {}, 5000);
			
			var  confirm_mark_as_done = translate('confirm_mark_as_done');
			var  confirm_mark_as_done_title = translate('confirm_mark_as_done_title');
			jConfirm(confirm_mark_as_done, confirm_mark_as_done_title, function(r) {
				if(r)
				{	
					mark_event_done(event_id, event_tabname, row_id, event_done_date, filter,event_extra);
					return true;
					
				} else {
					$('#loading_div_'+ row_id).hide();
					$('#done_event_'+ row_id).prop('checked', false).removeAttr('checked');
					
				}
			});*/
		}
	});
	
	 

	$('.undone_event').live('click', function() {
		if ($(this).is(':checked')) {
			var row_id = $(this).attr('rel');
			var event_id = $('#event_done_' + row_id).val();
			$('#loading_div_'+ row_id).show();
			
			var  confirm_mark_as_undone = translate('confirm_mark_as_undone');
			var  confirm_mark_as_undone_title = translate('confirm_mark_as_undone_title');
			jConfirm(confirm_mark_as_undone, confirm_mark_as_undone_title, function(r) {
				if(r)
				{	
					mark_event_undone(event_id, row_id);
					return true;
				}
				else 
				{
					$('#loading_div_'+ row_id).hide();
					$('#undone_event_'+ row_id).prop('checked', false).removeAttr('checked');
					
				}
			});
		}
	});

	
	
	
	
	$("select[name='vw_list_length']").live('change', function() {
		
		if($(this).val())
		{
			$.ajax({
				url : 'ajax/saveuserpageresults',
				type : 'POST',
				data : {
					page: "todos",
					results: $(this).val()
				} 
			});
		}
		
	}); 
	
	
	$('#exclude_discharged').live('click', function() {
		$("body").addClass("body-overlay");
		
	});
	
	$('#exclude_dead').live('click', function() {
		$("body").addClass("body-overlay");
		
	});
	
	
});/*-- END  $(document).ready ----------- --*/


//ISPC - 2368
function closepopup() {
	disablePopup();
}

function saveCompleteComment(row_id)
{
	var val = "";
	
	//if($("#completecomment").val().length>0) {
	val = $("#completecomment").val();

	$("#completecomment_"+row_id).val(val);
	
	var event_id = $('#event_done_' + row_id).val();
	var event_tabname = $('#tabname_' + row_id).val();
	var event_done_date = $('#done_date_' + row_id).val();
	var event_extra = $('#event_extra_' + row_id).val();
	var filter = $('#label_filter').val();
	$('#loading_div_'+ row_id).show();
	var event_comment = $('#completecomment_' + row_id).val(); //ISPC - 2368
	//$('tr#d_row_' + row_id).effect("highlight", {}, 5000);
	
	var  confirm_mark_as_done = translate('confirm_mark_as_done');
	var  confirm_mark_as_done_title = translate('confirm_mark_as_done_title');
	jConfirm(confirm_mark_as_done, confirm_mark_as_done_title, function(r) {
		if(r)
		{	
			mark_event_done(event_id, event_tabname, row_id, event_done_date, filter,event_extra,event_comment);
			closepopup();
			return true;
			
		} else {
			$('#loading_div_'+ row_id).hide();
			$('#done_event_'+ row_id).prop('checked', false).removeAttr('checked');
			
		}
	});
	/*}
	else
	{
		jAlert(translate('entercomment'));
	}*/
}

function uncheckbox(chk)
{
       $('#'+chk).attr('checked',false);
}
//ISPC - 2368