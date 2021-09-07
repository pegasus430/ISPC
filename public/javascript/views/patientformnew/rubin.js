var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}

function calcscore(radio)
{
	var parent = radio.data("parent");
	if(typeof parent !== "undefined")			
	{	
		//$('.question11').removeAttr('checked');		//ISPC-2353 @Lore 31.10.2019 (question11)
		//$('.yesno').removeAttr('checked');			//ISPC-2353 @Lore 31.10.2019 (question11)
		$('#'+parent).attr('checked', 'checked');		
	}
	radio.prop('checked','checked');
	if(radio.val().substr(0, 4) =='bmi_')
	{ 
		//$('#weight').attr('readonly', true).css('border-color', '#eee');
		//$('#height').attr('readonly', true).css('border-color', '#eee');
		//$('#bmi').css('border-color', '#eee');

//ISPC-2353 @Lore 31.10.2019 (question6)	
/*		$('#weight, #height, #bmi').mousedown(function(e){
			  e.preventDefault();
			  $(this).blur();
			  return false;
			});*/
	}
	scores_arr['before_anamnesis_total'] = 0;
	scores_arr['anamnesis_total'] = 0;
	total = 0;
	$('.calcscore').each(function(){
		kcat = $(this).data("cat")+'_total';
		var scoreArray = $(this).data("score");
		if($(this).is(":checked") || $(this).attr("id") == 'mini_nutritional_assessment-q11_hidden')
		{
			scores_arr[kcat] += parseFloat(scoreArray[$(this).val()]);
			total += parseFloat(scoreArray[$(this).val()]);
		} 
		$('#'+kcat).val(scores_arr[kcat]);
		$('#total').val(total);
	});
	if($('#before_anamnesis_total').val() <= 11)
	{
		//$('.anamnesis').show();
		$('#text_before_anamnesis_total').val('Gefahr der Mangelernährung');
	}
	else
	{
		//ISPC - 2353
		/*if($('input[name^="mini_nutritional_assessment[anamnesis"]').is(":checked"))
		{
			$('input[name^="mini_nutritional_assessment[anamnesis"]').removeAttr('checked');
		}*/
		//$('.anamnesis').hide();
		$('#text_before_anamnesis_total').val('Normaler Ernährungszustand');
	}
	
	if($('#total').val() >= 17 && $('#total').val() < 23.5)
	{
		$('#text_total').val('Risikobereich für Unterernährung');
	}
	else
	{
		$('#text_total').val('schlechter Ernährungszustand');
	}
	/*scores_arr[kcat] += parseFloat(scoreArray[radio.val()]);
	total += parseFloat(scoreArray[radio.val()]);
	$('#'+kcat).val(scores_arr[kcat]);
	$('#total').val(total);*/
	//alert(scoreArray[radio.val()]);
}

function setunset(radio)
{
	$('.question11').removeAttr('checked');
	$('.yesno').removeAttr('checked');
    radio.prop('checked','checked');
}

function calcbmi(value)
{
	if($('#weight').val() && $('#height').val())
	{
		if($('#height').val().indexOf('.') >= 0 || $('#height').val().indexOf(',') >=0)
		{
			
			var weight = $('#weight').val().replace(/\,/g, '.');
			var height = $('#height').val().replace(/\,/g, '.');
			
			var bmi = Math.round(weight/(height*height));
			$('#bmi').val(bmi);
			
			
			if(bmi < 19) 
			{			
				$("input[value='bmi_lt_19']").attr('checked', 'checked');
				$("input[value='bmi_lt_19']:disabled").attr('disabled', false);
				//$('input[value^="bmi_"]').attr('readonly', true);
				$('input[value^="bmi_"]:not(:checked)').attr('disabled', true);
				
				scores_arr['before_anamnesis_total'] = 0;
				scores_arr['anamnesis_total'] = 0;
				total = 0;
				
				$('.calcscore').each(function(){
					kcat = $(this).data("cat")+'_total';
					var scoreArray = $(this).data("score");
					if($(this).is(":checked"))
					{
						scores_arr[kcat] += parseFloat(scoreArray[$(this).val()]);
						total += parseFloat(scoreArray[$(this).val()]);
					}
					$('#'+kcat).val(scores_arr[kcat]);
					$('#total').val(total);
				});	
				/*
				var scoreArray = $("input[value='bmi_lt_19']").data("score");
				var kcat = $("input[value='bmi_lt_19']").data("cat")+'_total';
				scores_arr[kcat] += parseFloat(scoreArray[$("input[value='bmi_lt_19']").val()]);
				total += parseFloat(scoreArray[$("input[value='bmi_lt_19']").val()]);*/			
			}
			else if(bmi >= 19 && bmi < 21)
			{
				$("input[value='bmi_between_19_21']").attr('checked', 'checked');
				$("input[value='bmi_between_19_21']:disabled").attr('disabled', false);
				//$('input[value^="bmi_"]').attr('readonly', true);
				$('input[value^="bmi_"]:not(:checked)').attr('disabled', true);
				
				scores_arr['before_anamnesis_total'] = 0;
				scores_arr['anamnesis_total'] = 0;
				total = 0;
				
				$('.calcscore').each(function(){
					kcat = $(this).data("cat")+'_total';
					var scoreArray = $(this).data("score");
					if($(this).is(":checked"))
					{
						scores_arr[kcat] += parseFloat(scoreArray[$(this).val()]);
						total += parseFloat(scoreArray[$(this).val()]);
					}
					$('#'+kcat).val(scores_arr[kcat]);
					$('#total').val(total);
				});	
				/*
				var scoreArray = $("input[value='bmi_between_19_21']").data("score");
				var kcat = $("input[value='bmi_between_19_21']").data("cat")+'_total';
				scores_arr[kcat] += parseFloat(scoreArray[$("input[value='bmi_between_19_21']").val()]);
				total += parseFloat(scoreArray[$("input[value='bmi_between_19_21']").val()]);*/
			}
			else if(bmi >= 21 && bmi < 23)
			{
				$("input[value='bmi_between_21_23']").attr('checked', 'checked');
				$("input[value='bmi_between_21_23']:disabled").attr('disabled', false);
				//$('input[value^="bmi_"]').attr('readonly', true);
				$('input[value^="bmi_"]:not(:checked)').attr('disabled', true);
				
				scores_arr['before_anamnesis_total'] = 0;
				scores_arr['anamnesis_total'] = 0;
				total = 0;
				
				$('.calcscore').each(function(){
					kcat = $(this).data("cat")+'_total';
					var scoreArray = $(this).data("score");
					if($(this).is(":checked"))
					{
						scores_arr[kcat] += parseFloat(scoreArray[$(this).val()]);
						total += parseFloat(scoreArray[$(this).val()]);
					}
					$('#'+kcat).val(scores_arr[kcat]);
					$('#total').val(total);
				});	
				/*
				var scoreArray = $("input[value='bmi_between_21_23']").data("score");
				var kcat = $("input[value='bmi_between_21_23']").data("cat")+'_total';
				scores_arr[kcat] += parseFloat(scoreArray[$("input[value='bmi_between_21_23']").val()]);
				total += parseFloat(scoreArray[$("input[value='bmi_between_21_23']").val()]);*/
			}
			else if(bmi >= 23)
			{
				$("input[value='bmi_gt_23']").attr('checked', 'checked');
				$("input[value='bmi_gt_23']:disabled").attr('disabled', false);
				//$('input[value^="bmi_"]').attr('readonly', true);
				$('input[value^="bmi_"]:not(:checked)').attr('disabled', true);
				
				scores_arr['before_anamnesis_total'] = 0;
				scores_arr['anamnesis_total'] = 0;
				total = 0;
				
				$('.calcscore').each(function(){
					kcat = $(this).data("cat")+'_total';
					var scoreArray = $(this).data("score");
					if($(this).is(":checked"))
					{
						scores_arr[kcat] += parseFloat(scoreArray[$(this).val()]);
						total += parseFloat(scoreArray[$(this).val()]);
					}
					$('#'+kcat).val(scores_arr[kcat]);
					$('#total').val(total);
				});	
				/*
				var scoreArray = $("input[value='bmi_gt_23']").data("score");
				var kcat = $("input[value='bmi_gt_23']").data("cat")+'_total';
				scores_arr[kcat] += parseFloat(scoreArray[$("input[value='bmi_gt_23']").val()]);
				total += parseFloat(scoreArray[$("input[value='bmi_gt_23']").val()]);*/
			} 
			
				/*$('#'+kcat).val(scores_arr[kcat]);
				$('#total').val(total);*/
			if($('#before_anamnesis_total').val() <= 11)
			{
				//$('.anamnesis').show();
				$('#text_before_anamnesis_total').val('Gefahr der Mangelernährung');
			}
			else
			{
				//ISPC - 2353
				/*if($('input[name^="mini_nutritional_assessment[anamnesis"]').is(":checked"))
				{
					$('input[name^="mini_nutritional_assessment[anamnesis"]').removeAttr('checked');
				}*/
				//$('.anamnesis').hide();
				$('#text_before_anamnesis_total').val('Normaler Ernährungszustand');
			}
		}
		else
		{
			jAlert(translate('the height has to be in m!'), 'Alert');
		}
	}
	else if(!$('#weight').val())
	{	
	
		if($('#height').val())
		{
			if($('#height').val().indexOf('.') == -1 && $('#height').val().indexOf(',') == -1)
			{
				jAlert(translate('the height has to be in m!'), 'Alert');
			}
		}
		else
		{
			$('#bmi').val('');
			
			$('input[value^="bmi_"]').attr('checked', false);
			$('input[value^="bmi_"]').attr('disabled', false);
		}
	}		
}


function calcscoreq11(radio)
{
	var qr = 0
	$('.sub_question11').each(function(){
		if($(this).is(":checked") && $(this).val() == "yes" ){
			qr +=1;
		}
	});	
	 
	$('#mini_nutritional_assessment-q11_hidden').val(qr);
	
	calcscore(radio);
}

$(document).ready(function() {
	$('#bmi').css('border-color', '#eee');
	
	$( ".form_date" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	
    if($('#before_anamnesis_total').val() >=12)
    {
    	//$('.anamnesis').hide();
    	$('#text_before_anamnesis_total').val('Normaler Ernährungszustand');
    }
    else
    {
    	$('#text_before_anamnesis_total').val('Gefahr der Mangelernährung');
    }
    
    if($('#total').val() >= 17 && $('#total').val() < 23.5)
	{
		$('#text_total').val('Risikobereich für Unterernährung');
	}
	else
	{
		$('#text_total').val('schlechter Ernährungszustand');
	}
    //add var
   // $('#total').val(scores_arr['breathing']+scores_arr['negative_utterance']+scores_arr['face_expression']+scores_arr['body_language']+scores_arr['consolation']);
    
});