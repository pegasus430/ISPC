var langurl = appbase + 'javascript/data_tables/de_language.json';
var setrecipients = appbase + 'orders/recipientslist?action=setrecipients';

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	recipientslisttable = drawDatatable('table', langurl);
	recipientslisttableP = drawDatatablePseudo('pseudo_table', langurl);
	
	$(".recipient").live('click', function() {		
		$.confirmrecid = $(this).attr('rel');
		if($(this).val() == "")
		{
			$("#"+$.confirmrecid).val("1");
		}
		else
		{
			$("#"+$.confirmrecid).val("");
		}			
	});
	
	// SET RECIPIENTS
	$(".save").live('click', function() {
		var checkrecs = document.getElementsByName('recipient[]');
	
				var confirmrecids = "";
				var delim = "";
				for(i=0; i<checkrecs.length;i++)
				{					
					if(checkrecs[i].value == '1')
					{
						confirmrecids += delim + checkrecs[i].id;
						delim = "|";
					}
				}
				//alert(confirmrecids);
				$("#recids").val(confirmrecids);
				$("#recform").submit();
		});

});/*-- END  $(document).ready ----------- --*/

//DATATABLE materialslist
function drawDatatable(id, langurl) {
	var table = $('#'+id).DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom":'t'+
			'<"#bottom_export">',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],
			
		"processing": true,

		"info": false,
		"filter": true,
		"paginate": false,
		"destroy": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": false,
		"scrollX": false,
		"scrollCollapse": true,
		
		"sAjaxSource" : window.location.href+'?op=users',
		
		"fnServerData" : function(sSource, aoData, fnCallback, oSettings) {

			if (oSettings.jqXHR) {
				oSettings.jqXHR.abort();
			}
			
			var _sorting = oSettings.aaSorting[0];		

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
 		        	  title: '',
 		        	  data: "userid",
 		        	  className: "",
 		        	  "width": "2%"
 		          },
		          {
		        	  title: translate('recipient_username'),
		        	  data: "username",
		        	  className: "",
		        	  "width": "15%"
		          },
		          {
		        	  title: translate('recipient_user_title'),
		        	  data: "user_title",
		        	  className: "",
		        	  "width": "10%"
		          },
		          {
		        	  title: translate('recipient_last_name'),
		        	  data: "last_name",
		        	  className: "",
		        	  "width": "20%"
		          },
		          {
		        	  title: translate('recipient_first_name'),
		        	  data: "first_name",
		        	  className: "",
		        	  "width": "20%"
		          },
		          
			],
			
		"columnDefs": [
		          { "targets": 0, "searchable": false, "orderable": false }
				],
		"order": [[1, "asc"]],
		
		
		"createdRow": function( row, data, dataIndex ) {
			if(data.checked =="1"){
				var check_info = 'checked="checked"';
			} else{
				var check_info = '';
			}
		var _cbx = "<div class='datatable_cb_row'><label>"
		+ "<input type='checkbox' class='row_select' id='" + data.userid + "' name='users[]' "+data.selected+"  value='" + data.userid + "' "+check_info +"   />"
		+ "</label>"
		+ "</div>";
		
		
		$("td", row).eq(0).html(_cbx);
	},
		
		
		"initComplete": function(){
//			var html = '<form id="recform" action='+setrecipients+' method="post">';
//			html += '<input type="hidden" id="recids" name="recids" value="" /><button type="button" class="save">' + translate('save') + '</button></form>';
//		      $("#bottom_export").html(html);           
		   }
		
	});
	
	return table;
}


//DATATABLE
function drawDatatablePseudo(id, langurl) {
	var ajaxurl = appbase + 'orders/recipientslist?op=pseudousers';
	
	var table = $('#'+id).DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		sDom:'t',

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
 		"columns": [
 	 		          { 
 	 		        	  title: '',
 	 		        	  data: "userid",
 	 		        	  className: "",
 	 		        	  "width": "2%"
 	 		          },
 			          {
 			        	  title: translate('servicesname'),
 			        	  data: "servicesname",
 			        	  className: "",
 			        	  "width": "85%"
 			          } 
 			          
 				],
			
 				"columnDefs": [
 					          { "targets": 0, "searchable": false, "orderable": false }
 							],
		
		
		
 		order: [[ 0, "asc" ]],
		
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		},
		"createdRow": function( row, data, dataIndex ) {
			if(data.checked =="1"){
				var check_info = 'checked="checked"';
			} else{
				var check_info = '';
			}
		var _cbx = "<div class='datatable_cb_row'><label>"
		+ "<input type='checkbox' class='row_select' onClick='_select_associated_users(this)'  name='users[]' "+data.selected+"  data-system_id='"+ data.system_id +"' data-assigned_users='"+data.assigned_users+"'  value='" + data.userid + "' "+check_info +"   />"
		+ "</label>"
		+ "</div>";
		
		
		$("td", row).eq(0).html(_cbx);
	},
 	
	});
	return table;
}

function _select_associated_users(_this){
	
	// on select - select related users
	if( $(_this).prop('checked')){
		
		var assinged_user_str = $(_this).data('assigned_users');
		var assinged_user = assinged_user_str.split(',');
		
		$.each( assinged_user, function( key, value ) {
			  $('#u'+value).prop('checked', true);
			});
		
	}
	
}
