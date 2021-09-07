/**
 * @auth Ancuta 23.04.2019
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}




function calcscore(that,elem_type)
{
//	var parent = radio.data("parent");
 
//	radio.prop('checked','checked');

	if(elem_type == "radio") {
//		alert("radiooooooo");
//		console.log(that);
		
		var score_value= $(this).data("score");
		
	}
	else if(elem_type == "checkbox") {
		
		total = 0;
		$('.calculate_score').each(function(){
			var score_value= $(this).data("score");
			
			if($(this).is(":checked"))
			{
				//			var this_value = parseFloat( $(this).val());
				//			console.log(this_value);
				//			total += parseFloat(scoreArray[$(this).val()]);
				total += parseFloat($(this).val());
				//			total = parseFloat(total + this_value) ;
			} else{
				//			var this_value = parseFloat( $(this).val());
				//			total = parseFloat(total - this_value) ;
			} 
			
			$('.total_slot').html(total);
			$('.form_total').val(total);
		});
	}
	
}

function setunset(radio)
{
	$('.question11').removeAttr('checked');
	$('.yesno').removeAttr('checked');
    radio.prop('checked','checked');
}

function calcbmi(value)
{ }

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