/**
 * @auth Ancuta 23.04.2019
 */
if (typeof (DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : ' + document.currentScript.src);
}

var formular_button_action = window.formular_button_action;

function form_submit_validate() {

	return true;
}

function calculate_score(that, elem_type) {
	if (elem_type == "radio") {
		var score_value = $(this).data("score");

		
	} else if (elem_type == "checkbox") {

		var score_value = that.data("score");
		var question_id = that.data("question_id");

		
		if ($('.form_total').val()) {
			total = parseFloat($('.form_total').val());
		} else {
			total = 0;
		}
		
		
		var qtotal = 0;
		var qtotal = parseFloat($('.total_' + question_id).val());
		

		
		
		var qtotal_full = 0
		$('.' + question_id).each(function() {
			if ($(this).is(":checked")) {
				var q_score_value = $(this).data("score");
				qtotal_full += parseFloat(q_score_value);
			} else {
			}
		});
		

//console.log(qtotal,qtotal_full);
 
		//		console.log(qtotal)

		if (that.is(":checked")) {
			console.log(qtotal < 1);
			if (qtotal < 1) {
				total += parseFloat(score_value);
				qtotal += parseFloat(score_value);
			}

		} else {

			// calculate - qtotal 
			if (qtotal > 0 && qtotal_full == 0) {
				total = parseFloat(total - score_value);
				qtotal = parseFloat(qtotal - score_value);
			}
		}

		$('.total_slot').html(total);
		$('.form_total').val(total);
//		console.log(total);
		$('.total_' + question_id).val(qtotal);

	}

}

function setunset(radio) {
	$('.question11').removeAttr('checked');
	$('.yesno').removeAttr('checked');
	radio.prop('checked', 'checked');
}

function calcbmi(value) {
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