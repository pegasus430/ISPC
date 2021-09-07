;
/**
 * cloned for ISPC-26-25, AOK Kurzasssessment, 07.07.2020
 * assessment.js
 * mambo
 * @date 04.12.2018
 * @author @cla
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

var feedbackJS = {}; //this hold what buttons are available for each block


var formular_button_action = null;
var submited = false;
var changes = false;
var dontLeave = function(e) {
	if (changes === true && submited === false) {
		return translate('no_save_leave_alert');
	}
};
window.onbeforeunload = dontLeave;

var _block_name = "AokprojectsKurzassessment";

function form_submit_validate(){
	
	submited = false;

	/*
	 * removed per request from ISPC-2292 14.02.2019 3)
	 * 
	canvas_save();
	*/
	
	if ($('.formular_time_start').val() == '' || $('.formular_time_end').val() == '') {
		
		setTimeout(function(){alert(translate('Please fill in start-end time'));}, 200);
		
		return false;
		
	} else if( ! checkclientchanged('wlassessment_form')) {
		
		
		return false;
		
	} else {
				
		if (formular_button_action == 'print_pdf' ) {			
			
			$('body').addClass('body-overlay');
			
			setTimeout(function(){
				location.href = appbase +"patientcourse/patientcourse?id=" +idpd;
			}, 1500);
		}
		
		submited = true;
		changes = false;
		
		return true;
	}
	
}

var getmedicationblocksWasLoaded =  false;
$.getmedicationblocks = function() {
	
	if (getmedicationblocksWasLoaded) return;
	 
	var url = appbase + 'patientnew/medicationeditblocks?id=' + idpd;
	
	//show a loading gif
	$('.Medications2_holder_div').html('<br/><div class="loadingdiv" align="center" style="display:block;">'
			+'<img src="' + appbase + 'images/ajax-loader.gif"><br />' 
			+ translate('loadingpleasewait') 
			+ '</div><br/>'
	);
	
	xhr = $.ajax({
		url : url,
		cache : false,
		success : function(response) {
			$('.Medications2_holder_div').html(response);
			medicationeditblocks_ready();
		}
	});
};


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

$(document).ready(
	function() {
		
		var _assessment_id_Editmode = $('#_page_1-__formular-id').val() || 0;
		$('#MainContent_resultMessages').checkFormularEditmode({'ajax':{'data' : {'pathname' : 'mambo/assessment', 'search' : 'assessment_id=' + _assessment_id_Editmode }}});

//		
//		$.extend( $.ui.accordion, {
//			_toggle : function (){
//				console.log("ssssss");
//				this._super();
//			},
//		});
//		
//		
		
		
		$('input:checkbox, input:radio, select').on('change',function() {
			submited = false;
			changes = true;
		});
		
		$('input:text, textarea').on('focusout',function() {
			submited = false;
			changes = true;
		});
		
		
		

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
//				maxDate: '0',
				beforeShow : function(e, o) {
					if($(e).hasClass('allow_future')) {
						$(e).datepicker("option", "maxDate", "+1y");
					} else {
						$(e).datepicker("option", "maxDate", 0);
					}
				},
				beforeShowDay : isDateWithinRanges,
//			
			
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
					if (_start != '' &&
						$(this).hasClass(
							'formular_time_start')) { // calculate
						// formular
						// end
						// time
						var _end = $('.formular_time_end',
							_parent).val();
//						if (_end == '' ||
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
		
		//set formular time to now
		var dateObj = new Date();
		if ($('.formular_time_start').val() == '' && $('.formular_time_end').val() == '') {
			var _startT = dateObj.getHours() + ":" + dateObj.getMinutes();
			var _endT = HHmmAddMinutes(_startT, 45);
			
			$('.formular_time_start').val(_startT);
			$('.formular_time_end').val(_endT);
		}
		if ($('.formular_date').val() == '') {
			$('.formular_date').val(dateObj.getDate() + '.' + (dateObj.getMonth() + 1) + '.' + dateObj.getFullYear()) ;
		} 
		

		$(".contact_person_accordion, .acp_accordion, .specialists_accordion, .patient_pflegedienst_accordion").accordion({
			active: false,
			collapsible: true,
			autoHeight: true,
			header: '> fieldset > legend',
			
//			create : function (event, ui ){
//			},
//			
//			changestart : function (event, ui ){				
//			},
//			
			change :function (event, ui ){
				feedbackQtipReposition($(this).parents('.mamboTabsPageBody'));
			},
		});
		
		
		
		$('.qq_file_uploader_placeholder').each(function(){
			uploader_create(this, ['pdf','docx','doc']);
		});
		
		
		try {
			
			
			$('.livesearchFormEvents')
			.livesearchCityZipcode({'limitSearchResults' : 50})
			.livesearchFamilyDoctor({'limitSearchResults' : 50})
			.livesearchHealthInsurance({'limitSearchResults' : 50})
			.livesearchDiacgnosisIcd({'limitSearchResults' : 50})
			.livesearchSpecialist({'limitSearchResults' : 50})
			.livesearchCareservice({'limitSearchResults' : 50})
//			.livesearchSupplies({'limitSearchResults' : 50})
//			.livesearchPharmacy({'limitSearchResults' : 50})
//			.livesearchSuppliers({'limitSearchResults' : 50})
//			.livesearchHomecare({'limitSearchResults' : 50})
//			.livesearchPhysiotherapists({'limitSearchResults' : 50})
//			.livesearchVoluntaryworkers({'limitSearchResults' : 50})
//			.livesearchChurches({'limitSearchResults' : 50})
//			.livesearchHospiceassociation({'limitSearchResults' : 50})
//			.livesearchRemedySupplies({'limitSearchResults' : 50})
//			.livesearchRemedyAid({'limitSearchResults' : 50})
			.livesearchUnifiedProvider({
				'selectorParents'		: 'tr',
				'limitSearchResults'	: 50, 
				'limitSearchGroups'		: ['user', 'voluntaryworker'],
			})
//			.livesearchSapvVerordner({'limitSearchResults' : 50})
			;
						
		} catch (e) {
			if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
				console.log(e);
			}
		}
		
		
		try {
			$("input:text.autocomplete").each(function() {
				var _arr = $(this).data('autocomplete_source') || null;
				if (_arr && typeof(_arr) ==='object') {
					$(this).autocomplete({
						source: _arr
					});
				}
			});
		} catch (e) {
			if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
				console.log(e);
			}
		}
		
		
		/*
		 *ajax fetch medication page 
		 */
		$.getmedicationblocks();
		
		
		

//		var _active_tab = null; 
//		
//		if(window.location.hash) {
//			var _hash = window.location.hash.substring(1);		
//			var _tabs = $(".mamboTabsPage").find("> fieldset").each(function(i){
//				if ( $(this).attr('id') == _hash) {
//					_active_tab = i;
//					return false;
//				}
//			});
//		}
		
		
		
		
		
		var _selectedTab = findGetParameter('page') || $('.assessment_current_page').val();
		
		$( "#wlassessment_form" ).tabs({
			
			selected : _selectedTab,//findGetParameter('page'),
			
			show: function(event, ui) {
				
				/*
				 * add the yellow thing on the right
				 */
				$.map($(".has_feedback_options", $(ui.panel)), function(i) {
					feedbackQtipCreator(i);
				});
				
				feedbackQtipReposition(ui.panel);
				
				/*
				 * removed per request from ISPC-2292 14.02.2019 3)
				 * 
				if(ui.index == 2) { //page3 tab
					//Render after tabs have been shown.
					$('.human_canvas_holder').createHumanBodyCanvas({
						width:544, 
						height:397,
						oldImage: $('.old_human_canvas_holder').val() 
					});
				
					//$.getmedicationblocks();
				}
				*/
				
				$('.assessment_current_page').val(ui.index);
				
				
				$("#fakeTabsOnBottom")
				.find('li')
					.removeClass('ui-tabs-selected ui-state-active')
					.eq(ui.index).addClass('ui-tabs-selected ui-state-active')
				;
				
			},
			
			select: function(event, ui) {
				
			},
			
			create: function( event, ui ) {
				
				/*
				 * add navigation tabs also at the bottom
				 */
				createFakeTabsOnBottom();
				
			},
			
		});
		
});







function createFakeTabsOnBottom ()
{
	var _clone = $(".tabs_gray_class").clone(true);
	_clone
	.css({'padding' : '0 0 0.2em 0.4em'})
	.find('li').removeClass('ui-corner-top').addClass('ui-corner-bottom') //change corners for the tabs
	;
	_clone.find("a")
	.unbind("click.tabs")
	.bind("click.fakeTabsOnBottomClick", fakeTabsOnBottomClick)
	;
	
	$("#fakeTabsOnBottom").append(_clone);
}


function fakeTabsOnBottomClick (event)
{
	event.preventDefault();
	event.stopPropagation();
	
	var el = this,
	$li = $(el).closest( "li" );

	if ($li.hasClass( "ui-tabs-selected" ) 
			|| $li.hasClass( "ui-state-disabled" ) 
			|| $li.hasClass( "ui-state-processing" ))
	{
		return false;
	}
	
	$("#wlassessment_form").tabs("option", "selected", $li.index());

	window.location.hash = "#wlassessment_form";
	
	return false;
}





(function($) {
	$.fn.assertKeydownNumber = function(e) {
		
		try {
			// Allow: backspace, delete, tab, escape, enter, dot, comma 
			if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 188]) !== -1 ||
					// Allow: Ctrl+A
					(e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
					// Allow: Ctrl+C
					(e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
					// Allow: Ctrl+X
					(e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
					// Allow: home, end, left, right
					(e.keyCode >= 35 && e.keyCode <= 39) ||
					//Allow numbers 
					((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105))) {
				// let it happen, don't do anything
				return true;
			}
			// Ensure that it is a number and stop the keypress
			if ((!e.shiftKey && (e.keyCode < 48 || e.keyCode > 57)) || (e.keyCode < 96 || e.keyCode > 105)) {
//				e.preventDefault();
				return false;
			}
		} catch (e) {
			return true;
		}
	};	
})(jQuery);

(function($) {
	$.fn.validate0to10 = function(options) {
		try {
			var val = parseInt($(this).val(), 10);
			if (isNaN(val) || Number(val) < 0 || Number(val)>10) {
				val = '';
			}
			$(this).val(val);
			
		} catch (e) {
			return true;
		}
	};	
})(jQuery);		



function canvas_save(){

	$('.advanced_human_body_canvas').each(function() {
		//current canvas
		var canvas = $(this)[0];
		var image_data = '';
		
		//question id
		var qid = $(this).attr('id');

		//data container
		var answer_container = 'input[name=\'canvas_container\['+qid+'\]\']';


		image_data = canvas.toDataURL("image/png");
		if(image_data.length < 16 && image_data.length > 0) {
			// bad Android
			var canvasWidth = canvas.width;
			var canvasHeight = canvas.height;
			var start = 0;
			var pixelarray = '';
			var canvastr = 'cmd=6104536&data=';
			var c = canvas.getContext("2d");

			// get all canvas pixel data
			imageData = c.getImageData(0, 0, canvasWidth, canvasHeight);
			pixelarray = imageData.data;
			for(var y = 0; y < canvasHeight; ++y) {
				for(var x = 0; x < canvasWidth; ++x) {
					var index = (y * canvasWidth + x) * 4; // red channel, we don't need the rest

					if(pixelarray[index] > 0) {
						canvastr += start + ',' + x + ',' + y + ',' + pixelarray[index] + '|';
						start++;
					}
				}
			}

			if(canvastr.length > 0) {
				$.ajax({
					type: "POST",
					async: false,
					cache: false,
					data: canvastr,
					url: '<?php echo APP_BASE; ?>array2png.php',
					success: function(response) {
						image_data = response;
					}
				});
			}
		}

		//fill data container for server side transport & processing
		$(answer_container).val(image_data);
	});	
	
	
	//console.log(canvas.toDataURL('image/png'));
}



function artificial_exits_changed(evt) {
	if (evt.target.value == "5" && evt.target.checked) {
		$("input[name*='freetext']", $(evt.target).parents('fieldset')).show();
	} else if (evt.target.value == "5" && !evt.target.checked) {
		$("input[name*='freetext']", $(evt.target).parents('fieldset')).hide()
			.val('');
	}
}


function Contact_Person_addnew (_target, _parent_form) {
	
	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
	selected_page++;
	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
	
	$.get(appbase + 'ajax/createformcontactperson?parent_form='+parent_form 
			+ '&_block_name=' + _block_name 
			+ '&id=' + window.idpd, 
			
			function(result) {

				var newFieldset = $(result).insertBefore($(_target).parent('div'));
				
				var _delClicker = jQuery('<div class="delete_row" style="float:right"></div>')
				.appendTo($('legend', newFieldset));
				_delClicker.click(function(event){
					$(this).parent('legend').parent('fieldset').remove();
					return false;
				});
				
				$(newFieldset).livesearchCityZipcode();
				
//				feedbackQtipCreator(newFieldset);
				
				//@tody why isn;t working?
				//$(".contact_person_accordion").accordion( "refresh" ); 
		
				$(".contact_person_accordion").accordion( "destroy" );
				$(".contact_person_accordion").accordion({
					active: false,
					collapsible: true,
					autoHeight: true,
					header: '> fieldset > legend',
					create: function( event, ui ) {
						
						index = $("fieldset", event.target).length || 0;
						$(this).accordion({active:index-1});
						
					},
					
					change :function (event, ui ){
						feedbackQtipReposition($(this).parents('.mamboTabsPageBody'));
					},
			});
		
	});
	
}
function Patient_Specialist_addnew (_target, parent_form) {
	
	$.get(appbase + 'ajax/createformpatientspcialist?parent_form='+parent_form
			+ '&_block_name=' + _block_name,
			function(result) {

				var newFieldset = $(result).insertBefore($(_target).parent('div'));
				
				var _delClicker = jQuery('<div class="delete_row" style="float:right"></div>')
				.appendTo($('legend', newFieldset));
				_delClicker.click(function(event){
					$(this).parent('legend').parent('fieldset').remove();
					return false;
				});
				
				$(newFieldset).livesearchSpecialist();
				$(newFieldset).livesearchCityZipcode();
				
		
				//@todo why isn;t working?
				//$(".contact_person_accordion").accordion( "refresh" ); 
				$(".specialists_accordion").accordion( "destroy" );
				$(".specialists_accordion").accordion({
					active: false,
					collapsible: true,
					autoHeight: true,
					header: '> fieldset > legend',
					create: function( event, ui ) {
						
						index = $("fieldset", event.target).length || 0;
						$(this).accordion({active:index-1});
						
					}
				});	
	});
	
}

function PatientPflegedienst_addnew (_target, _parent_form) {
	
	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
	selected_page++;
	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
	
	$.get(appbase + 'ajax/createformpatientpflegedienst'
			+ '?parent_form=' + parent_form
			+ '&_block_name=' + _block_name, 
			function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parent('div'));
		
		var _delClicker = jQuery('<div class="delete_row" style="float:right"></div>')
		.appendTo($('legend', newFieldset));
		_delClicker.click(function(event){
			$(this).parent('legend').parent('fieldset').remove();
			return false;
		});
		
		
		$(newFieldset).livesearchCareservice();
		$(newFieldset).livesearchCityZipcode();

//		feedbackQtipCreator(newFieldset);

		
		//@todo why isn;t working?
		//$(".contact_person_accordion").accordion( "refresh" ); 
		$(".patient_pflegedienst_accordion").accordion( "destroy" );
		$(".patient_pflegedienst_accordion").accordion({
			active: false,
			collapsible: true,
			autoHeight: true,
			header: '> fieldset > legend',
			create: function( event, ui ) {
				
				index = $("fieldset", event.target).length || 0;
				$(this).accordion({active:index-1});
				
			},
			change :function (event, ui ){
				feedbackQtipReposition($(this).parents('.mamboTabsPageBody'));
			},
		});
	});
}

function PatientDiagnosis_addnew (_target, _parent_form) {
	
	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
	selected_page++;
	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
	
	$.get(appbase + 'ajax/createformdiagnosisrow?parent_form='+parent_form + '&_block_name=' + _block_name, function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parents('tr'));
		
//		$(newFieldset).livesearchDiacgnosisIcd({'selectorParents': ''});
		$('.livesearchFormEvents')
		.livesearchDiacgnosisIcd({'limitSearchResults' : 50})
		.livesearchUnifiedProvider({
			'selectorParents'		: 'tr',
			'limitSearchResults'	: 50, 
			'limitSearchGroups'		: ['user', 'voluntaryworker'],
		})
		;

	});
}
function PatientRegularChecks_hospitalizations_addnew (_target, _parent_form) {
	
	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
	selected_page++;
	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
	
	
	
	$.get(appbase + 'ajax/createformhospitalizationsrow?'
			+ 'parent_form=' + parent_form 
			+ '&_block_name=' + _block_name, 
			
			function(result) {		
				var newFieldset =  $(result).insertBefore($(_target).parents('tr'));
	});
}





//extensions = array[];
function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
	//defaults
	var _max_filesize = 102400000;
	var _allowed_extensions = ['pdf','docx'];
	var _multiple_files = false;
	
	if ( ! $.isNumeric(max_filesize) ) {
		max_filesize = _max_filesize;
	}
	if ( ! $.isArray(allowed_extensions) ) {
		allowed_extensions = _allowed_extensions;
	}
	if ( ! typeof multiple_files === "boolean"  ) {
		multiple_files = _multiple_files;
	}
	
	var holderElement, tabname, action_name;
	
	if (typeof holderId === 'object') {
		holderElement = holderId;
	} else {
		holderElement = document.getElementById(holderId);
	}
	
	
	if (holderElement == null) {
		return;//holderId not found
	}
	
	qq_uploader = new qq.FineUploader({
		debug: false,
		multiple : multiple_files,
		element: holderElement,
		template: 'qq-template',
		
		request: {
			customHeaders: {},
			endpoint: appbase+'patient/fileupload',
			filenameParam: "qqfilename",
			forceMultipart: true,
			inputName: "qqfile",
			method: "POST",
			params: {
				//params are overwriten on submit
				'action'	: 'upload_file_attachment',
				'id'		: window.idpd,
				'tabname'	: holderId,
				'action_name': holderId,
				'date'		: function() {
					return new Date();
				},
				'multiple'	: multiple_files,
				'file_date' : '',
				'upload_and_save' : false,
			},
			paramsInBody: true,
			totalFileSizeName: "qqtotalfilesize",
			uuidName: "qquuid",
		},
		
		
		deleteFile: {            
			enabled: true, // defaults to false
			method: "POST",
			endpoint: appbase+'patient/fileupload',
			customHeaders: {},
			params: {
				'action':'delete',
				'date': new Date()
			},
		},
		    
		retry: {
			enableAuto: false
		},
		
		validation: {
			allowedExtensions: allowed_extensions,
			sizeLimit: max_filesize
		},
		
		messages: {
			typeError: translate('FineUploader_lang')['typeError'],
			sizeError: translate("FineUploader_lang")["sizeError"],
			minSizeError: translate("FineUploader_lang")["minSizeError"],
			emptyError: translate("FineUploader_lang")["emptyError"],
			noFilesError: translate("FineUploader_lang")["noFilesError"],
			tooManyItemsError: translate("FineUploader_lang")["tooManyItemsError"],
			maxHeightImageError: translate("FineUploader_lang")["maxHeightImageError"],
			maxWidthImageError: translate("FineUploader_lang")["maxWidthImageError"],
			minHeightImageError: translate("FineUploader_lang")["minHeightImageError"],			
			minWidthImageError: translate("FineUploader_lang")["minWidthImageError"],
			retryFailTooManyItems: translate("FineUploader_lang")["retryFailTooManyItems"],
			onLeave: translate("FineUploader_lang")["onLeave"],
			unsupportedBrowserIos8Safari: translate("FineUploader_lang")["unsupportedBrowserIos8Safari"],

		},
		
		callbacks: {
			
	
			onSubmit: function(id, name) {
				
				var el = this._options.element;
				
				var parent = $(el).closest($(el).data('parent'));
				
				var file_date = $('.date', parent).val();
				
				if( ! file_date) {

					//cancel upload
					setTimeout(function () {
						alert(translate('acp_box_lang')["Please first select file date"]);
						},50);
					this.cancelAll();
					this.clearStoredFiles();
					
					return false;
					
				} else {
					//setParams
					tabname =  $(el).data('tabname');
					action_name = tabname;
					
					var params = {
						'action'	: 'upload_file_attachment',
						'id'		: window.idpd,
						'tabname'	: tabname,
						'action_name'	: action_name,
						'date'		: function() {
							return new Date();
						},
						'multiple'	: multiple_files,
						'file_date'	: file_date,
						'upload_and_save' : false
					};
		            this.setParams(params, id);
		            return true;
				}
			},
			
			onComplete: function(id, fileName, responseJSON){	
								
				if (responseJSON.success == true){
					//update			
//					
//					console.log($('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)));
//					console.log(this.getUuid(id));
					
					$('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
				}
								
				if (responseJSON.redirect == true){
					if (typeof responseJSON.redirect_location != 'undefined') {
						
					} else {
//						window.location.reload();//redirect to self
					}
					
				}
				
			},
		}
		
	});
	
	return qq_uploader;
}


function reCreateTimebasedValues(_date) {
	
//	
//	var _parent_form_begleitung = "_page_1[PatientDgpKern]",
//	_parent_form_ecog = "_page_4[PatientDgpKern]";
//	
	var _target = "";
	var _messages = [];
	$.post(
			appbase + 'ajax/createformDgpKern', 
			{
				'id': window.idpd, 
				'form_type': 'adm',
				'form_blocks' : ['partners', 'ecog', 'vitalsigns'],
				'belongsTo' : {'partners': '_page_1[PatientDgpKern]', 'ecog' : '_page_3[PatientDgpKern]', 'vitalsigns' : '_page_3[PatientFeedbackVitalSigns]' },
				'date' : _date 
			}, 
			function(result) {
				if ('success' in result && result.success == true && 'form_blocks' in result) {
					var _tr = translate('Form_PatientDgpKern');
					
					if ('partners' in result.form_blocks) {
//						$("fieldset#fieldset-PatientDgpKern.create_form_partners").remove().replaceWith(result.form_blocks.partners);
						$("fieldset#fieldset-PatientDgpKern.create_form_partners").replaceWith(result.form_blocks.partners);
						
						_messages.push(translate("Block") + " `"+ _tr["Description of the current or immediately planned supply:"] + "` "+ translate("was updated"));
					}
					
					if ('ecog' in result.form_blocks) {
						$("fieldset#fieldset-PatientDgpKern.create_form_ecog").replaceWith(result.form_blocks.ecog);				
						_messages.push(translate("Block") + " `"+ _tr["ECOG:"] + "` "+ translate("was updated"));

					}
							
					_messages = '<ul id="messages_dgp" class="success"><li>' + _messages.join('</li><li>')  + '</li></ul>';
					$(_messages).insertBefore("#wlassessment_form");
					
					setTimeout(function(){$('#messages_dgp').fadeOut(300, function(){ $(this).remove();});}, 10000);
					
				} else {
					//what failed?
					//console.log(result);
				}
			},
			'json'
	);
}






function feedbackQtipCreator(_that) 
{
	var _feedback_options = $(_that).data('feedback_options') || false,
	_qtip ;
	
	if (! _feedback_options 
		|| typeof(_feedback_options) !== 'object' 
		|| ! _feedback_options.hasOwnProperty("__block_name") 
		|| ! _feedback_options.hasOwnProperty("__parent") 
		|| ! _feedback_options.hasOwnProperty("__fnName") 
		|| ! _feedback_options.hasOwnProperty("__meta")) 
	{
		return; //continue to next one.. this is not good
	}
	
	
	if($(_that).data('hasqtip') != undefined) {
//		just show and return
		$(_that).qtip('show');
		return;
	}
	
	var _inputs = [];
	_inputs = $.map(_feedback_options.__meta, function (checked, val){
		
		var __checked = (checked == 'yes') ? 'checked' : '';
		__displayText = '';
		
		if (checked == 'yes' && _feedback_options.hasOwnProperty("__meta_val") && _feedback_options.__meta_val.hasOwnProperty(val)) {
			
			if (_feedback_options.__meta_val[val]['date'].length) {
				__displayText +=  _feedback_options.__meta_val[val]['date'];
			}
			
			if (_feedback_options.__meta_val[val]['user'].length) {
				var __userVALTEXT = $.map(_feedback_options.__meta_val[val]['user'].split(","), function(item) {
					var __txt = $("#todo_selectbox option[value='"+item+"']").text() || item;
					return __txt;
				}).join(', ');
				__displayText += " : " + __userVALTEXT; //_feedback_options.__meta_val[val]['user'];
			}
					
			if (__displayText.length) __displayText += "<br>";
			
			if (_feedback_options.__meta_val[val]['freetext'].length) {
				__displayText += nl2br(_feedback_options.__meta_val[val]['freetext']);
			}
		}
		
		
		
		return "<div class='selector_feedback_options_option' data-type='"+val+"'>\
					<label>\
						<input type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+val+"]' value='no' > \
						<input " + __checked + " type='checkbox' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+val+"]' value='yes' onclick='feedbackQtipOptionChange(this)'> \
						<b>" + translate('feedback_options')[val] + "</b>\
					</label>\
					<br><span class='selector_feedback_options_option_text' onclick='feedbackQtipOptionDialog($(this).parent());' title='"+translate('Click to edit')+"'>\
						" + __displayText + "\
					</span>\
					<span>"
						+ "<input type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+ val +"_val][type]' value='"+ val +"' >"
						+ "<textarea data-type='freetext' style='display:none' name='feedback_options[" + _feedback_options.__block_name + "][" + _feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+ val +"_val][freetext]'>" + (_feedback_options.hasOwnProperty("__meta_val") && _feedback_options.__meta_val.hasOwnProperty(val)  && _feedback_options.__meta_val[val] !== null && typeof _feedback_options.__meta_val[val] === 'object' && _feedback_options.__meta_val[val].hasOwnProperty('freetext') ? _feedback_options.__meta_val[val]['freetext'] : '' ) + "</textarea>"				
						+ "<input data-type='userstodo' type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+ val +"_val][user]' value='" + (_feedback_options.hasOwnProperty("__meta_val") && _feedback_options.__meta_val.hasOwnProperty(val) && _feedback_options.__meta_val[val] !== null && typeof _feedback_options.__meta_val[val] === 'object' && _feedback_options.__meta_val[val].hasOwnProperty('user') ? _feedback_options.__meta_val[val]['user'] : '' ) + "' >"
						+ "<input data-type='date' type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+ val +"_val][date]' value='" + (_feedback_options.hasOwnProperty("__meta_val") && _feedback_options.__meta_val.hasOwnProperty(val) && _feedback_options.__meta_val[val] !== null && typeof _feedback_options.__meta_val[val] === 'object' && _feedback_options.__meta_val[val].hasOwnProperty('date') ? _feedback_options.__meta_val[val]['date'] : '' ) + "' >"
						+ "<input data-type='sogood' type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"]["+ val +"_val][sogood]' value='0' >"
					+"</span>"
				+ "</div>";
	});
	
    if (_feedback_options.hasOwnProperty("__parentID")) {
    	_inputs.push("<input type='hidden' name='feedback_options["+_feedback_options.__block_name+"]["+_feedback_options.__parent+"]["+_feedback_options.__fnName+"][__parentID]' value='"+_feedback_options.__parentID+"'>");
    } 
     
	_inputs = _inputs.join("\n");
	
	_qtip = $(_that).qtip({
	      
         content: {   
        	 title: false,
             text: function() {
            	 return _inputs;
             }
         },
         show: { 
        	 solo: false, 
        	 event: false,
        	 ready: true,
    	 },
    	 
         hide: false,
         
         'style': { 
             'classes': 'label_same_size_auto position_fixed',
             tip: false,   
         },
         
         position: {
             at: 'top right', // at the bottom right of...
             container: $(_that),
             adjust: {
//                 x: -150,
                 y: 7
             }
         },
         /*
         prerender: true,
         events: {
        	 render: function(event, api) {
            	 switch ($(_that).prop("tagName")) {
	            	 case "FIELDSET":
	            		 $(this).prependTo($(_that));
	            		 break;
	            	 case "TR":
	            		 
	            		 var _colspan = getColumnCount($(_that).parents('table')) || 1;
	            		 var _x = $("<tr>").append($("<td>", {"colspan" : _colspan}).append($(this)));
	            		 $(_x).insertBefore(_that);
	            		 
	            		 break;
            	 }
               
//               $(_that).qtip('api').reposition();
             },
             show : function (){
//            	 $(_that).qtip('reposition');
             }
         
         },
         */
         
     })
     ;
	
	//console.log(_qtip);
	
	return _qtip;
	
}

function feedbackQtipReposition(_parent) {	
	
	setTimeout(function(){
		$.map($(".has_feedback_options", $(_parent)), function(_i) {
			if($(_i).data('hasqtip') != undefined) {
				$(_i).qtip('api').reposition();
			}
		});
	}, 100);
}

function feedbackQtipToggle(){
	$.map($(".has_feedback_options"), function(_i) {
		if($(_i).data('hasqtip') != undefined) {
			$(_i).qtip('toggle');
		}
	});
}

function getColumnCount(e) { //Expects jQuery table object
    var c= 0;
    e.find('tbody tr:first td').map(function(i,o) { c += ( $(o).attr('colspan') === undefined ? 1 : parseInt($(o).attr('colspan')) ); } );
    return c;
}


function feedbackQtipOptionChange(_that) //_that is the clicked CB
{ 

	var _checked =  $(_that).is(":checked") ? true : false;
	
	var _optionRow = $(_that).parents('.selector_feedback_options_option').get(0);
	
	var _usertext = $('.selector_feedback_options_option_text', _optionRow);
	
	_usertext.toggle(_checked);
	
	if ( _checked) {
		//do dialog
		feedbackQtipOptionDialog(_optionRow);
	} else {
		//_usertext.html('');
	}

}


function feedbackQtipOptionDialog(_that) //_that is the _optionRow
{
	
	var __dialogType = $(_that).data('type'),
	__dialogTitle = '',
	__dialogEl = $('<div>', {
		'class'	: 'feedback-form-group',
		'css' : {},
		'html' : [
		     	   $("<label>" , {
		     		   html : [
		     		           $('<span>', {'text' : translate('add the text')}),
			     	   
					     	   $('<textarea>', {
					     		   'data-type' : 'freetext',
					     		   'placeholder' : translate('Feedback comments'),
					     		   'rows' : 3,
			     			   }),
			     			   
					     	   $('<span>', { 
					     		   'class' : 'icon-plus', 
					     		   'title' : translate('add text from list of sentence blocks'),
					     		   'click' : function (event) {forms_texts_search(event, __dialogType);}
					     	   }),
					     	   
					     	   $('<input>', {
					     		   'data-type' : 'sogood',
					     		   'type' : 'checkbox',
					     		   'value' : 1,
					     		   'class' : 'icon-sogood', 
					     		   'title' : translate('text is so good, save for later usage'),
					     	   })
		     	   ]})
		     	  ]
	});
	
	
	
	
	switch (__dialogType) 
	{
	
		case "feedback":
			
			__dialogTitle = 'Add/edit Feedback details';
			
		break;
	
		case "todo":
			
			__dialogTitle = 'Add/edit TODO details';
			
			__dialogEl.append(
					
					$("<label>" , {
	     				   'html': [
	     				            $('<span>', {'text' : translate('select the user')}),
	     				            $("#todo_selectbox").clone(true).removeAttr('id').data('type', 'userstodo'),
                    ]}),
                    
                    $("<label>" , {
						   'html': [
						            $('<span>', {'text' : translate('add the date')}),
					                           
									$('<input>', {
										'data-type' : 'date',
										'type' : 'text', 
									    'class' :  'date',
									 })
					 ]})
			);
			
		break;
		
	}
	
	__dialogEl
	.appendTo('body')
	.dialog({
        autoOpen: true,
        modal: true,
        zIndex: 16000, //15k is qTip
        title : translate(__dialogTitle),

        width: '500',
        height: __dialogType == 'todo' ? '500' : 'auto',
        autoResize:true,
        resize: 'auto',
        
        open : function() {
        	
			$("textarea[data-type='freetext']", this).val($("textarea[data-type='freetext']", _that).val());
			$("select[data-type='userstodo']", this).val($("input[data-type='userstodo']", _that).val().split(","));
			$("input[data-type='date']", this).val($("input[data-type='date']", _that).val() || moment().format('DD.MM.YYYY'));
			$("input[data-type='sogood']", this).attr("checked" , ($("input[data-type='sogood']", _that).val() == 1 ? true : false));
        	
        	$('.date', this)
			.datepicker({
				dateFormat: 'dd.mm.yy',
				showOn: "both",
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: '',
				maxDate: '+2y',
				minDate: '-1y',
			});
        	
        	
        	$('.todo_selectbox', this).chosen({
				placeholder_text_single: translate('please select'),
				placeholder_text_multiple : translate('please select'),
				multiple:1,
				width:'250px',
				style: "padding-top:10px",
				"search_contains": true,
				no_results_text: translate('noresultfound')
			});
        	
        },
        
        beforeClose : function() {},
        
        close : function() {
	        //clear and destroy
	        $(this).dialog('destroy').remove();
        },
	
		buttons : [
					//save button
					{
						'class' : "rightButton",
						text : translate('save'),
						click : function() {
							
							var __displayText = '',
							__textareaVAL = $("textarea[data-type='freetext']", this).val() || '',
							__userVAL =  $("select[data-type='userstodo']  option:selected", this).toArray().map(function(item) {return item.value;}).join(',') || '',
							__userVALTEXT = $("select[data-type='userstodo']  option:selected", this).toArray().map(function(item) {return item.text;}).join(', ') || '',
							__date = $("input[data-type='date']", this).val() || '',
							__sogood = $("input[data-type='sogood']", this).is(":checked") ? 1 : 0;
							
							/*
							 * set hidden inputs that will be saved later
							 */
							$("textarea[data-type='freetext']", _that).val(__textareaVAL);
							$("input[data-type='userstodo']", _that).val(__userVAL);
							$("input[data-type='date']", _that).val(__date);
							$("input[data-type='sogood']", _that).val(__sogood);
							
							/*
							 * display to user 
							 */
							if (__date.length) {
								__displayText +=  __date;
							}
							if (__userVALTEXT.length) {
								__displayText += " : " + __userVALTEXT;
							}
							if (__textareaVAL.length) {
								__displayText += "<br>" + nl2br(__textareaVAL);
							}
							$('.selector_feedback_options_option_text', _that).html(__displayText);
							
							
							$(this).dialog("close");
								
						}
					},
					
					//cancel button
					{
						'class' : "rightButton",
						text : translate('cancel'),
						click : function() {
							$(this).dialog("close");
						},
						
					}
					
		]
				
	
	
	
    });
}




function nl2br (str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}





var forms_texts_search_xhr = false;

function forms_texts_search(event , __dialogType) 
{	
	var __field_name = __dialogType,
	__form_name = null, //'mambo/assessment'
	__url = appbase + 'ajax/formstexts';
	
	
	if (window.forms_texts_search_xhr !== false) {
		window.forms_texts_search_xhr.abort();
	}
	
	window.forms_texts_search_xhr = $.ajax({
		url: __url,
		data: {
			field_name: __field_name,
			form_name: __form_name
		},
		
		success: function(response) {
			forms_texts_dialog(response, event);
		}
	});
};


function forms_texts_dialog(htmlContent, event) {

	return $('<div>', {
		'class'	: 'feedback-form-group',
		'css'	: {},
		'html'	: htmlContent
	})
	.appendTo('body')
	.dialog({
		autoOpen: true,
		resizable: true,
		autoResize:true,
		height: 500,
		width: 1000,
		scroll: true,
		modal: true,
        zIndex: 16000, //15k is qtip
        title : translate("Please select previously saved text"),
	      
        close : function() {
	        //clear and destroy
	        $(this).dialog('destroy').remove();
        },
		
		buttons: [{
			
			text: translate('cancel'),
			
			click: function() {
				$(this).dialog("close");
			}
		},
		{
			text: translate('save'),
			
			click: function() {
				
				var __txt = $("textarea[data-type='freetext']", $(event.target).parent()).val();
				
				if (__txt.length) __txt += "\n";
				
				__txt +=  $("input[type='checkbox']:checked", this).toArray().map(function(item) {return item.value;}).join('\n') || '';

				$("textarea[data-type='freetext']", $(event.target).parent()).val(__txt);
				
				$(this).dialog("close");
			}
		}
	]
	
	});
}



