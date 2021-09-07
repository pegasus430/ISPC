var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'familydoctor/deletefamilydoctor?id=';
var ajaxurl = appbase + 'familydoctor/familydoctorlist';

var vwlist; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');

	
	var load_from = $('#load_from').val();
	if(load_from == "listx"){
		console.log('DataTables_'+window.location.pathname);
//		console.log(sessionStorage);
//		console.log('DataTables_'+window.location.pathname );
		sessionStorage.removeItem( 'DataTables_'+window.location.pathname );
		sessionStorage.removeItem('DataTables_'+window.location.pathname);
		localStorage.removeItem('DataTables_'+window.location.pathname );
//		sessionStorage.clear();
		location.reload();
		
//		alert(load_from);
//		$('#table').DataTable().state.clear();
//		window.location.reload();
		
//		var url = 'www.domain.com/abc?num=4';
		var url = window.location.href;
		window.location.href = url.split('?')[0];
		
//		alert(url);
//		if (url.indexOf('?') > -1){
//		   url += '&lf=Vasile'
//		}else{
//		   url += '?lf=Vasile'
//		}
//		window.location.href = url;
		$('#load_from').val("vasile");
//		window.vwlist = drawDatatable(true);
	}
	else{
//		window.vwlist = drawDatatable();
	}
 
	
	
	
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
	
	
	
	
	
	
	
	var table = $('#table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		sDom: 
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',

		"lengthMenu": [[25, 50, 100], [25, 50, 100]],
			
		"processing": true,
		"info": true,
		"filter": true,
		"paginate": true,
		
		"serverSide": true,
		"autoWidth": false,
		"stateSave": true,
		"stateDuration": -1,
		"scrollX": false,
		"scrollCollapse": true,
		/*"stateLoadCallback": function (settings)
		{

		},*/
		columns: [
		          { data: "practice", className: "","width": "10%"},
		          { data: "first_name", className: "","width": "10%"},
		          { data: "last_name", className: "","width": "10%"},
		          { data: "zip", className: "","width": "10%"},
		          { data: "city", className: "","width": "10%"},
		          { data: "phone_practice", className: "","width": "10%"},
		          { data: "email", className: "","width": "10%"},
		          { data: "actions", className: " ","width": "10%" }
			],
			
		columnDefs: [ { "targets": -1, "searchable": false, "orderable": false }],
 		order: [[ 2, "asc" ]],
		
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		}
 	
	});
	
	

});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable() {
	
	
	
	var load_from = $('#load_from').val();
	if(load_from == "list"){
		table.state.clear().draw();
		var url = window.location.href;
		window.location.href = url.split('?')[0];
		
		$('#load_from').val("vasile");
	}	
	
	
	
//	if(clin){
//		table.state.clear().draw();
//		window.vwlist = drawDatatable();
//	}
 	
//	var load_from = $('#load_from').val();
//	if(load_from == "list"){
//		table.state.clear();
////		window.location.reload();
//		
//		var url = window.location.href;    
//		if (url.indexOf('?') > -1){
//		   url += '&lf=Vasile'
//		}else{
//		   url += '?lf=Vasile'
//		}
//		window.location.href = url;
		
		
//		load_from = "VASILE";
//		alert(load_from);
//		$('#load_from').val("vasile")
//	}
	
	
	return table;
}