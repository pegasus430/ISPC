/**
careplanning.js 
ISPC-2921 Ancuta 27.05.2021
 */ 
;;

var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl_file =appbase + 'team/teammeetinghistorylist';
var ajaxurl_patient =appbase + 'team/teammeetingpatientsearchlist';

var left_menu_list_file;
var table_patient;
var custom_data;

$(document).ready(function() { /*------ Start $(document).ready --------------------*/


$(document).off('change',".thema_select").on('change', '.thema_select', function(){
	change_select($(this));
	patientid = $(this).data('cat');
	new_patient_row(patientid);
});

$('.saved_thema_select').each(function(){
	
	//change_select($(this));
})
    
        	$('.date').datepicker({
				dateFormat: 'dd.mm.yy',
				showOn: "both",
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: '',
				maxDate: '+2y',
				minDate: '-1y',
			});
	
	$('.todo_selectbox', this).chosen({
		placeholder_text_single: translate('please select'),
		placeholder_text_multiple : translate('please select'),
		multiple:1,
		width:'500px',
		style: "padding-top:10px",
		"search_contains": true,
		no_results_text: translate('noresultfound')
	});

		
	$('.delete_patient_row').live('click', function(){
		var rowid = $(this).attr('rel');
		var category = $(this).data('category');
		$('#'+category+'_'+rowid).remove();
		

alert($('.appended_'+category).length);
	
		if($('.appended_'+category).length > '0') {				
			var new_rowspan = parseInt(parseInt($('.appended_'+category).length) + 1);

			$('td.fixed_cell_' + category).each(function () {
				if( $(this).hasClass( "spantodo" )) {
			    	$(this).attr('rowspan', new_rowspan+1);
				}
				else
				{
					$(this).attr('rowspan', new_rowspan);
				}
			});
		} else {
			$('td.fixed_cell_' + category).each(function () {
				if( $(this).hasClass( "spantodo" )) {
					$(this).attr('rowspan', '2');
				}
				else
				{
					$(this).removeAttr('rowspan');
				}
			});
		}
	});
	
		
	$('.add_new_patient_row').live('click', function(){
		
		var patientid = $(this).attr('rel');
		new_patient_row(patientid);
	});
 
	
});/*-- END  $(document).ready ----------- --*/

var patient_rows = []; //IM-162,elena, 08.12.2020

 

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

function new_patient_row(patient , defaults, area_type) //IM-162,elena,03.12.2020
{
	var _defaults = defaults || {};

	var row_html = '';

	//new row id is based on last patient row, not the last rowid
	
	if ($('tr.appended_' + patient + ':last').length > '0' && patient_rows[patient] !== true) { 
		var last_row_elements = $('tr.appended_'+patient+':last').attr('id').split('_');
		var previous_row = last_row_elements[1];
	}
	else if($('tr.generated_'+patient+':last').length > '0')
	{
		var last_row_elements = $('tr.generated_'+patient+':last').attr('id').split('_');
		var previous_row = last_row_elements[1];
 
	}

	var row_incr = parseInt(parseInt(previous_row) + 1);
	

	if(patient_rows[patient] === true){ 
	    return;
    }

	//add rowspan to the parent patient row 
	$('td.fixed_cell_'+patient).each(function() {
		if($(this).hasClass( "spantodo" ))
		{
			var rowspan_value  = parseInt(parseInt($('.appended_'+patient).length)+2);
		}
		else
		{
			var rowspan_value  = parseInt(parseInt($('.appended_'+patient).length)+2);
		}			
		$(this).attr('rowspan', rowspan_value);
	});

	//append new rowspanned rows
	row_html += '<tr class="appended_'+patient+'" id="'+patient+'_'+row_incr+'">';
    //patient_rows[patient] = true; //IM-162,elena,04.1.2.2020
 

	// problem 
	row_html += '<td id="thema_'+patient+'_'+row_incr+'">';
	row_html += '</td>';
  
	//Pflegethema - Pflegeproblem
	row_html += '<td id="col_probleme_'+patient+'_'+row_incr+'" class="p_subselect_col">';
	row_html += '</td>';
	//Pflegethema - Pflegemaßnahmen
	row_html += '<td id="col_massnahmen_'+patient+'_'+row_incr+'" class="p_subselect_col">';
	row_html += '</td>';
	//Pflegethema - Ressourcen
	row_html += '<td id="col_ressourcen_'+patient+'_'+row_incr+'" class="p_subselect_col">';
	row_html += '</td>';
	//Pflegethema - Pflegeziel
	row_html += '<td id="col_ziele_'+patient+'_'+row_incr+'" class="p_subselect_col">';
	row_html += '</td>';
	//Evaluationskriterien
	row_html += '<td>';
	row_html += '<textarea rows="5" cols="25"></textarea>';
	row_html += '</td>';
	//Kommentar zu Evaluation
	row_html += '<td>';
	row_html += '<textarea rows="5" cols="25"></textarea>';
	row_html += '</td>';

	row_html += '<td>';
//	row_html += '<img src="'+res_file_path+'/images/action_delete.png"  class="delete_patient_row" data-category="'+patient+'" rel="'+row_incr+'"  />';
//	row_html += '<img src="'+res_file_path+'/images/action_add.png" rel="'+patient+'" class="add_new_patient_row" />';
	row_html += '</td>';
	row_html += '</tr>';


	$(row_html).insertAfter($('tr#' + patient + '_' + previous_row));


	
	var ajaxurl_select = appbase + 'patientformnew/careplanningselect';

	xhr = $.ajax({
		url : ajaxurl_select,
		data: {
			cat: patient,
			problem: "",
			line_nr: row_incr
		},
		success : function(response) {
			var returned_data = jQuery.parseJSON(response);
			if(returned_data !== null){
				$('#thema_'+patient+'_'+row_incr).html(returned_data.main_options);
				$('#thema_select_'+patient+'_'+row_incr).css("width","250px;")
//				$('#thema_select_'+patient+'_'+row_incr).change(function(){
//					change_select($(this));
//				});
				
			} else{
			}
			
		}
	});
	$('#line_nr_'+patient).val(row_incr);
}

function change_select(_that){
	var category = $(_that).data('cat');
	var value = $(_that).val();
	var line_nr = $(_that).attr('id').split('_')[3];

	var ajaxurl_select = appbase + 'patientformnew/careplanningselect';

	xhr = $.ajax({
		url : ajaxurl_select,
		data: {
			cat: category,
			problem: value,
			line_nr: line_nr
		},
		success : function(response) {
			var returned_data = jQuery.parseJSON(response);
			if(returned_data !== null){
				$('#col_probleme_'+category+'_'+line_nr).html(returned_data.col_probleme);
				$('#col_ressourcen_'+category+'_'+line_nr).html(returned_data.col_ressourcen);
				$('#col_ziele_'+category+'_'+line_nr).html(returned_data.col_ziele);
				$('#col_massnahmen_'+category+'_'+line_nr).html(returned_data.col_massnahmen);
			} else{
				$('#col_probleme_'+category+'_'+line_nr).html("");
				$('#col_ressourcen_'+category+'_'+line_nr).html("");
				$('#col_ziele_'+category+'_'+line_nr).html("");
				$('#col_massnahmen_'+category+'_'+line_nr).html("");
			}
			
		}

	});
	
}



