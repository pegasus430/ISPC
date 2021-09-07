$(function(){
	var gc = '';		
	var gcarr = gc.split(',');

	$('.contactform_dragvbox').each(function() {
		var blockId = $(this).find('.contactform_dragvbox_content').attr('id');

		if (gc) {
			if (jQuery.inArray(blockId, gcarr) != -1) {
				$(this).find('.contactform_dragvbox_content').show();
			} else {

				$(this).find('.contactform_dragvbox_content').hide();
			}
		}

		$(this)
		.hover(function() {
			$(this).find('h2').addClass('collapse');
		}, function() {
			$(this).find('h2').removeClass('collapse');
		})
		
		.find('h2').hover(function() {
			$(this).find('.configure').css('visibility', 'visible');
		}, function() {
			$(this).find('.configure').css('visibility', 'hidden');
		})
		
		.click(function() {
			if ($(this).siblings('.contactform_dragvbox_content').is(":hidden")) {
				gc = gc + blockId + ',';
			}
			else
			{
				gc = gc.replace(blockId + ',', '');
			}
			
			$(this).siblings('.contactform_dragvbox_content').toggle();
		})
		.end()
		.find('.configure').css('visibility', 'hidden');
	});
});