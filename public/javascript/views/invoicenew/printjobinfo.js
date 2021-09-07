//ISPC-2609 Ancuta
var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'invoicenew/printjobdelete?delete=1&id=';
var clearurl = appbase + 'invoicenew/printjobclear';
var ajaxurl = appbase + 'invoicenew/printjobinfo?invoice_type=';


$(document).ready(function() { /*------ Start $(document).ready --------------------*/

	var conf_del_job = translate('confirmdeletepjob');
	var conf_del_job_title = translate('confirmdeletepjobtitle');
	var conf_clear = translate('confirm_clear_print_jobs');
	var conf_clear_title = translate('confirm_clear_print_jobs_title');
	 
	drawDatatable();
	
	setInterval(function(){
		$('#print_jobs_table').DataTable().clear().destroy();
		
		drawDatatable(); 
	}, 60*1000);
	// --
	
	setTimeout(function(){
		$('#print_job_success_info').remove()
	}, 8000);
	
	
	$(".reloadButton").live('click', function() {
		$('#print_jobs_table').DataTable().clear().destroy();
		drawDatatable(); 
	});
	
	// DELETE
	$(".job_delete").live('click', function() {
		$.confirmdeleteid = $(this).attr('rel');
		jConfirm(conf_del_job, conf_del_job_title, function(r) {
			if(r)
			{
				$.ajax({
					  method: "POST",
					  url : delurl+$.confirmdeleteid,
					  data: {id : $.confirmdeleteid},
					  
					  success: function(data)
					  {  
							$('#print_jobs_table').DataTable().clear().destroy();
							
							drawDatatable(); 
					  }
					})
					  .done(function( msg ) {
					   // alert( "Data Saved: " + msg );
					  });
			}
			
		 
		});
	});
	
	$("#clear_user_jobs").live('click', function() {
		$.confirm_userid= $(this).data('user');
		$.confirm_client= $(this).data('client');
		$.confirm_invoice_type = $(this).data('invoice_type');
		
		jConfirm(conf_clear, conf_clear_title, function(r) {
			if(r)
			{
				$.ajax({
					method: "POST",
					url : clearurl,
					data: {
						user : $.confirm_userid,
						client : $.confirm_client,
						invoice_type : $.confirm_invoice_type
						},
					
					success: function(data)
					{  
						$('#print_jobs_table').DataTable().clear().destroy();
						
						drawDatatable(); 
					}
				})
				.done(function( msg ) {
					// alert( "Data Saved: " + msg );
				});
			}
			
			
		});
	});
	

	
});/*-- END  $(document).ready ----------- --*/
	
	// DATATABLE
function drawDatatable() {
 
	var table = $('#print_jobs_table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
     	sDom:'<"reloadButton">t',
			
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
		columns: [
		          { data: "queue_nr", className: "","width": "10%"},
		          { data: "print_user", className: "","width": "10%"},
		          { data: "print_status", className: "","width": "10%"},
		          { data: "print_link", className: "","width": "10%"},
		          { data: "print_date", className: "","width": "10%"},
		          { data: "actions", className: " ","width": "10%" }
			],
			
		columnDefs: [ { "targets": 0, "searchable": false, "orderable": false },
					  { "targets": 1, "searchable": false, "orderable": false },
					  { "targets": 2, "searchable": false, "orderable": false },
					  { "targets": 3, "searchable": false, "orderable": false },
					  { "targets": 4, "searchable": false, "orderable": false },
					  { "targets": 5, "searchable": false, "orderable": false }
		],
		
 		order: [[ 0, "asc" ]],
		
 		ajax: {
			url:ajaxurl+window.client_allowed_invoice,
			type: 'POST'		
		},
      initComplete: function()
      {
    	  $("div.reloadButton").html("");
      }
	});
 


	
	return table;
}
