var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}

$(document).ready(function(){
	$( ".datepick" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		nextText: '',
		prevText: ''
	});
	
	$('textarea').elastic();
	
	$(document).on('click', '.selector_user', function() {
		if ($(this).is(':checked')) {
			if($(this).val() == loginuser)
			{
				$(this).parent().next('td').find('input').val('1');
			}
		}
		else
		{
			$(this).parent().next('td').find('input').val('0');
		}
		
	});
	
});