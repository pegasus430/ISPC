/*------ Changes for ISPC-1848 F --------------------*/


/**
 * TODO-1503
 * TODO-1510
 */
$('#print_button').live('click',function(){
	
	var formname = "print_medications";
	
	$('form#' + formname + ' #print_button').attr('disabled', true);
	setTimeout(function () { $('form#' + formname + ' #print_button').attr('disabled', false);}, 8000);

});



/*
 * Code commented- and moved in separate file -   patientmedication/datamatriximport.js  (17.07.2019) By Ancuta
 */ 

/*
 * ISPC-2002 datamatrixImport created
 * @todo create independent dialog with steps from the callbacks
*/
/*
(function($) {

	$.fn.datamatrixImport = function(options) {

		var datamatrixDialog;

		var datamatrix = this;

		var debugmode = false;

		var patid = window.idpd;

		var loginfo = function(_msg) {
			if (debugmode) {
				console.info(_msg);
			}
		}

		var defaults_post = {
			post_url: window.appbase + 'patientnew/datamatriximport',
			id: window.idpd,
			action: "datamatrix",
		};

		var defaults = {

			dialog_id : "#datamatix_dialog",
			step: 1, // open on step
		
			btn_close: translate('datamatrix_lang')['btn_close'],
			btn_continue: translate('datamatrix_lang')['btn_continue'],

			step_in_progress_title: translate('datamatrix_lang')['step_in_progress_title'],
			step_in_progress_infotext: translate('datamatrix_lang')['step_in_progress_infotext'],

			step1_title: translate('datamatrix_lang')['step1_title'],
			step1_label: translate('datamatrix_lang')['step1_label'],
			step1_infotext: translate('datamatrix_lang')['step1_infotext'],

			step2_title: translate('datamatrix_lang')['step2_title'],
			step2_label: translate('datamatrix_lang')['step2_label'],
			step2_infotext: translate('datamatrix_lang')['step2_infotext'],

			general_error: translate('datamatrix_lang')['general error'],

		};
		//append custom options
		var _opts = $.extend(defaults, options);

		var create_dialog = function(_step) {

			datamatrixDialog = $(_opts.dialog_id).dialog({

				autoOpen: true,
				modal: true,
				maxWidth: 800,
				maxHeight: 500,
				width: 600,
				height: 500,
				
				create: function (event, ui) { // this is more reliable
					$("textarea", this).val("");
				},

				close: function(ev, ui) {
					//		            $(this).dialog("destroy");
					//		            $(this).remove();
					_opts.step = 1; //reset step
				},
				open: function() {
					
					if(allow_reader == 0){
						$('.datamatrix_xml_div').removeClass('hide_details');	
						$('.second').removeClass('hide');
						$('.first_step').removeClass('hide');
					}
					
					if(_step =="1" && allow_reader == 1){
						
	    	    		$('.datamatrix_xml_div').addClass('hide_details');
						// 'BARCODE-READER-LICENSE-KEY'
				        BarcodeReader.licenseKey = Barcodereader_LicenseKey;
				        
				        let scanner = new BarcodeReader.Scanner({
				    		videoSettings: { video: { width: 480, height: 480, facingMode: "environment" } },
				    	    //The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.
				    	    runtimeSettings: { mAntiDamageLevel: 15, mDeblurLevel: 15, mBarcodeFormatIds: ["DATAMATRIX"] },
				    	    
				    	    // Use 
				    	    htmlElement: document.getElementById('div-video-container'),
				    	    
				    	    // The same code awlways alert? Set duplicateForgetTime longer.
				    	    duplicateForgetTime: 1000,
				    	    onFrameRead: results => {
				    	    	console.log(results);
				    	    },
				    	    
				    	    onNewCodeRead: (txt, result) => {
//				    	    		 alert(txt);
				    	    		// console.log(txt);
 	    		
				    	    		$('#div-video-container').hide();
				    	    		$('.datamatrix_xml').hide();
				    	    		$('.datamatrix_xml').val(txt);
				    	    		scanner.close();
				    	    		
				    	    		// Proceed to next step
				    	    		dialog_IN_PROGRESS();

									//do the callback for this step
									callbacks[_opts.step]();
				    	    	}
				        });
				        scanner.open().catch(ex=>{
//				            console.log(ex);
//				            alert(ex.message || ex);
				        	$('#div-video-container').remove();
				        	$('.datamatrix_xml_div').removeClass('hide_details');
				            scanner.close();
				        });
					}
					
					
					$("div.row", this).hide(); //hide all steps
					
					$("textarea", this).val(""); // clear the textareas

					var step_div = $("div[data-step='" + _step + "']", this);

					if (step_div) {
						$(this).dialog({
							title: step_div.data("title")
						});
						step_div.show();
					}
				},

				buttons: [{
						text: _opts.btn_close,
						click: function() {
							$(this).dialog("close");
						}
					},

					{
						text: _opts.btn_continue,

						click: function() {
							//									
							//show a in progress
							dialog_IN_PROGRESS();

							//do the callback for this step
							callbacks[_opts.step]();

						}
					},


				]
			});
		};

		//this will change the step
		var changeStep = function(_step) {

			_opts.step = _step;

			var step_div = $("div[data-step='" + _step + "']", datamatrixDialog);

			if (step_div) {

				$("div.row", datamatrixDialog).hide(); //hide all steps

				$(datamatrixDialog).dialog({
					title: step_div.data("title")
				});

				step_div.show();

			}

		};


		//this is a default intermediate step
		var dialog_IN_PROGRESS = function() {

			loginfo("dialog_IN_PROGRESS");

			$("div.row", datamatrixDialog).hide(); //hide all steps

			var inprogress_div = $("div[data-step='inprogress']", datamatrixDialog);
			if (inprogress_div) {

				//do the callback
				callbacks.inprogress();

				datamatrixDialog.dialog({
					title: _opts.step_in_progress_title
				});

				//				$(this).dialog({title: inprogress_div.data("title")});

				inprogress_div.show();
			}
		};

		
		//callbacks for each step
		//@todo : this needs to be moved in _opts
		var callbacks = {

			"inprogress": function() {
				loginfo("callbacks inprogress ... add some fancy html spinner");
			},

			"1": function(_opt) {

				//					loginfo("callbacks (" + _opt + " )");
				//					loginfo("ajax the xml and wait for the response ");
				//					
				var _data_post = {
					step: "1",
					datamatrix_xml: $(".datamatrix_xml", datamatrixDialog).val(),
				}
				//append custom options
				var _data_post = $.extend(defaults_post, _data_post, _opt);

				$.ajax({

					type: "POST",
					method: "POST",

					url: _data_post.post_url,
					dataType: "json",
					//						async: true,
					cache: false,
					timeout: (600 * 1000), //10 minutes is more than enough

					data: _data_post,

					success: function(json, status, xhr) {
						loginfo("ajax success");
						if (typeof json !== 'undefined' &&
							json.success === true &&
							typeof json.medication_html_grid !== 'undefined') {

							var next_step = 2;

							//set view for next step
							var step_div = $("div[data-step='" + next_step + "']", datamatrixDialog);

							if (step_div) {
								$('.second').removeClass('hide');
								$(".medication_html_grid", step_div).html(json.medication_html_grid)
								
								$("select.dm_group", step_div).each(function(){
									dm_group_change(this);
								})

							}

							//go to next step
							changeStep(next_step);


						}
					},
					error: function(xhr) {
						//					    	alert ("Oopsie: " + xhr.statusText);
						setTimeout((function() {
							alert(_opts.general_error);
						}), 50);
						$('.datamatrix_xml_div').removeClass('hide_details');
						$('.datamatrix_xml').show();
						$('.datamatrix_xml').show();
						$('.first_step').removeClass('hide');
						changeStep(1);
					}
				});


			},


			"2": function(_opt) {

				loginfo("callbacks 2 (" + _opt + " )");
				loginfo("ajax the selected medis and wait for the response ");

				//serialize the form
				var serializeObject = $("#datamatrixSelected").serializeObject();				

				var _data_post = {
					"step": "2",
					"dataObj": serializeObject,
				}

				//append custom options
				var _data_post = $.extend(defaults_post, _data_post, _opt);


				loginfo(_data_post);


				$.ajax({

					type: "POST",
					method: "POST",

					url: _data_post.post_url,
					dataType: "json",
					//						async: true,
					cache: false,
					timeout: (600 * 1000), //10 minutes is more than enough

					data: _data_post,

					success: function(json, status, xhr) {
						loginfo("ajax success");
						if (typeof json !== 'undefined') {

							if (json.success === true) {
								var next_step = 3;
	
								//go to next step to display the OK message
								changeStep(next_step);
								
								//disable weiter buttons
								$("button", datamatrixDialog).attr("disabled", true);
								
								//reload the page
								window.location.reload();
								
							} else {
								setTimeout((function() {
									alert(json.message);
								}), 50);
								
								changeStep(2);//go back to the medis step
							}

						}
					},
					error: function(xhr) {
						//this should neved be !
						setTimeout((function() {
							alert(_opts.general_error);
						}), 50);
						changeStep(2);//go back to the medis step 
					}
				});

			},
			
			"3": function(_opt) {
				loginfo("callbacks 2 (" + _opt + " )");
			}

		};

		create_dialog(_opts.step); //this.start = create_dialog; //make this callable directly

		return;
	};
})(jQuery);


function dm_group_change ( _this )
{
	//_this = selectbox that is inside a row
	
	var group = $(_this).val();
	
	var parent_tr = $(_this).parents('tr');
	
	var parent_dosages_array = parent_tr.data('dosage') || [];
	var parent_dosages_text = parent_tr.data('dosage_original') || [];
	
	var parent_id =  $('.dm_import_cb', parent_tr).val();
	
	var $selectBox =  $("select.dosage_intervals" , parent_tr);
	
	var $inputBox =  $("input.dosage_intervals" , parent_tr);
	
	if ( typeof dosage_intervals_json[group] != 'undefined') {

		var group_length = $.map(dosage_intervals_json[group], function(n, i) { return i; }).length;
		
		var length_error_class = '';
		if (parent_dosages_array.length == group_length ) {
			//console.log("same length nothing to color");
		} else {
			length_error_class = "error";
		}
		
		var $cnt = 0;
		var inputs = $.map(dosage_intervals_json[group], function( i, v){
			
			var value= '';
			if (typeof parent_dosages_array[$cnt] !== 'undefined' ) {
				value = parent_dosages_array[$cnt];
			}
			$cnt++;

			var $elem = $('<input/>', {'class': 'dosage_qty ' + length_error_class, text: v, 'value':value, name: "dm_medication["+parent_id+"][dosage]["+v.replace(":", "_")+"]"});
		    return "<div class='dosage_div'>" + $elem.get(0).outerHTML + " um "+ v + "</div>";
		});
		
		
		$('.xml_dosage', parent_tr)
		.html( inputs)
		.prepend( parent_dosages_text + "<br />" );
		
		 
		
	} else {
		//our single input this is the one input with style 1-2-0-1-1 
		//NOT array so we can identify easier later
		
		var one_dosage = parent_dosages_array.join("-");
		
		
		var $elem = $('<input/>', {'class': 'dosage_qty_single', text: one_dosage, 'value':one_dosage, name: "dm_medication["+parent_id+"][dosage]"});
		
		$('.xml_dosage', parent_tr).html( parent_dosages_text + "<br/>" + $elem.get(0).outerHTML);
		
	}	
}*/