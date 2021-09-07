/* 16.11.2017 wlassessment.js */
var formular_button_action = null;
var submited = false;
var changes = false;
var dontLeave = function(e) {
	if (changes === true && submited === false) {
		return translate('no_save_leave_alert');
	}
}
window.onbeforeunload = dontLeave;



function form_submit_validate(){
	
	submited = false;
	
	canvas_save();
		
	if ($('.formular_time_start').val() == '' || $('.formular_time_end').val() == '') {
		
		setTimeout(function(){alert(translate('Please fill in start-end time'));}, 200)
		
		return false;
		
	} else if( ! checkclientchanged('wlassessment_form')) {
		
		
		return false;
		
	} else {
				
		if (formular_button_action == 'print_pdf' ) {
			//TODO-3049 Lore 03.04.2020
			if ( ! checkDiagnosisTypeSelected() ) {			
				
				return false;
			}
			
			//ISPC-2430 Carmen 26.11.2019
			if ( ! validateForm_edit()) {
				return false;
			}
			
			$('body').addClass('body-overlay');
			
			setTimeout(function(){
				location.href = appbase +"patientcourse/patientcourse?id=" +idpd;
			}, 1500);
			
		}
		//ISPC-2430 Carmen 26.11.2019
		//if ( ! validateForm_edit()) {
		if ( ! validateForm_edit()   ||  ! checkDiagnosisTypeSelected() ) {			//TODO-3049 Lore 03.04.2020
	
			return false;
		}
		else
		{
			submited = true;
			changes = false;
			
			return true;
		}
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
}


function findGetParameter(parameterName) {
    var result = 0,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
          tmp = item.split("=");
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

$(document).ready(
	function() {
		
		$('input, textarea, select').on('change',function() {
			submited = false;
			changes = true;
		});
		
		$( "#wlassessment_form" ).tabs({
			
			selected : findGetParameter('page'),
			
			show: function(event, ui) {
				if(ui.index == 3) { //page4 tab
					//Render after tabs have been shown.
					$('.human_canvas_holder').createHumanBodyCanvas({
						width:544, 
						height:397,
						oldImage: $('.old_human_canvas_holder').val() 
					});
				
					//$.getmedicationblocks();
				}
			},
			select: function(event, ui) {
				//add the page number to the post
				$('.wlassessment_current_page').val(ui.index);
			},
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
			header: '> fieldset > legend'
		});
		
		
		$('.qq_file_uploader_placeholder').each(function(){
			uploader_create(this, ['pdf','docx','doc']);
		});
		
		
		try {
			$('.livesearchFormEvents').livesearchCityZipcode();
			$('.livesearchFormEvents').livesearchFamilyDoctor();
			$('.livesearchFormEvents').livesearchHealthInsurance();
			$('.livesearchFormEvents').livesearchDiacgnosisIcd();
			$('.livesearchFormEvents').livesearchSpecialist();
			$('.livesearchFormEvents').livesearchCareservice();
						
		} catch (e) {
			//console.log(e);
		}
		
		//reset canvas
	    $('.canvas_reset').on('click',function() {
			//get canvas id
	    	var qid = $('.human_canvas_holder').find('canvas').attr('id');
			var canvas = $('#'+qid)[0];

			var context = canvas.getContext("2d");

			//reset canvas
			context.clearRect(0, 0, canvas.width, canvas.height);

			return false;
	    });
		
		$.getmedicationblocks();
});








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
	}	
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
	}	
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

//TODO-3049 Lore 03.04.2020
function checkDiagnosisTypeSelected(){
	
	var err = "";
	var elm_rows = document.querySelectorAll('.icd_holder_row');
	var dcntr = elm_rows.length;
	
	 var nr_rows = elm_rows.length;
	 var ct = 0;
	 
	 jQuery('tr.icd_holder_row').find('input[type="radio"]').each(function(){

	    if($(this).prop("checked") == true){	 
			 ct++;
		 }

	 });
		
	
	if(ct != nr_rows)
	{
		setTimeout(function(){alert(translate('selectdiagnosisfor'));}, 200);
		return false;
	}else {
		return true;
	}

}

function Contact_Person_addnew (_target, parent_form) {
	
	$.get(appbase + 'ajax/createformcontactperson?parent_form='+parent_form, function(result) {

		var newFieldset = $(result).insertBefore($(_target).parent('div'));
		
		var _delClicker = jQuery('<div class="delete_row" style="float:right"></div>')
		.appendTo($('legend', newFieldset));
		_delClicker.click(function(event){
			$(this).parent('legend').parent('fieldset').remove();
			return false;
		});
		
		$(newFieldset).livesearchCityZipcode();
		
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
				
			}
		});
		
	});
	
}
function Patient_Specialist_addnew (_target, parent_form) {
	
	$.get(appbase + 'ajax/createformpatientspcialist?parent_form='+parent_form, function(result) {

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

function PatientPflegedienst_addnew (_target, parent_form) {
	$.get(appbase + 'ajax/createformpatientpflegedienst?parent_form='+parent_form, function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parent('div'));
		
		var _delClicker = jQuery('<div class="delete_row" style="float:right"></div>')
		.appendTo($('legend', newFieldset));
		_delClicker.click(function(event){
			$(this).parent('legend').parent('fieldset').remove();
			return false;
		});
		
		$(newFieldset).livesearchCareservice();
		$(newFieldset).livesearchCityZipcode();

		
		
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
				
			}
		});
	});
}

function PatientDiagnosis_addnew (_target, _parent_form) {
	
	var selected_page = $("#wlassessment_form").tabs('option', 'selected') || 0;
	selected_page++;
	var parent_form = "_page_" + selected_page + "[" + _parent_form + "]";
	
	$.get(appbase + 'ajax/createformdiagnosisrow?parent_form='+parent_form, function(result) {

		var newFieldset =  $(result).insertBefore($(_target).parents('tr'));
		
		//$(newFieldset).livesearchDiacgnosisIcd({'selectorParents': ''});

		$('.livesearchFormEvents').livesearchDiacgnosisIcd();
		
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


function reCreateDgpKern(_date) {
	
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
				'form_blocks' : ['partners', 'ecog'],
				'belongsTo' : {'partners': '_page_1[PatientDgpKern]', 'ecog' : '_page_4[PatientDgpKern]'},
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