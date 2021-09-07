;
/* 
 * versorger.js
 * requires jquery.changeEventOrder.js
 * requires fine-uploader.js
 * modified by Carmen on 22.01.2020 for ISPC-2508
 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
 */



var dirty=0;
var current_category = null;

$(document).ready(function(){


	

	/*
	 * @cla - i overload HTMLInputElement , for.. contactPersons get address from patient => isdirty
	 * example from here: //https://stackoverflow.com/questions/42427606/event-when-input-value-is-changed-by-javascript
	 * 
	 */
	HTMLInputElement.prototype.addInputChangedByJsListener = function(cb) {
	    if(!this.hasOwnProperty("_inputChangedByJSListeners")) {
	        this._inputChangedByJSListeners = [];
	    }
	    this._inputChangedByJSListeners.push(cb);
	}

	var valueDescriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value");

	Object.defineProperty(HTMLInputElement.prototype, "value", {
	    get: function() {
	        return valueDescriptor.get.apply(this, arguments);
	    },
	    set: function() {
	        var self = this;
	        valueDescriptor.set.apply(self, arguments);
	        
	        if(this.hasOwnProperty("_inputChangedByJSListeners")){
	            this._inputChangedByJSListeners.forEach(function(cb) {
	                cb.apply(self);
	            })
	        }
	    }
	});
    //ISPC-2703, elena, 05.01.2021
	$('.versorger-left, .versorger-right, .versorger-third').addClass('versorger_sortable');

	
	
    $(document).on('click', '.versorger-catheader', function(){
        $(this).next().toggle();
        if($(this).hasClass('active')){
            $(this).removeClass('active').addClass('inactive');
        }else{
            $(this).addClass('active').removeClass('inactive');
        }
        //TODO-3842,elena,09.02.2021
		adjustContainers();
    });

	//TODO-3842,Elena,09.02.2021
	console.log('width',$('#tab_container_provider').width());
	if($('#tab_container_provider').width() < 1200 && $('#tab_container_provider').width() > 800){
		$('#provider_third').css('margin-left', '-800px');
		console.log('height left', $('#provider_left').height());
		$('#provider_third').css('margin-top', $('#provider_left').height());
		setTimeout(function(){
			$('#provider_third').css('margin-top', $('#provider_left').height());
		}, 1000);



	}
	function adjustContainers(){
		if($('#tab_container_provider').width() < 1200 && $('#tab_container_provider').width() > 800){
			$('#provider_third').css('margin-top', $('#provider_left').height());
			setTimeout(function(){
				$('#provider_third').css('margin-top', $('#provider_left').height());
			}, 1000);
	}

	}
	$( window ).resize(function() {
		console.log('on resize');
		if($('#tab_container_provider').width() < 1200 && $('#tab_container_provider').width() > 800){
			$('#provider_third').css('margin-left', '-800px');
			$('#provider_third').css('margin-top', $('#provider_left').height());
		}else{
			$('#provider_third').css('margin-left', '0');
			$('#provider_third').css('margin-top', '0');
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
	// default parameter only in ES6, but we need to support ie11
	function editordialog(entrydata_ID, cat, p_elem, action){
    	
		if(action===undefined){
			action=null;
		}

		current_category = cat;
		
		dirty=0;
        
        var editor=$('<div>');

        var _html = '';
        
        if (entrydata_ID != null && p_elem != null && cat != 'PatientArtificialEntriesExits') {
        	_html = $('script#' + cat + '_editDialogHtml_' + entrydata_ID).html();
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
        	_html = $('script#' + cat + '_addnewDialogHtml').html();
        	  
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

        
        $("input", $('#versorger-entryeditor .content')).each(function(){
        	var _el = $(this).get(0).addInputChangedByJsListener(function(){
        		dirty = 1;
        	});
        });
        
        
        $("input, select, textarea", $('#versorger-entryeditor .content')).change(function(){
            dirty=1;
        });
       
        if(action == 'refresh' || action == 'remove')
        {
        	dirty=1;
        }
        //ISPC-2432 Ancuta 21.01.2020 - enable passage to next  modal  to activate modal
        if(current_category == 'MePatientDevices' ){
        	 dirty=1;
        }
        //
        
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
        	title: tr_Title,//ISPC-2801 Ancuta 18.01.2021 
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
        			$(this).parents(".versorgerEditDialog").css("width", "740px");
        		}
        		

				//ISPC-2539, elena, 29.10.2020
        		$('#select_primary').hide();
				$('#select_secondary').hide();
				$('.set_new_status_verordnung').on('click', function(e){
					$('#select_primary').toggle();
				});
        		$('.set_new_status_verordnung_2').on('click', function(e){
					$('#select_secondary').toggle();
				});

        		$('.status_remove').on('click', function(){
        			var journalid = $(this).attr('data-journalid');
        			console.log(journalid);
        			var itemrows = $(this).parents('tr.verordnung_history_item');
        			console.log(itemrows);
						jConfirm('Möchten Sie den Eintrag wirklich entfernen?', 'Verlaufseintrag entfernen', function(r) {
							if(r)
							{
								$.ajax({
									type: 'POST',
									url:  appbase + '/ajax/removeverordnungjournal?id=' + pat_id ,
									data: {
										journalid: journalid,

									},

									success:function(data){
										//console.log(data);
										$(itemrows).remove();
										var answer = jQuery.parseJSON(data);


									},
									error:function(){
										//var ajax_done = "1";
									}
								});

							}
						});

				});


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
   								//ISPC-2508 Carmen 19.05.2020 new design
   								if(action == 'refresh' || action == 'remove')
   								{
   									formValidateSubmit('save', action);
   								}
   								else
   								{
   									formValidateSubmit('save');
   								}
   								//--
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
        	$('#patient_actions_modal').dialog('close');
        	var buttons = $('#versorger-entryeditor').dialog('option', 'buttons');
        	$('#versorger-entryeditor').dialog('close');
        	buttons[1].click.apply($('#versorger-entryeditor'));
        }
      //ISPC-2508 Carmen 19.05.2020 new design
        /*if(action == 'refresh')
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
        }*/
        //--
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
									//ISPC-2694, elena, 08.01.2021
		                    		$("#box-" + _category).find('.entry_anamnese').html('');
		                    			                    		
		                    		addresses[_category] = {};
		                    		
		                    		//add new
		                    		var _entries = [];
		                    		
	//	                    		$.each(response.data, function(key, entry) {
	                    			$.each(cat_data, function(key, entry) {
										//ISPC-2694, elena, 08.01.2021
										if(_category == 'Anamnese'){
											//console.log(_category);
											//console.log(entry);
											//console.log(entry.meta);
											//console.log(entry.meta.addDataHTML);
											var _script = $('<script>', {
												type	: 'text/template',
												id		: _category + '_addDataHTML_' + key,
												html	: entry.meta.addDataHTML
											}).appendTo('body');
											$("#box-" + _category).find('.entry_anamnese').append($(entry.meta.addDataHTML));
										}

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
			                    			$("script#" +_category + '_editDialogHtml_extra_remove_' + key).remove(); //ISPC-2508 Carmen 19.05.2020 new design
			                    			
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
			                    			//ISPC-2508 Carmen 19.05.2020 new design
			                    			//add new editbox content
			                    			var _script = $('<script>', {
			                    				type	: 'text/template',
			                    				id		: _category + '_editDialogHtml_extra_remove_' + key,
			                    				html	: entry.editDialogHtml_extra_remove
			                    			 }).appendTo('body');
			                    			//--
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
		                    				if(_category == 'PatientArtificialEntriesExits' && firstartif === true)
			                    			{
		                    					
		                    					var _arttitle = $('<div>', {
				                					'class'	: 'artificial_title',
				                					html 	: [translate('artificial_entries')]
				                    			});
				                    			_entrylines.push(_arttitle);
			                    			}
		                    				else if(_category == 'PatientArtificialEntriesExits' && firstartif === false)
		                    				{
		                    					var _arttitle = $('<div>', {
				                					'class'	: 'artificial_title',
				                					html 	: [translate('artificial_exits')]
				                    			});
				                    			_entrylines.push(_arttitle);
		                    				}
		                    				
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
			                    				if($(this).is(':empty') || $(this).text().indexOf('justNotEmpty') >= 0)
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
	                    		
	                    	
	                    		//ISPC-2432 Ancuta 21.01.2020
	                    		
	                           	if (response.hasOwnProperty("device_data") && response.device_data) {
	                           		var __device_data = response.device_data;
	                           		
	                           		if(__device_data.active == "no" && (__device_data.allow_photo_upload != ""  ||  __device_data.MePatientDevicesSurveys != undefined) ){
		                           		$('#qr_device_modal')
		                    			.data('device_data',__device_data)
		                    			.dialog('open');
	                           		} else {
	                           			window.location.reload();//redirect to self
	                           		}
	                           	}
	                           	
	                           	//--
	        
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
        
        
        
    	// default parameter only in ES6, but we need to support ie11
        function formValidateSubmit ( _step, _action) {
        	
	        if(action===undefined){
				action=null;
			}
        	 
        	if (window.patientIsDischarged == 1) {
        		
        		//check - alert patient IsDischarged
        		jConfirm(translate('[The patient is already discharged, are you sure you want to make this change ?]'), '', function(r) {
        			if (r) {
        				
        				if (_step == 'save') {
        					//ISPC-2508 Carmen 19.05.2020 new design
        					if(_action == 'refresh' || _action == 'remove')
        					{
        						$('<input>', {
            	       				name	: '__action',
            	       				type	: 'hidden',
            	       				value	: 'savePatientDetails'
            	       			}).appendTo('#versorger-entryeditor form .content'); 
            	       			
            	       			$('<input>', {
            	       				name	: '__subaction',
            	       				type	: 'hidden',
            	       				value	: _action
            	       			}).appendTo('#versorger-entryeditor form .content');
        					}
//--
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
                			jConfirm(translate('[Are you sure you want to remove this ?]'), '', function(r) {
                				if (r) {
                					dirty = 1;
                					
                	       			$('<input>', {
                	       				name	: '__action',
                	       				type	: 'hidden',
                	       				//value	: 'deletePatientDetails'
                	       				value	: 'savePatientDetails'
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
        			//ISPC-2508 Carmen 19.05.2020 new design
        			if(_action == 'refresh' || _action == 'remove')
					{
						$('<input>', {
    	       				name	: '__action',
    	       				type	: 'hidden',
    	       				value	: 'savePatientDetails'
    	       			}).appendTo('#versorger-entryeditor form .content'); 
    	       			
    	       			$('<input>', {
    	       				name	: '__subaction',
    	       				type	: 'hidden',
    	       				value	: _action
    	       			}).appendTo('#versorger-entryeditor form .content');
					}
        			//--
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
    		editordialog(entrydata_ID, cat, p);
    	}
    	else
    	{
    		var entrydata_ID = $(this).find('.hidden-json-data').val();
    		//ISPC-2508 Carmen 19.05.2020 new design
    		$('#patient_charts_actions_modal').data('recid', entrydata_ID).data('openfrom', 'patientdetails').dialog('open');
    	
    		/*$('#patient_actions_modal').dialog({

				autoOpen: true,
				modal: true,
				maxWidth: 400,
				maxHeight: 400,
				width: 300,
				height: 350,
				
				close: function() {
					$('#patient_actions_modal').html('');
				},
				open: function() {
					var url = appbase + 'ajax/modalactions';
	
					$.get(url, function(result) {
						var newFieldset =  $('#patient_actions_modal').append(result);
						
				});
					
					 jQuery('.ui-widget-overlay').on('click', function () {
						 $('#patient_actions_modal').dialog('close');
			            });
				},

				buttons: [{
						text: translate('cancel'),
						click: function() {
							$(this).dialog("close");
						}
					},
				]
			});*/
    		//--
    	}
    	
    	//ISPC-2508 Carmen 19.05.2020 new design
    	/*if($(this).find('[name="action"]').val() == 'delete')
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
    	}*/
    	
    	$(document).off('click', '.artificial_action').on('click', '.artificial_action', function(){

			if($(this).val() != 'edit')
			{
				editordialog(entrydata_ID, cat, p, $(this).val());
			}
        	else
        	{
        		editordialog(entrydata_ID, cat, p);
        	}
        	
        	//$('#patient_actions_modal').dialog('close');
			$('#patient_charts_actions_modal').dialog('close');
    	});
    	//--
    	
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
    
    
// ISPC-2432 Ancuta 12.03.2020
    $(document).on('click', '.send_now_button', function() {
    	send_push_now($(this));
    });
//--     
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
    	    
    
//ISPC-2432 Ancuta 21.01.2020
    $('#qr_device_modal').dialog({
    	dialogClass : "qr_device_modal",
    	modal : true,
    	autoOpen : false,
    	closeOnEscape : true,
    	title: translate("mePatient_activate_device"), 
    	minWidth : 620,
    	minHeight : 300,
    	
    	beforeClose : function() {
			// return false; // don't allow to close
		},
		close : function(event, ui) {
			//MEP-72 Ancuta 05.02.2020
			$('#device_name').html();
    		$('#device_id').val('');
    		$('#device_internal_id').val('');
    		
    		$('#activation_code').val('');
    		$('#device_password').val('');
    		$('.qr_code').html('');
    		
		},
    	
    	open : function() {
    		var device_data  = $(this).data('device_data');
    		$('#device_name').html(device_data.device_name);
    		$('#device_id').val(device_data.id);
    		$('#device_internal_id').val(device_data.device_internal_id);
    	 
			var _data = {};
			_data.__device_data = device_data;
			
			
			var _device_id = device_data.id;

			$('#refreshQr').hide();
			$('#refreshQr').data('device_id',device_data.id);
			qrCode(_device_id);
 
    	},
    	
    	buttons : [
    ////ISPC-2432 Carmen 04.06.2020
	//refresh button
	{
		'class' : "refreshbutton leftButton",
		'id' : 'refreshQr',
		text : 'refresh',
		click : function() {
			var device_id = $('#refreshQr').data('device_id');
	    	qrCode(device_id);			
		},
		
	},
			//save button
			{
				'class' : "rightButton",
				text : translate('save'),
				click : function() {
					
					if ( $('#activation_code').val() != "" && $('#device_password').val() != "") {   		
						
						var _url = appbase +"ajax/activatedevice?" + "pid=" + window.idpd;
	
						var _data = $("#form_activate_device").serializeObject() || {};
						_data.__action = 'activateDevice';
	
						$.ajax({
					        dataType	: "json",
					        type		: "POST",
					        url			: _url,
					        data		: _data,
						
					        success: function (response, request) {
		                    	if (response.hasOwnProperty("success") && response.success) {
		                    		dirty=1;
		                    		$('#versorger-entryeditor').dialog('close');
		                    		$('#qr_device_modal').dialog('close');
		                    		window.location.reload();//redirect to self
		                    	} else {
		                    		alert(response.msg)
		                    		
		                    	}
		                    }
						});
						
					} else{
						alert('Enter activation code');
					}
				}
			},
 
    		//cancel button
			{
				'class' : "rightButton",
				text : translate('cancel'),
				click : function() {
				
					dirty = 1;
		   			$('<input>', {
		   				name	: '__action',
		   				type	: 'hidden',
		   				value	: 'deletePatientDetails'
		   			}).appendTo('#versorger-entryeditor form .content');   
		   			
		   			$('<input>', {
		   				name	: 'patientDetails[MePatientDevices][id]',
		   				type	: 'hidden',
		   				value	: $('#device_id').val(),
		   			}).appendTo('#versorger-entryeditor form .content');   
		   			
		   			
		   			$( "#versorger-entryeditor form" ).trigger( "submit" );	
		
		    		$('#versorger-entryeditor').dialog('close');
		    		$('#qr_device_modal').dialog('close');
		   			
		   			
				},
				
			}
		]
					
	});
    $('#send_push_modal').dialog({
    	dialogClass : "send_push_modal",
    	modal : true,
    	autoOpen : false,
    	closeOnEscape : true,
    	title: translate("send_push_modal"), 
    	minWidth : 620,
    	minHeight : 200,
    	
    	beforeClose : function() {
    		// return false; // don't allow to close
    	},
    	close : function(event, ui) {
    		// dialog was closed
    	},
    	
    	open : function() {
//    		var device_data  = $(this).data('device_data');
//    		$('#device_name').val(device_data.device_name);
//    		$('#device_id').val(device_data.id);
    		$('#push_comment').val("");
    		$('.send_Noti_NOW').removeAttr('disabled');
    	},
    	
    	buttons : [
    		//ISPC-2801 Ancuta 18.06.2020 - buttons 
    		//save button
    		{
    			'class' : "rightButton send_Noti_NOW",//Ancuta 27.02.2020 - stop for double subminiting
    			text : translate('send_now'),
    			click : function() {
    				
    				$('.send_Noti_NOW').attr('disabled', 'disabled');//Ancuta 27.02.2020 - stop for double subminiting
    				
    				if ($('#push_comment').val() != "") {   		
    					
    					var _url = appbase +"ajax/sendpushnotification?" + "pid=" + window.idpd;
    					
    					var _data = $("#send_push_notifications").serializeObject() || {};
    					_data.__action = 'sendPushNow';
    					
    					$.ajax({
    						dataType	: "json",
    						type		: "POST",
    						url			: _url,
    						data		: _data,
    						
    						success: function (response, request) {
    							if (response.hasOwnProperty("success") && response.success) {
    								dirty=0;
    								//$('#versorger-entryeditor').dialog('close');
    								$('#send_push_modal').dialog('close');
    								
    								//window.location.reload();//redirect to self
    							} else{
    								alert(translate('Something_went_wrong:: Notification not sent') );
    								$('.send_Noti_NOW').removeAttr('disabled');
    							}
    						} 
    					});
    					
    				} else{
    					alert(translate('Enter notification message'));
    					$('.send_Noti_NOW').removeAttr('disabled');
    				}
    			}
    		},
    		//cancel button
    		{
    			'class' : "rightButton",
    			text : translate('cancel'),
    			click : function() {
    				$(this).dialog("close");
    				window.location.reload();//redirect to self
    			},
    			
    		},
    		//--
    		]
    	
    });
    
    //Qr refresh button
  //ISPC-2432 Carmen 04.06.2020
   /* $(document).on('click', '#refreshQr', function(e){
    	e.preventDefault();
    	var device_id = $(this).data('device_id');
    	qrCode(device_id);
    	
    });*/
    //--
    $(document).on('keyup', '#device_password', function(e){
    	e.preventDefault();
    	var dpass = $(this).val();
    	if(dpass.length >0){
    		validate_device_inputs($(this),dpass,'password');
    	}
    });
    $(document).on('blur', '#device_password', function(e){
    	e.preventDefault();
    	var dpass = $(this).val();
    	if(dpass.length >0){
    		validate_device_inputs($(this),dpass,'password');
    	}
    });
    //-- 
     
    
    //wfwfweffwefA2
    
    
});

function attachBoxEvents( _parentObj )
{
	try {
		attachBoxAccordion(_parentObj);
	} catch(e) {
//		console.log(e);
	}
	
	try {
		$('.qq_file_uploader_placeholder').each(function(){
			uploader_create(this, ['pdf','docx','doc']);
		});		
	} catch (e) {
//		console.log(e);
	}
	
	
	try {
		attachDate(_parentObj);
	} catch(e) {
//		console.log(e);
	}
	
	
	
	try {
		inlineEdit_attachInputEvents(_parentObj);
	} catch(e) {
//		console.log(e);
	}
	
	
	try {
		attachBoxSortable(_parentObj);
	} catch(e) {
//		console.log(e);
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
	if ($("#box-PatientMaintainanceStage").length && $("#box-PatientMaintainanceStage .entry_extra_wrapper").length < 1) { 
		$("#box-PatientMaintainanceStage").find('div.entry').each(function(){
			$(this).find("div.info-button").appendTo($("div.entryline:first", this)).addClass('info-button_2').css({"position": "relative", "float": "right" , "top":"-2px"}).find('span').css({"position": "relative",  "top":"-2px"}); //ISPC-2703, elena, 04.01.2021
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
	//provider_third will be 103 //ISPC-2703, elena, 04.01.2021
	
	
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
		
    	connectWith: '.versorger_sortable', //ISPC-2703, elena, 05.01.2021
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
//	.disableSelection()
	;
	//ISPC-2703, elena, 04.01.2021
 	$('#provider_third').sortable({

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
//	.disableSelection()
	;
}


function inlineEdit_formValidateSubmit( _step, _this, _extraObject )
{
	
	var ajax_inlineEdit = $(_this).parents('.catdetails').data('ajax_inlineEdit') || false;
	var inlineEdit_formValidateSubmit_Delayed_Timeout = $(_this).parents('.catdetails').data('inlineEdit_formValidateSubmit_Delayed_Timeout') || false;
	
	
	if (ajax_inlineEdit !== false) {
		ajax_inlineEdit.abort();
	}
	
	if (inlineEdit_formValidateSubmit_Delayed_Timeout !== false) {
		clearTimeout(inlineEdit_formValidateSubmit_Delayed_Timeout);
	}

	inlineEdit_formValidateSubmit_Delayed_Timeout = setTimeout(function(){
		inlineEdit_formValidateSubmit_Delayed(_step, _this, _extraObject);
	}, 2000);
	
	$(_this).parents('.catdetails').data('inlineEdit_formValidateSubmit_Delayed_Timeout', inlineEdit_formValidateSubmit_Delayed_Timeout); 
}



function inlineEdit_formValidateSubmit_Delayed( _step, _this , _extraObject) 
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
	var _category = $(_this).parents('.catdetails').find('.category').val(),
	ajax_inlineEdit;
	
	if (_category != '') {		
		current_category = _category;
	} else {
		_category = current_category;
	}
	
	var _data = $(_this).parents('.catdetails').find('input, select, textarea').serializeObject() || {};
	var _action = _data.__action || 'savePatientDetails';
	
	_data.__action = _action;
	_data.__category = _category;
	
	$.extend(true, _data, _extraObject);//append/overwrite _data
	
	var _url = appbase +"patientnew/patientdetails?" + "id=" + window.idpd;
	
	ajax_inlineEdit =  $.ajax({
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
				message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>',
//				
//				onBlock : function (){
//				},
//				onUnblock : function (){
//				},
				
				focusInput: false,
				
				baseZ : 1000,
				fadeIn :100,
				fadeOut :200,
				timeout :0,			// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock 
				ignoreIfBlocked: true,
				
			});
	    },
	    complete: function (jqXHR) {
	    	
	    	$(_this).parents('.catdetails').unblock();
	    	
	    	closeSubFormDialog(_this);
	    },
	    
	    
	    success: function (response, request) {
        	
	    	$(_this).parents('.catdetails').unblock();
	    	
        	if (response.hasOwnProperty("success") && response.success) {
        		//TODO
        		
        		$.each(response.data, function(cat_as_key, cat_data) {
        			
        			var _category = cat_as_key;
        			
        			if (_category == '__meta-timestamp' || _category == '__meta-categorys') {
        				//this will utilized in a separate $.each()
        				return;
        			}
        			
        			if (response['data']['__meta-categorys'][_category].hasOwnProperty("inlineEdit") && response['data']['__meta-categorys'][_category]['inlineEdit']) {            				
        				if ( ! response['data']['__meta-categorys'][_category].hasOwnProperty("inlineEdit_reload") || ! response['data']['__meta-categorys'][_category]['inlineEdit_reload']) {   
        					return;
        				}
        			}
        			
        			
            		//reset old
            		$("#box-" + _category).find('.entries .entry, .entry_extra_wrapper').remove();
            		
            		addresses[_category] = {};
            		
            		
            		//add new
            		var _entries = [];
            		
        			$.each(cat_data, function(key, entry) {

        				if (key == 'addnewDialogHtml') return;

            			if ( ! entry.hasOwnProperty("editDialogHtml") || entry.editDialogHtml == null) return;
            			
            			
            			
            			addresses[_category][key] = entry.address;
                		
            			//reset old
            			
            			                    			
            			//remove the previous one
            			$("script#" +_category + '_editDialogHtml_' + key).remove();
            			//add new editbox content
            			var _script = $('<script>', {
            				type	: 'text/template',
            				id		: _category + '_editDialogHtml_' + key,
            				html	: entry.editDialogHtml
            			 }).appendTo('body');
            			
            			
            			var _entrylines = [];
            			
            			if (entry.meta.hasOwnProperty("inlineEdit") && entry.meta.inlineEdit) {            				
            				if (entry.meta.hasOwnProperty("inlineEdit_reload") && entry.meta.inlineEdit_reload) {            					
	            				if (entry.editDialogHtml != null) {
	            					_entrylines.push(entry.editDialogHtml); 
	            				} else {
	            					_entrylines.push(response['data']['__meta-categorys'][_category]['addnewDialogHtml']);
	            				} 
            				}
            				
            			} else {
            				
                			$.each(entry.extract, function(_i, _line) {
                				
                				if (_line[0] != null) {	                    				
                    				var _entryline = $('<div>', {
                    					'class'	: 'entryline',
                    					html 	: [
                    					     	   $('<label>', {'text' : _line[0]}) , 
                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail'})
                    					     	   ]
                        			});
                				} else {
                					if ( _category in metaCategorys && metaCategorys[_category].hasOwnProperty("extractEscape") && ! metaCategorys[_category]['extractEscape']) {
                						var _entryline = $('<div>', {
	                    					'class'	: 'entryline',
	                    					html 	: [
	                    					     	   $('<span>', {'html' : _line[1], 'class' :  'entrydetail'})
	                    					     	   ]
	                        			});
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
                		
                			
                			var _infobutton = $('<div>', {
            					'class'	: 'info-button',
            					html 	: [$("<img>", {"src" : appbase + 'images/info_med.png'}), " " + translate('Details')]
                			});
                			_entrylines.push(_infobutton);
            			}
            			

            			var _hiddenjsondata = $('<input>', {
            				type	: "hidden",
            				'class'	: 'hidden-json-data',
            				value 	: key
            			});
            			_entrylines.push(_hiddenjsondata);
            			
            			var _entry = $('<div>', {
        					'class'	: 'entry',
        					html 	: _entrylines
            			});
            			_entries.push(_entry);
            		});
        			
        			
            		if (_entries.length > 0) {
            			
            			$(_entries).prependTo("#box-" + _category + " .entries");
            			
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
	
    
    $(_this).parents('.catdetails').data('ajax_inlineEdit', ajax_inlineEdit); 
	
}

var inlineEdit_Timeout = null;

function inlineEdit_attachInputEvents()
{
	
	/*
	 * r.c. 1 , using $.bindLast so we fire first the other onChange and onFocusout
	 */
	$.each($('input:checkbox, input:radio, select', $('.inlineEdit')).not('.inlineEdit_not_onChange'), function () {
		if ( ! $(this).isBoundNamespaced('change.inlineEdit')) {
			var _this = this;
			$(this).bindLast('change.inlineEdit', function() {
				setTimeout(function() {inlineEdit_formValidateSubmit('save', _this);}, 100);		
			});
		}
	});
		
	$.each($('input:text, textarea', $('.inlineEdit')).not('.inlineEdit_not_onFocusout'), function () {
				
		$(this).bindFirst('focusin.inlineEdit', function() {
			var inlineEdit_formValidateSubmit_Delayed_Timeout = $(this).parents('.catdetails').data('inlineEdit_formValidateSubmit_Delayed_Timeout') || false;
			if (inlineEdit_formValidateSubmit_Delayed_Timeout != false) {
				clearTimeout(inlineEdit_formValidateSubmit_Delayed_Timeout);
			}
		});
		
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
	
	//ISPC-2694,elena,17.12.2020
	try {
		attachAnamneseEvents(_dialogObj);
	}catch(e) {
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
	
	try {
		attachCustomEvents(_dialogObj);
	} catch(e) {
		//console.log(e);
	}
	
	
	
	
	
	
}


//function prefill_contact_person_btn(_that)
//{
//	if ($("input[name$=\"\[cnt_street1\]\"]", $(_that).parents("table")).val() == '' 
//		&& $("input[name$=\"\[cnt_zip\]\"]", $(_that).parents("table")).val()  == '' 
//		&& $("input[name$=\"\[cnt_city\]\"]", $(_that).parents("table")).val()  == ''  )
//	{
//		var _parent = $($('script#PatientMaster_editDialogHtml_0').html());
//		var _street1 = $(_parent).find("input[name$='\[street1\]']").val();
//		var _zip = $(_parent).find("input[name$='\[zip\]']").val();
//		var _city = $(_parent).find("input[name$='\[city\]']").val();
//		
//		$("input[name$=\"\[cnt_street1\]\"]", $(_that).parents("table")).val(_street1);
//		$("input[name$=\"\[cnt_zip\]\"]", $(_that).parents("table")).val(_zip);
//		$("input[name$=\"\[cnt_city\]\"]", $(_that).parents("table")).val(_city);	
//	}
//}

function attachCustomEvents(_dialogObj) 
{
	/*
	 * add here very special js for box ... js that is needed only on this page, and not allways on the box 
	 * 
	 */
	
//	if ( ! $(".prefill_contact_person_btn" , $(_dialogObj)).isBoundNamespaced('click')) {
//		console.log("ss1");
//		var _this = $(".prefill_contact_person_btn" , $(_dialogObj));
//		$(_this).bindLast('click', function() {
//			console.log("ss2");
//			setTimeout(function(){prefill_contact_person_btn(_this);}, 100);
//		});
//	}
//	
	
}
//ISPC-2694, elena, 17.12.2020
function attachAnamneseEvents(_dialogObj){

	$('.datepicker').datepicker();
	$('#group_anamnese').tabs();

	$('.radio_details').on('change', function(e){
		console.log($(this));
		console.log($(this).next('textarea'));
		var details_area = $(this).next('textarea');
		details_area.removeClass('closed').addClass('opened');;
	})
	$('.radio_nodetails').on('change', function(e){
		console.log($(this));
		var elname = $(this).attr('name');
		console.log(elname);
		$('input[type=radio][name="'+ elname + '"]').next('textarea').removeClass('opened').addClass('closed');

		//details_area.hide();
	})


	$('.checkbox_details').on('change', function(e){
		var area = $(this).parent().siblings('td').find('textarea');
		//console.log(area);
		if($(this).is(':checked')){
			area.removeClass('closed').addClass('opened');
		}else{
			area.removeClass('opened').addClass('closed');
		}


	})


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
		/*
        onSelect:function(){
        	if($(this).data('box') != 'undefined'){
        	var box_name = $(this).data('box');
        	var file_date= $(this).val();
        	if(box_name == "living_will" || box_name == "healthcare_proxy" || box_name == "care_orders" || box_name == "emergencyplan"){
				var file_date= $(this).val();

				
				if(file_date){
					$('.uploader_tr_'+box_name).show()
				} 
			}
        	}
           
        },*/
		beforeShow : function(e, o) {
			//TODO-2367 Added condition by Ancuta 20.06.2019
			if($(e).hasClass('date_range_150')) {
				$(e).datepicker("option", "yearRange", '1900:+10')
			}
			
			if($(e).hasClass('allow_future')) {
				$(e).datepicker("option", "maxDate", "+5y"); //todo, change this to data-maxDate
			} else {
				$(e).datepicker("option", "maxDate", 0);
			}
			
			if($(e).hasClass('disable_past')) {
				$(e).datepicker("option", "minDate", 0);
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
	
	//this also does a tone of element relocating
	
	//APC accordion
	if (typeof _parentObj == 'undefined' || $(_parentObj).attr("id") == "box-PatientAcp") {
		if ($("#tab_container_provider #box-PatientAcp").length > 0 ){
			
			var _accordion_active = $("#tab_container_provider #box-PatientAcp").data('accordion_active') || 0;
			
			$(".contact_person_accordion, .acp_accordion").accordion({
				active: _accordion_active,
				collapsible: true,
				autoHeight: false,
				header: '> fieldset > legend',
				
				change: function (event, ui) {
					$("#tab_container_provider #box-PatientAcp").data('accordion_active', $(event.target).accordion("option", 'active'));
			    },
			
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
	
//	
//	if (_parentObj && $(_parentObj).attr('id') == "box-SapvVerordnung") {
//		
//		console.log(_parentObj);
//	}
//	
	
	if (typeof _parentObj == 'undefined' || $(_parentObj).attr("id") == "box-SapvVerordnung") {
		//SapvVerordnung accordion 
		if ($("#tab_container_provider #box-SapvVerordnung .entries .entry").length > 1 
			//&& $("#tab_container_provider #box-SapvVerordnung .entries").find(".wrapper_selector").length < 1
				)
		{
	
	//		console.log("SapvVerordnung accordion");
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
	//		var _addnewdiv = _entries.find(" > div.dontPrint > .addbutton").appendTo( _entries.find(".division_1 > .accordion_body"));
			//move memo buttons last
			var _memodiv = _entries.find(" > div.dontPrint").appendTo(_entries); 
	
			
			var _accordion_active = $("#tab_container_provider #box-SapvVerordnung").data('accordion_active') || 0;
			
			$("#tab_container_provider #box-SapvVerordnung .entries").accordion({
				active: _accordion_active,
				collapsible: true,
				autoHeight: false,
				header: '> .acc_group > .accordion_head',
				
				change: function (event, ui) {
					$("#tab_container_provider #box-PatientAcp").data('accordion_active', $(event.target).accordion("option", 'active'));
			    },
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
				
				if (_legend.find('label').length) {
					//this is not the name.. if another field... set as empty
					_legend = $("<div class='entryline accordion_head'><span class='entrydetail'> - </span></div>");
				} else {
					
					_legend.addClass('accordion_head');
					$(_legend).remove();
				}
				
				
				
				$( this ).wrapInner( "<div class='accordion_body wrapper_selector'></div>" );
				
				$(_legend).prependTo(this);
				
			});
			
			var _accordion_active = $("#tab_container_provider #box-ContactPersonMaster").data('accordion_active') || 0;
			
			$("#tab_container_provider #box-ContactPersonMaster .entries").accordion({
				active: _accordion_active,
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
					var obj = {'patientDetails' : {'PatientAcp' : objTab }}; //asta este si nu era undefined
					
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
/* ISPC-2432 */

function send_push_now (that){
		$('#send_push_modal')
		.dialog('open');
}

function qrCode(_device_id){
	
	$('#refreshQr').hide();
	
	// get qr code :: every time qr is requested new db line is added 
	var url = appbase + 'mepatient/deviceqrcode?pateintid=' + idpd ;

	var _data = {};
	_data.__device_id = _device_id;

	xhr = $.ajax({
		url : url,
		data: _data,
		cache: false ,
		success : function(response) {
			$('.qr_code').html(response);
		}
	});
		
	// the code is no longer valid after 3 minutes
	 setTimeout(function() {
		 //ISPC-2432 Carmen 04.06.2020
		 //$('.qr_code').html('<br/><div class="loadingdiv" align="center"><img src="'+res_path+'/images/ajax-loader.gif"><br />'+translate('Qr code expired please press refresh button to generate a new one')+'</div><br/>');
		 $('.qr_code').html('<br/><div align="center">'+translate('Qr code expired please press refresh button to generate a new one')+'</div><br/>');
		 //--
		 $('#refreshQr').show();
	}, 5*60*1000);
	 
}

function validate_device_inputs(_that,input_text,input_type){
	
	var url = appbase + 'mepatient/validatedeviceinput';

	var _data = {};
	_data.__input_type = input_type ;
	_data.__text = input_text;

	xhr = $.ajax({
		url : url,
		data: _data,
		cache: false ,
		success : function(response) {
			var response_data=JSON.parse(response);
			
			if (response_data.valid ==0) {
				_that.addClass('input_error')
				$('#device_error').html(response_data.msg);
			} else{
				_that.removeClass('input_error').addClass('input_success');
				$('#device_error').html('');
				
				 setTimeout(function() {
					 _that.removeClass('input_success');
				}, 3000);
			}
		}
	});
	
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

