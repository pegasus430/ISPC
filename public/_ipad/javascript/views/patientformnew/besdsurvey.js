var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}

function calcscore(radio)
{
	if(radio.val() == 'yes')
	{
		for(i = 0; i< categ_arr.length; i++)
		{
			scores_arr[categ_arr[i]] = 0;
		}
		
		$('.yesbox:checkbox:checked').each(function() {			
			kcat = $(this).data("cat");
			scores_arr[kcat] += $(this).data("score");
			
			if(scores_arr[kcat] > 2)
			{
				scores_arr[kcat] = 2;
			}
			
		});		
		
		var oposite_id = radio.parent().parent().prev('td').find('input');
		
		$('#total').val(scores_arr['breathing']+scores_arr['negative_utterance']+scores_arr['face_expression']+scores_arr['body_language']+scores_arr['consolation'])
		//alert($('#total').val());
	}	
	else
	{
		var oposite_id = radio.parent().parent().next('td').find('input');
	}

	if(oposite_id.is(":checked"))
	{
		
		oposite_id.attr('checked', false);
	}
	
}
$(document).ready(function() { 
    
    //add var
    $('#total').val(scores_arr['breathing']+scores_arr['negative_utterance']+scores_arr['face_expression']+scores_arr['body_language']+scores_arr['consolation']);
    
});