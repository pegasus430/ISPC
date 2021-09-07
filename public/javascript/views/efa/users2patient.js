/**
ISPC-2894 Ancuta 19.05.2021
*/ 
$(document).ready(function() {

		$('.show_content').on('click', function() {
		var line = $(this).data('line');
		var userinfo = $(this).data('userinfo');

		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
		} else {
			$(this).addClass('active');
		}

		$(".content_" + line + "_" + userinfo  ).toggle();

	});

}); 