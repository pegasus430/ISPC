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




function calculate_score(that,elem_type)
{
	if(elem_type == "radio") {
		
		var score_value= $(this).data("score");
		
	}
	else if(elem_type == "checkbox") {
		
		var score_value= that.data("score");
		
		
		if($('.form_total').val()){
			total = parseFloat( $('.form_total').val());
		} else{
			total = 0;
		}
		if(that.is(":checked"))
		{
			total += parseFloat(score_value);
		} else{
			total = parseFloat(total - score_value) ;
		}
		
		$('.total_slot').html(total);
		$('.form_total').val(total);
 
	}
	
}


function calculate_score_mmst(that,elem_type)
{
//	var parent = radio.data("parent");
 
//	radio.prop('checked','checked');

	if(elem_type == "radio") {
//		alert("radiooooooo");
//		console.log(that);
		
		var score_value= $(this).data("score");
		
	}
	else if(elem_type == "checkbox") {
		
		var score_value= $(this).data("score");
		
		
		total = 0;
		$('.calculate_score').each(function(){
			var score_value= $(this).data("score");
			if($(this).is(":checked"))
			{
//				alert(score_value);
				//			var this_value = parseFloat( $(this).val());
				//			console.log(this_value);
				//			total += parseFloat(scoreArray[$(this).val()]);
				if(total < 30){
					total += parseFloat($(this).val());
//					console.log(total);
				}
				//			total = parseFloat(total + this_value) ;
			} else{
//							var this_value = parseFloat( $(this).val());
//							total = parseFloat(total - this_value) ;
			} 
			
			$('.total_slot').html(total);
			$('.form_total').val(total);
		});
	}
	
}

//TODO-2621 Ancuta 30.10.2019 - added empty form to mmst 
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
//-- 

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