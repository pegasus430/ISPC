/**
 * @auth Lore 06.01.2020 copy of dsv
 * ISPC-2509
 */

if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}




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
	 
	 
	 var $content = $(".content").hide();
	 $(".toggle_text").on("click", function (e) {
	     $(this).toggleClass("expanded");
	     $content.slideToggle();
     });

	 
});