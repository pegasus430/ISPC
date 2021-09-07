var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}




$(document).ready(function() {
	//disable enter key 
	$('input[type="text"]').keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
	
	$( ".kh_date" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	
	/*$(document).on('change', '.onevalue', function() {	
		var oposite_id = $(this).parent().next('label').find('input');
		if(oposite_id.length <= 0)
		{
			var oposite_id = $(this).parent().prev('label').find('input');
		}
		if(oposite_id.is(":checked"))
		{
			oposite_id.attr('checked', false);
		}
	});*/
	
});
