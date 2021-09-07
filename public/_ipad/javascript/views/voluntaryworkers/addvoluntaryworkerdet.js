var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl_visits =appbase + 'voluntaryworkers/gethospizworkervisits';
var ajaxurl_work =appbase + 'voluntaryworkers/getworkerwork';
var ajaxurl_activity =appbase + 'voluntaryworkers/getworkeractivity';

var visits_table_simple;
var visits_table_bulk;
var work_table_simple;
var work_table_bulk;
var vw_activity;

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	$( "#from_simple_visits" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#from_simple_visits").mask("99.99.9999");
	
	$( "#to_simple_visits" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#to_simple_visits").mask("99.99.9999");
	
	$( "#from_bulk_visits" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#from_bulk_visits").mask("99.99.9999");
	
	$( "#to_bulk_visits" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#to_bulk_visits").mask("99.99.9999");
	
	$( "#from_simple_work" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#from_simple_work").mask("99.99.9999");
	
	$( "#to_simple_work" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#to_simple_work").mask("99.99.9999");
	
	$( "#from_bulk_work" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#from_bulk_work").mask("99.99.9999");
	
	$( "#to_bulk_work" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#to_bulk_work").mask("99.99.9999");
	
	$( "#from_activity" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#from_activity").mask("99.99.9999");
	
	$( "#to_activity" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	$("#to_activity").mask("99.99.9999");
	
	var from_activity = $('#from_activity').val();
	var to_activity = $('#to_activity').val();
	vw_activity = drawDatatable_activity('table_activity', langurl, ajaxurl_activity, from_activity, to_activity);
	$(vw_activity.table().body()).addClass( 'vwactivity' );
	
	// ##########	
	// activities 
	// ##########	
	$('.add_new_line').live('click',function(){
		var row_status = $('#rows_status').val();

		var event_type_select = '<select name="activity['+row_status+'][team_event_type]"  class="event_select" style="width:130px;"><option value="">'+translate("select")+'</option>'+sel_str+'</select>';
		

		var tr ='<tr class="activity_row">';
		var a_date ='<td><input type="text" name="activity['+row_status+'][date]" value="" id="a_date_'+row_status+'"   class="vw_date"  /><input type="hidden" name="activity['+row_status+'][id]" value="'+row_status+'" /></td>';
		var a_activity ='<td><input type="text" name="activity['+row_status+'][activity]" value="" class="vw_activity" /></td>';
		var a_comment ='<td><input type="text" name="activity['+row_status+'][comment]" value="" class="vw_comment"  />  <input type="hidden" name="activity['+row_status+'][team_event]" value="0" />  <input type="hidden" name="activity['+row_status+'][team_event_id]" value="0"  /></td>';
		var a_duration ='<td><input type="text" name="activity['+row_status+'][duration]" value="" class="vw_duration"  />  </td>';
		var a_driving_time ='<td><input type="text" name="activity['+row_status+'][driving_time]" value="" class="vw_driving_time"  />  </td>';
		var a_team_event ='<td>'+event_type_select+'</td>';
		var a_delete ='<td><a href="javascript:void(0)" class="delete_row" rel="'+row_status+'" id="delete_'+row_status+'"><img src="'+appbase+'/images/action_delete.png" /></a></td>';
		var tr_end = '</tr>';
		$('#acivity_table').append(tr
				+a_date
				+a_activity
				+a_comment
				+a_duration
				+a_driving_time
				+a_team_event
				+a_delete
				+tr_end);		
		
		$('.vwactivity').append(
			tr+a_date+a_activity+a_comment+a_duration+a_driving_time+a_team_event+a_delete+tr_end
		);		
		
		$('#a_date_'+row_status).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",					
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: ''
		});
		var new_row = parseInt(row_status) + 1;

		$('#rows_status').val(new_row);

	});
	
	// =========Modal EDIT ===============================
	$('.edit_wb').live('click', function(){
		var id =$(this).data('id');

		$('#edit_work_bulk_dialog').load(appbase+'voluntaryworkers/editworkbulk?vid='+id, function() {
			$('#edit_work_bulk_dialog').dialog('open');
		});
	});
	

	$('#edit_work_bulk_dialog').dialog({
			resizable: false,
			modal: true,
			autoOpen: false,
			width: 750,
			height: 500,
			title: translate('edit'),
			buttons: [{
				text: translate('cancel'),
				click: function () {
					$(this).dialog("close");
				} },
				{
				text: translate('save'),
				click: function () {
					 $.ajax({
					type: 'POST',
					url: 'voluntaryworkers/editworkbulk',
					data: $("#edit_work_bulk").serialize(),
					success:function(data){
						$('#edit_work_bulk_dialog').dialog("close");
						//$('#bulk_work').load('<?php  echo APP_BASE."voluntaryworkers/workdatalist?id=".$_REQUEST['id'].""; ?>'); 
						
						work_table_bulk.ajax.reload();
					}
				}); 
				}
			}]
	});
	
	
	  //delete row
    $('.delete_work_data').live('click', function() { 
    	var idd= $(this).data('id');
    	
    	//url=$('frmremedie').attr('action);
    	//ert(id);
    	jConfirm(translate('confirmdeleterecord'), translate('confirmdeletetitle'), function(r) {
			if(r)
			{
				$.ajax({
					  method: "POST",
					  url : 'ajax/deleteworkdata?work_id='+idd+'=&vw_id='+workerid,
					  data: {work_id : idd},
					  
					  success: function(data)
					  {  
						//$('#bulk_work').load('<?php  echo APP_BASE."voluntaryworkers/workdatalist?id=".$_REQUEST['id'].""; ?>'); 
							
						 work_table_bulk.ajax.reload();
					  }
					})
					  .done(function( msg ) {
					   // alert( "Data Saved: " + msg );
					  });
			}
		}); 
        return false;
   });

    //delete row visits
    //TODO-3796 Lore 16.02.2021
    $('.delete_hvisit_data').live('click', function() { 
    	var idd= $(this).data('id');
		var visit_type = $(this).data('vtype');    	

    	//url=$('frmremedie').attr('action);
    	//ert(id);
    	jConfirm(translate('confirmdeleterecord'), translate('confirmdeletetitle'), function(r) {
			if(r)
			{
				$.ajax({
					  method: "POST",
					  url : 'ajax/deletehvisitdata?work_id='+idd+'=&vw_id='+workerid,
					  data: {work_id : idd},
					  
					  success: function(data)
					  {  							
						if(visit_type == "n"){
						  visits_table_simple.ajax.reload();
						} else{
							visits_table_bulk.ajax.reload();
						}
						  
					  }
					})
					  .done(function( msg ) {
					   // alert( "Data Saved: " + msg );
					  });
			}
		}); 
        return false;
   });
    
	$('.filter_visits').click(function(e) {
		e.preventDefault();
  		
  		var target = $(this).data("work_type");
		
  		if(target == 'simple_visits')
		{
  			var html = '<table id="table_simple_visits" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
						html += '<thead>';
						html += '<tr>';
						html += '<th>'+translate('#')+'</th>';
						html += '<th>'+translate('Patients')+'</th>';
						html += '<th>'+translate('Grund')+'</th>';
						html += '<th>'+translate('date')+'</th>';
						html += '<th>'+translate('hospizv_amount')+'</th>';
						html += '<th>'+translate('hospizv_duration')+'</th>';
						html += '<th>'+translate('hospizv_distance')+'</th>';
						html += '<th>'+translate('timetravel')+'</th>';
						html += '<th>'+translate('nightshift')+'</th>';
						html += '</tr>';
						html += '</thead>';
						html += '</table>';
			
  			var from_simple_visits = $('#from_simple_visits').val();
  	  		var to_simple_visits = $('#to_simple_visits').val();
  			$('#table_simple_visits_wrapper').empty();
			$('#table_simple_visits_wrapper').html(html);
			visits_table_simple = drawDatatable_visits('table_simple_visits', langurl, ajaxurl_visits, from_simple_visits, to_simple_visits);
		}
		else
		{
			var html = '<table id="table_bulk_visits" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
			html += '<thead>';
			html += '<tr>';
			html += '<th>'+translate('#')+'</th>';
			html += '<th>'+translate('Patients')+'</th>';
			html += '<th>'+translate('Grund')+'</th>';
			html += '<th>'+translate('date')+'</th>';
			html += '<th>'+translate('hospizv_amount')+'</th>';
			html += '<th>'+translate('hospizv_duration')+'</th>';
			html += '<th>'+translate('hospizv_distance')+'</th>';
			html += '<th>'+translate('timetravel')+'</th>';
			html += '<th>'+translate('nightshift')+'</th>';
			html += '</tr>';
			html += '</thead>';
			html += '</table>';
			
			var from_bulk_visits = $('#from_bulk_visits').val();
  	  		var to_bulk_visits = $('#to_bulk_visits').val();
  	  		
			$('#table_bulk_visits_wrapper').empty();
			$('#table_bulk_visits_wrapper').html(html);
			visits_table_bulk = drawDatatable_visits('table_bulk_visits', langurl, ajaxurl_visits, from_bulk_visits, to_bulk_visits);
		}
	});
	
	$('.filter_work').click(function(e) {
		e.preventDefault();
  		
  		var target = $(this).data("work_type");
		
  		if(target == 'simple_work')
		{
  			var html = '<table id="table_simple_work" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
						html += '<thead>';
						html += '<tr>';
						html += '<th>'+translate('#')+'</th>';
						html += '<th>'+translate('Grund')+'</th>';
						html += '<th>'+translate('date')+'</th>';
						html += '<th>'+translate('hospizv_duration')+'</th>';
						html += '<th>'+translate('hospizv_distance')+'</th>';
						html += '<th>'+translate('timetravel')+'</th>';
						html += '<th>'+translate('nightshift')+'</th>';
						html += '</tr>';
						html += '</thead>';
						html += '</table>';
			
  			var from_simple = $('#from_simple_work').val();
  	  		var to_simple = $('#to_simple_work').val();
  			$('#table_simple_work_wrapper').empty();
			$('#table_simple_work_wrapper').html(html);
			work_table_simple = drawDatatable_work('table_simple_work', langurl, ajaxurl_work, from_simple_work, to_simple_work);
		}
		else
		{
			var html = '<table id="table_bulk_work" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
			html += '<thead>';
			html += '<tr>';
			html += '<th>'+translate('#')+'</th>';
			html += '<th>'+translate('Grund')+'</th>';
			html += '<th>'+translate('date')+'</th>';
			html += '<th>'+translate('hospizv_duration')+'</th>';
			html += '<th>'+translate('hospizv_distance')+'</th>';
			html += '<th>'+translate('timetravel')+'</th>';
			html += '<th>'+translate('nightshift')+'</th>';
			html += '<th>'+translate('actions')+'</th>';
			html += '</tr>';
			html += '</thead>';
			html += '</table>';
			
			var from_bulk_work = $('#from_bulk_work').val();
  	  		var to_bulk_work = $('#to_bulk_work').val();
  	  	
			$('#table_bulk_work_wrapper').empty();
			$('#table_bulk_work_wrapper').html(html);
			work_table_bulk = drawDatatable_work('table_bulk_work', langurl, ajaxurl_work, from_bulk_work, to_bulk_work);
		}
	});
	
	$('#filter_activity').click(function(e) {
		e.preventDefault();

  		var html = '<table id="table_activity" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
						html += '<thead>';
						html += '<tr>';
						html += '<th rowspan="2">'+translate('date')+'</th>';
						html += '<th rowspan="2">'+translate('activity_trainning')+'</th>';
						html += '<th rowspan="2">'+translate('comment')+'</th>';
						html += '<th colspan="2">'+translate('activity_duration_th')+'</th>';
						html += '<th rowspan="2">'+translate('vw team event type')+'</th>';
						html += '<th rowspan="2">'+translate('delete')+'</th>';
						html += '</tr>';
						html += '<tr>';
						html += '<th>'+translate('activity_duration_column')+'</th>';
						html += '<th>'+translate('activity_driving_time_column')+'</th>';
						html += '</tr>';
						html += '</thead>';
						html += '</table>';
			
  			var from_activity = $('#from_activity').val();
  	  		var to_activity = $('#to_activity').val();
  			$('#table_activity_wrapper').empty();
			$('#table_activity_wrapper').html(html);
			vw_activity = drawDatatable_activity('table_activity', langurl, ajaxurl_activity, from_activity, to_activity);
			$(vw_activity.table().body()).addClass( 'vwactivity' );
	});
	
	$('.show_saved_data').click(function(e) {
		e.preventDefault();  		
		var target = $(this).data("work_type");
		
		//alert(target);
		var arr_class = $(this).data("arr_class");
		
		if(arr_class == "down"){
			if(target == 'simple_visits')
			{
				var from_simple_visits = $('#from_simple_visits').val();
	  	  		var to_simple_visits = $('#to_simple_visits').val();
				var html = '<table id="table_simple_visits" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
							html += '<thead>';
							html += '<tr>';
							html += '<th>'+translate('#')+'</th>';
							html += '<th>'+translate('Patients')+'</th>';
							html += '<th>'+translate('Grund')+'</th>';
							html += '<th>'+translate('date')+'</th>';
							html += '<th>'+translate('hospizv_amount')+'</th>';
							html += '<th>'+translate('hospizv_duration')+'</th>';
							html += '<th>'+translate('hospizv_distance')+'</th>';
							html += '<th>'+translate('timetravel')+'</th>';
							html += '<th>'+translate('nightshift')+'</th>';
							html += '</tr>';
							html += '</thead>';
							html += '</table>';				
				$('#table_simple_visits_wrapper').html(html);
				
				visits_table_simple = drawDatatable_visits('table_simple_visits', langurl, ajaxurl_visits, from_simple_visits, to_simple_visits);
			}
			else if(target == 'bulk_visits')
			{
				var from_bulk_visits = $('#from_bulk_visits').val();
	  	  		var to_bulk_visits = $('#to_bulk_visits').val();
				var html = '<table id="table_bulk_visits" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
							html += '<thead>';
							html += '<tr>';
							html += '<th>'+translate('#')+'</th>';
							html += '<th>'+translate('Patients')+'</th>';
							html += '<th>'+translate('Grund')+'</th>';
							html += '<th>'+translate('date')+'</th>';
							html += '<th>'+translate('hospizv_amount')+'</th>';
							html += '<th>'+translate('hospizv_duration')+'</th>';
							html += '<th>'+translate('hospizv_distance')+'</th>';
							html += '<th>'+translate('timetravel')+'</th>';
							html += '<th>'+translate('nightshift')+'</th>';
							html += '</tr>';
							html += '</thead>';
							html += '</table>';
				$('#table_bulk_visits_wrapper').html(html);
				
				visits_table_bulk = drawDatatable_visits('table_bulk_visits', langurl, ajaxurl_visits, from_bulk_visits, to_bulk_visits);
			}
			else if(target == 'simple_work')
			{
				var from_simple_work = $('#from_simple_work').val();
	  	  		var to_simple_work = $('#to_simple_work').val();
				var html = '<table id="table_simple_work" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
							html += '<thead>';
							html += '<tr>';
							html += '<th>'+translate('#')+'</th>';
							html += '<th>'+translate('Grund')+'</th>';
							html += '<th>'+translate('date')+'</th>';
							html += '<th>'+translate('hospizv_duration')+'</th>';
							html += '<th>'+translate('hospizv_distance')+'</th>';
							html += '<th>'+translate('timetravel')+'</th>';
							html += '<th>'+translate('nightshift')+'</th>';
							html += '</tr>';
							html += '</thead>';
							html += '</table>';				
				$('#table_simple_work_wrapper').html(html);
				
				work_table_simple = drawDatatable_work('table_simple_work', langurl, ajaxurl_work, from_simple_work, to_simple_work);
			}
			else if(target == 'bulk_work')
			{
				var from_bulk_work = $('#from_bulk_work').val();
	  	  		var to_bulk_work = $('#to_bulk_work').val();
				var html = '<table id="table_bulk_work" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
							html += '<thead>';
							html += '<tr>';
							html += '<th>'+translate('#')+'</th>';
							html += '<th>'+translate('Grund')+'</th>';
							html += '<th>'+translate('date')+'</th>';
							html += '<th>'+translate('hospizv_duration')+'</th>';
							html += '<th>'+translate('hospizv_distance')+'</th>';
							html += '<th>'+translate('timetravel')+'</th>';
							html += '<th>'+translate('nightshift')+'</th>';
							html += '<th>'+translate('actions')+'</th>';
							html += '</thead>';
							html += '</table>';
				$('#table_bulk_work_wrapper').html(html);
				
				work_table_bulk = drawDatatable_work('table_bulk_work', langurl, ajaxurl_work, from_bulk_work, to_bulk_work);
			}
			$(this).removeClass("arrow_down").addClass("arrow_up");
			$(this).data("arr_class","up");
		} else{
			if(target == 'simple_visits')
			{
				$('#table_simple_visits_wrapper').empty();			
				$('#from_simple_visits').val('');
				$('#to_simple_visits').val('');
			}
			else if(target == 'bulk_visits')
			{
				$('#table_bulk_visits_wrapper').empty();			
				$('#from_bulk_visits').val('');
				$('#to_bulk_visits').val('');
			}
			else if(target == 'simple_work')
			{
				$('#table_simple_work_wrapper').empty();			
				$('#from_simple_work').val('');
				$('#to_simple_work').val('');
			}
			else
			{
				$('#table_bulk_work_wrapper').empty();			
				$('#from_bulk_work').val('');
				$('#to_bulk_work').val('');
			}
			$(this).removeClass("arrow_up").addClass("arrow_down");
			$(this).data("arr_class","down");
		}
		
  		$( "#"+target ).toggle();
	});
	
});/*-- END  $(document).ready ----------- --*/

// DATATABLE visits
function drawDatatable_visits(id, langurl, ajaxurl, from, to) {
	if(id == 'table_simple_visits')
	{	
		//alert(to_simple);
		var custom_data = {workerid: workerid, vtype: 'n', from_simple: from, to_simple: to};
		var visparam = false;
	}
	else
	{
		var visparam = true;
		var custom_data = {workerid: workerid, vtype: 'b', from_bulk: from, to_bulk: to};
	}
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
 		"columns": [
 		          { data: "no", className: "", "width": "5%"},
 		          { data: "patient_name", className: "", "width": "40%"},
		          { data: "reason_name", className: "", "width": "10%"},
		          { data: "hospizvizit_date", className: "", "width": "5%"},
		          { data: "amount", className: "", "width": "5%"},
		          { data: "besuchsdauer", className: "", "width": "5%"},
		          { data: "fahrtkilometer", className: "", "width": "5%"},
		          { data: "fahrtzeit", className: "", "width": "5%"},
		          { data: "nightshift_name", className: "", "width": "5%"},
		          { data: "actions", className: "", "width": "5%"}				//TODO-3796 Lore 16.02.2021
			],
			
		"columnDefs": [ 
		          { "targets": 0, "searchable": false, "orderable": false },
		          { "targets": 4, "visible": visparam },
				],
		order: [[3, "desc"]],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
//				 Object.assign(d, custom_data);
//				    return d;
				if(id == 'table_simple_visits')
				{	
					d.workerid = workerid; 
					d.vtype = 'n'; 
					d.from_simple = from;
					d.to_simple = to;
				}
				else
				{
					d.workerid = workerid;
					d.vtype = 'b';
					d.from_bulk = from;
					d.to_bulk = to;
				}
			}			
		}
 	
	});
	
	return table;
}

//DATATABLE work
function drawDatatable_work(id, langurl, ajaxurl, from, to) {
	if(id == 'table_simple_work')
	{	
		//alert(to_simple);
		var visparam = false;
		var custom_data = {workerid: workerid, vtype: 'n', from_simple: from, to_simple: to};
	}
	else
	{
		var visparam = true;
		var custom_data = {workerid: workerid, vtype: 'b', from_bulk: from, to_bulk: to};
	}
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
 		"columns": [
 		          { data: "no", className: "", "width": "5%"},
		          { data: "reason_name", className: "", "width": "10%"},
		          { data: "work_date", className: "", "width": "5%"},
		          { data: "besuchsdauer", className: "", "width": "5%"},
		          { data: "fahrtkilometer", className: "", "width": "5%"},
		          { data: "fahrtzeit", className: "", "width": "5%"},
		          { data: "nightshift_name", className: "", "width": "5%"},
		          { data: "actions", className: "", "width": "5%"}
			],
			
		"columnDefs": [
		          { "targets": -1, "searchable": false, "orderable": false, "visible": visparam },
		          { "targets": 0, "searchable": false, "orderable": false }
				],
		order: [[2, "desc"]],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
//				 Object.assign(d, custom_data);
//				    return d;
				if(id == 'table_simple_work')
				{	
//					var custom_data = {workerid: workerid, vtype: 'n', from_simple: from, to_simple: to};
					d.workerid =  workerid;
					d.vtype =  'n';
					d.from_simple =  from;
					d.to_simple =  to;
				}
				else
				{
//					var custom_data = {workerid: workerid, vtype: 'b', from_bulk: from, to_bulk: to};
					d.workerid =  workerid;
					d.vtype =  'b';
					d.from_bulk =  from;
					d.to_bulk =  to;
				}
			}			
		}
 	
	});
	
	return table;
}

//DATATABLE activity
function drawDatatable_activity(id, langurl, ajaxurl, from, to) {	
	var custom_data = {workerid: workerid, from_activity: from, to_activity: to};

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
 		"columns": [
		          { data: "date", className: "", "width": "8%"},
		          { data: "activity", className: "", "width": "20%"},
		          { data: "comment", className: "", "width": "30%"},
		          { data: "duration", className: "", "width": "5%"},
		          { data: "driving_time", className: "", "width": "5%"},
		          { data: "team_event_type_name", className: "", "width": "5%"},
		          { data: "actions", className: "", "width": "5%"}
			],
			
		"columnDefs": [
		          { "targets": -1, "searchable": false, "orderable": false}
				],
		order: [[0, "desc"]],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
//				 Object.assign(d, custom_data);
//				    return d;
				d.workerid = workerid; 
         		d.from_activity = from;
         		d.to_activity = to;
			}			
		}
	});
	
	return table;
}