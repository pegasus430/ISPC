/**
 * ISPC-2654 Ancuta 07.10.2020
 * @returns 
 * ancuta Oct 7, 2020
 */

if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

$(document).ready(function(){
	$(".datetype").datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	 $('#diagnosis_tabs').tabs();
	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');
		$('ul.tabs li').removeClass('current');
		$('.tab_block').removeClass('current');
		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	})

 
});

//
//function PatientDiagnosis_addnew (_target, _parent_form) {
//	
//	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
//	selected_page++;
//	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
//	
//	$.get(appbase + 'ajax/createformdiagnosisrow?parent_form='+parent_form, function(result) {
//
//		var newFieldset =  $(result).insertBefore($(_target).parents('tr'));
//		
////		$(newFieldset).livesearchDiacgnosisIcd({'selectorParents': ''});
////		$('.livesearchFormEvents').livesearchDiacgnosisIcd();
//
//	});
//}