/**
 * @auth Ancuta 23.04.2019
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






function calculate_q_score(that,question)
{
	var pat_age = $('#patient_age').val();
	var score_value= that.data("score");
	
	var total = 0 ;
	if($('.'+question+'_total').val()){
		total = parseFloat( $('.'+question+'_total').val());
	} else{
		total = 0;
	}
	
	if(question == "q_4"){
		var q4_values_array = [];	
		$('.q_4_calc').each(function(){
			
			if($(this).is(":checked")  )
			{
				q4_values_array.push(parseFloat($(this).data('score')));
			}
			else
			{
				var result = q4_values_array.filter(function(elem){
					   return elem != parseFloat(parseFloat($(this).data('score'))); 
				})
				q4_values_array = result;
			} 
		});
		if( jQuery.isEmptyObject(q4_values_array) ){
			total = 0 ;
		} else{
			total = Math.max.apply(Math, q4_values_array);
		}
		
	}	 
	else if(question == "q_3"){
		new_total  = total;
		var q3_action = $(that).data('action');

		if(q3_action == 'add'){
			if(new_total < 30){
				total += parseFloat(score_value);
			}
			
		} else if(q3_action == 'substract'){ 
			
			new_total  = parseFloat(total - score_value);
			
			if(new_total < 0){
				total = 0 
			} else {
				total = parseFloat(total - score_value) ;
			}
		}		
		
		$('.demTec_q3input').val(total);
	}	 
	else
	{
		new_total  = total;
		
		if(that.is(":checked"))
		{
			total += parseFloat(score_value);
			
		} 
		else 
		{
			new_total = parseFloat(total - score_value);
			if(new_total <= 0){
				total = 0 
			} else {
				total = parseFloat(total - score_value) ;
			}
		}
	}
	
	// popolate question total 
	$('.'+question+'_total').val(total);

	
	
	// Populate form total 
	
	var final_total = 0 ; 
	var final_total_extra = 0 ; 
	
	$('.all_q_total').each(function(){
		
		if($(this).hasClass('q_1_total')){
			var q_result = 0;
			var question_current_total = parseFloat( $(this).val() ) ;
			
			if(pat_age < 60){
				
                if(question_current_total <= 7){
                    q_result = 0;
				}
                else if(question_current_total >=8 && question_current_total <=10){
                    q_result = 1;
				}
                else if(question_current_total >=11 && question_current_total <=12){
                    q_result = 2;
				}
                else if(question_current_total >=13){
                    q_result = 3;
				}
			} else {
	             if(question_current_total <= 6){
                     q_result = 0;
					}
                 else if(question_current_total >= 7 && question_current_total <= 8){
                     q_result = 1;
					}
                 else if(question_current_total >= 9 && question_current_total <= 10){
                     q_result = 2;
					}
                 else if(question_current_total >= 11){
                     q_result = 3;
					}
			}
			
			
			
			final_total_extra +=  parseFloat(q_result);
			
//			console.log('Q1', question_current_total,q_result, final_total_extra);
			
		}
		else if($(this).hasClass('q_2_total')){
			var q_result = 0;
			var question_current_total = parseFloat( $(this).val() );
			
			if(question_current_total == 0){
				q_result = 0	
			}
			else if(question_current_total == 1 ||question_current_total == 2 ){
				q_result = 1	
			}
			else if(question_current_total == 3 ){
				q_result = 2	
			}
			else if(question_current_total == 4 ){
				q_result = 3	
			}
			
			final_total_extra +=  parseFloat(q_result);
//			console.log('Q2', question_current_total,q_result, final_total_extra);
		}
		else if($(this).hasClass('q_3_total')){

			var q_result = 0;
			var question_current_total = parseFloat( $(this).val() );
			
			if(pat_age < 60){
				
                if(question_current_total >=0 && question_current_total <=12){
                    q_result = 0;
				}
                else if(question_current_total >=13 && question_current_total <=15){
                    q_result = 1;
				}
                else if(question_current_total >=16 && question_current_total <= 19){
                    q_result = 2;
				}
                else if(question_current_total >=20){
                    q_result = 4;
				}
                
			} else {
				
	             if(question_current_total >= 0 && question_current_total <= 5 ){
                     q_result = 0;
					}
                 else if(question_current_total >= 6 && question_current_total <= 9){
                     q_result = 1;
					}
                 else if(question_current_total >= 10 && question_current_total <= 15){
                     q_result = 2;
					}
                 else if(question_current_total >= 16){
                     q_result = 4;
					}
			}

			final_total_extra +=  parseFloat(q_result);
			
//			console.log('Q3', question_current_total,q_result, final_total_extra);
		}
		else if($(this).hasClass('q_4_total')){
			var q_result = 0;
			var question_current_total = parseFloat( $(this).val() );
			
			if(pat_age < 60){
				
                if(question_current_total == 0 ){
                    q_result = 0;
				}
                else if(question_current_total == 2 || question_current_total == 3){
                    q_result = 1;
				}
                else if(question_current_total == 4){
                    q_result = 2;
				}
                else if(question_current_total >= 5){
                    q_result = 3;
				}
                
			} else {
				
                if(question_current_total == 0 ){
                    q_result = 0;
				}
                else if(question_current_total == 2){
                    q_result = 1;
				}
                else if(question_current_total == 3){
                    q_result = 2;
				}
                else if(question_current_total >= 4){
                    q_result = 3;
				}
			}

			final_total_extra +=  parseFloat(q_result);			
			
//			console.log('Q4', question_current_total,q_result, final_total_extra);
		}
		else if($(this).hasClass('q_5_total')){

			var q_result = 0;
			var question_current_total = parseFloat( $(this).val() );
			
			if(pat_age < 60){
				
                if(question_current_total == 0 ){
                    q_result = 0;
				}
                else if(question_current_total >=1  && question_current_total <= 3){
                    q_result = 1;
				}
                else if(question_current_total >=4  && question_current_total <= 5){
                    q_result = 2;
				}
                else if(question_current_total >= 6){
                    q_result = 5;
				}
                
			} else {
				
                if(question_current_total == 0 ){
                    q_result = 0;
				}
                else if(question_current_total == 1 || question_current_total == 2){
                    q_result = 1;
				}
                else if(question_current_total == 3 || question_current_total == 4){
                    q_result = 2;
				}
                else if(question_current_total >= 5){
                    q_result = 5;
				}
			}
			
			final_total_extra +=  parseFloat(q_result);
//			console.log('Q5', question_current_total,q_result, final_total_extra);
		}
		
		
		var  q_total  = 0
		if($(this).val() ) {
			q_total  = $(this).val();
		}
		
		final_total +=  parseFloat(q_total);
		
		
	});
	
	$('.form_total').val(final_total_extra);
//	$('.form_total_extra').val(final_total_extra);
	
	
}

function calculate_score(that,elem_type)
{
 
	
}



function input_calc(that, direction)
{ 
	if($('.demTec_q3input').val()){
		
		var current_value =  parseFloat($('.demTec_q3input').val());
	} else{
		var current_value =  0;
	}
	
	var new_value= 0 ; 
	if(direction == "add" && current_value < 30){
		new_value = parseFloat(current_value+1); 
	}
	
	if(direction == "substract" && current_value > 0){
		new_value = parseFloat(current_value - 1); 
	}
	
	
	$('.demTec_q3input').val(new_value);
	$('.q_3_total').val(new_value);
	
	
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