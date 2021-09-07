/*------ ISPC-2020--------------------*/
$(document).ready(function(){
 
	$(".datetype").datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	
	
	$('.submit_form').click(function(e){
		e.preventDefault();
		var action = $(this).data("action");
		
		$('#form_action').val(action);
		$('#ambulant').submit();
		setTimeout(function () {$('.submit_form').attr('disabled', true);}, 150);
		setTimeout(function () {$('.submit_form').attr('disabled', false);}, 3000);
	});

	//ISPC-2658, elena, 08.09.2020
	$('.full_textarea').addClass('prefilled_data');
	$('.full_textarea').on('change', function(e){
		$(this).removeClass('prefilled_data');
	});



	$('.insert_texts').live('click', function() {
		
		$('.content').html("");
		 
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
					$('.content').html(response);
				}
			});
		 }
		 
		$('#option-modal')
		.data('options',field_name)
		.dialog('open');
	});
	
	$("#option-modal").dialog({
		autoOpen: false,
		resizable: false,
		height: 500,
		scroll: true,
		width: 1000,
		modal: true,
		
		buttons: [{
			text: translate('cancel'),
			click: function() {
				$(this).dialog("close");
			}
		},
		{
			text: translate('save'),
			click: function() {
				var field_value  = $(this).data('options');
				var new_txt  = "";
				$('.vals_'+field_value+':checked').each(function(index) {
					console.log($(this).val());
					new_txt +=$(this).val();
					new_txt  += ', ';
					$(this).attr('checked', false);
				});

				var $txt = $('.'+field_value+'');
				var cur_txt = $txt.val();
				var txt  = cur_txt + new_txt ;

				$txt.val(txt);
				$(this).dialog("close");
			}
		}


	]
 
	});
	
}); /* $(document).ready END  */