/*
 * @ancuta 21.11.2019
 * ISPC-2452
 */

(function($) {
	
})(jQuery);



function generatehidebitornr(_obj, _inputName){
	
	var _input = $(_obj).parent().find("input[name*='" + _inputName + "']");
	
	if (_input.length == 1) {
		
		
		$(_input).addClass('loading');
		
		$.ajax({
			dataType: "json",
			method: "POST",
			type: "POST",
			url: appbase+'ajax/createhidebitornumber',
			data: {
				'id': window.idpd
			},
			
			complete : function () {
				$(_input).removeClass('loading');
			},
			
			success:function(data) {
				if(data.hi_debitor_number != "-1"){
					$(_input).val(data.hi_debitor_number);			
				}
			},
			
			error : function(){}
		});
	}
	
	
}
