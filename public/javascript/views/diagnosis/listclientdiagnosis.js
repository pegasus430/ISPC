var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'diagnosis/listclientdiagnosis?action=delete&id=';

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	listdiagnosisclienttable = drawDatatable('table', langurl);

	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	var delentatt = translate('youcannotdeletethisdiagnosisbecauseitsused');
	
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

//DATATABLE listclientdiagnosis ISPC-2412 Carmen 21.11.2019
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

			
		"lengthMenu": [[25, 50, 100], [25, 50, 100]],
			
		"processing": true,

		"info": true,
		"filter": true,
		"paginate": true,
		"destroy": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": true,
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
		
		"fnStateLoadParams": function (oSettings, oData) {
	    	if(reset_datatable == 1){
	    		return false;
	    	} else{
	    		 oData.search.search= "";
	    		return true;
	    	}
	      },
		
 		"columns": [
		          {
		        	  title: translate('icd_primary'),
		        	  data: "icd_primary",
		        	  className: "",
		        	  "width": "8%"
		          },
		          {
		        	  title: translate('description'),
		        	  data: "description",
		        	  className: "",
		        	  "width": "30%"
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