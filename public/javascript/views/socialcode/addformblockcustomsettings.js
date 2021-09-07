//ISPC-2454
$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var ctype;
	
	var itemtype = $('#fbcset').find('.ctype').val();
	$('#fbcset').find('.ctypehidd').val(itemtype);
	
	$(document).on("click", '.addbutton', function(){
		var parent_form = 'block_content';
		
		$.get(appbase + 'ajax/createformblockcustomitemrow?parent_form='+parent_form, function(result) {
		var newFieldset =  $(result).insertAfter($('#formblockitems tr:last'));
	});
	});

	$(document).on('change', '.ctype', function(){
		$(this).parent().parent().parent().find('.ctypehidd').val($(this).val());
	});
	
});/*-- END  $(document).ready ----------- --*/
	