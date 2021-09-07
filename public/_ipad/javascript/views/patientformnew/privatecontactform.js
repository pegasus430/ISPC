/**
privatecontactform.js 
ISPC-2909 Ancuta 01.06.2021
 */ 
;;


$(document).ready(function() { /*------ Start $(document).ready --------------------*/
  
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
		width:'600px',
		style: "padding-top:10px",
		"search_contains": true,
		no_results_text: translate('noresultfound')
	});
	
});/*-- END  $(document).ready ----------- --*/
