/*------ISPC-2499 Lore 12.12.2019 --------------------*/

$(document).ready(function(){
	
	var slider = document.getElementById("myRange");
	var output = document.getElementById("stres_value");
	//var output = $("#stres_value").val();
	//output.innerHTML = slider.value;


	slider.onchange = function() {
//		  this is for IE browser
			$("#stres_value").val(this.value)
			$("#myRange").val(this.value)
		}	

	slider.oninput = function() {
//		  this is for others browsers but not IE !!!!
		$("#stres_value").val(this.value)
		$("#myRange").val(this.value)
	}

	
	$('#save_form').bind('click', function(e) {
		e.preventDefault();
		//check required textarea filled if renew is checked

		$('#frmuser').submit();
		setTimeout(function () {$('#save_form').attr('disabled', true);}, 150);
		setTimeout(function () {$('#save_form').attr('disabled', false);}, 22000);
	});
	
}); /* $(document).ready END  */