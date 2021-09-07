var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'orders/materialslist?action=delete&id=';

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	materialslisttable = drawDatatable('table', langurl);

	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	
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

//DATATABLE materialslist
function drawDatatable(id, langurl) {
	var table = $('#'+id).DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom":'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],
			
		"processing": true,

		"info": true,
		"filter": true,
		"paginate": true,
		"destroy": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": false,
		"scrollX": false,
		"scrollCollapse": true,
		
		"sAjaxSource" : window.location.href,
		
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];		
			
			aoData.push({
				"name" : "category",
				"value" : category
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
		
 		"columns": [
		          {
		        	  title: translate('material_name'),
		        	  data: "title",
		        	  className: "",
		        	  "width": "30%"
		          },
		          {
		        	  title: translate('material_unit'),
		        	  data: "unit",
		        	  className: "",
		        	  "width": "8%"
		          },
		          {
		        	  title: translate('material_pzn'),
		        	  data: "pzn",
		        	  className: "",
		        	  "width": "20%"
		          },
		          {
		        	  title: translate('material_pieces'),
		        	  data: "pieces",
		        	  className: "",
		        	  "width": "8%"
		          },
		          {
		        	  title: translate('material_quantity'),
		        	  data: "quantity",
		        	  className: "",
		        	  "width": "8%"
		          },
		          {
		        	  title: translate('actions'),
		        	  data: "actions",
		        	  className: "",
		        	  "width": "5%"
		          }
			],
			
		"columnDefs": [
		          { "targets": -1, "searchable": false, "orderable": false}
				],
		"order": [[0, "asc"]],
		
	});
	
	return table;
}