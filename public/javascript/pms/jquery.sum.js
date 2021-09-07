;
/**
 * jquery.sum.js
 * @date 28.12.2018
 * @author @cla
 * 
 * example: $('.selector_sumA, .selector_sumXYZ').sum();
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

(function($) {
	$.fn.sum = function() {
		var _sum = 0;
		$(this).each(function(i, e) {
			_val = parseFloat($(e).val());
			if ( ! isNaN(_val)) {
				_sum += _val;				
			}
		});
		return _sum;
	};
})(jQuery);