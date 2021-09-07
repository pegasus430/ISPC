var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl1 =appbase + 'iconsystem/getsysicons';
var ajaxurl2 =appbase + 'iconsystem/getclienticons';
var delurl =appbase + 'iconsystem/listicons';
var id1 = 'table1';
var id2 = 'table2';
var left_menu_list1; // this will be the datatable object
var left_menu_list2; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	var titledelic = translate('Delete Icon');
	var mesdelicsys = translate('delete_custom_system_icon');
	var mesdelicl = translate('delete_custom_icon');
		
	window.left_menu_list1 = drawDatatable(id1, langurl, ajaxurl1);
	window.left_menu_list2 = drawDatatable(id2, langurl, ajaxurl2);	

	$('#icon_delete_dialog').dialog({
		autoOpen: false,
		modal: true,
		resizable: false,
		draggable: false,
		buttons: [
		           {
		               text: translate('submit'),
		               click: function() {
		            	   $('#delicon').submit();
		            	   reset_delete_dialog();
		               }
		           },
		           {
		               text: translate('cancel'),
		               click: function() {
		            	   $('#icon_delete_dialog').hide();
		            	  reset_delete_dialog();
		   			$(this).dialog("close"); }
		           }
		       ]
	});
	
	$('.delete').live('click', function () {
		if( $(this).attr('type') == 'icl')
		{
			$('#icon_delete_dialog').append('<span>' + mesdelicl + '</span>');
		}
		else
		{
			$('#icon_delete_dialog').append('<span>' + mesdelicsys + '</span>');
		}
		
		$( "#icon_delete_dialog" ).dialog({ title: titledelic });
		$('#icon_delete_dialog').dialog('open');
		$('#now_del_id').val($(this).attr('rel'));
	});	

});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable(id, langurl, ajaxurl) {
	var table = $('#'+id).DataTable({
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
		          { data: "image", className: "","width": "10%"},
		          { data: "name", className: "","width": "30%"},
		          { data: "color", className: "","width": "10%"},
		          { data: "create_date", className: "","width": "10%"},
		          { data: "actions", className: " ","width": "10%" }
			],
			
		columnDefs: [ { "targets": -1, "searchable": false, "orderable": false },
		              { "targets": 0, "searchable": false, "orderable": false },
					  { "targets": 2, "searchable": false, "orderable": false }
		             ],
 		//order: [[ 1, "asc" ]],
		order: [],
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		}
 	
	});
	return table;
}

function reset_delete_dialog()
{
	$('#now_del_id').val('');
}
