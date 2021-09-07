;
/**
 * assessment.js
 * mambo
 * @date 04.12.2018
 * @author @cla
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = null;

var _block_name = "MamboAssessment";



$(document).ready(function() {
	$('.date')
	.datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: '',
		//maxDate: '0',
		beforeShow : function(e, o) {
			if($(e).hasClass('allow_future')) {
				$(e).datepicker("option", "maxDate", "+1y");
			} else {
				$(e).datepicker("option", "maxDate", 0);
			}
		},
		beforeShowDay : isDateWithinRanges
	});

	//if altfield&&altformat add datepicker opts
	$('.date').each(function(){
		if ($(this).data('altfield') && $(this).data('altformat')) {
			var _that = this;
			$(this).parent().find('input[type=hidden]').each(function(){
				if ($(this).attr('name') == $(_that).attr('name')) {
					$(_that).datepicker( "option", {"altField": this, "altFormat": $(_that).data('altformat')});
					return false;
				}
			});
		}
	});
		

		

	$('.time')
	.timepicker({

		// minTime: { hour: 0, minute: 30 },
		// maxTime: { hour: 23, minute: 30 },

		defaultTime: new Date(), // +":"+dosages_limits[i]['min_minutes'],

		showPeriodLabels: false,
		showLeadingZero: true,
		minutes: {
			interval: 5
		},
		rows: 4,
		hourText: 'Stunde',
		minuteText: 'Minute',

		onClose: function() {
			var _parent = $(this).parents('fieldset');
			var _start = this.value;
			if (_start != '' 
				&& $(this).hasClass('formular_time_start')) 
			{ 
				// calculate
				// formular
				// end
				// time
				var _end = $('.formular_time_end', _parent).val();
				
				if ( !compareDates(_end, 'HH:mm',_start, 'HH:mm')) 
				{
					// add our 45 minutes if end is
					// empty or start if after end
					_end = HHmmAddMinutes(_start, 45);
					$('.formular_time_end', _parent)
						.val(_end);
				}
			}
		},

	})
	.mask("99:99")
	;
		


	
	
	attachDatetimepicker(this);
	
	attachInputmask(this);
	
	attachAutocomplete(this);

	


	//$('#layout_result_messages').delay(10000).fadeOut('fast');
	$('#layout_result_messages').hide();

		
});




function form_submit_validate(){
	return true;
}

function isDateWithinRanges( _date ) 
{

	if ( ! $(this).data('allowranges')) {
		return [true];
	}
	
	var _result = [true];
	
	// !! allowranges must be php-format : [ {from: yyyy-n-j, till:yyyy-n-j}, .. ]
	$.each($(this).data('allowranges'), function(_i, _range) {

		var _date_from = _range.from.split("-");
		var _date_till = _range.till.split("-");
		
		//JavaScript counts months from 0 to 11. January is 0. December is 11.
		var dateFROM = new Date(Date.UTC(_date_from[0], _date_from[1] -1, _date_from[2]));
		var dateTILL = new Date(Date.UTC(_date_till[0], _date_till[1] -1, _date_till[2]));

		if ((_range.till == null || _range.till == '') && dateFROM <= _date) {					
			_result = [true];
			return false;//break1
		
		} else if (dateFROM <= _date && _date <= dateTILL) {
			_result = [true];
			return false; //break1
		} else {
			_result = [false, 'error_day_notinrange'];
		}
		
	});
	return _result;			
}

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
          tmp = item.split("=");
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
};

function attachDatetimepicker (_selectorObj)
{
	try {
		
		$('.datetimepicker', _selectorObj).datetimepicker({
			timeFormat: 'HH:mm',
			
			showHour: null,
			showMinute: null,
			showSecond: false,
			showMillisec: false,
			showMicrosec: false,
			showTimezone: false,
			showTime: true,
		});
	} catch (e) {
		if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
			console.log(e);
		}
	}
}

function attachInputmask (_selectorObj)
{
	try {
		var _inputsArr = $('*[data-inputmask]', _selectorObj);
		
		if (_inputsArr.length) {
			_inputsArr.inputmask();
		}
	} catch (e) {
		if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
			console.log(e);
		}
	}
}
function attachAutocomplete (_selectorObj)
{
	var methods = ['manual', 'user'];

	
	try {
		$("input:text.autocomplete", _selectorObj).each(function() {
				
			var __that = this;
			
			$(this)
			.autocomplete({
				
				minLength: 2,
				
				delay: 500,
				
				search: function( event, ui ) {
		    		  $(this).parent().find(".selector_autocomplete_type").val('manual');
			    	  $(this).parent().find(".selector_autocomplete_id").val('');
				},
				
				source: function( request, response ) {
					$.ajax({
						
						url			: appbase + "ajax/autocomplete",
						type		: "POST",
						dataType	: "json",
						data		: {
							"q"		: request.term,
							"methods"	: methods,
							"autocomplete_manual" : $(__that).data('autocomplete_manual') || null,
							"limit"	: 20,
						},
						
						beforeSend: function (jqXHR) {
							$(__that).addClass('loading');
						},
						complete: function (jqXHR) {
							$(__that).removeClass('loading');
						},
						error: function (xOptions, textStatus) {
							response([]); //something went wrong, please contact admin
						},
						success: function( data ) {
							$(__that).removeClass('loading');
			        	  
							var method,
							length = methods.length,
							results = [];
							  
							for (var i = 0; i < length; i++) {
								
								method = methods[i];
								
								if (data.hasOwnProperty(method)) {
									var methodResults = $.map(data[method], function (item) {
						                return {
						                    label	: item.nice_name,
						                    value	: item.nice_name,
						                    type	: item.type || 'manual',
						                    id		: item.id || 0,
						                };
									});	 
									
									if (methodResults.length > 0) {											
										results.push({
						                    label		: translate(method + " autocomplete title"),
						                    value		: null,
						                    "type"		: null,
						                    "class"	: 'ui-state-disabled autocomplete_category',
						                });
										$.merge(results, methodResults);//append/overwrite _data
									}
								}
							}
							
							response(results);
						},
						
			        });//eo ajax
					
			      },//eo source
			      
			      
			      
			      select: function( event, ui ) {
			    	  if (ui.item){					
				    	  $(this).parent().find(".selector_autocomplete_type").val(ui.item.type);
				    	  $(this).parent().find(".selector_autocomplete_id").val(ui.item.id);
			    	  } else {
			    		  $(this).parent().find(".selector_autocomplete_type").val('manual');
				    	  $(this).parent().find(".selector_autocomplete_id").val('');
			    	  }
			      },
			      
			      open: function() {
			        //$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			      },
			      
			      close: function() {
			        //$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			      },
			      
			});
			
			
			$(this)
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				
				var _class = (item.hasOwnProperty('class') ? item['class'] : '');
				
		        return $( "<li></li>")
		        .data("item.autocomplete", item)
		        .addClass(_class)
		        .append( $( "<a></a>" ).text( item.label ) )
	        	.appendTo( ul );
			};
			$(this)
			.data( "autocomplete" )._renderMenu = function( ul, items ) {
				var that = this;
				$.each( items, function( index, item ) {
					that._renderItem( ul, item );
				});
				$( ul ).find( "li:odd" ).addClass( "odd" );
			};
		
		});
		
		
		
	} catch (e) {
		if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
			console.log(e);
		}
	}
	
}


/**
 * fn not used, it was made to add course via ajax on click.. decided to revert and save course on the normal post
 * @param __that
 * @param __extraObject
 */
function addNewCourse (__that, __extraObject){

	var _data = $(__that).parents('table').eq(0).find('input, select, textarea').serializeObject() || {};
	var _action = _data.__action || 'addNewCourse';
	
	_data.__formular = {};
	_data.__formular.__action = _action;	
	//$.extend(true, _data, __extraObject);//append/overwrite _data

	var _url = appbase +"mambo/assessmentbenefitplan?" + "id=" + window.idpd;
	
    $.ajax({
        dataType	: "json",
        type		: "POST",
        url			: _url,
        data		: _data,
        
        beforeSend: function (jqXHR) {	
        	$(__that).parents('table').eq(0).block({
				css: {
					border: 'none',
					padding: '15px',
					backgroundColor: '#000',
					'-webkit-border-radius': '10px',
					'-moz-border-radius': '10px',
					opacity: .5,
					color: '#fff',
					height: 'auto'
				},
				message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>',
//				
//				onBlock : function (){
//				},
//				onUnblock : function (){
//				},
				
				focusInput: false,
				
				baseZ : 1000,
				fadeIn :100,
				fadeOut :200,
				timeout :0,			// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock 
				ignoreIfBlocked: true,
				
			});
	    },
	    complete: function (jqXHR) {
	    	
	    	$(__that).parents('table').eq(0).unblock();
	    },
	    
	    
	    success: function (response, request) {
	    }
    });

}



function add_custom_problem(parent_form){
	
	var _parent_form = "AssessmentProblems",
	_block_name = "AssessmentProblems";
	

	$.get(appbase + 'ajax/createformassessmentoneproblemrow?parent_form='+_parent_form
			+ '&_block_name=' + _block_name
			+ '&id=' + window.idpd,
			function(result) {

				//ISPC-2293 Carmen 02.06.2020
				//var newFieldset = $(result).insertAfter($("#assessment_problems_table>tbody>tr:last"));
				var newFieldset = $(result).appendTo($("#assessment_problems_table"));
				//--
		
				attachDatetimepicker(newFieldset);
				
				attachInputmask(newFieldset);
				
				attachAutocomplete(newFieldset);	
				
		
	});
	
}


