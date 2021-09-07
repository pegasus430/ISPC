;
/* 
 * versorger.js
 * requires jquery.changeEventOrder.js
 * requires fine-uploader.js
 * modified by Carmen on 22.01.2020 for ISPC-2508
 */



var dirty=0;
var current_category = null;

$(document).ready(function(){
	
    $(document).on('click', '.versorger-catheader', function(){
        $(this).next().toggle();
        if($(this).hasClass('active')){
            $(this).removeClass('active').addClass('inactive');
        }else{
            $(this).addClass('active').removeClass('inactive');
        }
    });

    /*
    $(document).on('click','.versorger-livesearch-entry', function(){
        var json_str=$(this).find('.json-data').val();
        var json_data=JSON.parse(json_str);
        var cat=$('#versorger-entryeditor').prop('category');
        var mymap=mappings[cat];
        var cols=mymap['cols'];
        for (var i=0; i<cols.length; i++){
            var colname=cols[i]['class'];
            $('#versorger-entryeditor .content').find('input[name="'+colname+'"]').val(json_data[colname]);
        }
        $('input[name="_just_update"]').val(0);
    });
    */

//    function editordialog(entrydata, cat, p_elem){
	function editordialog(entrydata_ID, cat, p_elem, action = null){
    	
		current_category = cat;
		
		dirty=0;
        
        var editor=$('<div>');

        var _html = '';
        
        if (entrydata_ID != null && p_elem != null && cat != 'PatientArtificialEntriesExits') {
        	_html = $('#' + cat + '_editDialogHtml_' + entrydata_ID).html();
        } else if (entrydata_ID != null && p_elem != null && cat == 'PatientArtificialEntriesExits') {
        	//ISPC-2508 Carmen 19.05.2020 new design
        	if(action != 'remove' && action != 'refresh')
        	{
        		_html = $('script#' + cat + '_editDialogHtml_extra_' + entrydata_ID).html();
        	}
        	else
        	{	
        		_html = $('script#' + cat + '_editDialogHtml_extra_remove_' + entrydata_ID).html();
        		if(action == 'remove')
        		{
        			var label_text = translate('artificial_remove_date')+'<font style="color: red">*</font>';
        		}
        		else if(action == 'refresh')
        		{
        			var label_text = translate('artificial_refresh_date')+'<font style="color: red">*</font>';
        		}
        		
        			
        		 _html = _html.replace("&#160;", label_text);
        		
        	}
        	//--
		} else {
        	_html = $('#' + cat + '_addnewDialogHtml').html();
        	//ISPC-2774 Carmen 17.12.2020
            if(current_category == 'PatientTherapy' ){
           	 dirty=1;
            }
            //--
        }
        
        
        $(editor).append(_html);
		//ISPC-2788  Ancuta 02.02.2021
        $('#versorger-entryeditor .content').addClass('cnt_'+cat);
        //--        
        
        
        $('#versorger-entryeditor .content').empty();
        $('#versorger-entryeditor .content').append(editor);

        
        $('button.prefill_contact_person_btn').on('click', function(){
        	dirty=1;
        });

        
        $("input, select, textarea", $('#versorger-entryeditor .content')).change(function(){
            dirty=1;
        });
        
        if(action == 'refresh')
        {
        	dirty=1;
        }
        
        $('#versorger-entryeditor').prop('category',cat);

//        if(p_elem==null){
//            $('#versorger-entryeditor .delbutton, #versorger-entryeditor .clipboard-button').hide();
//        } else {
//        	$('#versorger-entryeditor .delbutton, #versorger-entryeditor .clipboard-button').show();
//        }

        $("#versorger-clipboardcopy").dialog('close');
        
		//ISPC-2801 Ancuta 18.01.2021
		var tr_Title = translate("Eintrag einsehen/ändern");
		if(cat == "MePatientDevices"  || cat == "MePatientDevicesNotifications" ){
			var tr_Title = translate("[mePatient_devices_settings_title]");
		}
		//--
		
        $('#versorger-entryeditor').dialog({
        	dialogClass : "versorgerEditDialog",
        	modal : true,
        	autoOpen : true,
        	closeOnEscape : true,
        	title: tr_Title, //ISPC-2801 Ancuta 18.01.2021
        	minWidth : 620,
        	minHeight : 300,
        	
        	beforeClose : function() {
    			// return false; // don't allow to close
    		},
//    		close : function(event, ui) {
//    			// dialog was closed
//    		},
        	close: entryeditorDialogOnClose,
        	
        	open : function() {
        		
        		attachDialogEvents($('#versorger-entryeditor'));
        		
        		//sapv is bigger
        		if ($(this).find('fieldset.create_form_patient_sapv_verordnung').length == 1 ) {
        			$(this).parents(".versorgerEditDialog").css("width", "760px");
        		}
        		

        		if(p_elem == null) {
        			$(this).parent().find('.delbutton, .clipboard-button').hide();
    	        } else {
    	        	$(this).parent().find('.delbutton, .clipboard-button').show();
    	        }  
        		
        		
        		if (cat == 'PatientMaster' || cat == 'PatientHospizverein' || cat == 'PatientArtificialEntriesExits') {
        			$(this).parent().find('.delbutton').hide();
        		}
        		
        		$(this).parent().find('.refreshbutton').hide();
        		$(this).parent().find('.removebutton').hide();
        		
        		if (typeof(addresses[cat]) == 'undefined' 
    				|| typeof(addresses[cat][entrydata_ID]) == 'undefined' ) 
        		{
        			$(this).parent().find('.clipboard-button').hide();
        		
        		} else if (typeof(addresses[cat]) != 'undefined' 
        			&& typeof(addresses[cat][entrydata_ID]) != 'undefined' 
    				&& addresses[cat][entrydata_ID] == null) 
        		{
        			$(this).parent().find('.clipboard-button').hide();
        			
        		} else {	
        			$(this).parent().find('.clipboard-button').show();
        		}
        		
        		$('.time')
        		.timepicker({
        			minutes: {
        				interval: 5
        			},
        			showPeriodLabels: false,
        			rows: 4,
        			hourText: 'Stunde',
        			minuteText: 'Minute'
        		})
        		.mask("99:99")
        		;
        		
        	},
        	
        	buttons : [
    		           
   					//copy address button
   					{
   						'class' : "clipboard-button leftButton",
   						text : translate('Copy address'),
   						click : function() {
   							
   							var _text = '';
   				        	var _lineDelimiter = "";
   				        	
   				        	if(typeof(addresses[cat]) != 'undefined' && typeof(addresses[cat][entrydata_ID]) != 'undefined') {
   				        		_text = addresses[cat][entrydata_ID];
   				        		
//   				        		if (addresses[cat][entrydata_ID] == null) {
//   				        			console.log('null');
//   				        		} else {
//   				        			console.log('NOTnull');
//   				        		}
   				        		
   				        	}
   				        	
   				        	$(this).dialog("close");
   				        	
   				        	copyAddressDialog(_text);
   				        	
   							
   						},
   					
   					},
   					//delete button
   					{
   						'class' : "delbutton leftButton",
   						text : translate('Delete versorger'),
   						click : function() {
   							
   							formValidateSubmit('delete');
   							
   						},
   						
   					},
   					
	   				//refresh button
	   					{
	   						'class' : "refreshbutton leftButton",
	   						text : translate('Refresh versorger'),
	   						click : function() {
	   							
	   							formValidateSubmit('refresh');
	   							
	   						},
	   						
	   					},
	   					
	   				//remove button
	   					{
	   						'class' : "removebutton leftButton",
	   						text : translate('Remove versorger'),
	   						click : function() {
	   							
	   							formValidateSubmit('remove');
	   							
	   						},
	   						
	   					},
   					
   					//save button
   					{
   						'class' : "rightButton",
   						text : translate('save'),
   						click : function() {
   							
   							if (dirty == 1) {   								
   								formValidateSubmit('save');
   							}
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
        
        if(action == 'delete')
        {
        	var buttons = $('#versorger-entryeditor').dialog('option', 'buttons');
        	$('#versorger-entryeditor').dialog('close');
        	buttons[1].click.apply($('#versorger-entryeditor'));
        }
        
        if(action == 'refresh')
        {
        	var buttons = $('#versorger-entryeditor').dialog('option', 'buttons');
        	$('#versorger-entryeditor').dialog('close');
        	buttons[2].click.apply($('#versorger-entryeditor'));
        }
        
        if(action == 'remove')
        {
        	var buttons = $('#versorger-entryeditor').dialog('option', 'buttons');
        	$('#versorger-entryeditor').dialog('close');
        	buttons[3].click.apply($('#versorger-entryeditor'));
        }
         

        $("#versorger-entryeditor form").off('submit');
        
$("#versorger-entryeditor form").submit(function(e) {
            
//        	$('#versorger-entryeditor').dialog('close');
        	var _category = cat;
        	
        	e.preventDefault();
        	
            if(dirty==1 && checkclientchanged()) {
            	
            	
            	var _data = $("#versorger-entryeditor form").serializeObject() || {};
            	
            	var _action = _data.__action || 'savePatientDetails';
            	
            	_data.__action = _action;
            	_data.__category = _data.__category || _category;
            	
            	//ISPC-2508 Carmen 12.06.2020
            	var aerrart = '0';
            	if(_data.__category == 'PatientArtificialEntriesExits' && _data.hasOwnProperty('__subaction'))
            	{
            		var option_date = $('.create_form_artificial_entries_exits tr').map(function() {
						var formset = null;
						 $(this).find(':input').each(function() {				    		
							
							 if(this.name == 'patientDetails[PatientArtificialEntriesExits][option_date]')
							 {
								
								 formset = $(this).val();
							 }
							 
							
						 });
						
					        return formset;
						
					 }).get();
            		
	            	if(_data.patientDetails.PatientArtificialEntriesExits.remove_date == "")
	            	{
	            		aerrart = '1';
	            	}
	            	else
	            	{
	            		
	            		 var date_arr = String(option_date).split('.');
					     var end_date_arr = _data.patientDetails.PatientArtificialEntriesExits.remove_date.split('.');
					     
					     var start = new Date(date_arr[2], date_arr[1]-1, date_arr[0], '0', '0', '0', '0');
					     var end = new Date(end_date_arr[2], end_date_arr[1]-1, end_date_arr[0], '0', '0', '0', '0');
					     if((end-start) < 0)
					     {
					    	 aerrart = '1';
					     }
					  
	            	}
            	}
            	//ISPC-2381 Carmen 13.01.2021
            	else if(_data.__category == 'PatientAids')
            	{
            		if(_data.patientDetails.PatientAids.aid == "")
            		{
            			aerrart = 1;
            		}
            	}
            	//--
            	
            	if(aerrart == '1')
            	{
            		//ISPC-2381 Carmen 13.01.2021
            		if(_data.__category == 'PatientAids')
            		{
            			$("#aid_error").css('color', 'red').show();
            		}
            		else
            		{
            			alert(translate('End date cant be before start date'));
            		}
            		//--
            	}
            	else
            	{
            	
	            	var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
	        		
	                $.ajax({
	                    dataType	: "json",
	                    type		: "POST",
	                    url			: _url,
	                    data		: _data,
	                    
	                    beforeSend: function (jqXHR) {	
	    					$('#versorger-entryeditor').block({
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
	    						message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>'
	    					});
	            	    },
	            	    complete: function (jqXHR) {
		            		$('#versorger-entryeditor').unblock();
	            	    },
	            	    
	                    success: function (response, request) {
	                    	
	                    	if (response.hasOwnProperty("success") && response.success) {
	                    		
	                    		dirty=0;
	                    		
	                    		$('#versorger-entryeditor').dialog('close');
	                    		
	                    		var firstartif = true;
	                    		$.each(response.data, function(cat_as_key, cat_data) {
		                    		
	                    			if (cat_as_key == '__meta-timestamp' || cat_as_key == '__meta-categorys') {
	                    				//this will utilized in a separate $.each()
	                    				return;
	                    			}
	                    			
	                    			var _category = cat_as_key;
	                    			
		                    		//reset old
		                    		$("#box-" + _category).find('.entries .entry, .entry_extra_wrapper').remove();
		                    			                    		
		                    		addresses[_category] = {};
		                    		
		                    		//add new
		                    		var _entries = [];
		                    		
	//	                    		$.each(response.data, function(key, entry) {
	                    			$.each(cat_data, function(key, entry) {
		                    			if (key == 'addnewDialogHtml') return;
		                    			
		                    			if ( (! entry.hasOwnProperty("editDialogHtml") || entry.editDialogHtml == null) &&  _category != 'PatientArtificialEntriesExits') return;
		                    			
		                    			addresses[_category][key] = entry.address;
		                        		
		                    			//reset old         			
		                    			
		                    			if(_category != 'PatientArtificialEntriesExits')
		                    			{
		                    				//remove the previous one
			                    			$("script#" +_category + '_editDialogHtml_' + key).remove();
			                    			//add new editbox content
			                    			var _script = $('<script>', {
			                    				type	: 'text/template',
			                    				id		: _category + '_editDialogHtml_' + key,
			                    				html	: entry.editDialogHtml
			                    			 }).appendTo('body');
		                    			
		                    			}
		                    			else
		                    			{
		                    				//remove the previous one
			                    			$("script#" +_category + '_editDialogHtml_' + key).remove();
			                    			$("script#" +_category + '_editDialogHtml_extra_' + key).remove();
			                    			
		                    				//add new editbox content
			                    			var _script = $('<script>', {
			                    				type	: 'text/template',
			                    				id		: _category + '_editDialogHtml_' + key,
			                    				html	: entry.editDialogHtml
			                    			 }).appendTo('body');
			                    			
			                    			//add new editbox content
			                    			var _script = $('<script>', {
			                    				type	: 'text/template',
			                    				id		: _category + '_editDialogHtml_extra_' + key,
			                    				html	: entry.editDialogHtml_extra
			                    			 }).appendTo('body');
			                    			
		                    			}
		                    			
		                    			var _entrylines = [];
		                    			
		                    			if (entry.meta.inlineEdit) {
		                    				//place the editDialogHtml
	//	                    				var _entryline = $('<div>', {
	//	                    					'class'	: 'entryline',
	//	                    					html 	: entry.editDialogHtml
	//	                        			});
	//	                    				_entrylines.push(_entryline); 
		                    				_entrylines.push(entry.editDialogHtml); 
		                    				
		                    			} else {
		                    				
			                    			$.each(entry.extract, function(_i, _line) {
			                    				
			                    				if (_line[0] != null) {	                    				
			                    					//ISPC-2381 Carmen 13.01.2021
			                    					if(_category == 'PatientAids')
		    		                    			{
				                    					var _entryline = $('<div>', {
			    	                    					'class'	: 'entryline',
			    	                    					html 	: [
			    	                    					     	   $('<span>', {'html' : _line[1], 'class' :  'entrydetail'})
			    	                    					     	   ]
			    	                        			});
		    		                    			}
													//--TODO-3830 Ancuta 09.02.2021
			                    					else if(_category == 'PatientTherapy')
		    		                    			{
														var _entryline = $('<div>', {
					                    					'class'	: 'entryline',
					                    					html 	: [
					                    					     	   $('<label>', {'text' : _line[0]}) , 
					                    					     	   $('<span>', {'html' : _line[1], 'class' :  'entrydetail'})
					                    					     	   ]
					                        			});
		    		                    			}
													//--
			                    					else
			                    					{
					                    				var _entryline = $('<div>', {
					                    					'class'	: 'entryline',
					                    					html 	: [
					                    					     	   $('<label>', {'text' : _line[0]}) , 
					                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail'})
					                    					     	   ]
					                        			});
			                    					}
			                    					//--
			                    				} else {
			                    					if ( _category in metaCategorys && metaCategorys[_category].hasOwnProperty("extractEscape") && ! metaCategorys[_category]['extractEscape']) {
			                    						if(_category != 'PatientArtificialEntriesExits')
			    		                    			{
			                    							var _entryline = $('<div>', {
				    	                    					'class'	: 'entryline',
				    	                    					html 	: [
				    	                    					     	   $('<span>', {'html' : _line[1], 'class' :  'entrydetail'})
				    	                    					     	   ]
				    	                        			});
			    		                    			}
			                    						else
			                    						{
			                    							if(cat_data.length != 1)
			                    							{
				                    							if(firstartif === true)
				                    							{
						                    						var _entryline = $('<div>', {
						    	                    					'class'	: 'entryline',
						    	                    					html 	: [
						    	                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail', 'id' : 'artificial_entries'})
						    	                    					     	   ]
						    	                        			});
						                    						firstartif = false;
				                    							} else if(firstartif === false){
				                    								var _entryline = $('<div>', {
						    	                    					'class'	: 'entryline',
						    	                    					html 	: [
						    	                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail', 'id' : 'artificial_exits'})
						    	                    					     	   ]
						    	                        			});
				                    								firstartif = null;
				                    							}
			                    							}
			                    							
			                    						}
			                    					} else {
			                            				var _entryline = $('<div>', {
			    	                    					'class'	: 'entryline',
			    	                    					html 	: [
			    	                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail'})
			    	                    					     	   ]
			    	                        			});
			                            			}
			                    				}
			                    				
			                    				_entrylines.push(_entryline);
			                    			});	
			                    		
			                    			if(_category != 'PatientArtificialEntriesExits')
			                    			{
			                    			var _infobutton = $('<div>', {
			                					'class'	: 'info-button',
			                					html 	: [$("<img>", {"src" : appbase + 'images/info_med.png'}), " " + translate('Details')]
			                    			});
			                    			_entrylines.push(_infobutton);
			                    			}
		                    			}
		                    			
		                    			if(_category != 'PatientArtificialEntriesExits')
		                    			{
		                    			var _hiddenjsondata = $('<input>', {
		                    				type	: "hidden",
		                    				'class'	: 'hidden-json-data',
		                    				value 	: key
		                    			});
		                    			_entrylines.push(_hiddenjsondata);
		                    			}
		                    			var _entry = $('<div>', {
		                					'class'	: 'entry',
		                					html 	: _entrylines
		                    			});
		                    			_entries.push(_entry);
		                    		});
	                    			
		                    		
		                    		if (_entries.length > 0) {
		                    		
		                    			$(_entries).prependTo("#box-" + _category + " .entries");
		                    			
		                    			if(_category == 'PatientArtificialEntriesExits')
		                    			{
		                    				$('.entry').each(function(){
			                    				if($(this).is(':empty') || $(this).text() == 'justNotEmpty')
			                    				{
				                    				$(this).remove();
			                    				}
			                    			});
		                    				
		                    				eval($('#artificial_entries').html($('#artificial_entries').text()));
		                    				eval($('#artificial_exits').html($('#artificial_exits').text()));
		                    				
		                    			}
		                    			if (_category in metaCategorys && ! metaCategorys[_category]['multipleEntries']) {
		                        			$("#box-" + _category).find('.addbutton').hide();                        			
		                        		}
		                    			
		                    			attachBoxEvents( $("#box-" + _category));
		                    			
		                    		} else {
		                    			$("#box-" + _category).find('.addbutton').show();
		                    		}
		                    		
		                    		//gotobox
		                    		//window.location.hash = '#box-' + _category;
	                    			
	                    		 
	                    		});
	                    		
	                    		
	                    		/*
	                    		 * reCreate the addnewDialogHtml for this box.. something may have changed.. you could have a new option/module etc etc
	                    		 */
	                    		$.each(response['data']['__meta-categorys'], function(cat_as_key, cat_data) {
		                    		
	                    			var _category = cat_as_key;
	                    			
	                    			if ('addnewDialogHtml' in cat_data && cat_data.addnewDialogHtml != null) {
	                    				
		                    			$("script#" +_category + '_addnewDialogHtml').remove();
		                    			
		                    			var _script = $('<script>', {
		                    				type	: 'text/template',
		                    				id		: _category + '_addnewDialogHtml',
		                    				html	: cat_data.addnewDialogHtml
		                    			}).appendTo('body');	
	                    			}
	                    		});
	                    		
	                           	//ISPC-2673 Ancuta 01.10.2020
	                           	//if(_category == 'FormBlockResources')
	                           	//ISPC-2776 Lore 16.12.2020														
	                           	if(_category == 'FormBlockResources' 
	                           		|| _category == 'PatientChildrenDiseases' 			//ISPC-2776 Lore 16.12.2020	
	                           		|| _category == 'PatientNutritionInfo'				//ISPC-2788 Lore 08.01.2021
	                           		|| _category == 'PatientStimulatorsInfo' 			//ISPC-2787 Lore 11.01.2021
	                           		|| _category == 'PatientFinalPhase' 			    //ISPC-2790 Lore 12.01.2021
	                           		|| _category == 'PatientExcretionInfo' 			//ISPC-2791 Lore 14.01.2021
	                           		|| _category == 'PatientPersonalHygiene' )			//ISPC-2792 Lore 15.01.2021
                   			{
	                           		window.location.reload();//redirect to self
                    			}
	                           	//--
	                    	
	                    	} else if (response.hasOwnProperty("editDialogHtmlWithErrors"))  {
	                    		
	                    		$('#versorger-entryeditor .content div').empty();
	                    		$('#versorger-entryeditor .content div').append($(response.editDialogHtmlWithErrors));
	                    		
	                    		attachDialogEvents($('#versorger-entryeditor'));
	                    		
	                    		
	                    	} else if (response.hasOwnProperty("message"))  {
	                    		
	                    		alert (response.message);
	                    		
	                    	} else {
	                    		//this should not be
	                    		alert ("Save failed, please contact admin");
	                    	}
	                    	
	                    	
	                    	
	                    	
	                    	return;
	                    	
	                    }
	                });
            	}
            	//--
            }
            
        });
        
        
        

        function formValidateSubmit ( _step ) {
        	
        	
        	if (window.patientIsDischarged == 1) {
        		
        		//check - alert patient IsDischarged
        		jConfirm(translate('[The patient is already discharged, are you sure you want to make this change ?]'), '', function(r) {
        			if (r) {
        				
        				if (_step == 'save') {
        					
        					$( "#versorger-entryeditor form" ).trigger( "submit" );		
        					
        				} else if (_step == 'delete') {
        					jConfirm(translate('[Are you sure you want to delete this ?]'), '', function(r) {
        						if (r) {
        							dirty = 1;
        							
        			       			$('<input>', {
        			       				name	: '__action',
        			       				type	: 'hidden',
        			       				value	: 'deletePatientDetails'
        			       			}).appendTo('#versorger-entryeditor form .content');   
        			       			
        			       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
        						}
        					});
        				}
        				else if (_step == 'refresh') {
                			jConfirm(translate('[Are you sure you want to refresh this ?]'), '', function(r) {
                				if (r) {
                					dirty = 1;
                					
                	       			$('<input>', {
                	       				name	: '__action',
                	       				type	: 'hidden',
                	       				value	: 'savePatientDetails'
                	       			}).appendTo('#versorger-entryeditor form .content'); 
                	       			
                	       			$('<input>', {
                	       				name	: '__subaction',
                	       				type	: 'hidden',
                	       				value	: 'refresh'
                	       			}).appendTo('#versorger-entryeditor form .content'); 
                	       			
                	       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
                				}
                			});
                		}
                		else if (_step == 'remove') {
                			jConfirm(translate('[Are you sure you want to remove this artificial entry/exit ?]'), '', function(r) {
                				if (r) {
                					dirty = 1;
                					
                	       			$('<input>', {
                	       				name	: '__action',
                	       				type	: 'hidden',
                	       				value	: 'deletePatientDetails'
                	       			}).appendTo('#versorger-entryeditor form .content'); 
                	       			
                	       			$('<input>', {
                	       				name	: '__subaction',
                	       				type	: 'hidden',
                	       				value	: 'remove'
                	       			}).appendTo('#versorger-entryeditor form .content'); 
                	       			
                	       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
                				}
                			});
                		}
        				
        				return true;
        			}
        		});
        		
        		
        		
        	} else {
        		
        		if (_step == 'save') {
        			
        			$( "#versorger-entryeditor form" ).trigger( "submit" );	
        			
        		} else if (_step == 'delete') {
        			jConfirm(translate('[Are you sure you want to delete this artificial entry/exit ?]'), '', function(r) {
        				if (r) {
        					dirty = 1;
        					
        	       			$('<input>', {
        	       				name	: '__action',
        	       				type	: 'hidden',
        	       				value	: 'deletePatientDetails'
        	       			}).appendTo('#versorger-entryeditor form .content');   
        	       			
        	       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
        				}
        			});
        		}
        		else if (_step == 'refresh') {
        			jConfirm(translate('[Are you sure you want to refresh this artificial entry/exit ?]'), '', function(r) {
        				if (r) {
        					dirty = 1;
        					
        	       			$('<input>', {
        	       				name	: '__action',
        	       				type	: 'hidden',
        	       				value	: 'savePatientDetails'
        	       			}).appendTo('#versorger-entryeditor form .content'); 
        	       			
        	       			$('<input>', {
        	       				name	: '__subaction',
        	       				type	: 'hidden',
        	       				value	: 'refresh'
        	       			}).appendTo('#versorger-entryeditor form .content'); 
        	       			
        	       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
        				}
        			});
        		}
        		else if (_step == 'remove') {
        			jConfirm(translate('[Are you sure you want to remove this artificial entry/exit ?]'), '', function(r) {
        				if (r) {
        					dirty = 1;
        					
        	       			$('<input>', {
        	       				name	: '__action',
        	       				type	: 'hidden',
        	       				value	: 'deletePatientDetails'
        	       			}).appendTo('#versorger-entryeditor form .content'); 
        	       			
        	       			$('<input>', {
        	       				name	: '__subaction',
        	       				type	: 'hidden',
        	       				value	: 'remove'
        	       			}).appendTo('#versorger-entryeditor form .content'); 
        	       			
        	       			$( "#versorger-entryeditor form" ).trigger( "submit" );	
        				}
        			});
        		}
        	}

        	return false;
        }
        
        
        
        
        
        

    }

    $(document).on('click', '.catdetails span.addbutton', function(){

        var cat=$(this).parents('.catdetails').find('.category').val();
        var entrydata={};
        current_category = cat;
        
        editordialog(entrydata, cat, null);
    });

    
    $(document).on('click', '.versorger .info-button', function(){
    	
    	if ($(this).parents('.entry_relocate').length > 0) {
			var p = $(this).parents('.entry_relocate')
		} else {
			var p = $(this).parents('.entry');
			
		}

    	var cat = $(p).parents('.catdetails').find('.category').val();
    	if(cat != 'PatientArtificialEntriesExits')
    	{
    		var entrydata_ID = $(p).find('.hidden-json-data').val();
    	}
    	else
    	{
    		var entrydata_ID = $(this).find('.hidden-json-data').val();
    	}
    	
    	if($(this).find('[name="action"]').val() == 'delete')
    	{
    		editordialog(entrydata_ID, cat, p, 'delete');
    	}
    	else if($(this).find('[name="action"]').val() == 'refresh')
    	{
    		editordialog(entrydata_ID, cat, p, 'refresh');
    	}
    	else if($(this).find('[name="action"]').val() == 'remove')
    	{
    		editordialog(entrydata_ID, cat, p, 'remove');
    	}
    	else
    	{
    		editordialog(entrydata_ID, cat, p);
    	}
    	
        //var entrydata_ID = $(p).find('.hidden-json-data').val();
        //editordialog(entrydata_ID, cat, p);
        

    });
    

    $(document).on('click', '.catdetails .memobutton', function(){
        var cat=$(this).parents('.catdetails').find('.category').val();
        
        if(memos!=undefined && cat in memos) {
            var mydata = memos[cat];
        }else{
            mydata={memo:'',color:'none'};
        }
        
        var _title = translate("Memo for") + " " + translate('[' + cat + ' Box Name]');
        
        $('#versorger-memo input[name="color"]').filter('input[value="'+mydata['color']+'"]').prop('checked',true);
        $('#versorger-memo').find('textarea').val(mydata['memo']);
        $('#versorger-memo').find('input[name="__category"]').val(cat);
        $('#versorger-memo').dialog({width:"400px",title: _title});
    });

    
    $(document).on('click', '.versorger-memo-button', function(){
        var memo=$('#versorger-memo').find('textarea').val();
        var color=$('#versorger-memo input[name="color"]:checked').val();
        var cat=$('#versorger-memo input[name="__category"]').val();
        var mydata={memo:memo, color:color};
        if(memos==undefined){
            memos=[];
            }
        memos[cat]=mydata;

        $('#box-'+cat).find('.headtitle-img').empty();
        if(color!="none") {
            var imgurl = appbase + "images/circle_" + color + "24.png";
            var img=$("<img src='"+imgurl+"'>");
            $('#box-' + cat).find('.headtitle-img').append(img);
        }
        $('#versorger-memo').dialog('close');
        var url = appbase + "patientnew/patientdetails?" + "id=" + idpd ;
        
        var _mdata=$('#versorger-memo form').serializeObject() || {};
        _mdata.__action = "updateMemos";
        _mdata.__category = cat;
        
        $.post(url, _mdata);
    });
    
    

    $(document).on('click', '.catdetails .historybutton, a.allhistorybutton', function() {
    	
        var cat=$(this).parents('.catdetails').find('.category').val() || '';
        
        var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
        
        var _data = {
        		"__action" : "loadBoxHistory",
        		"__category" : cat
        };
        

        $.ajax({
            dataType	: "json",
            type		: "POST",
            url			: _url,
            data		: _data,
            
            beforeSend: function (jqXHR) {
            	
            	$.blockUI({
    				css: {
    					border: 'none',
    					padding: '15px',
    					backgroundColor: '#000',
    					'-webkit-border-radius': '10px',
    					'-moz-border-radius': '10px',
    					opacity: .5,
    					color: '#fff',
    					height: 'auto',
    					'z-index' : 99999
    				},
    				message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>'
    			});
    	    },
    	    complete: function (jqXHR) {
    	    	$.unblockUI();
    	    },
    	    
    	    
    	    success: function (response, request) {
            	
            	if (response.hasOwnProperty("success") && response.success) {
            		//TODO
            		var editor=$('<div class="selector_historyBox catdetails">');
            		
            		
	       			$('<div>', {
	       				'class' : 'historyDiv',
	       				'html'	: response.message,
	       				'css'	: {
	       					'position': 'absolute',
	       					'height' : '350px',
	       					'right'	: 0,
	       					'margin':'5px 5px 5px 10px',
	       					'overflow-y' : 'scroll',
	       					'overflow-x' : 'hidden'
   						}
	       			}).appendTo(editor);   
	       			
            		;
//            		
//            		var _html = response.message;
//            		
//            		$(editor).append(_html);
            	    
            		$(editor).dialog({
            	    	dialogClass : "versorgerEditDialog catdetails",
            	    	modal : true,
            	    	autoOpen : true,
            	    	closeOnEscape : true,
            	    	title: (cat != '' ? translate('[' + cat + ' Box Name]') : '') + " " + translate("History"),  
            	    	minWidth : 610,
            	    	minHeight : 430,
            	    	buttons : null,
//            	    	close: function(event, ui)
//            	        {
//            	            $(this).dialog("close");
//            	            $(this).remove();
//            	        }
            						
            		});
            		
            	} else {
            		//this should not be
            		alert ("Failed to load history, please contact admin");
            	}
            	
            	return;
            	
            } 
        });
        
        
        
        return;
        
        if(memos!=undefined && cat in memos) {
            var mydata = memos[cat];
        }else{
            mydata={memo:'',color:'none'};
        }
        
        var _title = translate("Memo for") + " " + translate('[' + cat + ' Box Name]');
        
        $('#versorger-memo input[name="color"]').filter('input[value="'+mydata['color']+'"]').prop('checked',true);
        $('#versorger-memo').find('textarea').val(mydata['memo']);
        $('#versorger-memo').find('input[name="__category"]').val(cat);
        $('#versorger-memo').dialog({width:"400px",title: _title});
    });
    
    
    
    
    
    attachBoxEvents();
    
	    
});

function attachBoxEvents(_parentObj)
{
	try {
		attachBoxAccordion(_parentObj);
	} catch(e) {
		//console.log(e);
	}
	
	try {
		$('.qq_file_uploader_placeholder').each(function(){
			uploader_create(this, ['pdf','docx','doc']);
		});		
	} catch (e) {
		//console.log(e);
	}
	
	
	try {
		attachDate(_parentObj);
	} catch(e) {
		//console.log(e);
	}
	

	try {
		inlineEdit_attachInputEvents(_parentObj);
	} catch(e) {
		//console.log(e);
	}
	
	
	try {
		attachBoxSortable(_parentObj);
	} catch(e) {
		//console.log(e);
	}
	
	try {
		customElementsRelocate(_parentObj)
	} catch (e) {
		
	}
	
}



function customElementsRelocate(_parentObj)
{
	if ($("#box-PatientLocation").length) {
		
		$("#box-PatientLocation").find('a.addbutton').appendTo($('#box-PatientLocation span.memobutton').parent());
	}
	
	
	/*TODO all this should have been from the ZF .. because of this box all the ajax.success must be changed ?*/
	if ($("#box-PatientMaintainanceStage").length) { 
		$("#box-PatientMaintainanceStage").find('div.entry').each(function(){
			$(this).find("div.info-button").appendTo($("div.entryline:first", this)).css({"position": "relative", "float": "right" , "top":"-2px"});
		});
		
		$("#box-PatientMaintainanceStage").find('div.entry').removeClass('entry').addClass('entry_relocate');
		
		$("#box-PatientMaintainanceStage").find('div.entries').wrapInner( "<div class='entry_extra_wrapper'></div>" );

		var _i = 0;
		$("#box-PatientMaintainanceStage div.entry_relocate").each(function() {			
			$(this).addClass(_i%2 ? "odd" : "even");
			_i++;
		});
		
		
		$("#box-PatientMaintainanceStage").find('div.entry_extra_wrapper > input.category').appendTo( $("#box-PatientMaintainanceStage > div.entries"));
		$("#box-PatientMaintainanceStage").find('div.entry_extra_wrapper > div.dontPrint').appendTo( $("#box-PatientMaintainanceStage > div.entries"));
		
	}
	
}

function attachBoxSortable()
{
	//provider_left  will be 101
	//provider_right will be 102
	
	
    $('#provider_left').sortable({
		
    	connectWith: '#provider_right',
		handle: 'div.versorger-catheader > div.headtitle',
		cursor: 'move',
		
		placeholder: 'boxsort_placeholder',
		forcePlaceholderSize: true,
		
		opacity: 0.4,
		
		stop: function(event, ui){
			//$(ui.item).find('div.versorger-catheader').click();
			var itemorder=$(this).sortable('toArray');
		},
		
		update : function () {
			
			var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
			
			var _data = {};
			_data.__action = 'updateBoxOrder';
			_data.col = $(this).attr("id");
			_data.order = $(this).sortable('toArray');
			
			$.ajax({
		        dataType	: "json",
		        type		: "POST",
		        url			: _url,
		        data		: _data,
		    });
			
		}
	})
	//.disableSelection()
	;
    
    
    
 $('#provider_right').sortable({
		
    	connectWith: '#provider_left',
		handle: 'div.versorger-catheader > div.headtitle',
		cursor: 'move',
		
		placeholder: 'boxsort_placeholder',
		forcePlaceholderSize: true,
		
		opacity: 0.4,
		
		stop: function(event, ui){
			//$(ui.item).find('div.versorger-catheader').click();
			var itemorder=$(this).sortable('toArray');
		},
		
		update : function () {
			
			var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
			
			var _data = {};
			_data.__action = 'updateBoxOrder';
			_data.col = $(this).attr("id");
			_data.order = $(this).sortable('toArray');
			
			$.ajax({
		        dataType	: "json",
		        type		: "POST",
		        url			: _url,
		        data		: _data,
		    });
			
		}
	})
	//.disableSelection()
	;
}



function inlineEdit_formValidateSubmit( _step, _this , _extraObject) 
{
	var isemergencyplan = false;
	if (window.patientIsDischarged == 1) {
		
		//check - alert patient IsDischarged
		jConfirm(translate('[The patient is already discharged, are you sure you want to make this change ?]'), '', function(r) {
			if (r) {
				
				if (_step == 'save') {
					
					if (checkclientchanged()) {
						inlineEdit_submit( _step, _this, _extraObject);		            	
		            }
					
				} else if (_step == 'delete') {
					
					//TODO if needed
					
				}
				
				return true;
			}
		});
		
		
		
	} else {
		if( typeof _extraObject !== 'undefined' && JSON.stringify(_extraObject).indexOf("emergencyplan") > -1 ) {
			isemergencyplan = true;
		}
		if($('#isnotupload').val() != '1' && isemergencyplan) //ISPC - 2129
		{
			jConfirm(translate('your are about to upload an emergency plan. shall this file be taken as latest version?'), '', function(r) {
				if (r) {
					$('#isactive').val('1');
					if (_step == 'save') {
						
						if (checkclientchanged()) {
							inlineEdit_submit( _step, _this, _extraObject);		            	
			            }
						
					} else if (_step == 'delete') {
						
						//TODO if needed
						
					}
				}
				else
				{
					$('#isactive').val('0');
					if (_step == 'save') {
						
						if (checkclientchanged()) {
							inlineEdit_submit( _step, _this, _extraObject);		            	
			            }
						
					} else if (_step == 'delete') {
						
						//TODO if needed
						
					}
				}
			});
		}
		else
		{
			$('#isnotupload').val('0');
			if (_step == 'save') {
				
				if (checkclientchanged()) {
					inlineEdit_submit( _step, _this, _extraObject);		            	
	            }
				
			} else if (_step == 'delete') {
				
				//TODO if needed
				
			}
		}
		
	}

}



function inlineEdit_submit( _step, _this, _extraObject )
{
	
	var _category = $(_this).parents('.catdetails').find('.category').val();
	
	if (_category != '') {		
		current_category = _category;
	} else {
		_category = current_category;
	}
	
	var _data = $(_this).parents('.catdetails').find('input, select').serializeObject() || {};
	var _action = _data.__action || 'savePatientDetails';
	
	_data.__action = _action;
	_data.__category = _category;
	
	$.extend(true, _data, _extraObject);//append/overwrite _data
	
	var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
	
    $.ajax({
        dataType	: "json",
        type		: "POST",
        url			: _url,
        data		: _data,
        
        beforeSend: function (jqXHR) {	
        	$(_this).parents('.catdetails').block({
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
				message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>'
			});
	    },
	    complete: function (jqXHR) {
	    	
	    	$(_this).parents('.catdetails').unblock();
	    	
	    	closeSubFormDialog(_this);
	    },
	    
	    
	    success: function (response, request) {
        	
        	if (response.hasOwnProperty("success") && response.success) {
        		//TODO
        	} else if (response.hasOwnProperty("editDialogHtmlWithErrors"))  {
        		
        	} else if (response.hasOwnProperty("message"))  {
        		
        		alert (response.message);
        		
        	} else {
        		//this should not be
        		alert ("Save failed, please contact admin");
        	}
        	
        	return;
        	
        } 
    });
	
	
}

var inlineEdit_Timeout = null;

function inlineEdit_attachInputEvents()
{
	
	/*
	 * r.c. 1 , using $.bindLast so we fire first the other onChange and onFocusout
	 */
	$.each($('input:checkbox, input:radio, select', $('.inlineEdit')), function () {
		if ( ! $(this).isBoundNamespaced('change.inlineEdit')) {
			var _this = this;
			$(this).bindLast('change.inlineEdit', function() {
				setTimeout(function() {inlineEdit_formValidateSubmit('save', _this);}, 100);		
			});
		}
	});
		
	$.each($('input:text', $('.inlineEdit')), function () {
		if ( ! $(this).isBoundNamespaced('focusout.inlineEdit')) {
			var _this = this;
			$(this).bindLast('focusout.inlineEdit', function() {
				setTimeout(function(){inlineEdit_formValidateSubmit('save', _this);}, 100); //delay to try to finish date()		
			});
		}
	});
	
//	v.1 , using delay
	
//	$.each($('input:text', $('.inlineEdit')), function() {
//		$(this).on('focusout', function() {//			
//			var _this = this;
//			setTimeout(function(){inlineEdit_formValidateSubmit('save', _this);}, 3350); //delay to try and fire inline event first
//			
//		});
//	});

//	$.each($('input:checkbox, input:radio, select', $('.inlineEdit')), function () {
//		$(this).on('change', function() {//			
//			var _this = this;
//			setTimeout(function(){inlineEdit_formValidateSubmit('save', _this);}, 3350); //delay to try and fire inline event first
//			
//		});
//	});
	
	
	
	
	
	
}



function attachDialogEvents(_dialogObj) 
{
	try {
		attachDate(_dialogObj);		
	} catch(e) {
		//console.log(e);
	}
	
	try {
		attachMask(_dialogObj);
	} catch(e) {
		//console.log(e);
	}
	
	
	try {
		attachLivesearch(_dialogObj);
	} catch(e) {
		//console.log(e);
	}
	
	try {
		attachDialogAccordion(_dialogObj);
	} catch(e) {
		//console.log(e);
	}
	
	
	
}

function attachDate (_dialogObj)
{
	$('.date', _dialogObj)
	.datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: '',
//		maxDate: '0',
onClose:function(){
			var box_name = $(this).data('box');
			if(box_name == "living_will" || box_name == "healthcare_proxy" || box_name == "care_orders" || box_name == "emergencyplan"){
				var file_date= $(this).val();
				
				if(file_date){
					$('.uploader_tr_'+box_name).show()
				}
			}
		},
		
		onChange:function(){
			var box_name = $(this).data('box');
			if(box_name == "living_will" || box_name == "healthcare_proxy" || box_name == "care_orders" || box_name == "emergencyplan"){
				var file_date= $(this).val();

				if(file_date){
					$('.uploader_tr_'+box_name).show()
				} 
			}
		},
       /* onSelect:function(){
        	
        	var box_name = $(this).data('box');
        	var file_date= $(this).val();
        	if(box_name == "living_will" || box_name == "healthcare_proxy" || box_name == "care_orders" || box_name == "emergencyplan"){
				var file_date= $(this).val();

				
				if(file_date){
					$('.uploader_tr_'+box_name).show()
				} 
			}
           
           
        },*/

		beforeShow : function(e, o) {
			//TODO-2367 Added condition by Ancuta 20.06.2019
			if($(e).hasClass('date_range_150')) {
				$(e).datepicker("option", "yearRange", '1900:+10')
			}			
			if($(e).hasClass('allow_future')) {
				$(e).datepicker("option", "maxDate", "+5y");
			} else {
				$(e).datepicker("option", "maxDate", 0);
			}
		}
	});

	//if altfield&&altformat add datepicker opts
	$('.date', _dialogObj).each(function(){
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
	
	
	
	$('.timepicker')
	.timepicker({
		minutes: {
			interval: 5
		},
		showPeriodLabels: false,
		rows: 4,
		hourText: 'Stunde',
		minuteText: 'Minute'
	});

}

function attachMask (_dialogObj)
{
	if ($('*[data-mask]', _dialogObj).length) {
		
		$('*[data-mask]', _dialogObj).each(function(){
			var _mask = $(this).data('mask');
			$(this).mask(_mask);
		});
	}
}


function attachLivesearch(_dialogObj)
{
	try {
		
//		/**
//		 * example to init with class :
//		 */
//		$('.livesearchFormEvents', _dialogObj).livesearchRemedyAid({
//			'livesearch_class'	: 'livesearch_unified_style myVerySpecialClassForThis',
//		});
		
		
		
		$('.livesearchFormEvents', _dialogObj)
		.livesearchCityZipcode({'limitSearchResults' : 50})
//		.livesearchFamilyDoctor({'limitSearchResults' : 50})
//		.livesearchHealthInsurance({'limitSearchResults' : 50})
//		.livesearchDiacgnosisIcd({'limitSearchResults' : 50})
//		.livesearchSpecialist({'limitSearchResults' : 50})
//		.livesearchCareservice({'limitSearchResults' : 50})
//		.livesearchSupplies({'limitSearchResults' : 50})
//		.livesearchPharmacy({'limitSearchResults' : 50})
//		.livesearchSuppliers({'limitSearchResults' : 50})
//		.livesearchHomecare({'limitSearchResults' : 50})
//		.livesearchPhysiotherapists({'limitSearchResults' : 50})
//		.livesearchVoluntaryworkers({'limitSearchResults' : 50})
//		.livesearchChurches({'limitSearchResults' : 50})
//		.livesearchHospiceassociation({'limitSearchResults' : 50})
//		.livesearchRemedySupplies({'limitSearchResults' : 50})
//		.livesearchRemedyAid({'limitSearchResults' : 50})
//		.livesearchUserVW({'limitSearchResults' : 50})
		.livesearchSapvVerordner({'limitSearchResults' : 50})
 		 
		
		
		
	} catch (e) {
		//console.log(e);
	}
}





function attachDialogAccordion(_parentObj) 
{

	if ($("div.content > div > fieldset", _parentObj).length > 1) {
		
		$("div.content > div", _parentObj)
		.accordion({
			active: 0,
			collapsible: true,
			autoHeight: true,
			header: '> fieldset > legend'
		});
		
		
		$("div.content > div > fieldset", _parentObj)
		.css({
			'border': "0",
			'padding': "0px",
			'margin': "0px",
			'text-indent': "25px",
			'width' : 'auto'
		})
		;
		
		
		$("div.content > div > fieldset > legend", _parentObj)
		.css({
			'width': "100%",
			'text-indent': "25px",
			'padding-top': "5px"
		})
		.addClass('accordion_head');
		
		
		$("div.content > div > fieldset > table", _parentObj)
		.css({'width' : '100%'})
		.addClass('accordion_body')
		;
		
	}
	
}








var entryeditorDialogOnClose = function()
{
	//hide the livesearch
	$(".livesearch_unified_style").hide();
}





function attachBoxAccordion(_parentObj) 
{
	//APC accordion
	if (typeof _parentObj == 'undefined' || $(_parentObj).attr("id") == "box-PatientAcp") {
		if ($("#tab_container_provider #box-PatientAcp").length > 0 ){
			$(".contact_person_accordion, .acp_accordion").accordion({
				active: 0,
				collapsible: true,
				autoHeight: false,
				header: '> fieldset > legend'
			});
			
			$(".acp_accordion")
			.find('legend')
			.addClass('accordion_head');
			
			$(".acp_accordion")
			.find('table')
			.addClass('accordion_body');
			
				
			$("#tab_container_provider #box-PatientAcp .entries .entry").css({
				'min-height' : 'auto',
				'border' : 'none',
				'padding' : '0',
				'margin' : 0
			});
			
			$("#tab_container_provider #box-PatientAcp .entries").css({
				'width' : 'auto',
				'padding' : '2px'
			});
		}
	}
	
	//SapvVerordnung accordion 
	if (typeof _parentObj == 'undefined' || $(_parentObj).attr("id") == "box-SapvVerordnung") {
		if ($("#tab_container_provider #box-SapvVerordnung .entries .entry").length > 1 
			&& $("#tab_container_provider #box-SapvVerordnung .entries").find(".wrapper_selector").length < 1)
		{
	
			var _groups = {};
			var _legends = {};
			
			$("#tab_container_provider #box-SapvVerordnung .entries").accordion("destroy"); 
			$("#tab_container_provider #box-SapvVerordnung .entries .acc_group").remove(); 
			
			
			$("#tab_container_provider #box-SapvVerordnung .entries .entry").each(function(){
				
				var _selector = $(this).find('.selector_divisions');
				var _legend = _selector.parents('.entryline').first();
				var _division = _selector.data('division');
				
				_groups[_division] = _groups[_division] || [];
				
				_legend.addClass('accordion_head');
				
				$(_legend).remove();
				
				_groups[_division].push(this);
				
				_legends[_division] = _legend;
				
				
				if (_division != 1 ) {
					
					$(this).find(".info-button").remove();
				}
				
				$(this).remove();
				
			})
			
			var _entries = $("#tab_container_provider #box-SapvVerordnung .entries");
		 
			$.each(_groups, function(i, item) { 
					
				var accordioGroup = $('<div class="acc_group division_'+i+'" />');
				
				$(_legends[i]).prependTo(accordioGroup);
				
				var accordioBody = null; 
					
				if (i==3 || i == 4) {
					accordioBody = $("<div class='accordion_body wrapper_selector'>"+ $(_legends[i]).html() +"</div>");
				} else {
					accordioBody = $("<div class='accordion_body wrapper_selector'></div>");
					$.each(item, function(){
						$(this).appendTo(accordioBody);
					});
				}
	
				$(accordioBody).appendTo(accordioGroup);
				
				$(accordioGroup).appendTo(_entries);
				
			})
			//move addnew to sapv tab
			var _addnewdiv = _entries.find(" > div.dontPrint > .addbutton").appendTo( _entries.find(".division_1 > .accordion_body")); 
			
			
			
			//move memo buttons last
			var _memodiv = _entries.find(" > div.dontPrint").appendTo(_entries); 
	
			
			
			$("#tab_container_provider #box-SapvVerordnung .entries").accordion({
				active: 0,
				collapsible: true,
				autoHeight: false,
				header: '> .acc_group > .accordion_head'
			});
			
			
		}
	}

	
	
	
	
	
	
	
	if (typeof _parentObj == 'undefined' || $(_parentObj).attr("id") == "box-ContactPersonMaster") {
		if ($("#tab_container_provider #box-ContactPersonMaster .entries .entry").length > 1 
				&& $("#tab_container_provider #box-ContactPersonMaster .entries .entry").find(".wrapper_selector").length < 1) 
		{
			
			
			$("#tab_container_provider #box-ContactPersonMaster .entries").accordion("destroy"); 
			
			$("#tab_container_provider #box-ContactPersonMaster .entries .entry").each(function(){
				
				var _legend = $('div.entryline:eq(0)', this);
				
				_legend.addClass('accordion_head');
				
				$(_legend).remove();
				
				$( this ).wrapInner( "<div class='accordion_body wrapper_selector'></div>" );
				
				$(_legend).prependTo(this);
				
			});
			
			
			$("#tab_container_provider #box-ContactPersonMaster .entries").accordion({
				active: 0,
				collapsible: true,
				autoHeight: false,
				header: '> .entry > .accordion_head'
			});
			
			
			$("#tab_container_provider #box-ContactPersonMaster .entries .entry").css({
				'min-height' : 'auto',
				'border' : 'none',
				'padding' : '0',
				'margin' : 0
			});
			
			$("#tab_container_provider #box-ContactPersonMaster .entries").css({
				'width' : 'auto',
				'padding' : '2px'
			});
		
		} else if ($("#tab_container_provider #box-ContactPersonMaster .entries").length == 1 ) {
			
			$("#tab_container_provider #box-ContactPersonMaster .entries .entry .entryline:eq(0)").hide();
				
		}
		
	}
	
	
	
	
	
}

function copyAddressDialog (_text) {
       $("#versorger-clipboardcopy").find("textarea").empty();
       $("#versorger-clipboardcopy").find("textarea").text(_text);
       $('#versorger-clipboardcopy').dialog({width:"340px",title: translate("address")});
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
					
					var objTab = {};
					objTab[tabname] =  {'qquuid': this.getUuid(id), 'division_tab' : tabname};
					
					/*hack for remove.. hardcoded var*/
					var obj = {'patientDetails' : {'PatientAcp' : objTab }};
					
					inlineEdit_formValidateSubmit('save', holderElement , obj);

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


function remove_ishospiz_ishospizverein_visitors( _thatAction , _this)
{
	jConfirm(translate('removeassignedusershospizv'), '', function(r) {
		if (r) {
			
			/*hack for remove.. hardcoded var*/
			var obj = {'patientDetails' : {'PatientMaster' : {"removeassignedusers" : _thatAction} }};
			
			inlineEdit_formValidateSubmit('save', _this , obj);
			
			return true;
		}
	});
}




function closeSubFormDialog( _this ) 
{	
	$(_this)
//	.parents('.selector_SubFormDialog')	
	.parents('.versorgerEditDialog')	
	.dialog('destroy')
	.remove();
}

function createSubFormDialog( _scriptTemplate_ID , _this)
{
	if ($("#" +_scriptTemplate_ID ).length != 1) {
		return false; //fail-safe
	}
	
	var editor=$('<div class="selector_SubFormDialog catdetails">');
	
	var _category = $(_this).parents('.catdetails').find('.category').val();
	$('<input>', {
		'class'	: 'category',
		'type'	: 'hidden',
		'value'	: _category
	}).appendTo(editor);   
		
	
	
	
	var _html = '';
	_html = $("#" +_scriptTemplate_ID ).html();
	
	$(editor).append(_html);
    
	$(editor).dialog({
    	dialogClass : "versorgerEditDialog catdetails",
    	modal : true,
    	autoOpen : true,
    	closeOnEscape : true,
    	title: translate("Bitte Benutzer zuweisen, die Rechte für diesen Patienten bekommen sollen"), 
    	minWidth : 400,
    	minHeight : 400,
    	
    	beforeClose : function() {
		},
    	close: entryeditorDialogOnClose,
    	
    	open : function() {
    		
    	},
    	
    	buttons : [
					
					//save button
					{
						'class' : "rightButton",
						text : translate('save'),
						click : function() {
							
							inlineEdit_formValidateSubmit('save', this)
//							formValidateSubmit('save');
							
						}
					},
					
					//cancel button
					{
						'class' : "rightButton",
						text : translate('cancel'),
						click : function() {
							
							
							$(this).dialog("close");
							
							closeSubFormDialog(this);
							
						},
						
					}
					
		]
					
	});
	
	return 1;
}

function clientSettings(opt_id, client_options)
{
	var cl_set;
	$.each(client_options, function(key, option){
		cl_set = {};
		if(option.id == opt_id)
		{
			cl_set['localization_available'] = option.localization_available;
			cl_set['name'] = option.name;
			cl_set['days_availability'] = option.days_availability;
			return false;
		}
	});
	return cl_set;
}

//ISPC-2381 Carmen 19.01.2021
function show_aids_extrafields(that){
	
    var belongsTo = $(that).data("belongsto");
    var aidid = $(that).closest("tr").prev().prev().find("select").val();
   
    $.get(appbase + 'ajax/createpatientaidsextrafields?aidid='+aidid+'&belongsTo='+belongsTo, function(result) {
    	
   					//$('.extrafieldsrow').remove();
    					var newFieldset =  $(result).insertAfter($(that).closest("tr"));
    				
    				    
    					//var newFieldset =  $(result).appendTo($('#aidtable'));
    				
		
					$( '.date' ).datepicker({
					dateFormat: 'dd.mm.yy',
					showOn: 'button',
					buttonImage: $('#calImg').attr('src'),
					buttonImageOnly: true,
					changeMonth: true,
					changeYear: true,
					nextText: '',
					prevText: '',
					maxDate: '0',
				}).mask('99.99.9999');
   					
				});
 }
function addnewpatientaids(that, parent_form){
    
	 try{
			
 		var form_table = $(that).closest("table").prev();
			$.get(appbase + 'ajax/createpatientaidsadditionalrow?parent_form='+parent_form, function(result) {
				var newFieldset =  $(result).appendTo($(form_table));
		   		
		   		$( ".date" ).datepicker({
					dateFormat: 'dd.mm.yy',
					showOn: "button",
					buttonImage: $('#calImg').attr('src'),
					buttonImageOnly: true,
					changeMonth: true,
					changeYear: true,
					nextText: '',
					prevText: '',
					maxDate: '0',
				}).mask("99.99.9999");
			});



 } catch (e) {
     console.log(e);
 }
 }
//--







