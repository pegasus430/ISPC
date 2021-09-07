//ISPC-2520 Lore 08.04.2020
var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'clientlists/organicentriesexitslist?action=delete&id=';

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	orgentexlisttable = drawDatatable('table', langurl);

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
		        	  title: translate('organic_shortcut'),
		        	  data: "shortcut",
		        	  className: "",
		        	  "width": "10%"
	          },
	          {
		        	  title: translate('organic_name'),
		        	  data: "name",
		        	  className: "",
		        	  "width": "20%"
		          },
		          
		          {
		        	  title: translate('organic_type'),
		        	  data: "type",
		        	  className: "",
		        	  "width": "10%"
		          },		          
		          {
		        	  title: translate('actions'),
		        	  data: "actions",
		        	  className: "",
		        	  "width": "8%"
		          }
			],
			
		"columnDefs": [
		          { "targets": -1, "searchable": false, "orderable": false}
				],
		"order": [[0, "asc"]],
		
	});
	
	return table;
}