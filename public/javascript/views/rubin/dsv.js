/**
 * @auth Ancuta 28.08.2019 copy of bdi
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}
// NEW Score calculation - 13.03.2020
function calcscore(that,q_ident,elem_type)
{
	var qt8a_no = 0; 
	var score_info ="";
	$('.calculate_score').each(function(){
		var current_score = $(this).val(); 
		if($(this).is(":checked")){
			if($(this).attr('id') == "qt_8a-no" && $(this).val() == "1")
			{
				qt8a_no =1;			
			}  
		}
		
		if(qt8a_no ==1){
			score_info = "green";
			$('.dsv_green_score').show();
			$('.dsv_red_score').hide();
		} else {
			score_info = "red";
			$('.dsv_green_score').hide();
			$('.dsv_red_score').show();
		}
		$('.score_info').val(score_info);
		
	});
	
}
// --

function calcscoreOld(that,q_ident,elem_type)
{
	
	
	var yes_questions=0;
	var qt1_yes = 0; 
	var qt6_no = 0; 
	var qt7_no = 0; 
	var qt8a_no = 0; 
	var score_info ="";
	$('.calculate_score').each(function(){
		 
		var current_score = $(this).val(); 
		if($(this).is(":checked")){
	  
			if($(this).attr('id') == "qt_1-yes" && $(this).val() == "2" )
			{
				qt1_yes =1;			
			}
			
			if($(this).attr('id') == "qt_2-yes" && $(this).val() == "2" )
			{
				yes_questions +=1;			
			}  
			if($(this).attr('id') == "qt_3-yes" && $(this).val() == "2")
			{
				yes_questions +=1;			
			}  
			
			if($(this).attr('id') == "qt_4-yes" && $(this).val() == "2")
			{
				yes_questions +=1;			
			}  
			if($(this).attr('id') == "qt_5-yes" && $(this).val() == "2")
			{
				yes_questions +=1;			
			}
			
			if($(this).attr('id') == "qt_6-no" && $(this).val() == "1")
			{
				qt6_no =1;			
			}  
			if($(this).attr('id') == "qt_7-no" && $(this).val() == "1")
			{
				qt7_no =1;			
			}  
			if($(this).attr('id') == "qt_8a-no" && $(this).val() == "1")
			{
				qt8a_no =1;			
			}  
		}
		
		if( qt1_yes == 1
				&& yes_questions <= 2
				&& qt6_no ==1  
				&& qt7_no ==1  
				&& qt8a_no ==1  
			){
				score_info = "green";
				$('.dsv_green_score').show();
				$('.dsv_red_score').hide();
			} else {
				score_info = "red";
				$('.dsv_green_score').hide();
				$('.dsv_red_score').show();
			}
			$('.score_info').val(score_info);
		
	});
	
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


function isInteger(_this,start_str = 0, end_str=false)
{
//	console.log($(_this).val());
	var input_id = $(_this).attr('id');
//	console.log(input_id);
 	var s = document.getElementById(input_id).value;
 	var chars = "0123456789";
 	
 	var start = Number(start_str);
 	if(  end_str !== false){
 		var end = Number(end_str);
 	} else {
 		var end = 0;
 	} 
 	
	console.log(s);
	console.log(start );
	console.log(s < start);
	console.log(end);
	
	
	if(s < start )
	{
		document.getElementById(input_id).value = "";
		return false;
	}
	
 	if(end != 0 && s > end)
 	{
		document.getElementById(input_id).value = "";
		return false;
 	}

 	var i;
 	s = s.toString();
	for (i = start; i < s.length; i++)
	{
		var c = s.charAt(i);
		if (chars.indexOf(c)==-1)
		{
 			document.getElementById(input_id).value = "";
 			return false;
		}
	}
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
	 
	 
	 $( ".date" ).datepicker({
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
	 
	 
	 
	 $('.info_triger').live('click',function(){
		$($(this)).parent().find('.row_info').toggle();
	 });
	 
	 
	 $('.question_7').on('change',function(){
 
		 if($(this).val() == "1"){
			 $('.question_7_extra').hide();
			 $('.question_7_extra').removeClass('display_block');
			 $('#dsv-dsv-q_7_a-opt_2').val('');
			 $('#dsv-dsv-q_7_a-opt_3').val('');
					 
		   $('.q7extraradio').removeAttr('checked');
			 
		 }else{
			 $('.question_7_extra').show();
		 }
	 });
	 
});