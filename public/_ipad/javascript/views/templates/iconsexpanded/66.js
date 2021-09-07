;
/**
 * 66.js = sapv_appl
 * @date 04.04.2019
 * @author @cla
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


jQuery(document).ready(function ($) {
	
	$('span.current_problem_text', $("#placeholder\\.patient\\.icons\\.new"))
	.off('click.iconexpandbar')
	.on('click.iconexpandbar', function() {
		$(this)
		.hide()
		.parent().find('textarea.current_problem_text').show();
	});
	
	$('textarea.current_problem_text', $("#placeholder\\.patient\\.icons\\.new"))
	.off('blur.iconexpandbar')
	.on('blur.iconexpandbar', function() {
		
		var _that = this,
		_url = appbase + '/ajax/savecurrentproblem?fieldname=' + this.name + '&pid=' + window.idpd,
		_content = this.value;
		
		$.ajax({
			"dataType" : 'json',
			"type" : "POST",
			"url" : _url,
			"data" : {
				'content' : _content,
			},
			"success" : function() {
				$(_that)
				.hide()
				.parent().find('span.current_problem_text').html(nl2br(_content)).show();
			}
		});
		
	});
});