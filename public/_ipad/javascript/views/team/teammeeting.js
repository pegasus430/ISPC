/* teammeeting.js */
;;

var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl_file =appbase + 'team/teammeetinghistorylist';
var ajaxurl_patient =appbase + 'team/teammeetingpatientsearchlist';

var left_menu_list_file;
var table_patient;
var custom_data;

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	table_patient = drawDatatable('table_patient', langurl, ajaxurl_patient);
	window.left_menu_list_file = drawDatatable('table_file', langurl, ajaxurl_file);
	
	$('#checkall_pat').live("click", function() {
		checkbox_pat = $('.select_patients');
		
		if($(this).is(":checked"))
		{
			for(i = 0; i < checkbox_pat.length; i++)
			{
				checkbox_pat[i].checked = true;
			}
	    } else {
			for(i = 0; i < checkbox_pat.length; i++)
			{
				checkbox_pat[i].checked = false;
			}
	    }
		
	});
	
	//add extra patients 
	$("#extra_patients-dialog").dialog({
        autoOpen: false,
        height: 500,
        width: 650,
        modal: true,
        open: function () {

        },

	    buttons:[{
	    	text: translate('save'),
			click: function(){

				
				var cnt_existing = $('#number_of_existing_patients').val();
				var cnt_start = Number(cnt_existing) +1;
				
//						alert(cnt_start);
				 var selected_patients = $('.select_patients:checked').map(function() {
					//  form an array with all the data -> get details of patients 							 
					    return this.value;
					}).get();

				 $('#extra_patients').val(selected_patients);

				 // submit form
				 $('#save_by_modal').val('1');
				  formmodified = 0;
				 $('#teammeeting').submit();
				 $("#extra_patients-dialog").dialog("close");
					$('#MainContent').block({
						css: {
							border: 'none',
							padding: '5px',
							backgroundColor: '#000',
							'-webkit-border-radius': '5px',
							'-moz-border-radius': '5px',
							opacity: .5,
							color: '#fff',
							height: 'auto'
						},
						message: '<h1 style="padding:5px;">Verarbeitung</h1><img src="'+appbase+'/images/ajax-loader-bar.gif">'
					});

			}},
			{
			text: translate('cancel'),
			click: function(){
				 $("#extra_patients-dialog").dialog("close");		
			}
		    }],
        
        close: function() {
    
        }
	});
	                       
	$('.add_extra_patients').live('click', function(){
    	$("#extra_patients-dialog").dialog("open");
	     return false;
	});
    	
	$('.delete_patient').live('click', function(){
		var rowid = $(this).attr('rel');
		jConfirm(translate('are you sure you want to remove patient from list?'), '', function(r) {
			if(r)
			{
				$('.generated_'+rowid).remove();
				$('.appended_'+rowid).remove();
			}
			
		});
	});
		
	$('.delete_patient_row').live('click', function(){
		var rowid = $(this).attr('rel');
		$('#'+rowid).remove();
		
		//get patient_id and user_id from row_id
		var row_elements = rowid.split('_');
//			console.log($('.appended_'+row_elements[0]).length);
		if($('.appended_'+row_elements[0]).length > '0') {
			var new_rowspan = parseInt(parseInt($('.appended_'+row_elements[0]).length) + 1);

			$('td.fixed_cell_' + row_elements[0]).each(function () {
				if($('#todos_'+row_elements[0]).length > '0' && $(this).hasClass( "spantodo" )) {
			    	$(this).attr('rowspan', new_rowspan+1);
				}
				else
				{
					$(this).attr('rowspan', new_rowspan);
				}
			});
		} else {
			$('td.fixed_cell_' + row_elements[0]).each(function () {
				if($('#todos_'+row_elements[0]).length > '0' && $(this).hasClass( "spantodo" )) {
					$(this).attr('rowspan', '2');
				}
				else
				{
					$(this).removeAttr('rowspan');
				}
			});
		}
	});
	
	//ISPC-2896 Lore 23.04.2021
	$('.add_new_patient_row').live('click', function(){
		var patientid = $(this).attr('rel');
		new_patient_row(patientid);
	});
		
	$('#patientsearch_team_meeting').live('keyup', function(){
		if($('#checkall_pat').is(":checked"))
		{
			$('#checkall_pat').prop( "checked", false );
		}
		var field_value = $(this).val();
		
		var myRadio = $('input[name=status]');
		var patient_status = myRadio.filter(':checked').val();
		// buba daca nu am nici un status selectat
		if(typeof patient_status == 'undefined')
		{
			patient_status ='standby';
			$('input[name=status][value=standby]').attr('checked','checked');
		}
		custom_data = {meetingid: meetingid, field_value: field_value, status: patient_status};
		
		if(field_value.length > 2){
			//load_extra_patients(field_value,patient_status);
			table_patient.ajax.reload();
			$('#patient_status_tr').html(translate(patient_status+'_tr'));
			$('#patients_resulted').show();
		}
	});
	
	$('input[name=status]').live('click', function(){
		if($('#checkall_pat').is(":checked"))
		{
			$('#checkall_pat').prop( "checked", false );
		}
		field_value = $('#patientsearch_team_meeting').val();
		
		var myRadio = $('input[name=status]');
		patient_status = myRadio.filter(':checked').val();
		
		custom_data = {meetingid: meetingid, field_value: field_value, status: patient_status}; 
		
		table_patient.ajax.reload();
		
		$('#patient_status_tr').html(translate(patient_status+'_tr'));
		$('#patients_resulted').show();							
		
		//load_extra_patients(field_value,patient_status);
		
	});
	
//	$('input[name^=meeting]').live('', function(){
	//$('textarea[name^=meeting]:not(.users_livesearch)').live('keyup', function(){
	$('textarea[name^=meeting]:not(.users_livesearch)').live('input change keyup', function(){ //ISPC - 2161 p.5
		var elem_id = $(this).attr('id');
		var element_parts = elem_id.split('_');
		var element_type = element_parts[0];
		var patient_id = element_parts[1];
		var patient_row = element_parts[2];
//		console.log("element parts");
//		console.log(element_parts);
		
		$('#patient_master_'+patient_id).removeAttr('disabled');
		
//		if(check_for_new_row(patient_id, patient_row)) {
		//ISPC-2896 Lore 23.04.2021
		if(check_for_new_row(patient_id, patient_row, element_type)) {
			//create new rows
			new_patient_row(patient_id);
		}
	});
	
	
});/*-- END  $(document).ready ----------- --*/

//DATATABLE
function drawDatatable(id, langurl, ajaxurl) {
	if(id == 'table_file')
	{
		var columns = [
		 		          { data: "create_date", className: "", "width": "10%"},
				          { data: "title", className: "", "width": "40%"},
				          { data: "filetype", className: "", "width": "5%"},
				          { data: "create_user_name", className: "", "width": "20%"},
				          { data: "actions", className: "", "width": "5%", "searchable": false, "orderable": false}
					];
		var order = [0, "desc"];
		custom_data = {meetingid: meetingid_del};
		var paginate = true;
		var info = true;
		var domcontent = '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
		't'+'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>';
	}
	else
	{
		var columns = [
		 		          { data: "checkloc", className: "","width": "2%", "searchable": false, "orderable": false},
				          { data: "epid", className: "", "width": "10%"},
				          { data: "first_name", className: "", "width": "20%"},
				          { data: "last_name", className: "", "width": "20%"}				          
					];
		var order = [1, "asc"];
		custom_data = {meetingid: meetingid, field_value: field_value, status: patient_status};
		var paginate = false;
		var info = false;
		var domcontent = '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
		't';
	}
	
	var table = $('#'+id).DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom": domcontent,
			//'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			//'t'+
			//'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
			
		"processing": true,

		"info": info,
		"filter": true,
		"paginate": paginate,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": false,
		"scrollX": false,
		"scrollCollapse": true,
 		/*"columns": [
 		          { data: "create_date", className: "", "width": "10%"},
		          { data: "title", className: "", "width": "40%"},
		          { data: "filetype", className: "", "width": "5%"},
		          { data: "create_user_name", className: "", "width": "20%"},
		          { data: "actions", className: "", "width": "5%"}
			],*/
		"columns" : columns,
			
		"columnDefs": [ 
				       	
				],
		order: [order],
		//order: [],
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
				// Object.assign(d, custom_data);
				//    return d;
				if(id == 'table_file')
				{
					d.meetingid =  custom_data.meetingid_del;
				} else{
					d.meetingid= custom_data.meetingid;
					d.field_value= custom_data.field_value;
					d.status= custom_data.status;
				}   
				    
			}			
		}
 	
	});
	
	return table;
}

function prefill_team_problem(_this){

	var _pat = $(_this).parents('tr').find('input[name="patient\[\]"]').val();
	
	$(_this).prop('onclick',null).off('click');//once click allowed on the button
	
	/* keepig the tradition alive */
	var elem_id = $(_this).attr('id');
	var element_parts = elem_id.split('_');
	var element_type = element_parts[0];
	var patient_id = element_parts[1];
	var patient_row = element_parts[2];
	
	var meeting_start_date = $('#meeting_date').val();
	var meeting_start_time = $('#meeting_time_start').val();
	
	$.ajax({
        "dataType": 'json',
        "type": "POST",
        "url": appbase + "team/prefillteamproblem",
        "async" : false,
        "data": {
        	'ipid' : _pat,
        	'actual_id' : meetingid,
        	'meeting_start_date' : meeting_start_date,
        	'meeting_start_time' : meeting_start_time
        	},
   		"success": function( data ){ 

//   			$(_this).hide();//hide the button.. for now leave visible
   			$('#patient_master_'+patient_id).removeAttr('disabled');
   			
   			if (data.length > 0) {
   				$(data).each(function(i, obj){
   					new_patient_row(patient_id, obj);
   					changes = true;
   				});
   			} else{
   				alert("Keine Daten vorhanden aus der letzten Teambesprechung");
   			} 
   		},
   		"error" : function(xhr, ajaxOptions, thrownError){
   			
   		}
  	});
};

function check_for_new_row(patient, row, column) {		//ISPC-2896 Lore 23.04.2021 --- added column
	
	//check if the curently edited row id is less than last generated id
	var excedded_curent_id = false
	if ($('tr.appended_' + patient).length) {
		var last_row_id = $('tr.appended_'+patient+':last').attr('id').split('_')[1];
		
		if (row < last_row_id) {
			excedded_curent_id = true;
		} else {
			excedded_curent_id = false;
		}
	}
	
	//check if there is allready next row created
	//also check if curent edit rowid is smaller than last row (excedded_curent_id)
	//if(($('textarea#problems_'+patient+'_'+row).val().length > '0' || $('textarea#todo_'+patient+'_'+row).val().length > '0') && 
	//ISPC-2896 Lore 23.04.2021
	if(($('textarea#'+column+'_'+patient+'_'+row).val().length > '0') && 
			($('tr#'+patient+'_'+ parseInt(parseInt(row)+1)).length == '0' && !excedded_curent_id))
	{
		return true;
	} 
}

function new_patient_row(patient , defaults)
{
	var _defaults = defaults || {};
	var row_html = '';
	var row_html_users = '';
	var status_team = $('#status').val();
	
	//new row id is based on last patient row, not the last rowid
	
	if ($('tr.appended_' + patient + ':last').length > '0') {
		var last_row_elements = $('tr.appended_'+patient+':last').attr('id').split('_');
		var previous_row = last_row_elements[1];
		$('teaxtarea#problems_'+patient+'_'+previous_row).val(_defaults.problem);
	}
	else if($('tr.generated_'+patient+':last').length > '0')
	{
		var last_row_elements = $('tr.generated_'+patient+':last').attr('id').split('_');
		var previous_row = last_row_elements[1];
		$('textarea#problems_'+patient+'_'+previous_row).val(_defaults.problem);
	}
	var row_incr = parseInt(parseInt(previous_row) + 1);
	
	//add rowspan to the parent patient row 
	$('td.fixed_cell_'+patient).each(function() {
		if($(this).hasClass( "spantodo" ))
		{
			var rowspan_value  = parseInt(parseInt($('.appended_'+patient).length)+3);
		}
		else
		{
			var rowspan_value  = parseInt(parseInt($('.appended_'+patient).length)+2);
		}
		$(this).attr('rowspan', rowspan_value);
	});
	
//	row_html_users += '<table id="selected_users_'+patient+'_'+row_incr+'" class="table_asigned_users" style="display: none;>';
//	row_html_users +=	'<tr class="assigned_user_row_header">';
//	row_html_users +=		'<th class="last_name_row">';
//	row_html_users +=			'<?php echo $this->translate('lastname'); ?>';
//	row_html_users +=		'</th>';
//	row_html_users +=		'<th class="first_name_row" colspan="2">';
//	row_html_users +=			'<?php echo $this->translate('firstname'); ?>';
//	row_html_users +=		'</th>';
//	row_html_users +=	'</tr>';
//	row_html_users += '</table>';
	
	//append new rowspanned rows
	row_html += '<tr class="appended_'+patient+'" id="'+patient+'_'+row_incr+'">';
	if (showTODOcolumn != 'no') {
		row_html += '<td>';
		row_html += '<input type="checkbox" name="meeting[send_todo]['+patient+']['+row_incr+']" id="send_todo_'+patient+'_'+row_incr+'" value="1" />';
		row_html += '</td>';
	}
	
	if (showXTcolumn) {
		row_html += '<td>';
		row_html += '<input type="checkbox" name="meeting[verlauf]['+patient+']['+row_incr+']" id="verlauf_'+patient+'_'+row_incr+'" value="1" />';
		row_html += '</td>';
	}
	
	if(status_team == "1"){ 
		//ISPC-2896 Lore 23.04.2021
		if (show_treatment_process != 'no') {
			row_html += '<td>';
			//		row_html +=			'<input type="text" name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" autocomplete="off"/>';
			row_html += '<textarea name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
			row_html += '</td>';
		}

		
		//ISPC-2556 Lore 26.06.2020
		row_html += '<td>';
		//		row_html +=			'<input type="text" name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" autocomplete="off"/>';
		row_html += '<textarea name="meeting[targets]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
		row_html += '</td>';
		//.

		if (showactioncolumn != 'no') {
			row_html += '<td>';
					//		row_html +=			'<input type="text" name="meeting[todo]['+patient+']['+row_incr+']" value="" id="todo_'+patient+'_'+row_incr+'" autocomplete="off"/>';
			row_html += '<textarea name="meeting[todo]['+patient+']['+row_incr+']" value="" id="todo_'+patient+'_'+row_incr+'" rows="5" cols="20"></textarea>';
			row_html += '</td>';
		}
	} else { 
		//ISPC-2896 Lore 23.04.2021
		if (show_treatment_process != 'no') {
			row_html += '<td>';
			//		row_html +=			'<input type="text" name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" autocomplete="off"/>';
			row_html += '<textarea name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" rows="5" cols="25" ></textarea>';
			row_html += '</td>';
		}
		
		//ISPC-2556 Lore 26.06.2020
		row_html += '<td>';
		//		row_html +=			'<input type="text" name="meeting[problem]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" autocomplete="off"/>';
		row_html += '<textarea name="meeting[targets]['+patient+']['+row_incr+']" value="" id="problems_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
		row_html += '</td>';
		//.
		
		if (showactioncolumn != 'no') {
			row_html += '<td>';
					//		row_html +=			'<input type="text" name="meeting[todo]['+patient+']['+row_incr+']" value="" id="todo_'+patient+'_'+row_incr+'" autocomplete="off"/>';
			row_html += '<textarea name="meeting[todo]['+patient+']['+row_incr+']" value="" id="todo_'+patient+'_'+row_incr+'" rows="5" cols="25"></textarea>';
			row_html += '</td>';
		}
	}
	
	//ISPC-2896 Lore 19.04.2021
	if (show_problems) {
		row_html += '<td>';
		row_html += '<textarea name="meeting[currentsituation]['+patient+']['+row_incr+']" value="" id="currentsituation_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
		row_html += '</td>';
		row_html += '<td>';
		row_html += '<textarea name="meeting[hypothesisproblem]['+patient+']['+row_incr+']" value="" id="hypothesisproblem_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
		row_html += '</td>';
		row_html += '<td>';
		row_html += '<textarea name="meeting[measuresproblem]['+patient+']['+row_incr+']" value="" id="measuresproblem_'+patient+'_'+row_incr+'" rows="5" cols="20" ></textarea>';
		row_html += '</td>';
	}
	
	//===  module status		
    if(status_team == "1"){
	row_html += '<td>';
	row_html += '<select name="meeting[status]['+patient+']['+row_incr+']"  id="status_'+patient+'_'+row_incr+'" >';
	if(t == "0")
	{
		row_html +=' <option value="0" selected="selected"></option>';
	}
	else
	{
		row_html +=' <option value="0"></option>';
	}
	if(t == "1")
	{
		row_html +=' <option value="1" selected="selected">'+translate("rehabilitativ")+'</option>';
	}
	else
	{
		row_html +=' <option value="1">'+translate("rehabilitativ")+'</option>';
	}
	if(t == "2")
	{
		row_html +=' <option value="2" selected="selected">'+translate("pre-final")+'</option>';
	}
	else
	{
		row_html +=' <option value="2">'+translate("pre-final")+'</option>';
	}
	if(t == "3")
	{
		row_html +=' <option value="3" selected="selected">'+translate("final")+'</option>';
	}
	else
	{
		row_html +=' <option value="3">'+translate("final")+'</option>';
	}
	if(t == "4")
	{
		row_html +=' <option value="4" selected="selected">'+translate("terminal")+'</option>';
	}
	else
	{
		row_html +=' <option value="4">'+translate("terminal")+'</option>';
	}
	row_html +='</select>';
	row_html += '</td>';
    }
    //=== end module status
	
    if (showuserscolumn != 'no') {
		row_html += '<td>';
		row_html += '<input type="text" name="meeting[user]['+patient+']['+row_incr+']" value="" class="users_livesearch" rel="'+patient+'_'+row_incr+'" id="select_user_txt_'+patient+'_'+row_incr+'" />';
	//	row_html +=			'<input type="text" class="users_livesearch" rel="'+patient+'_'+row_incr+'" id="select_user_txt_'+patient+'_'+row_incr+'" />';
	//	row_html +=			row_html_users;
		row_html += '</td>';
    }
	row_html += '<td>';
	row_html += '<img src="'+res_file_path+'/images/action_delete.png" rel="'+patient+'_'+row_incr+'" class="delete_patient_row" />';
	row_html += '</td>';
	row_html += '</tr>';
	
	$(row_html).insertAfter($('tr#' + patient + '_' + previous_row));
	
	$('#select_user_txt_' + patient + '_' + row_incr).tagit({
		autocomplete : {
			source : users_data,
			delay : 0
		},
		placeholderText : "Benutzer",
		allowDuplicates : true,
		allowSpaces : true,
		hiddenInput : true,
		showAutocompleteOnFocus : true,
		beforeTagAdded : function(event, ui) {
			if (!ui.tag.children('input[type="hidden"]').val()) {
				return false;
			}
		}
	});
}
