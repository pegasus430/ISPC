var langurl = appbase + "javascript/data_tables/de_language.json";
var delurl = appbase + 'churches/deletechurch?id=';
var ajaxurl = appbase + 'churches/churcheslist';

var left_menu_list; // this will be the datatable object
	
$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	window.left_menu_list = drawDatatable(); 
	
	// DELETE
	$(".delete").live('click', function() {
		$.confirmdeleteid = $(this).attr('rel');
		jConfirm(confdel, conftitle, function(r) {
			if(r)
			{	
				location.href = delurl + $.confirmdeleteid;
			}
		});
	});
	
});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable() {
	var table = $('#table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom": 
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[25, 50,100, 150], [25, 50,100, 150]],
			
		"processing": true,

		"info": true,
		"filter": true,
		"paginate": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": true,
		"scrollX": false,
		"scrollCollapse": true,
		
	    "fnStateLoadParams": function (oSettings, oData) {
	    	if(reset_datatable == 1){
	    		return false;
	    	} else{
	    		 oData.search.search= "";
	    		return true;
	    	}
	      },
 		"columns": [
 		          { data: "name", className: "", "width": "20%"},
		          { data: "contact_firstname", className: "", "width": "10%"},
		          { data: "contact_lastname", className: "", "width": "10%"},
		          { data: "street", className: "", "width": "10%"},
		          { data: "zip", className: "", "width": "10%"},
		          { data: "city", className: "", "width": "10%"},
		          { data: "phone", className: "", "width": "15%"},
		          { data: "email", className: "", "width": "10%"},
		          { data: "actions", className: "", "width": "5%"}
			],
			
		"columnDefs": [ 
				       	{ "targets": -1, "searchable": false, "orderable": true }
				],
		order: [[ 2, "asc" ]],
		//order: [],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST'
		}
 	
	});
	return table;
}
