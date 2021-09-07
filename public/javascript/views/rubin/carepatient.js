/**
 * @auth Lore 11.12.2019 ISPC-2493
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = window.formular_button_action;
//var pat_age = window.patient_age;
//alert(pat_age);

function form_submit_validate() {
	
		return true;
}


$(document).ready(function() { 

	 $( ".form_date" ).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: "0"
		}).mask("99.99.9999");
});