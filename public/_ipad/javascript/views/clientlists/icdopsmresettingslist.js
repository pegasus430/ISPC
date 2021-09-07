//ISPC-2654 Lore 06.10.2020
var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'clientlists/icdopsmresettingslist?action=delete&id=';

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	icdopsmresettingslisttable = drawDatatable_icdopsmresettingslist('table', langurl);

	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	var delentatt = translate('youcannotdeletethissettingbecauseitsused');
	
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

//DATATABLE materialslist
function drawDatatable_icdopsmresettingslist(id, langurl) {
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

		"info": false,
		"filter": false,
		"paginate": false,
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
		        	  title: translate('category'),
		        	  data: "category",
		        	  className: "",
		        	  "width": "20%"
		          },
		          {
		        	  title: translate('shortcut'),
		        	  data: "shortcut",
		        	  className: "",
		        	  "width": "10%"
		          },
		          {
		        	  title: translate('color'),
		        	  data: "color",
		        	  className: "",
		        	  "background-color": "color",
		        	  "width": "10%"
		          },
/*		          {
		        	  title: translate('sort_order'),
		        	  data: "sort_order",
		        	  className: "",
		        	  "width": "10%"
		          },*/
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