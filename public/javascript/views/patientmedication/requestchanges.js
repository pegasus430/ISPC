/*<!-- ISPC-2507 Lore 31.01.2020  -->*/

$(document).ready(function(){
 
		
		
	$('.submit_form').click(function(e){
		e.preventDefault();
		var action = $(this).data("action");
		
		$('#form_action').val(action);
		$('#add_requestchanges_medication').submit();
		setTimeout(function () {$('.submit_form').attr('disabled', true);}, 150);
		setTimeout(function () {$('.submit_form').attr('disabled', false);}, 3000);
	})
	
	
	$('.insert_texts').live('click', function() {
		
		 
		 var field_name  = $(this).data('id');
		 var form_name  = $(this).data('form_name');
		 
		 if(field_name){
			var url = appbase + 'ajax/formstexts';
			xhr = $.ajax({
				url : url,
				data : {
					field_name : field_name,
					form_name : form_name
				},
				success : function(response) {
					$('.requestchanges_content').html(response);

				}
			});
		 }
		 
		$('#requestchanges-modal')
			.data('options',field_name)
			.dialog('open');
	});

	
}); /* $(document).ready END  */