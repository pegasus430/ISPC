/*
 *	ispc-1752
 * 
 *  search into dbf for zip-city corelation
 *  
 */

var selectCityZipcode = function selectCityZipcode(doc_id , context , extraid)
	{
		switch(context) {
    		case "PatMAddPatie_Stamm":
    		case "PatentEditDiv":
    		case "specialist_details":
    		case "patienthealthedit_edit":
    		case "family_dr_edit":
    		case "supplier_details":
    		case "tabs-2":
    		case "tabs-1":
				$('#'+context+' #zip ' ).val($('#statecityzipcode_drop_table #fdoc_zip_'+doc_id).val());
				$('#'+context+' #city ').val($('#statecityzipcode_drop_table #fdoc_city_'+doc_id).val());
				break;
			
			case "PatMaAdd_Hausarzt":
      			$('#'+context+' #doc_zip ' ).val($('#statecityzipcode_drop_table #fdoc_zip_'+doc_id).val());
				$('#'+context+' #doc_city ').val($('#statecityzipcode_drop_table #fdoc_city_'+doc_id).val());
				break;

			case "patienthealthedit_edit_accordion":
				$('#'+context+' #zip_sub'+extraid ).val($('#statecityzipcode_drop_table #fdoc_zip_'+doc_id).val());
				$('#'+context+' #city_sub'+extraid ).val($('#statecityzipcode_drop_table #fdoc_city_'+doc_id).val());
				break;
    		
			case "FormDiv":
			case "addcontactPerson_fieldset":
			case "frmuser":
			case "":
				$('#'+context+' #cnt_zip' ).val($('#statecityzipcode_drop_table #fdoc_zip_'+doc_id).val());
				$('#'+context+' #cnt_city' ).val($('#statecityzipcode_drop_table #fdoc_city_'+doc_id).val());
				break
				
			default:
				$(' #zip ' ).val($('#statecityzipcode_drop_table #fdoc_zip_'+doc_id).val());
				$(' #city ').val($('#statecityzipcode_drop_table #fdoc_city_'+doc_id).val());
				break;
		}
	
	};

	

	

$(document).ready(function(){

	var livesearch_url = 'ajax/getzipcities';
	var livesearch_id = "livesearch_services";
	var noResults_Delay = 500;
	var type_Delay = 500;	
	var parent_div_id = "";
	
	/* patient/patientdetails */
	parent_div_id = "FormDiv";
	if($("#"+parent_div_id).length && (window.location.href.indexOf("patientnew/patientdetails") > -1)){
		$("#"+parent_div_id+' #cnt_zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #cnt_city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}	
	
	
	/* patient/patientedit */
	parent_div_id = "PatentEditDiv";
	if($("#"+parent_div_id).length){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}
	
	
	/* patient/patientmasteradd */
	if((window.location.href.indexOf("patient/patientmasteradd") > -1) || (window.location.href.indexOf("patient/patientmasteredit") > -1) ){

		parent_div_id = "PatMAddPatie_Stamm";	
		if($("#"+parent_div_id).length){
			$("#"+parent_div_id+' #zip')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			})
			.attr("pattern", "[0-9]*");
		
			$("#"+parent_div_id+' #city')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			});
			
		}
		
		
		parent_div_id = "PatMaAdd_Hausarzt";
		if($("#"+parent_div_id).length){
			$("#"+parent_div_id+' #doc_zip')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			})
			.attr("pattern", "[0-9]*");
		
			$("#"+parent_div_id+' #doc_city')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			});
			
		}
	
		return true;
	}
	
	/* patient/patientmasteradd */
	if((window.location.href.indexOf("contactpersonmaster/addcontactpersontemp") > -1)){
		parent_div_id = "addcontactPerson_fieldset";	
		if($("#"+parent_div_id).length){
			$("#"+parent_div_id+' #cnt_zip')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			})
			.attr("pattern", "[0-9]*");
		
			$("#"+parent_div_id+' #cnt_city')
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			});
			
		}
	
		return true;
	}
	
	
	/* patient/editcontactperson */
	parent_div_id = "frmuser";
	if($("#"+parent_div_id).length  && (window.location.href.indexOf("patient/editcontactperson") > -1)){
		$("#"+parent_div_id+' #cnt_zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #cnt_city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}
	
	/* patient/familydocedit*/
	/* patient/pharmacyedit */
	/* patient/pflegedienste */
	/* patient/voluntaryworkers */
	/* patient/physiotherapist */
	/* patient/homecares */
	/* patient/church */


	
	parent_div_id = "family_dr_edit";
	if($("#"+parent_div_id).length){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}
	
	
	
	
	/* patient/specialists */
	parent_div_id = "specialist_details";
	if($("#"+parent_div_id).length){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}
	
	/* patient/patienthealthedit */
	parent_div_id = "patienthealthedit_edit";
	if($("#"+parent_div_id).length){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}	
	
	
	/* patient/patienthealthedit */
	parent_div_id = "patienthealthedit_edit_accordion";
	if($("#"+parent_div_id).length){		
		$("#"+parent_div_id + " input[id^='zip_sub']").each(function( i, v){
			var this_id = $(this).attr("id");	
			var res = this_id.substr(7, this_id.length - 7 );

			$(this)
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&extraid='+res+'&mode=zipcode&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			})
			.attr("pattern", "[0-9]*");
			
		});		
		
		$("#"+parent_div_id + " input[id^='city_sub']").each(function( i, v){
			var this_id = $(this).attr("id");	
			var res = this_id.substr(8, this_id.length - 8 );

			$(this)
			.on('change', function() {})
			.liveSearch({
				url: livesearch_url+'?context='+parent_div_id+'&extraid='+res+'&mode=city&q=',
				id: livesearch_id,
				noResultsDelay: noResults_Delay,
				typeDelay: type_Delay
			});
			
		});
		return true;

	}	
	

	/* patient/suppliers */
	parent_div_id = "supplier_details";
	if($("#"+parent_div_id).length ){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		
		return true;
	}	
	
	
	/* voluntaryworkers page */
	/* voluntaryworkers/addvoluntaryworkerdet */
	/* voluntaryworkers/editvoluntaryworkerdet */
	parent_div_id = "tabs-2";
	if($("#"+parent_div_id).length && (window.location.href.indexOf("voluntaryworkers/addvoluntaryworkerdet") > -1 || window.location.href.indexOf("voluntaryworkers/editvoluntaryworkerdet") > -1)){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		
		return true;
		
	}	
	
	
	/* member page */
	/* member/addmember */
	/* member/editmember */
	parent_div_id = "tabs-1";
	if($("#"+parent_div_id).length && (window.location.href.indexOf("member/addmember") > -1 || window.location.href.indexOf("member/editmember") > -1)){
		$("#"+parent_div_id+' #zip')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=zipcode&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		})
		.attr("pattern", "[0-9]*");
	
		$("#"+parent_div_id+' #city')
		.on('change', function() {})
		.liveSearch({
			url: livesearch_url+'?context='+parent_div_id+'&mode=city&q=',
			id: livesearch_id,
			noResultsDelay: noResults_Delay,
			typeDelay: type_Delay
		});
		return true;
	}	
	
	
});//end jquery.document.ready

/*
 * since 11.2017
 * requires livesearch.js
 * example:
 * 	$(selector).livesearchCityZipcode(); -- if you use the default selectors table data-livesearch=zip & table data-livesearch=city
 * or
 * 	$(selector).livesearchFamilyDoctor({
 * 		'selectorInputAttr'		: 'data-livesearch', 
 * 		'selectorInputValues'	: ['zip1', 'zip2', 'city_one', 'city_two'],
 * 		'selectorParents'		: table,
 * 		callBackRowClicked : function(_target){console.log(arguments);}
 *  });
 * 
 * 
 * 
 */
(function($) {
	
	$.fn.livesearchCityZipcode = function(options) {
		
		var defaults = {
				//livesearch.js settings
				livesearch_url		: 'ajax/getzipcities',
				livesearch_id		: "livesearch_zipcities",
				livesearch_class	: 'livesearch_unified_style',
				noResults_Delay		: 500,
				aditionalWidth		: 300,
				type_Delay			: 500,
				//my settings
				callBackRowClicked	: function () {},// row clicked callback function
				selectorContext		: this, //this.selector replaces parent_class, parent_div_id,
				selectorParents		: 'table',
				selectorInputAttr	: 'data-livesearch',
				selectorInputValues	: ['zip', 'city'] ,
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
			
			$('input[data-livesearch="zip"]', _inputsOwner ).val($('input[id^="fdoc_zip_"]', _clickedTR).val());
			$('input[data-livesearch="city"]', _inputsOwner ).val($('input[id^="fdoc_city_"]', _clickedTR).val());
			
		};
		

		
		
		
		_opts.init = function(options) {			
			//overwrite
			window.selectCityZipcode = function (){
//				console.log('selectCityZipcode is disabled');
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
