/**
 * @auth Lore 12.09.2019 copy of cmai
 * ISPC-2455
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
 

		total = 0;
		$('.calculate_score').each(function(){
		 

//			var current_score = $(this).val(); 
			var current_score = $(this).data('score'); 
			if($(this).is(":checked"))
			{
				total += parseFloat(current_score);
 
			} else{
			} 
			

			$('.total_slot').html(total);
			$('.form_total').val(total);
 
		});
		
		
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

function save_custom_form(from_ident){
	
	var _post_data = {
			"form_ident" : from_ident,
			"form_date" : $('#'+from_ident+'-custom-form_date').val(),
			"form_total" : $('#'+from_ident+'-custom-form_total').val(),
		};

		$.ajax({
			"dataType" : 'json',
			"type" : "POST",
			"url" : appbase + "rubin/saveemptyform?id="+idpd,
			"data" : _post_data,
			"success" : function(data) {
	            if (data.success == true) {
	            	$('#'+from_ident+'-custom-form_date').val('');
	    			$('#'+from_ident+'-custom-form_total').val('');
	    			
	    			$('.custom_form_status').html('<span class="success" >'+data.message+'</span>')
	    			
	            } else {
	            	$('.custom_form_status').html('<span class="err" >'+data.message+'</span>')
	            	
	            }
			},
			"error" : function(xhr, ajaxOptions, thrownError) {
				if (typeof (DEBUGMODE) !== 'undefined' && DEBUGMODE === true) {
					alert('not saved');
					
				}
			}
		});
		
	
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