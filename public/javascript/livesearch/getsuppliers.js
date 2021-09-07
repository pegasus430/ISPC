/*
 * getsupplies.js requires livesearch.js
 * @claudiu update 29.06.2018
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
	
	$.fn.livesearchSuppliers = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/suppliers',
				livesearch_id		: "livesearch_suppliers",
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 900,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'table',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValue	: 'Suppliers',
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
			
			
			//this s**t MUST be replaced in future functions with a json
			//you have available js string.escapeValue() if needed
			$('input[name*="\[supplier\]"]', _inputsOwner ).val($('input[id^="supp_su_"]', _clickedTR).val());
			
			$('input[name*="\[type\]"]', _inputsOwner ).val($('input[id^="supp_ty_"]', _clickedTR).val());

			
			
			$('input[name*="\[last_name\]"]', _inputsOwner ).val($('input[id^="supp_ln_"]', _clickedTR).val());
			$('input[name*="\[first_name\]"]', _inputsOwner ).val($('input[id^="supp_fn_"]', _clickedTR).val());
			$('input[name*="\[salutation\]"]', _inputsOwner ).val($('input[id^="supp_sal_"]', _clickedTR).val());
			$('input[name*="\[street1\]"]', _inputsOwner ).val($('input[id^="supp_st_"]', _clickedTR).val());
			$('input[name*="\[zip\]"]', _inputsOwner ).val($('input[id^="supp_zip_"]', _clickedTR).val());
			$('input[name*="\[city\]"]', _inputsOwner ).val($('input[id^="supp_ci_"]', _clickedTR).val());
			$('input[name*="\[phone\]"]', _inputsOwner ).val($('input[id^="supp_ph_"]', _clickedTR).val());
			$('input[name*="\[fax\]"]', _inputsOwner ).val($('input[id^="supp_fax_"]', _clickedTR).val());
			$('input[name*="\[email\]"]', _inputsOwner ).val($('input[id^="supp_email_"]', _clickedTR).val());
			

			$('textarea[name*="\[supplier_comment\]"]', _inputsOwner ).text($('input[id^="supp_comm_"]', _clickedTR).val());
			
			/* hidden */

			$('input[name*="\[id\]"]', _inputsOwner ).val(0);
			$('input[name*="\[supplier_id\]"]', _inputsOwner ).val(-1);
			$('input[name*="\[self_id\]"]', _inputsOwner ).val($('input[id^="supp_id_"]', _clickedTR).val()); // to know what was the original

			return true;
		};
		
		_opts.init = function(options) {		
			//overwrite
			window.selectSuppliers = function (){
//				console.log('fn selectSpecialist is disabled');
			}
			return $( options.selectorParents + " [" + options.selectorInputAttr + "='" + options.selectorInputValue + "']", options.selectorContext).each(function() {
				var _inputsOwner =  $(this).parents(options.selectorParents);
//				console.log(_inputsOwner);
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

