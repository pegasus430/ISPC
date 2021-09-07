var formular_button_action = window.formular_button_action;
var totalscore = null;

function form_submit_validate() {
		$('#height').val($('#height').val().replace(/\,/g, '.'));
		$('#current_weight').val($('#current_weight').val().replace(/\,/g, '.'));
		$('#last_3_6_month_weight').val($('#last_3_6_month_weight').val().replace(/\,/g, '.'));
		$('#square_height').val($('#square_height').val().replace(/\,/g, '.'));
		$('#bmi').val($('#bmi').val().replace(/\,/g, '.'));
		$('#last_3_6_month_weight_proc').val($('#last_3_6_month_weight_proc').val().replace(/\,/g, '.'));
		return true;
}

function calcscore(radio)
{
	if(radio.is(":checked"))
	{
		var radio_id = radio.attr('id');
		if(radio_id =='acute_illness_yes-yes')
		{
			$('#acute_illness_score').val('2');
			var oposite_id = $('#acute_illness_no-no');
			totalscore = +$('#bmi_score').val() + +$('#last_3_6_month_weight_proc_score').val() + +$('#acute_illness_score').val();
			$('#total_score').val(totalscore);
		}	
		else
		{
			var oposite_id = $('#acute_illness_yes-yes');
			$('#acute_illness_score').val('0');
			totalscore = +$('#bmi_score').val() + +$('#last_3_6_month_weight_proc_score').val() + +$('#acute_illness_score').val();
			$('#total_score').val(totalscore);
		}	
	
		if(oposite_id.is(":checked"))
		{
			
			oposite_id.attr('checked', false);
		}
	}	
	
}


$(document).ready(function() {
	//disable enter key 
	$('input[type="text"]').keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
	
	$( ".must_date" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	
	$(document).on("change", '#current_weight, #last_3_6_month_weight', function() {
		$('#current_weight').val($('#current_weight').val().replace(/\./g, ','));
		$('#last_3_6_month_weight').val($('#last_3_6_month_weight').val().replace(/\./g, ','));
		
		if($('#last_3_6_month_weight').val().length > '0')
		{
			var current_weight = $('#current_weight').val().replace(/\,/g, '.');
			var last_3_6_month_weight = $('#last_3_6_month_weight').val().replace(/\,/g, '.');
			
			var last_3_6_month_weightval = (current_weight/last_3_6_month_weight)*100;
			
			var weight_lost = 100 - last_3_6_month_weightval;
			$('#last_3_6_month_weight_proc').val(parseFloat(weight_lost.toPrecision(4)).toFixed());
			
			if(parseInt($('#last_3_6_month_weight_proc').val()) > '10')
			{
				$('#last_3_6_month_weight_proc_score').val('2');
			}
			else if(parseInt($('#last_3_6_month_weight_proc').val()) >= '5' && parseInt($('#last_3_6_month_weight_proc').val()) <= '10')
			{
				$('#last_3_6_month_weight_proc_score').val('1');
			}
			else if(parseInt($('#last_3_6_month_weight_proc').val()) <= '5')
			{
				$('#last_3_6_month_weight_proc_score').val('0');
			}
			$('#last_3_6_month_weight_proc').val($('#last_3_6_month_weight_proc').val().replace(/\./g, ','));
			totalscore = +$('#bmi_score').val() + +$('#last_3_6_month_weight_proc_score').val() + +$('#acute_illness_score').val();
			$('#total_score').val(totalscore);
		}
	});
	
	$(document).on("change", '#current_weight, #height', function() {
		$('#current_weight').val($('#current_weight').val().replace(/\./g, ','));
		$('#height').val($('#height').val().replace(/\./g, ','));
		
		if($('#height').val().length > '0')
		{
			var height = $('#height').val().replace(/\,/g, '.');
			$('#square_height').val(parseFloat((Math.pow(height, 2).toPrecision(4))).toFixed(2));
		}
		
		var current_weight = $('#current_weight').val().replace(/\,/g, '.');
		
		if($('#square_height').val().length > '0')
		{
			var square_height = $('#square_height').val();
			var bmival = current_weight/square_height;
			$('#bmi').val(parseFloat(bmival.toPrecision(4)).toFixed(2));
			
			if(parseFloat($('#bmi').val()) > '20')
			{
				$('#bmi_score').val('0');
			}
			else if(parseFloat($('#bmi').val()) >= '18.5' && parseFloat($('#bmi').val()) <= '20')
			{
				$('#bmi_score').val('1');
			}
			else if(parseFloat($('#bmi').val()) <= '18.5')
			{
				$('#bmi_score').val('2');
			}
			$('#bmi').val($('#bmi').val().replace(/\./g, ','));
			totalscore = +$('#bmi_score').val() + +$('#last_3_6_month_weight_proc_score').val() + +$('#acute_illness_score').val();
			$('#total_score').val(totalscore);
		}
		$('#square_height').val($('#square_height').val().replace(/\./g, ','));
		//alert($('#square_height').val());
	});
	$('#height').val($('#height').val().replace(/\./g, ','));
	$('#current_weight').val($('#current_weight').val().replace(/\./g, ','));
	$('#last_3_6_month_weight').val($('#last_3_6_month_weight').val().replace(/\./g, ','));
	$('#square_height').val($('#square_height').val().replace(/\./g, ','));
	$('#bmi').val($('#bmi').val().replace(/\./g, ','));
	$('#last_3_6_month_weight_proc').val($('#last_3_6_month_weight_proc').val().replace(/\./g, ','));
	
});