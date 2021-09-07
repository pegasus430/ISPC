

$(document).ready(function() { /*------ Start $(document).ready --------------------*/

	 $.getmedicationblocks = function() {
		
		var url = appbase + 'patientnew/medicationeditblocks?id='+idpd;

		//show a loading gif
		$('#medication_blocks').html('<br/><div class="loadingdiv" align="center"><img src="'+res_path+'/images/ajax-loader.gif"><br />' + translate('loadingpleasewait') + '</div><br/>');


		xhr = $.ajax({
			url : url,
			cache : false,
			success : function(response) {
				$('#medication_blocks').html(response);
				
				medicationeditblocks_ready();
			}
		});
	}
	
	if(loadmediblocks)
	{
		$.getmedicationblocks();
	}
	
	$("#form_medicationedit").submit(function(event){
	
		if ( ! checkclientchanged()) {
			return false;
		}
		
		if (! validateForm_edit()) {
			event.preventDefault();
			event.stopPropagation(); //stop going forward
			event.stopImmediatePropagation(); //stop going forward
			
			return false;
		}
	
		
		var dlist = '<div class="loadingdiv" align="center">' + translate('loadingpleasewait') + '</div>';
		
		$('.submit-floating input').hide();
		$('.submit-floating').append(dlist);
		
		var hastag_in_viewport = $(".medication_block:in-viewport:eq(0) h1:eq(0)").attr("id") || "";
		hastag_in_viewport = hastag_in_viewport.split(/[\s_]+/).pop();
		if (hastag_in_viewport != ""){
			window.location.hash = '#'+hastag_in_viewport;
		}
		return true;
	});
	
	$('#form_medicationedit').keydown(function (e) {
		// ISPC-2120 - allow enter in comment textarea.
		if($(e.target).hasClass('med_com_textarea')){
			return true;
		}
		
	    if (e.keyCode == 13) {
	        e.preventDefault();
	        return false;
	    }
	});
	
	$(document).on('click', ':input', function(){
		if($(this).css('background-color') === 'rgb(243, 187, 191)')
		{
			$(this).css('background-color', '#f2f2f2');
		}
	});


});/*-- END  $(document).ready ----------- --*/

function toggle_all_extra_info(_this){
	
		var open_now = $(_this).data('is_open');
		
		if (typeof(open_now) === 'undefined' ){
			open_now = "true";
		}
		
		if (open_now == "true") {
			
			open_now =  true;
			
			$(_this).data('is_open', "false");
			
		} else {
			
			open_now = false;
			
			$(_this).data('is_open', "true");
		}
	
	
		$("tr.child_row_toggler").each(function(){
			medi_child_toggle(this, open_now);
		})
	}
	
//validate the hours and from  ISPC-2430 the name of medi
function validateForm_edit() {
	
	var time_interval = true;
	
	$( "input[name*='[medication]']").each(function(){
		if($(this).css('background-color') === 'rgb(243, 187, 191)')
		{
			$(this).css('background-color', '#f2f2f2');
		}
	});
	
	
	$('table.medication_edit_table').each(function (){
		
		var my_dosages = new Array(); 
		
		var table_nicename = $("#section_"+  $(this).data('medication_type')).text() + " :: ";
		
		var medication_type = $(this).data('medication_type');
		
		var all_sch_rows = false;
		
		var sch_extra = true;
		
		$('.dosage_intervals_holder input.time_interval_style', $(this) ).each(function(i){
			my_dosages[i] = $(this).val();
		});
					
		if (my_dosages.length > 0 )
			
		for (var i = 1; i < my_dosages.length; i++) {
			
			var d0 = parseInt( my_dosages[i-1].replace(new RegExp(":", 'g'), '') , 10);
			var d1 = parseInt( my_dosages[i].replace(new RegExp(":", 'g'), ''), 10);
							
			if (isNaN(d0) || isNaN(d1)  || String(d0) == "" || String(d1) == "" || d0  >= d1) {
				
				time_interval = false;
				setTimeout(function () {alert( table_nicename + translate("intervals must be consecutive") );}, 50);
				return false;
			}
		}
		
		//ISPC-2430	Carmen 26.11.2019		
		$( "input[name*='[medication]']", $(this) ).each(function(i){
			var empty_row = true;				
			if($(this).val() == '')
			{
				$(this).closest('tr').find(':input:not([type="hidden"])').not(this).each(function(){
					
					if(($(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $(this).val() != '' && $(this).val() != '0' && $(this).val() != 'MMI') || ($(this).attr('type') == 'radio' && $(this).is(':checked'))  || ($(this).attr('type') == 'checkbox' && $(this).is(':checked')))
					{
						if(medication_type != 'isschmerzpumpe')
						{
							empty_row = false;
							return false;
						}
						else
						{
							empty_row = false;
							sch_extra = false;
							return false;
						}
					}
				});

				if(medication_type != 'isschmerzpumpe' && medication_type != 'treatment_care' && medication_type != 'scheduled' && empty_row === true)
				{
					$(this).closest('tr').next().next().find(':input:not([type="hidden"])').each(function(){
						
						if(($(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $(this).val() != '' && $(this).val() != '0' && $(this).val() != 'MMI') || ($(this).attr('type') == 'radio' && $(this).is(':checked'))  || ($(this).attr('type') == 'checkbox' && $(this).is(':checked')))
						{
							empty_row = false;
							return false;
						}
					});
				}
				else if(medication_type == 'isschmerzpumpe' && empty_row === true && sch_extra === true)
				{
					var that = $(this);
					$(this).closest('tr').parent().parent().parent().find('table.datatable_sch').find(':input:not([type="hidden"])').each(function(){
						
						if(($(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $(this).val() != '' && $(this).val() != '0' && $(this).val() != 'MMI') || ($(this).attr('type') == 'radio' && $(this).is(':checked'))  || ($(this).attr('type') == 'checkbox' && $(this).is(':checked')))
						{
							if(that.closest('tr').parent().children().length == 1)
							{
								empty_row = false;
							}
							else
							{
								if((i+1) == that.closest('tr').parent().children().length)
								{
									that.closest('tr').parent().children().each(function(){
										
										if($(this).find(( "input[name*='[medication]']")).val() != '')
										{
											
											return false;
										}
										else
										{
											empty_row = false;
											all_sch_rows = true;
										}
									});
									
								}
							}
							return false;
						}
					});
				}
				
				if(!empty_row)
				{
					if(medication_type != 'isschmerzpumpe')
					{
						time_interval = false;
						$(this).css('background-color', 'rgb(243, 187, 191)');
						setTimeout(function () {alert( table_nicename + translate("name should be filled for line " + (i+1)) );}, 50);
					}
					else
					{
						if(all_sch_rows)
						{
							time_interval = false;
							$(this).closest('tr').parent().find("input[name*='[medication][1]']").css('background-color', 'rgb(243, 187, 191)');
							setTimeout(function () {alert( table_nicename + translate("name should be filled for line 1") );}, 50);
						}
						else
						{
							time_interval = false;
							$(this).css('background-color', 'rgb(243, 187, 191)');
							setTimeout(function () {alert( table_nicename + translate("name should be filled for line " + (i+1)) );}, 50);
						}
					}
				}
			}
		});
	});		
	return time_interval;
	
}
	
	