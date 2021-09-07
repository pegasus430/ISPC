//ISPC-2612 Ancuta 25.06.2020
var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'familydoctor/deletefamilydoctor?id=';
var ajaxurl = appbase + 'familydoctor/familydoctorlist';

var left_menu_list; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	
	$('#table_conn_lists').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		sDom: 
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',

			"lengthMenu": [[25, 50, 100], [25, 50, 100]],
			
		"processing": false,
		"info": true,
		"filter": true,
		"paginate": true,
		
		"serverSide": false,
		"autoWidth": false,
	 	"stateSave": false,
		"stateDuration": -1,
		"scrollX": false,
		"scrollCollapse": true,
		columns: [
				  { data: "menu_name", className: "","width": "20%"},
				  { data: "actions", className: " ","width": "5%" },
//		          { data: "mode_name", className: "","width": "10%"},
//		          { data: "indrop", className: "","width": "10%"},
//		          { data: "connection_id", className: "","width": "10%"},
//		          { data: "menu_link", className: "","width": "10%"},
			],
			
		columnDefs: [ 
			{ "targets": 0, "searchable": true, "orderable": true },
			{ "targets": 1, "searchable": true, "orderable": false },
//			{ "targets": 2, "searchable": true, "orderable": true },
//			{ "targets": 3, "searchable": true, "orderable": true },
//			{ "targets": 4, "searchable": true, "orderable": true },
//			{ "targets": 5, "searchable": true, "orderable": true }
		],
 		order: [[ 0, "asc" ]],
 	
	});
	
	
});/*-- END  $(document).ready ----------- --*/
//You keep the page address that set the sessionStorage and then check  it on the onload event. If they are the same, erase the sessionStorage  contents.  

