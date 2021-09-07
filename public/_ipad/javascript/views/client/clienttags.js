var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'client/clienttags?tagid=';
var ajaxurl = appbase + 'client/clienttags';

var left_menu_list; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	var edit_tag = translate('edit_tags');
	window.left_menu_list = drawDatatable();
 
	// DELETE
	$(".delete").live('click', function() {
		$.confirmdeleteid = $(this).attr('rel');
		jConfirm(confdel, conftitle, function(r) {
			if(r)
			{	
				location.href = delurl + $.confirmdeleteid + '&mode=deltag';
			}
		});
	});
	
	$('#edit_tag_dialog').dialog({
		autoOpen: false,
		modal: true,
		title: translate('edit_tags'),
		resizable: false,
		draggable: false,
		close: function () {
			$('#edit_tag_dialog').hide();
			reset_edit_dialog();
		},
		 buttons: [
		           {
		               text: translate('submit'),
		               click: function() {$('#save_edit_tag').submit();
		   			reset_edit_dialog(); }
		           },
		           {
		               text: translate('cancel'),
		               click: function() {
		            	   $('#edit_tag_dialog').hide();
		            	   reset_edit_dialog();
		   			$(this).dialog("close"); }
		           }
		       ]	
		});
	

	
	$('.edit').live('click', function () {
	var id = $(this).attr('rel');
	var tag = $(this).attr('relvalue');
	
	$('#tag_id').val(id);
	$('#tag_name').val(tag);
	$('#edit_tag').val('1');

	$('#edit_tag_dialog').dialog('open');
	});

});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable() {
	var table = $('#table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		sDom: 
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',

		"lengthMenu": [[25, 50, 100], [25, 50, 100]],
			
		"processing": true,
		"info": true,
		"filter": true,
		"paginate": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": true,
		"scrollX": false,
		"scrollCollapse": true,
//		"stateLoadCallback": function (settings)
//		{
//
//		},
	    "fnStateLoadParams": function (oSettings, oData) {
	    	if(reset_datatable == 1){
	    		return false;
	    	} else{
	    		 oData.search.search= "";
	    		return true;
	    	}
	      },
		columns: [
		          { data: "tag", className: "","width": "50%"},
		          { data: "counted_files", className: "","width": "10%"},
		          { data: "actions", className: " ","width": "10%" }
			],
			
		columnDefs: [ { "targets": -1, "searchable": false, "orderable": false }],
 		order: [[ 0, "asc" ]],
		
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		}
 	
	});
	return table;
}

function reset_edit_dialog()
{
	$('#tag_id').val('');
	$('#tag_name').val('');
	$('#edit_tag').val('')
}