//ISPC-2609 Ancuta + TODO-3668
var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'member/printjobdelete?delete=1&id=';
var clearurl = appbase + 'member/printjobclear';
var ajaxurl = appbase + 'member/printjobinfo?print_controller=member';


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
		$.confirm_print_controller = $(this).data('print_controller');
		
		jConfirm(conf_clear, conf_clear_title, function(r) {
			if(r)
			{
				$.ajax({
					method: "POST",
					url : clearurl,
					data: {
						user : $.confirm_userid,
						client : $.confirm_client,
						print_controller : $.confirm_print_controller
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
 
     	sDom:'<"reloadButton">lt',
			
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
			url:ajaxurl,
			type: 'POST'		
		},
      initComplete: function()
      {
    	  $("div.reloadButton").html("");
      }
	});
 


	
	return table;
}
