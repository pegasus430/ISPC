/*------ISPC-2494 Lore 06.12.2019 --------------------*/
$(document).ready(function(){
	$('#save_form').bind('click', function(e) {
		e.preventDefault();
		//check required textarea filled if renew is checked
		
		$('#frmuser input[name=save]').val('1');
		$('#frmuser input[name=print]').val('0');
	
		$('#frmuser').submit();
		setTimeout(function () {$('#save_form').attr('disabled', true);}, 150);
		setTimeout(function () {$('#save_form').attr('disabled', false);}, 22000);
	});
	
	$('#pdfexport').bind('click', function(e) {
		e.preventDefault();
		//check required textarea filled if renew is checked
		
		$('#frmuser input[name=save]').val('0');
		$('#frmuser input[name=print]').val('1');
		
		$('#frmuser').submit();

		setTimeout(function () {$('#pdfexport').attr('disabled', true);}, 150);
		setTimeout(function () {$('#pdfexport').attr('disabled', false);}, 22000);
	});



	
}); /* $(document).ready END  */