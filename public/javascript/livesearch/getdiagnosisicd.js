/*
 * patienthealthedit.js
 * since 11.2017
 * requires livesearch.js
 * example:
 * 	$(selector).livesearchDiacgnosisIcd(); -- if you use the default selectors table data-livesearch=zip & table data-livesearch=city
 * or
 * 	$(selector).livesearchDiacgnosisIcd({
 * 		'selectorInputAttr'		: 'data-livesearch', 
 * 		'selectorInputValues'	: ['diagnosis111', 'icdnumber111'],
 * 		'selectorParents'		: 'tr',
 * 		callBackRowClicked : function(_target){console.log(arguments);}
 *  });
 * 
 * @claudiu update 05.01.2018 : flawed , must change to add a _opts.reset function and make _opts.destroy
 */
(function($) {
	
	$.fn.livesearchDiacgnosisIcd = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/diagnosis',
				livesearch_id		: "livesearch_admission_diagnosis",
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 500,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'tr',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValues	: ['diagnosis', 'icdnumber'] ,
				limitSearchResults	: 100,
		};
		
		var _opts = {};
		
		_opts.uuidv4 = function(){
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
				return v.toString(16);
			});
		};
		
		_opts.callBackRowReset =  function (_target) {
			
			if (_opts.selectorParents == '') {
				var _inputsOwner = $(_target).parents('tr') || null;// BUG, must change approach
			} else {
				var _inputsOwner = $(_target).parents(_opts.selectorParents) || null;
			}

			if ( ! _inputsOwner) return; //wear protection
			
			//$('input[data-livesearch="id"]', _inputsOwner ).val('');
			$('input[data-livesearch="id_deleted"]', _inputsOwner ).attr('disabled', false);
			$('input[data-livesearch="diagnosis_id"]', _inputsOwner ).val('');
			$('input[data-livesearch="icd_id"]', _inputsOwner ).val('');
			$('input[data-livesearch="description"]', _inputsOwner ).val('');
			$('input[data-livesearch="date"]', _inputsOwner ).val('');
			$('input[data-livesearch="tabname"]', _inputsOwner ).val('diagnosis_freetext');
			
		};
		
		_opts.callBackRowClicked =  function(_target) {
			
			if (typeof(_target) === 'undefined' || typeof(_target) === 'null') return; //wear protection
			
			if (typeof this.selectorInputValues !== 'object') {
				this.selectorInputValues = [this.selectorInputValues];	
			}
			
			var _inputsOwner = {};
			var _hasInputsOwner = false;
			var _this = this;
			
			$(_this.selectorInputValues).each(function(index, value){
				_inputsOwner[value] = $( _this.selectorParents + " [" + _this.selectorInputAttr + "='" + value + "']", _this.selectorContext) || null; 
				if (_inputsOwner[value] !== null) {
					_hasInputsOwner = true;
				}
			});
			//jQuery.isEmptyObject({});
			if ( ! _hasInputsOwner) return; //wear protection
			
			//hardcoded <table/>... livesearch result must allways be as table 
			var returnRowId = $(_target).parents("#" + _opts.livesearch_id).find('table').data('returnrowid') || null;
			if (returnRowId !== null) {//filter down
				$.each(_inputsOwner, function(){			
					var myFilter = $(this).filter(function() { 
						  return $(this).data("returnrowid") == returnRowId 
					});
					if (myFilter.length == 1) {
						_inputsOwner = myFilter;
						return false;
					}
				});
			}
			_inputsOwner =  _inputsOwner.parents(_opts.selectorParents);//reset to its parent
			
			var _clickedTR = $(_target) || null;
			if (_clickedTR !== null && _clickedTR.is('td')) {
				_clickedTR = _clickedTR.parents('tr');//select parent tr
			}
			if ( ! _clickedTR) return; //wear protection
			
			
			//this s**t MUST be replaced in future functions with a json
			//you have available js string.escapeValue() if needed
			$('input[data-livesearch="icdnumber"]', _inputsOwner ).val($('input[id^="diag_icd_"]', _clickedTR).val());
			$('input[data-livesearch="diagnosis"]', _inputsOwner ).val($('input[id^="diag_de_"]', _clickedTR).val());

			//$('input[data-livesearch="diagnosis_type_id"]', _inputsOwner ).val($('input[id^="diag_id_"]', _clickedTR).val());
			$('input[data-livesearch="diagnosis_id"]', _inputsOwner ).val($('input[id^="diag_id_"]', _clickedTR).val());
			$('input[data-livesearch="icd_id"]', _inputsOwner ).val($('input[id^="diag_id_"]', _clickedTR).val());
			$('input[data-livesearch="description"]', _inputsOwner ).val('');
			$('input[data-livesearch="date"]', _inputsOwner ).val('');
			$('input[data-livesearch="tabname"]', _inputsOwner ).val('diagnosis');
			
			
				    
		};

		_opts.init = function(options) {			
			//overwrite
			window.selectDiagnosis = function (){
//				console.log('selectCityZipcode is disabled');
			}
			
			if (typeof options.selectorInputValues !== 'object') {
				options.selectorInputValues = [options.selectorInputValues];	
			}
			
			$(options.selectorInputValues).each(function(index, value){
				$( options.selectorParents + " [" + options.selectorInputAttr + "='" + value + "']", options.selectorContext).each(function() {
					$(this)
					.on('change keyup', function (){
						options.callBackRowReset(this); //reset_diagnosis(input_row);
					})
					.data('returnrowid', options.uuidv4())
					.liveSearch({
						url: options.livesearch_url
						+ '?context=' + encodeURIComponent(options.selectorContext.selector)
						+ '&mode=' + encodeURIComponent(value)
						+ '&limit=' + options.limitSearchResults
						+ '&q=',
						id: options.livesearch_id,
						livesearch_class: options.livesearch_class,
						noResultsDelay: options.noResults_Delay,
						aditionalWidth: options.aditionalWidth,
						typeDelay: options.type_Delay,
						returnRowId: function (input) {return $(input).data('returnrowid');}, //
						onSlideUp : function (_target) {return options.callBackRowClicked(_target);},
					});
				});
			});
			

			return true;
		};
		
		_opts.destroy = function() {
			return $( this.selectorParents + " [" + this.selectorInputAttr + "='" + this.selectorInputValues + "']", this.selectorContext).each(function() {
				$(this)
				.removeAttr('data-returnrowid')
				.liveSearch('destroy')
				;
			});
	    };
	    
		_opts = $.extend({}, defaults, _opts, options);//append/overwrite options
		
		_opts.init(_opts);//start		
		
		return this;
	};
})(jQuery);
