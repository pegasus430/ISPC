/*
 * patienthealthedit.js
 * @claudiu update 23.11.2017
 * this is intended to be used as default on all new form you add after 23.11.2017 ... only for <table/>
 * this will only work for 1 form/page, not multiple like zip
 * @param $
 */

(function($) {
	
	$.fn.livesearchHealthInsurance = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/shealthinsurance',
				livesearch_url_subdivisions		: 'ajax/healthinsurancesubdivisions',
				livesearch_id		: "livesearch_healthinsurance",
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 500,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'table',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValues	: ['HealthInsurance_company_name'] ,
				limitSearchResults	: 100,
		};
		
		var _opts = {};
		
		_opts.uuidv4 = function(){
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
				return v.toString(16);
			});
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
			
			
			var _hinsu_id = $('input[id^="hinsu_id_"]', _clickedTR).val();
			
			$('input[name*="company_name"]', _inputsOwner ).val($('input[id^="hinsu_nm_"]', _clickedTR).val());
			$('input[name*="insurance_provider"]', _inputsOwner ).val($('input[id^="hinsu_insurance_provider_"]', _clickedTR).val());
			$('input[name*="ins_contactperson"]', _inputsOwner ).val('');
			$('input[name*="ins_street"]', _inputsOwner ).val($('input[id^="hinsu_str_"]', _clickedTR).val());
			$('input[name*="ins_zip"]', _inputsOwner ).val($('input[id^="hinsu_zp_"]', _clickedTR).val());
			$('input[name*="ins_city"]', _inputsOwner ).val($('input[id^="hinsu_ci_"]', _clickedTR).val());
			$('input[name*="ins_phone"]', _inputsOwner ).val($('input[id^="hinsu_ph_"]', _clickedTR).val());
			$('input[name*="ins_phone2"]', _inputsOwner ).val($('input[id^="hinsu_ph2_"]', _clickedTR).val());
			$('input[name*="ins_post_office_box"]', _inputsOwner ).val($('input[id^="hinsu_pob_"]', _clickedTR).val());
			$('input[name*="ins_post_office_box_location"]', _inputsOwner ).val($('input[id^="hinsu_pobl_"]', _clickedTR).val());
			$('input[name*="ins_zip_mailbox"]', _inputsOwner ).val($('input[id^="hinsu_zm_"]', _clickedTR).val());
			$('input[name*="ins_phonefax"]', _inputsOwner ).val($('input[id^="hinsu_fax_"]', _clickedTR).val());
			$('input[name*="ins_email"]', _inputsOwner ).val($('input[id^="hinsu_em_"]', _clickedTR).val());
			$('input[name*="ins_debtor_number"]', _inputsOwner ).val($('input[id^="hinsu_debtor_number_"]', _clickedTR).val());
			$('input[name*="comment"]', _inputsOwner ).val($('input[id^="hinsu_comm_"]', _clickedTR).val());
			// Maria:: Migration CISPC to ISPC 20.08.2020 
			//ISPC-2648 ISPC:Krankenkasse Kommentarfeld, elena, 14.08.2020
			$('textarea[name*="comment"]', _inputsOwner ).val($('input[id^="hinsu_comm_"]', _clickedTR).val());
			$('input[name*="institutskennzeichen"]', _inputsOwner ).val($('input[id^="hinsu_ik_"]', _clickedTR).val());
			$('input[name*="kvk_no"]', _inputsOwner ).val($('input[id^="hinsu_kv_"]', _clickedTR).val());
			
			
			$("input[name$='\[companyid\]']", _inputsOwner).val(_hinsu_id);

			
			
			this.fetchSubdivisions(_hinsu_id, _inputsOwner);
			
			
		};
		

		_opts.fetchSubdivisions = function (_hinsu_id, _inputsOwner) {
			
			var _that = this;
			var _subdivOwner = _inputsOwner.parents(".livesearchFormEvents");			
			var _data = 'context=' + encodeURIComponent(_that.selectorContext.selector)
			+ '&hid=' + _hinsu_id;
			
			$.ajax({
				dataType	: "json",
				type		: "GET",
				url			: _that.livesearch_url_subdivisions,
				data		: _data,
				
				success: function (response, request) {
					
					if (response != '0') {

						$.each(response, function(_i, _item) {
							//_i is subdiv_id
							
							$("input[name$='\["+_i+"\]\[company_id\]']", _subdivOwner).val(_hinsu_id);
							
							$("input[name$='\["+_i+"\]\[ins2s_name\]']", _subdivOwner).val(_item.name);
							$("input[name$='\["+_i+"\]\[ins2s_insurance_provider\]']", _subdivOwner).val(_item.insurance_provider);
							$("input[name$='\["+_i+"\]\[ins2s_contact_person\]']", _subdivOwner).val(_item.contact_person);
							$("input[name$='\["+_i+"\]\[ins2s_street1\]']", _subdivOwner).val(_item.street1);
							$("input[name$='\["+_i+"\]\[ins2s_zip\]']", _subdivOwner).val(_item.zip);
							$("input[name$='\["+_i+"\]\[ins2s_city\]']", _subdivOwner).val(_item.city);
							$("input[name$='\["+_i+"\]\[ins2s_phone\]']", _subdivOwner).val(_item.phone);
							$("input[name$='\["+_i+"\]\[ins2s_phone2\]']", _subdivOwner).val(_item.phone2);
							$("input[name$='\["+_i+"\]\[ins2s_fax\]']", _subdivOwner).val(_item.fax);
							
							$("input[name$='\["+_i+"\]\[ins2s_post_office_box\]']", _subdivOwner).val(_item.post_office_box);
							$("input[name$='\["+_i+"\]\[ins2s_zip_mailbox\]']", _subdivOwner).val(_item.zip_mailbox);
							$("input[name$='\["+_i+"\]\[ins2s_post_office_box_location\]']", _subdivOwner).val(_item.post_office_box_location);
							
							$("input[name$='\["+_i+"\]\[ins2s_email\]']", _subdivOwner).val(_item.email);
							$("input[name$='\["+_i+"\]\[ins2s_kvnumber\]']", _subdivOwner).val(_item.kvnumber);
							$("input[name$='\["+_i+"\]\[ins2s_iknumber\]']", _subdivOwner).val(_item.iknumber);
							$("input[name$='\["+_i+"\]\[ins2s_ikbilling\]']", _subdivOwner).val(_item.ikbilling);
							$("input[name$='\["+_i+"\]\[ins2s_debtor_number\]']", _subdivOwner).val(_item.debtor_number);
							$("textarea[name$='\["+_i+"\]\[comments\]']", _subdivOwner).val(_item.comments);
						});
						
					}
				}
			});
			
			
			
			
		};
		
		
		_opts.init = function(options) {			
			//overwrite
			window.selectHealthInsurance = function (){
//				console.log('selectHealthInsurance is disabled');
			}
			
			if (typeof options.selectorInputValues !== 'object') {
				options.selectorInputValues = [options.selectorInputValues];	
			}
			
			return $(options.selectorInputValues).each(function(index, value){
				$( options.selectorParents + " [" + options.selectorInputAttr + "='" + value + "']", options.selectorContext).each(function() {
					$(this)
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




function generateppun(_obj, _inputName){
	
	var _input = $(_obj).parent().find("input[name*='" + _inputName + "']");
	
	if (_input.length == 1) {
		
	
	$(_input).addClass('loading');
	
		$.ajax({
			dataType: "json",
			method: "POST",
			type: "POST",
			url: appbase+'ajax/createpatientppun',
			data: {
				'id': window.idpd
			},
			
			complete : function () {
				$(_input).removeClass('loading');
			},
			
			success:function(data) {
				if(data.ppun != "-1"){
					$(_input).val(data.ppun);			
				}
			},
			
			error : function(){}
		});
	}

	
}

//ISPC-2452
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
