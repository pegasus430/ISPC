/*
 * versorger.js //Maria:: Migration CISPC to ISPC 22.07.2020
 * requires jquery.changeEventOrder.js
 * requires fine-uploader.js
 * Copy of patientdetails.js, which is a copy of versorger.js
 * Reason:  in contactformblocks it is possible, to have a block, which nedds the versorger.js (for example the block 'fb_versorger'
 * and other blocks, who need the patientdetails.js ('genogram', 'psychosocial care')
 * So we need a unique id for the Editor-Block an other elements like addbutton
 * TODO: clear up the three scripts
 */



var dirty=0;
var current_category = null;

//ISPC-2671 Ancuta 14.09.2020
//var addresses = [];
//var metaCategorys = [];
// ---

$(document).ready(function(){
	
	//ISPC-2672 - hack to omit code id versorger is in allowed blocks
	/*var allowed_blocks_s=JSON.parse(allowed_blocks);
	
	if( $.inArray("versorger", allowed_blocks_s) !== -1 ) {
		return;
	}*/
	//

	

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

	

			
    $(document).on('click', '.fb_versorger-catheader', function(){
        $(this).next().toggle();
        if($(this).hasClass('active')){
            $(this).removeClass('active').addClass('inactive');
        }else{
            $(this).addClass('active').removeClass('inactive');
        }
    });
    /*
    $(document).on('click','.fb_versorger-livesearch-entry', function(){
        var json_str=$(this).find('.json-data').val();
        var json_data=JSON.parse(json_str);
        var cat=$('#fb_versorger-entryeditor').prop('category');
        var mymap=mappings[cat];
        var cols=mymap['cols'];
        for (var i=0; i<cols.length; i++){
            var colname=cols[i]['class'];
            $('#fb_versorger-entryeditor .content').find('input[name="'+colname+'"]').val(json_data[colname]);
        }
        $('input[name="_just_update"]').val(0);
    });
    */
    
//    function editordialog(entrydata, cat, p_elem){
	function editordialog(entrydata_ID, cat, p_elem){
    	
		current_category = cat;
		
		dirty=0;
        
        var editor=$('<div>');

        var _html = '';
        
        if (entrydata_ID != null && p_elem != null) {
        	_html = $('script#' + cat + '_editDialogHtml_' + entrydata_ID).html();
        } else {
        	_html = $('script#' + cat + '_addnewDialogHtml').html();
        	
        	//ISPC-2381 Carmen 13.01.2021
            if(current_category == 'PatientTherapy' ){
           	 dirty=1;
            }
            //--
            
        }
        
        
        $(editor).append(_html);
        
        
        $('#fb_versorger-contactform-entryeditor .content').empty();
        $('#fb_versorger-contactform-entryeditor .content').append(editor);

        
        $("input", $('#fb_versorger-contactform-entryeditor .content')).each(function(){
        	var _el = $(this).get(0).addInputChangedByJsListener(function(){
        		dirty = 1;
        	});
        });
        
        
        $("input, select, textarea", $('#fb_versorger-contactform-entryeditor .content')).change(function(){
            dirty=1;
        });
        
        
        $('#fb_versorger-contactform-entryeditor').prop('category',cat);

//        if(p_elem==null){
//            $('#fb_versorger-contactform-entryeditor .delbutton, #fb_versorger-contactform-entryeditor .clipboard-button').hide();
//        } else {
//        	$('#fb_versorger-contactform-entryeditor .delbutton, #fb_versorger-contactform-entryeditor .clipboard-button').show();
//        }

        $("#fb_versorger-clipboardcopy").dialog('close');
        
        $('#fb_versorger-contactform-entryeditor').dialog({
        	dialogClass : "fb_versorgerEditDialog",
        	modal : true,
        	autoOpen : true,
        	closeOnEscape : true,
        	title: translate("Eintrag einsehen/ändern"), 
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
        		
        		attachDialogEvents($('#fb_versorger-contactform-entryeditor'));
        		
        		//sapv is bigger
        		if ($(this).find('fieldset.create_form_patient_sapv_verordnung').length == 1 ) {
        			$(this).parents(".fb_versorgerEditDialog").css("width", "740px");
        		}
        		

        		if(p_elem == null) {
        			$(this).parent().find('.delbutton, .clipboard-button').hide();
    	        } else {
    	        	$(this).parent().find('.delbutton, .clipboard-button').show();
    	        }  
        		
        		
        		if (cat == 'PatientMaster' || cat == 'PatientHospizverein') {
        			$(this).parent().find('.delbutton').hide();
        		}
        		
        		
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
         

        $("#fb_versorger-contactform-entryeditor form").off('submit');
        
        $("#fb_versorger-contactform-entryeditor form").submit(function(e) {

			$('#fb_versorger-entryeditor').dialog('close');
//        	$('#fb_versorger-contactform-entryeditor').dialog('close');
        	var _category = cat;
        	
        	e.preventDefault();
        	
            if(dirty==1 && checkclientchanged()) {
            	
            	
            	var _data = $("#fb_versorger-contactform-entryeditor form").serializeObject() || {};
            	
            	var _action = _data.__action || 'savePatientDetails';
            	
            	_data.__action = _action;
            	_data.__category = _data.__category || _category;
            	
            	//ISPC-2381 Carmen 13.01.2021
            	var aerrart = 0;            	
            	if(_data.__category == 'PatientAids')
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
	    					$('#fb_versorger-contactform-entryeditor').block({
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
		            		$('#fb_versorger-contactform-entryeditor').unblock();
	            	    },
	            	    
	                    success: function (response, request) {
	                    	
	                    	if (response.hasOwnProperty("success") && response.success) {
	                    		
	                    		dirty=0;
	                    		
	                    		$('#fb_versorger-contactform-entryeditor').dialog('close');
	                    		
	                    		
	                    		$.each(response.data, function(cat_as_key, cat_data) {
		                    		
	                    			if (cat_as_key == '__meta-timestamp' || cat_as_key == '__meta-categorys') {
	                    				//this will utilized in a separate $.each()
	                    				return;
	                    			}
	                    			
	                    			var _category = cat_as_key;
	                    			
		                    		//reset old
	                    			//Carmen 04.01.2021- hanging after edit in contactperson
		                    		//$("#box-" + _category).find('.entries .entry, .entry_extra_wrapper').remove();
	                    			$("#box-" + _category + " .entry").remove();
	                    			$("#box-" + _category + " .entry_extra_wrapper").remove();
		                    		//--                    		
		                    		addresses[_category] = {};
		                    		
		                    		//add new
		                    		var _entries = [];
		                    		
	//	                    		$.each(response.data, function(key, entry) {
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
		                    		
		                    			//Carmen 04.01.2021
		                    			//$(_entries).prependTo("#box-" + _category + " .entries");
		                    			$("#box-" + _category + " .entries").prepend(_entries);
		                    			//--
		                    			if (_category in metaCategorys && ! metaCategorys[_category]['multipleEntries']) {
		                        			$("#box-" + _category).find('.addbutton-contactform').hide();
		                        		}
		                    			
		                    			attachBoxEvents( $("#box-" + _category));
		                    			
		                    		} else {
		                    			$("#box-" + _category).find('.addbutton-contactform').show();
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
	                    		
	                    		$('#fb_versorger-contactform-entryeditor .content div').empty();
	                    		$('#fb_versorger-contactform-entryeditor .content div').append($(response.editDialogHtmlWithErrors));
	                    		
	                    		attachDialogEvents($('#fb_versorger-contactform-entryeditor'));
	                    		
	                    		
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
            }
            
        });
        
        
        

        function formValidateSubmit ( _step ) {
        	
        	
        	if (window.patientIsDischarged == 1) {
        		
        		//check - alert patient IsDischarged
        		jConfirm(translate('[The patient is already discharged, are you sure you want to make this change ?]'), '', function(r) {
        			if (r) {
        				
        				if (_step == 'save') {
        					
        					$( "#fb_versorger-contactform-entryeditor form" ).trigger( "submit" );
        					
        				} else if (_step == 'delete') {
        					jConfirm(translate('[Are you sure you want to delete this ?]'), '', function(r) {
        						if (r) {
        							dirty = 1;
        							
        			       			$('<input>', {
        			       				name	: '__action',
        			       				type	: 'hidden',
        			       				value	: 'deletePatientDetails'
        			       			}).appendTo('#fb_versorger-contactform-entryeditor form .content');
        			       			
        			       			$( "#fb_versorger-contactform-entryeditor form" ).trigger( "submit" );
        						}
        					});
        				}
        				
        				return true;
        			}
        		});
        		
        		
        		
        	} else {
        		
        		if (_step == 'save') {
        			
        			$( "#fb_versorger-contactform-entryeditor form" ).trigger( "submit" );
        			
        		} else if (_step == 'delete') {
        			jConfirm(translate('[Are you sure you want to delete this ?]'), '', function(r) {
        				if (r) {
        					dirty = 1;
        					
        	       			$('<input>', {
        	       				name	: '__action',
        	       				type	: 'hidden',
        	       				value	: 'deletePatientDetails'
        	       			}).appendTo('#fb_versorger-contactform-entryeditor form .content');
        	       			
        	       			$( "#fb_versorger-contactform-entryeditor form" ).trigger( "submit" );
        				}
        			});
        		}
        	}

        	return false;
        }
        
        
        
        
        
        

    }

    $(document).on('click', '.fb_psy_stat span.addbutton-contactform', function(){

        var cat=$(this).parents('.fb_psy_stat').find('.category').val();
        var entrydata={};
        current_category = cat;
        
        editordialog(entrydata, cat, null);
    });

    
    $(document).on('click', '.fb_versorger .info-button', function(){
    	
    	if ($(this).parents('.entry_relocate').length > 0) {
    		var p = $(this).parents('.entry_relocate')
    	} else {
    		var p = $(this).parents('.entry');
    		
    	}

    	var cat = $(p).parents('.fb_psy_stat').find('.category').val();
    	
        var entrydata_ID = $(p).find('.hidden-json-data').val();
        editordialog(entrydata_ID, cat, p);

    });
    

    $(document).on('click', '.fb_psy_stat .memobutton', function(){
        var cat=$(this).parents('.fb_psy_stat').find('.category').val();
        
        if(memos!=undefined && cat in memos) {
            var mydata = memos[cat];
        }else{
            mydata={memo:'',color:'none'};
        }
        
        var _title = translate("Memo for") + " " + translate('[' + cat + ' Box Name]');
        
        $('#fb_versorger-memo input[name="color"]').filter('input[value="'+mydata['color']+'"]').prop('checked',true);
        $('#fb_versorger-memo').find('textarea').val(mydata['memo']);
        $('#fb_versorger-memo').find('input[name="__category"]').val(cat);
        $('#fb_versorger-memo').dialog({width:"400px",title: _title});
    });

    
    $(document).on('click', '.fb_versorger-memo-button', function(){
        var memo=$('#fb_versorger-memo').find('textarea').val();
        var color=$('#fb_versorger-memo input[name="color"]:checked').val();
        var cat=$('#fb_versorger-memo input[name="__category"]').val();
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
        $('#fb_versorger-memo').dialog('close');
        var url = appbase + "patientnew/patientdetails?" + "id=" + idpd ;
        
        var _mdata=$('#fb_versorger-memo form').serializeObject() || {};
        _mdata.__action = "updateMemos";
        _mdata.__category = cat;
        
        $.post(url, _mdata);
    });
    
    

    $(document).on('click', '.fb_psy_stat .historybutton, a.allhistorybutton', function() {
    	
        var cat=$(this).parents('.fb_psy_stat').find('.category').val() || '';
        
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
            		var editor=$('<div class="selector_historyBox fb_psy_stat">');
            		
            		
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
            	    	dialogClass : "fb_versorgerEditDialog fb_psy_stat",
            	    	modal : true,
            	    	autoOpen : true,
            	    	closeOnEscape : true,
            	    	title: (cat != '' ? translate('[' + cat + ' Box Name]') : '') + " " + translate("History"),  
            	    	minWidth : 600,
            	    	minHeight : 400,
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
        
        $('#fb_versorger-memo input[name="color"]').filter('input[value="'+mydata['color']+'"]').prop('checked',true);
        $('#fb_versorger-memo').find('textarea').val(mydata['memo']);
        $('#fb_versorger-memo').find('input[name="__category"]').val(cat);
        $('#fb_versorger-memo').dialog({width:"400px",title: _title});
    });
    
    
    attachBoxEvents();
    	    
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
		$("#box-PatientLocation").find('a.addbutton-contactform').appendTo($('#box-PatientLocation span.memobutton').parent());
	}
	
	
	/*TODO all this should have been from the ZF .. because of this box all the ajax.success must be changed ?*/
	if ($("#box-PatientMaintainanceStage").length && $("#box-PatientMaintainanceStage .entry_extra_wrapper").length < 1) { 
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
		handle: 'div.fb_versorger-catheader > div.headtitle',
		cursor: 'move',
		
		placeholder: 'boxsort_placeholder',
		forcePlaceholderSize: true,
		
		opacity: 0.4,
		
		stop: function(event, ui){
			//$(ui.item).find('div.fb_versorger-catheader').click();
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
		handle: 'div.fb_versorger-catheader > div.headtitle',
		cursor: 'move',
		
		placeholder: 'boxsort_placeholder',
		forcePlaceholderSize: true,
		
		opacity: 0.4,
		
		stop: function(event, ui){
			//$(ui.item).find('div.fb_versorger-catheader').click();
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
	
	var ajax_inlineEdit = $(_this).parents('.fb_psy_stat').data('ajax_inlineEdit') || false;
	var inlineEdit_formValidateSubmit_Delayed_Timeout = $(_this).parents('.fb_psy_stat').data('inlineEdit_formValidateSubmit_Delayed_Timeout') || false;
	
	
	if (ajax_inlineEdit !== false) {
		ajax_inlineEdit.abort();
	}
	
	if (inlineEdit_formValidateSubmit_Delayed_Timeout !== false) {
		clearTimeout(inlineEdit_formValidateSubmit_Delayed_Timeout);
	}

	inlineEdit_formValidateSubmit_Delayed_Timeout = setTimeout(function(){
		inlineEdit_formValidateSubmit_Delayed(_step, _this, _extraObject);
	}, 2000);
	
	$(_this).parents('.fb_psy_stat').data('inlineEdit_formValidateSubmit_Delayed_Timeout', inlineEdit_formValidateSubmit_Delayed_Timeout); 
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
	var _category = $(_this).parents('.fb_psy_stat').find('.category').val(),
	ajax_inlineEdit;
	
	if (_category != '') {		
		current_category = _category;
	} else {
		_category = current_category;
	}
	
	var _data = $(_this).parents('.fb_psy_stat').find('input, select, textarea').serializeObject() || {};
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
        	$(_this).parents('.fb_psy_stat').block({
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
	    	
	    	$(_this).parents('.fb_psy_stat').unblock();
	    	
	    	closeSubFormDialog(_this);
	    },
	    
	    
	    success: function (response, request) {
        	
	    	$(_this).parents('.fb_psy_stat').unblock();
	    	
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
                			$("#box-" + _category).find('.addbutton-contactform').hide();
                		}
            			
            			attachBoxEvents( $("#box-" + _category));
            			
            		} else {
            			$("#box-" + _category).find('.addbutton-contactform').show();
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
	
    
    $(_this).parents('.fb_psy_stat').data('ajax_inlineEdit', ajax_inlineEdit); 
	
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
			var inlineEdit_formValidateSubmit_Delayed_Timeout = $(this).parents('.fb_psy_stat').data('inlineEdit_formValidateSubmit_Delayed_Timeout') || false;
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
	//		var _addnewdiv = _entries.find(" > div.dontPrint > .addbutton-contactform").appendTo( _entries.find(".division_1 > .accordion_body"));
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
       $("#fb_versorger-clipboardcopy").find("textarea").empty();
       $("#fb_versorger-clipboardcopy").find("textarea").text(_text);
       $('#fb_versorger-clipboardcopy').dialog({width:"340px",title: translate("address")});
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
	.parents('.fb_versorgerEditDialog')	
	.dialog('destroy')
	.remove();
}

function createSubFormDialog( _scriptTemplate_ID , _this)
{
	if ($("#" +_scriptTemplate_ID ).length != 1) {
		return false; //fail-safe
	}
	
	var editor=$('<div class="selector_SubFormDialog fb_psy_stat">');
	
	var _category = $(_this).parents('.fb_psy_stat').find('.category').val();
	$('<input>', {
		'class'	: 'category',
		'type'	: 'hidden',
		'value'	: _category
	}).appendTo(editor);   
		
	
	
	
	var _html = '';
	_html = $("#" +_scriptTemplate_ID ).html();
	
	$(editor).append(_html);
    
	$(editor).dialog({
    	dialogClass : "fb_versorgerEditDialog fb_psy_stat",
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






