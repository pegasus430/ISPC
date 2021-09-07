var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl_visits =appbase + 'voluntaryworkers/gethospizworkervisits';
var ajaxurl_work =appbase + 'voluntaryworkers/getworkerwork';
var ajaxurl_activity =appbase + 'voluntaryworkers/getworkeractivity';
var ajaxurl_assigns =appbase + 'voluntaryworkers/getworkerassigns';

var visits_table_simple;
var visits_table_bulk;
var work_table_simple;
var work_table_bulk;
var vw_activity;
var vw_assigns;

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
	$(document).on('change', '#table_activity :input', function() {
		var tr_changed = $(this).closest('tr');
		var col_changed = $(this).attr('name').slice($(this).attr('name').lastIndexOf("[")+1, $(this).attr('name').lastIndexOf("]"));
		if(typeof activity_changed[tr_changed.find('input').eq(1).val()] == 'undefined')
		{
			activity_changed[tr_changed.find('input').eq(1).val()] = {
					"change": "modified"	
			}
		}
		activity_changed[tr_changed.find('input').eq(1).val()][col_changed] = $(this).val();
		
	});
	

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
		
		/*$('#activity_table').append(tr
				+a_date
				+a_activity
				+a_comment
				+a_duration
				+a_driving_time
				+a_team_event
				+a_delete
				+tr_end);*/
		
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
		$("#a_date_"+row_status).mask("99.99.9999");

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

    //ISPC-2834,Elena,24.03.2021
    //edit row visits
	var visit_type_for_editing;
	$('.edit_hvisit_data').live('click', function() {
		var idd = $(this).data('id');
		var visit_type = $(this).data('vtype');
		visit_type_for_editing = $(this).data('vtype');;
		$('#edit_hvisit_bulk_dialog').load(appbase+'voluntaryworkers/edithvisitdata?vid='+idd, function() {
			$('#edit_hvisit_bulk_dialog').dialog('open');
		});

	})

	$('#edit_hvisit_bulk_dialog').dialog({
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
					//console.log('postdata', $('#addhospizv').serialize());
					$.ajax({
						type: 'POST',
						url: 'voluntaryworkers/edithvisitdata?vid=' + $('#vid0').val(),
						data: $('#addhospizv').serialize(),
						success:function(data){
							//console.log('alldata', data);
							var jdata;
							try {
								jdata = JSON.parse(data);
							}catch(e){
									console.log('parse error');
									console.log(data);
									$('#hvisit_err').html("Etwas ist schiefgegangen, versuchen Sie bitte später nochmals");
							}

							//console.log('parsed',jdata);
							if(jdata.success == true){
								if(visit_type_for_editing == "n"){
									visits_table_simple.ajax.reload();
								} else{
									visits_table_bulk.ajax.reload();
								}
								$('#edit_hvisit_bulk_dialog').dialog('close');
								$('#edit_hvisit_bulk_dialog').html('');


							}else if(jdata.success == false){
								var emsg = '';
								for(errindex in jdata.errors){
									emsg += jdata.errors[errindex] + ' ';
								}
								$('#hvisit_err').html(emsg);
							}

						}
					});
				}
			}]
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

	
	// ####################
	// patient associations ISPC-2401 11)
	// ####################

	$('.assign_patient_date').each(function(){
		
		var rowid = $(this).data('rowid');
	    //alert("var rowid este: "+rowid);

		$('#patient_assign_start_'+rowid).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",					
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: $("#patient_assign_end_"+rowid).val(),
			onSelect: function( selectedDate ) {
	              $("#patient_assign_end_"+rowid).datepicker( "option", "minDate", selectedDate );
			}
		});
		
		$('#patient_assign_end_'+rowid).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",					
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			minDate: $("#patient_assign_start_"+rowid).val(),
			onSelect: function( selectedDate ) {
	              $("#patient_assign_start_"+rowid).datepicker( "option", "maxDate", selectedDate );
	      }
		});
	});

	
	$('.add_new_patient_line').live('click',function(){

		var row_status = $('#associated_patients').val();

		var tr ='<tr class="patient_row">';
		var start_date ='<td><input type="text" name="patient_vw['+row_status+'][start]" class="assign_patient_date" value="" id="patient_assign_start_'+row_status+'"   /><input type="hidden" name="patient_vw['+row_status+'][custom]" value="1" /></td>';
		var end_date ='<td><input type="text" name="patient_vw['+row_status+'][end]"  class="assign_patient_date"  value="" id="patient_assign_end_'+row_status+'"     /></td>';
		var epid ='<td><input type="text" name="patient_vw['+row_status+'][patient_epid]" value="" id="patient_epid'+row_status+'" style="width:100px;" /></td>';
		var patient ='<td><input type="text" name="patient_vw['+row_status+'][patient]" value="" id="patient'+row_status+'"  /><input type="hidden" name="patient_vw['+row_status+'][patientid]" value="" id="patientid'+row_status+'"  /></td>';
		var a_delete ='<td><a href="javascript:void(0)" class="delete_row" rel="" id="delete_"><img src="'+res_path+'/images/action_delete.png" /></a></td>';
		var tr_end = '</tr>';
		$('#patient_table').append(tr+start_date+end_date+epid+patient+a_delete+tr_end);
		
		$('#patient_assign_start_'+row_status).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",					
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function( selectedDate ) {
	              $("#patient_assign_end_"+row_status).datepicker( "option", "minDate", selectedDate );
			}
		});
		
		$('#patient_assign_start_'+row_status).mask("99.99.9999");
		
		
		
		$('#patient_assign_end_'+row_status).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",					
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function( selectedDate ) {
	              $("#patient_assign_start_"+row_status).datepicker( "option", "maxDate", selectedDate );
	      }
		});
		$('#patient_assign_end_'+row_status).mask("99.99.9999");
		
		
		$('#patient_epid'+row_status).bind('keyup keydown change paste',function(){
			//livesearch patients ls
			$(this).live('change', function() {
				var input_row = parseInt($(this).attr('id').substr(('patient_epid').length));
				reset_patients(input_row);
			}).liveSearch({
				url: 'ajax/patientsearchvoluntaryworker?q=',
				id: 'livesearch_vw_patients',
				aditionalWidth: '62',
				noResultsDelay: '1200',
				typeDelay: '1200',
				returnRowId: function (input) {return parseInt($(input).attr('id').substr(('patient_epid').length));}
			});
		});
		
		$('#patient'+row_status).bind('keyup keydown change paste',function(){
			//livesearch patients ls
			$(this).live('change', function() {
				var input_row = parseInt($(this).attr('id').substr(('patient').length));
				reset_patients(input_row);
			}).liveSearch({
				url: 'ajax/patientsearchvoluntaryworker?q=',
				id: 'livesearch_vw_patients',
				aditionalWidth: '62',
				noResultsDelay: '1200',
				typeDelay: '1200',
				returnRowId: function (input) {return parseInt($(input).attr('id').substr(('patient').length));}
			});
		});
		
		var new_row = parseInt(row_status) + 1;
		$('#associated_patients').val(new_row);
		

	});
	
	

	//ISPC-2401 3)
	tinyMCE.init({

		// General options
		//plugins :"-example",
		mode : "exact",
		language : "en",
		elements : "fdcomments, reminder_text",
		theme : "advanced",
		relative_urls : false,
		absolute_urls : true,

		file_browser_callback : "openSwampyBrowser",
//		entity_encoding : "raw",


		plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		// Theme options
		theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|, fontsizeselect|,forecolor,backcolor",
		theme_advanced_buttons2: "",
		theme_advanced_buttons3: "",
		theme_advanced_buttons4: "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false,
		content_css : res_path+"/css/style.css",
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});

	 /*ISPC-2401 11)*/
	var yearassg = $('#year_assg').val();
	vw_assigns = drawDatatable_patientassg('patient_table', langurl, ajaxurl_assigns, yearassg);
	$(vw_assigns.table().body()).addClass( 'PatientDetail_datatable' );
	
	$('#year_assg').on('change',function(){
        var yearassg = $("#year_assg option:selected").val();
       // var yearassg = $("#year_assg option:selected").text();
       // alert("anul ales: "+yearassg);
        
      		var html = '<table id="patient_table" class="table table-striped table-bordered table-hover table-condensed dataTable no-footer">';
    						html += '<thead>';
    						html += '<tr>';
    						html += '<th >'+translate('start_association_date')+'</th>';
    						html += '<th >'+translate('end_association_date')+'</th>';
    						html += '<th >'+translate('epid')+'</th>';
    						html += '<th >'+translate('patientname')+'</th>';
    						html += '<th >'+translate('delete')+'</th>';
    						html += '</tr>';
    						html += '</thead>';
    						html += '</table>';
    			
    			 $('#patient_table_wrapper').empty();
    			 $('#patient_table_wrapper').html(html);
    			 
    			vw_assigns = drawDatatable_patientassg('patient_table', langurl, ajaxurl_assigns, yearassg);
    			$(vw_assigns.table().body()).addClass( 'PatientDetail_datatable' );
    
    });
});/*-- END  $(document).ready ----------- --*/

// DATATABLE visits
function drawDatatable_visits(id, langurl, ajaxurl, from, to) {
	if(id == 'table_simple_visits')
	{	
		//alert(to_simple);
		//var custom_data = {workerid: workerid, vtype: 'n', from_simple: from, to_simple: to};
		var visparam = false;
	}
	else
	{
		var visparam = true;
		//var custom_data = {workerid: workerid, vtype: 'b', from_bulk: from, to_bulk: to};
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
		//var custom_data = {workerid: workerid, vtype: 'n', from_simple: from, to_simple: to};
	}
	else
	{
		var visparam = true;
		//var custom_data = {workerid: workerid, vtype: 'b', from_bulk: from, to_bulk: to};
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

				d.workerid = workerid;
				if(id == 'table_simple_work')
				{
				 //Object.assign(d, custom_data);
					d.vtype = 'n';
					d.from_simple = from;
					d.to_simple = to;
				}
				else
				{
					d.vtype = 'b';
					d.from_bulk = from;
					d.to_bulk = to;
				}
				return d;
			}
 		}
 	
	});
	
	return table;
}

//DATATABLE activity
function drawDatatable_activity(id, langurl, ajaxurl, from, to) {
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
				//var custom_data = {workerid: workerid, from_activity: from, to_activity: to, changedrows: activity_changed};
				d.workerid = workerid;
				d.from_activity = from;
				d.to_activity = to;
				d.changedrows = activity_changed;
				activity_changed = {};
				 //Object.assign(d, custom_data);
					return d;
			}

		},
		initComplete: function()
		{
			$('.vw_date').datepicker({
				dateFormat: 'dd.mm.yy',
				showOn: "both",					
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: ''
			});
			$(".vw_date").mask("99.99.9999");
			
		}
	});
	
	return table;
}

//datatable patient associations ISPC-2401 11)
function drawDatatable_patientassg(id, langurl, ajaxurl, year) {
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
 		"columns": [
		          { data: "start_association_date", className: "", "width": "15%"},
		          { data: "end_association_date", className: "", "width": "15%"},
		          { data: "epid", className: "", "width": "10%"},
		          { data: "patientname", className: "", "width": "10%"},
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
				d.workerid = workerid;
				d.yearassg = year;
				d.changedrows = activity_changed;
				activity_changed = {};
					return d;
			}

		},
		
		initComplete: function()
		{
			
			
			$('.assign_patient_date').datepicker({
				dateFormat: 'dd.mm.yy',
				showOn: "both",					
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: ''
			});
			$(".assign_patient_date").mask("99.99.9999");
 
			
			 
		}
		
		
	});
	
	return table;
}



