var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'dischargelocation/deletelocation?id=';
var ajaxurl = appbase + 'dischargelocation/listlocation';
var left_menu_list; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	var delentatt = translate('youcannotdeletethislocationbecauseitsused');
	window.left_menu_list = drawDatatable();
	
	// DELETE
	$(".delete").live('click', function() {
		$.confirmdeleteid = $(this).attr('rel');
		$.askondel = $(this).attr('del');
		if($.askondel == '1')
		{
			jAlert(delentatt, conftitle);
		}
		else
		{
			jConfirm(confdel, conftitle, function(r) {
				if(r)
				{	
					location.href = delurl + $.confirmdeleteid;
				}
			});
		}
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
		          { data: "location", className: "","width": "40%"},
		          { data: "location_type", className: "","width": "20%"},
		          { data: "actions", className: " ","width": "10%" }
			],
			
		columnDefs: [ { "targets": -1, "searchable": false, "orderable": false }],
 		//order: [[ 0, "asc" ]],
		order: [],
		
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		}
 	
	});
	return table;
}