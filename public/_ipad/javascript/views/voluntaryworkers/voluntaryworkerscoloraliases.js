// ISPC-2401 pct.7 Lore
var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl =appbase + 'voluntaryworkers/voluntaryworkerscoloraliases';
var delurl =appbase + 'voluntaryworkers/voluntaryworkerscoloraliases?action=delete&id=';

var left_menu_list_colors; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
		
	window.left_menu_list = drawDatatable('coloraliastable', langurl, ajaxurl);

	// DELETE
	$(".delete").live('click', function() {
		$.confirmdeleteid = $(this).attr('rel');
		$.confirmdeleteprofile = $(this).attr('profile');
		jConfirm(confdel, conftitle, function(r) {
			if(r)
			{	
				location.href = delurl + $.confirmdeleteid+'&profile='+$.confirmdeleteprofile;
			}
		});
	});
});/*-- END  $(document).ready ----------- --*/

//DATATABLE
function drawDatatable(id, langurl, ajaxurl) {

	var columns = [
		 		          { data: "color", className: "", "width": "30%"},
				          { data: "colorname", className: "", "width": "30%"},
				          { data: "actions", className: "", "width": "5%", "searchable": false, "orderable": false}
					];
	
	var order = [0, "asc"];
	var paginate = true;
	var info = true;
	var domcontent = '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
	't'+'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>';
	
	var table = $('#'+id).DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom": domcontent,
			//'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			//'t'+
			//'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
			
		"processing": true,

		"info": info,
		"filter": true,
		"paginate": paginate,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": false,
		"scrollX": false,
		"scrollCollapse": true,
			
 		/*"columns": [
 		          { data: "create_date", className: "", "width": "10%"},
		          { data: "title", className: "", "width": "40%"},
		          { data: "filetype", className: "", "width": "5%"},
		          { data: "create_user_name", className: "", "width": "20%"},
		          { data: "actions", className: "", "width": "5%"}
			],*/
		"columns" : columns,
			
		"columnDefs": [ 
				       	
				],
		order: [order],
		//order: [],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
				// Object.assign(d, custom_data);
				//    return d;
				d.settingstable='voluntaryworkers_color_aliases';
  	    
			}
		}
 	
	});
	
	return table;
}

