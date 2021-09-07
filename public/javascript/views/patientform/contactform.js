;
/**
 * contactform.js
 * @date 21.12.2018
 * @author @cla
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

$(document).ready(function() {
	
	$('#block-Infusiontimes input.timepicker')
	.timepicker({

		defaultTime: new Date(), 

		showPeriodLabels: false,
		showLeadingZero: true,
		minutes: {
			interval: 5
		},
		rows: 4,
		hourText: 'Stunde',
		minuteText: 'Minute',

		onClose: function() {
			
			var _parent = $(this).parents('table'),
			
			_start = $('input.timepicker[name*="\[start\]"]', _parent).val(),
			_end = $('input.timepicker[name*="\[end\]"]', _parent).val(),
			_paused_from = $('input.timepicker[name*="\[paused_from\]"]', _parent).val(),
			_paused_till = $('input.timepicker[name*="\[paused_till\]"]', _parent).val();
			
			if (_start != '' && _end != '' && compareDates(_end, 'HH:mm',_start, 'HH:mm')) {
				
				var _elapsedT = getDateFromFormat(_end, 'HH:mm') - getDateFromFormat(_start, 'HH:mm');
				var _pausedT = 0;
				
				if ( _paused_from != '' && _paused_till != '' 
					&& compareDates(_paused_till, 'HH:mm',_paused_from, 'HH:mm')
					&& compareDates(_paused_from, 'HH:mm',_start, 'HH:mm') && compareDates(_paused_till, 'HH:mm',_start, 'HH:mm') 
					&& ! compareDates(_paused_from, 'HH:mm',_end, 'HH:mm') && ! compareDates(_paused_till, 'HH:mm',_end, 'HH:mm')) 
				{
					//_pausedMinutes = diff;
					var _pausedT = getDateFromFormat(_paused_till, 'HH:mm') - getDateFromFormat(_paused_from, 'HH:mm');
				}
				
				_elapsedT -= _pausedT;
				$('.selector_infusion_ttime').val(_elapsedT /60 / 1000);
				
			} else {
				
				$('.selector_infusion_ttime').val('');
				
			}
		},

	})
	.mask("99:99")
	;
	
	attachInputmask(this);
	
	attachAmpoulesRows($('.selector_ampoules'));
	
	//ISPC-2666 Lore 18.09.2020
	try {
		$('.livesearchFormEvents').livesearchCityZipcode();
		$('.livesearchFormEvents').livesearchHealthInsurance();					
	} catch (e) {
		//console.log(e);
	}
});

//ISPC-2667 Lore 22.09.2020
function show_kind_ins_extrafields(checkvals){

	if(checkvals == true){
		$('.kind_ins_view_extrafields').each(function() {
			$(this).attr('style', '');
		  }
		);
	}else{
		
		if( $('#PatientCareInsurance-kind_ins_legally').is(':checked') || 
				 $('#PatientCareInsurance-kind_ins_private').is(':checked') || 
				 $('#PatientCareInsurance-kind_ins_no').is(':checked') ||
				 $('#PatientCareInsurance-kind_ins_others').is(':checked') 
				 ){
					$('.kind_ins_view_extrafields').each(function() {
						$(this).attr('style', '');
					  }
					);
			}else {
				$('.kind_ins_view_extrafields').each(function() {
					$(this).attr('style', 'display:none;');
				  }
				);
			} 
	}	
}
//.

function attachInputmask (_selectorObj)
{
	var _inputsArr = $('*[data-inputmask]', _selectorObj);
	
	if (_inputsArr.length) {
		_inputsArr.inputmask();
	}
}


function attachAmpoulesRows(_selectorObj) 
{
	//#Infusion_ampoules_extra is hardcoded in the form
	$(_selectorObj).on('focusout', function(){
		
		var _cnt = Number($(this).val());
		
		if ( _cnt < 1) {
			$(".selector_extra_rows_ampoules:visible").hide().attr('disabled', true);
			$(".selector_extra_rows_ampoules:visible input").attr('disabled', true);
		} else {
			var _extra_form = $(this).attr('data-extraform');
			
			$('.selector_preparation_in_pharmacy_'+_extra_form+'.selector_extra_rows_ampoules').show();
			$('.selector_preparation_in_pharmacy_'+_extra_form+'.selector_extra_rows_ampoules input').attr('disabled', false);
			//clone first row we allways have one			
			
		    var _rowClone = $(this).closest('tr').next().clone(true);
		    $("td input:text", _rowClone).val("").attr('id', null);
		   
		    var _rowsCnt = $('.selector_extra_rows_ampoules:visible').length;
		    
		    
		    if (_rowsCnt < _cnt) {
		    	
		    	var _rows = [];
		    	var pattern = new RegExp("\[[0-9]+\]", "g");
		    	//var pattern = /\[ampoules_extra\]\[extra\]\[[0-9]+\]+/g;
		    	
		    	for (var _i=0; _i < (_cnt-_rowsCnt); _i++) {
		    		
		    		$("input:text", _rowClone).each(function(){
		    		
		    			$(this).attr(
		    					"name",
		    					$(this).attr("name").replace(pattern, "[" + (_rowsCnt+_i) + "]")
    					);
		    			
		    		});
		    		
		    		_rows.push(_rowClone[0].outerHTML);
		    	}
		    			    	
		    	//$(_rows.join()).insertAfter($(this).closest('tr').next());
		    	$(_rows.join()).insertAfter($('.selector_extra_rows_ampoules:visible:last'));
		    	
		    	try{
		    		attachInputmask($('.selector_extra_rows_ampoules:gt(' + (_rowsCnt-1) + ')'));		    		
		    	} catch (e) {
	    			console.error(e);
		    	}
		    	
		    } else if (_rowsCnt > _cnt) {
		    	
		    	$('.selector_extra_rows_ampoules:gt('+(_cnt-1)+')').remove();
		    }
		    
		}
		
	});
}

