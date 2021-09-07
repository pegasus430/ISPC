/*
 * getfamilydoctor.js requires livesearch.js
 * @claudiu update 23.11.2017
 * this is intended to be used as default on all new form you add after 23.11.2017 ...
 * it attaches liveSearch event to selectorContext>selectorParents>selectorInputuAttr = selectorInputuValue
 * example:
 * 	$('.livesearchFamilyDoctor').livesearchFamilyDoctor();
 * or
 * 	$('.livesearchFamilyDoctor').livesearchFamilyDoctor({
 * 		'selectorInputAttr'		: 'id', 
 * 		'selectorInputValue'	: 'FamilyDoctor66',
 * 		'selectorParents'		: 'table',
 * 		callBackRowClicked : function(_target){console.log(arguments);}
 *  });
 */
(function($) {
	
	$.fn.livesearchFamilyDoctor = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/sfamilydoctor',
				livesearch_id		: "livesearch_familydoctor",//hardcoded... we need a standard class for the dialog cause now is by id
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 500,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'table',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValue	: 'FamilyDoctor',
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
			
			$('input[name*="\[self_id\]"]', _inputsOwner ).val('');
			$('input[name*="\[id\]"]', _inputsOwner ).val('');
			
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
			
			if ( ! $('input[id^="fdoc_id_"]', _clickedTR).length) return; //you clicked the keine or something else
			
			//this s**t MUST be replaced in future functions with a json
			//you have available js string.escapeValue() if needed
			$('input[name*="\[id\]"]', _inputsOwner ).val('0');
			$('input[name*="\[practice\]"]', _inputsOwner ).val($('input[id^="fdoc_px_"]', _clickedTR).val());
			
			$('input[name*="\[title\]"]', _inputsOwner ).val($('input[id^="fdoc_tn_"]', _clickedTR).val());
			$('input[name*="\[last_name\]"]', _inputsOwner ).val($('input[id^="fdoc_ln_"]', _clickedTR).val());
			$('input[name*="\[first_name\]"]', _inputsOwner ).val($('input[id^="fdoc_fn_"]', _clickedTR).val());
			$('input[name*="\[salutation\]"]', _inputsOwner ).val($('input[id^="fdoc_sal_"]', _clickedTR).val());
			$('input[name*="\[street1\]"]', _inputsOwner ).val($('input[id^="fdoc_st_"]', _clickedTR).val());
			$('input[name*="\[zip\]"]', _inputsOwner ).val($('input[id^="fdoc_zip_"]', _clickedTR).val());
			$('input[name*="\[city\]"]', _inputsOwner ).val($('input[id^="fdoc_ci_"]', _clickedTR).val());
			$('input[name*="\[phone_practice\]"]', _inputsOwner ).val($('input[id^="fdoc_ph_"]', _clickedTR).val());
			$('input[name*="\[phone_private\]"]', _inputsOwner ).val($('input[id^="fdoc_pr_"]', _clickedTR).val());
			$('input[name*="\[fax\]"]', _inputsOwner ).val($('input[id^="fdoc_fax_"]', _clickedTR).val());
			$('input[name*="\[email\]"]', _inputsOwner ).val($('input[id^="fdoc_email_"]', _clickedTR).val());
			$('input[name*="\[doctornumber\]"]', _inputsOwner ).val($('input[id^="fdoc_dnr_"]', _clickedTR).val());
			$('input[name*="\[doctor_bsnr\]"]', _inputsOwner ).val($('input[id^="fdoc_bsnr_"]', _clickedTR).val());
			$('textarea[name*="\[comments\]"]', _inputsOwner ).text($('input[id^="fdoc_comments_"]', _clickedTR).val());
			
			$('input[name*="\[nice_name\]"]', _inputsOwner ).val($('input[id^="fdoc_tn_"]', _clickedTR).val() + ' ' + $('input[id^="fdoc_fn_"]', _clickedTR).val() +' '+ $('input[id^="fdoc_ln_"]', _clickedTR).val() );

			$('input[name*="\[self_id\]"]', _inputsOwner ).val($('input[id^="fdoc_id_"]', _clickedTR).val());
			
			$('input[name*="\[debitor_number\]"]', _inputsOwner ).val($('input[id^="fdoc_debitor_number_"]', _clickedTR).val());
			$('input[name*="\[shift_billing\]"]', _inputsOwner ).prop('checked', $('input[name="shift_billing"]', _clickedTR).val() == "1" ? true : false);
			
		};
		
		_opts.init = function(options) {		
			//overwrite
			window.selectFamilyDoctor = function (){
//				console.log('selectCityZipcode is disabled');
			}
			return $( options.selectorParents + " [" + options.selectorInputAttr + "='" + options.selectorInputValue + "']", options.selectorContext).each(function() {
				$(this)
				.on('change keyup', function (){
						options.callBackRowReset(this);
				})
				.data('returnrowid', options.uuidv4())
				.liveSearch({
					url: options.livesearch_url
					+ '?context=' + encodeURIComponent(options.selectorContext.selector)
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

