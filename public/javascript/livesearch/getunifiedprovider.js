/*
 * getunifiedprovider.js requires livesearch.js
 * @claudiu update 29.06.2018
 * this is intended to be used as default on all new form you add after 23.11.2017 ...
 * it attaches liveSearch event to selectorContext>selectorParents>selectorInputuAttr = selectorInputuValue
 * example:
 * 	$('.livesearchFamilyDoctor').livesearchUnifiedProvider();
 * or
 * 	$('.livesearchFamilyDoctor').livesearchUnifiedProvider({
 * 		'selectorInputAttr'		: 'id', 
 * 		'selectorInputValue'	: 'FamilyDoctor66',
 * 		'selectorParents'		: 'table',
 * 		callBackRowClicked : function(_target){console.log(arguments);}
 *  });
 *  
 * @cla on 01.04.2019
 * refactored, so we send params and have any
 */
(function($) {
	
	$.fn.livesearchUnifiedProvider = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/getunifiedprovider',
				livesearch_id		: "livesearch_unifiedprovider",
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 900,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'table',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValue	: 'unifiedProvider',
				limitSearchResults	: 100,
				limitSearchGroups	: ['user', 'voluntaryworker', 'member'],
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
			
			$('input[name*="\[participant_id\]"]', _inputsOwner ).val('');	
			$('input[name*="\[participant_type\]"]', _inputsOwner ).val('');
		};
		
		_opts.callBackRowClicked =  function(_target) {
			
			if (typeof(_target) === 'undefined' || typeof(_target) === 'null') return; //wear protection
			
			var _inputsOwner = $( this.selectorParents + " [" + this.selectorInputAttr + "='" + this.selectorInputValue + "']", this.selectorContext) || null;
			if ( ! _inputsOwner) return; //wear protection

			//hardcoded <table/>... livesearch result must allways be as table 
			var returnRowId = $(_target).parents("#" + _opts.livesearch_id).find('table').data('returnrowid') || null;
			if (returnRowId !== null) {//filter down
				$.each(_inputsOwner, function(){
					if ($(this).data('returnrowid') == returnRowId) {
						_inputsOwner = $(this);
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
			
			
			//you have available js string.escapeValue() if needed
			$('input[name*="\[participant_name\]"]', _inputsOwner ).val($('input[data-type="nice_name"]', _clickedTR).val());

			$('input[name*="\[last_name\]"]', _inputsOwner ).val($('input[data-type="last_name"]', _clickedTR).val());
			$('input[name*="\[first_name\]"]', _inputsOwner ).val($('input[data-type="first_name"]', _clickedTR).val());

			$('input[name*="\[salutation\]"]', _inputsOwner ).val($('input[data-type="salutation"]', _clickedTR).val());
			$('input[name*="\[street\]"]', _inputsOwner ).val($('input[data-type="street"]', _clickedTR).val());
			$('input[name*="\[zip\]"]', _inputsOwner ).val($('input[data-type="zip"]', _clickedTR).val());
			$('input[name*="\[city\]"]', _inputsOwner ).val($('input[data-type="city"]', _clickedTR).val());
			$('input[name*="\[email\]"]', _inputsOwner ).val($('input[data-type="email"]', _clickedTR).val());
			$('input[name*="\[mobile\]"]', _inputsOwner ).val($('input[data-type="mobile"]', _clickedTR).val());
			$('input[name*="\[phone\]"]', _inputsOwner ).val($('input[data-type="phone"]', _clickedTR).val());
			$('input[name*="\[comment\]"]', _inputsOwner ).val($('input[data-type="comments"]', _clickedTR).val());
			
			
			
			/*
			 * add what you need
			$('input[name*="\[title\]"]', _inputsOwner ).val($('input[data-type="title"]', _clickedTR).val());
			$('input[name*="\[user_title\]"]', _inputsOwner ).val($('input[data-type="user_title"]', _clickedTR).val());
			.
			.
			.
			*/
			
			/* hidden */
			$('input[name*="\[participant_id\]"]', _inputsOwner ).val($('input[data-type="id"]', _clickedTR).val()); // to know what was the original
			$('input[name*="\[participant_type\]"]', _inputsOwner ).val($('input[data-type="type"]', _clickedTR).val()); // to know what was the original

			return true;
		};
		
		_opts.init = function(options) {
			
			return $( options.selectorParents + " [" + options.selectorInputAttr + "='" + options.selectorInputValue + "']", options.selectorContext).each(function() {
				var _inputsOwner =  $(this).parents(options.selectorParents);
				//console.log(this);
				//console.log(_inputsOwner);
				
				var __opt,
				__groups = '';
				
				__opt = $(this).data('livesearch_options') || null;
				if (typeof __opt === 'object' && __opt.hasOwnProperty('limitSearchGroups')) {
					$(__opt.limitSearchGroups).map(function(){
						__groups += '&groups[]=' + encodeURIComponent(this);
					});
				} else {
					$(options.limitSearchGroups).map(function(){
						__groups += '&groups[]=' + encodeURIComponent(this);
					});
				}
				
				
				$(this)
				.on('change keyup', function (){
					options.callBackRowReset(this);
				})
				.data('returnrowid', options.uuidv4())
				.liveSearch({
					url: options.livesearch_url
					+ '?context=' + encodeURIComponent(options.selectorContext.selector)
					+ '&limit=' + options.limitSearchResults
					+ __groups
					+ '&q=',
					id: options.livesearch_id,
					livesearch_class: options.livesearch_class,
					noResultsDelay: options.noResults_Delay,
					aditionalWidth: options.aditionalWidth,
					typeDelay: options.type_Delay,
//					typeValue: function (input) {
//						return $("select[name$='[medical_speciality]']", $(input).parents(options.selectorParents)).val() || 1;
//					},
					returnRowId: function (input) {return $(input).data('returnrowid');}, //
					onSlideUp : function (_target) {return options.callBackRowClicked(_target);},
				});
			});
		};
		
		_opts.destroy = function() {
			return $( this.selectorParents + " [" + this.selectorInputAttr + "='" + this.selectorInputValue + "']", this.selectorContext).each(function() {
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

