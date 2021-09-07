;/* versorger.js */



$(document).ready(function(){
/*
    var mappings=<?php echo json_encode($this->mappings);?>;
    var memos=<?php echo json_encode($this->memos);?>;
*/
	
	$( 'div.versorger-catheader' ).on( 'click', function() {
//    $(document).on('click', '.versorger-catheader', function(){
    	
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
	function editordialog(entrydata_ID, cat, p_elem){
    	
    	
//        var mymap=mappings[cat];
//        var cols=mymap['cols'];
        
//        var pcols=mymap['patientmapping']['addcols'];
        var pcols=null;
        
        var dirty=0;
        
        //TODO
//        var address= mymap['address'];
        
        
//        if(pcols!=undefined) {
//            cols = cols.concat(pcols);
//        }

        var editor=$('<div>');

        var _html = '';
        
        if (entrydata_ID != null && p_elem != null) {
        	_html = $('#' + cat + '_editDialogHtml_' + entrydata_ID).html();
        } else {
        	_html = $('#' + cat + '_addnewDialogHtml').html();
        }
        
        
        $(editor).append(_html);
        
        
        
        
        /*
        

        var row=$("<input type='hidden' name='_id'>").val(entrydata['_id']);
        $(editor).append(row);

        var row=$("<input type='hidden' name='_pid'>").val(entrydata['_pid']);
        $(editor).append(row);

        var row=$("<input type='hidden' name='_category'>").val(cat);
        $(editor).append(row);

        var row=$("<input type='hidden' name='_just_update'>").val(1);
        $(editor).append(row);
*/
        
        $('#versorger-entryeditor .content').empty();
        $('#versorger-entryeditor .content').append(editor);
        
        $("input, select, textarea", $('#versorger-entryeditor .content')).change(function(){
            dirty=1;
        });
        
        
        $('#versorger-entryeditor').prop('category',cat);

        if(p_elem==null){
            $('#versorger-entryeditor .delbutton, #versorger-entryeditor .clipboard-button').hide();
        } else {
        	$('#versorger-entryeditor .delbutton, #versorger-entryeditor .clipboard-button').show();
        }

        $("#versorger-clipboardcopy").dialog('close');
        
        $('#versorger-entryeditor').dialog({
        	dialogClass : "versorgerEditDialog",
        	modal : true,
        	autoOpen : true,
        	closeOnEscape : true,
        	title: translate("Eintrag einsehen/ändern"), 
        	width:"630px",
        	minWidth : 630,
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
        		
        		if(p_elem == null) {
        			$(this).parent().find('.delbutton, .clipboard-button').hide();
    	        } else {
    	        	$(this).parent().find('.delbutton, .clipboard-button').show();
    	        } 
        		
        		//TODO-3813 Lore 19.03.2021
        		if (cat == 'PatientHealthInsurance' && entrydata_ID != 0) {
        			$(this).parent().find('.delbutton').hide();
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
   				        	}
   				        	
   				        	$(this).dialog("close");
   				        	
   				            $("#versorger-clipboardcopy").find("textarea").empty();
   				            $("#versorger-clipboardcopy").find("textarea").text(_text);
   				            $('#versorger-clipboardcopy').dialog({width:"340px",title: translate("address")});
   							
   						},
   					
   					},
   					//delete button
   					{
   						'class' : "delbutton leftButton",
   						text : translate('Delete versorger'),
   						click : function() {
   							jConfirm(translate('[Are you sure you want to delete this ?]'), '', function(r) {
   								if(r)
   								{
   									dirty = 1;
   									
   				        			$('<input>', {
   				        				name	: '__action',
   				        				type	: 'hidden',
   				        				value	: 'deleteVersorger'
   				        			 }).appendTo('#versorger-entryeditor form .content');
   									
   						            $("#versorger-entryeditor form").submit();
   						            
   								} else {
   									
   								}
   							});
   						},
   						
   					},
   					
   					//save button
   					{
   						'class' : "rightButton",
   						text : translate('save'),
   						click : function() {
   							$( "#versorger-entryeditor form" ).trigger( "submit" );
   							//$("#versorger-entryeditor form").submit()
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
         

        $("#versorger-entryeditor form").off('submit');
        
        $("#versorger-entryeditor form").submit(function(e) {
            
//        	$('#versorger-entryeditor').dialog('close');
        	var _category = cat;
        	
        	e.preventDefault();
        	
            if(dirty==1 && checkclientchanged()) {
            	
            	
            	var _data = $("#versorger-entryeditor form").serializeObject() || {};
            	
            	var _action = _data.__action || 'saveVersorger';
            	
            	_data.__action = _action;
            	_data.__category = _data.__category || _category;
            	
            	var _url = appbase +"patientnew/versorger?" + "id=" + window.idpd;
        		
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
                    		
                    		//reset old 
                    		$("script[id^='" + _category + "_editDialogHtml_']").remove();
                    		$("#box-" + _category).find('.entries .entry').remove();
                    		addresses[_category] = {};
                    		
                    		//add new
                    		var _entries = [];
                    		
                    		$.each(response.data, function(key, entry) {
                    			
                    			if (key == 'addnewDialogHtml') return;

                    			if ( ! entry.hasOwnProperty("editDialogHtml") || entry.editDialogHtml == null) return;
                    			
                    			
                    			addresses[_category][key] = entry.address;
                        		
                    			var _script = $('<script>', {
                    				type	: 'text/template',
                    				id		: _category + '_editDialogHtml_' + key,
                    				html	: entry.editDialogHtml
                    			 }).appendTo('body');
                    			
                    			
                    			var _entrylines = [];
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
                    					var _entryline = $('<div>', {
	                    					'class'	: 'entryline',
	                    					html 	: [
	                    					     	   $('<span>', {'text' : _line[1], 'class' :  'entrydetail'})
	                    					     	   ]
	                        			});
                    				}
                    				_entrylines.push(_entryline);                   	            
                    			});
                    			
                    			var _infobutton = $('<div>', {
                					'class'	: 'info-button',
                					html 	: [$("<img>", {"src" : appbase + 'images/info_med.png'}), " " + translate('Details')]
                    			});
                    			_entrylines.push(_infobutton);

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
                    			
                    			if ( ! metaCategorys[_category]['multipleEntries']) {
                        			$("#box-" + _category).find('.addbutton').hide();                        			
                        		}
                    			
                    			attachBoxEvents();
                    			
                    		} else {
                    			$("#box-" + _category).find('.addbutton').show();
                    		}
                    		
                    		
                    		
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
            
        });
        
        
        /*
        $("#versorger-entryeditor form .delbutton").click(function(e) {
        	
			jConfirm(translate('[Are you sure you want to delete this?]'), '', function(r) {
				if(r)
				{
					dirty=1;
					
        			$('<input>', {
        				name	: '__action',
        				type	: 'hidden',
        				value	: 'deleteVersorger'
        			 }).appendTo('#versorger-entryeditor form .content');
					
		            $("#versorger-entryeditor form").submit();
		            
				} else {
					
				}
			});
			return false;
			
        });
        */
        /*
        $("#versorger-entryeditor .clipboard-button").click(function(e) {
            
        	var _text = '';
        	var _lineDelimiter = "";
        	
        		
        	if(typeof(addresses[cat]) != 'undefined' && typeof(addresses[cat][entrydata_ID]) != 'undefined') {
        		_text = addresses[cat][entrydata_ID];
        	}
        	
        	$("#versorger-entryeditor").dialog('close');
            $("#versorger-clipboardcopy").find("textarea").empty();
            $("#versorger-clipboardcopy").find("textarea").text(_text);
            $('#versorger-clipboardcopy').dialog({width:"340px",title: translate("address")});
        	
        });
        */
        

    }

    $(document).on('click', '.catdetails .addbutton', function(){

        var cat=$(this).parents('.catdetails').find('.category').val();
        var entrydata={};

        editordialog(entrydata, cat, null);
    });

    /*
    $(document).on('click', '.versorger .info-button', function(){
        var p=$(this).parents('.entry');
        var jsonstr=$(p).find('.hidden-json-data').val();
        var cat=$(p).parents('.catdetails').find('.category').val();
        var entrydata=JSON.parse(jsonstr);

        editordialog(entrydata, cat, p);

    });
    */
    
   $(document).on('click', '.versorger .info-button', function(){
        
    	var p = $(this).parents('.entry');
    	var cat = $(p).parents('.catdetails').find('.category').val();
    	
        var entrydata_ID = $(p).find('.hidden-json-data').val();
        editordialog(entrydata_ID, cat, p);
        

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
        $('#versorger-memo').dialog({width:"440px", title: _title});
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
        var url = appbase + "patientnew/versorger?" + "id=" + idpd ;
        
        var _mdata=$('#versorger-memo form').serializeObject() || {};
        _mdata.__action = "updateMemos";
        _mdata.__category = cat;
        
        $.post(url, _mdata);
    });
    
    
    attachBoxEvents();
	    
});

function attachBoxEvents()
{
	try {
		attacBoxAccordion();
	} catch(e) {
		console.log(e);
	}
	

	try {
		attachBoxSortable();
	} catch(e) {
		console.log(e);
	}
}



function attachDialogEvents(_dialogObj) 
{
	try {
		attachDate(_dialogObj);		
	} catch(e) {
		console.log(e);
	}
	
	try {
		attachLivesearch(_dialogObj);
	} catch(e) {
		console.log(e);
	}
	
	try {
		attachDialogAccordion(_dialogObj);
	} catch(e) {
		console.log(e);
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
		beforeShow : function(e, o) {
			if($(e).hasClass('allow_future')) {
				$(e).datepicker("option", "maxDate", "+1y");
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
}

function attachLivesearch(_dialogObj)
{
	try {
		$('.livesearchFormEvents', _dialogObj).livesearchCityZipcode();
	
		$('.livesearchFormEvents', _dialogObj).livesearchFamilyDoctor();
	
		$('.livesearchFormEvents', _dialogObj).livesearchHealthInsurance();
	
		$('.livesearchFormEvents', _dialogObj).livesearchDiacgnosisIcd();
	
		$('.livesearchFormEvents', _dialogObj).livesearchSpecialist();
	
		$('.livesearchFormEvents', _dialogObj).livesearchCareservice();
	
		$('.livesearchFormEvents', _dialogObj).livesearchSupplies();

		$('.livesearchFormEvents', _dialogObj).livesearchPharmacy();

		$('.livesearchFormEvents', _dialogObj).livesearchSuppliers();

		$('.livesearchFormEvents', _dialogObj).livesearchHomecare();
		
		$('.livesearchFormEvents', _dialogObj).livesearchPhysiotherapists();

		$('.livesearchFormEvents', _dialogObj).livesearchVoluntaryworkers();

		$('.livesearchFormEvents', _dialogObj).livesearchChurches();

		$('.livesearchFormEvents', _dialogObj).livesearchHospiceassociation();
		
		$('.livesearchFormEvents', _dialogObj).livesearchRemedySupplies();

		/**
		 * example to init the class :
		 */
		$('.livesearchFormEvents', _dialogObj).livesearchRemedyAid({
			'livesearch_class'	: 'livesearch_unified_style myVerySpecialClassForThis',
		});
		
	} catch (e) {
		console.log(e);
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

//ISPC-2667 Lore 22.09.2020
function show_kind_ins_extrafields(checkvals){

	if(checkvals == true){
		$('.kind_ins_view_extrafields').each(function() {
			$(this).attr('style', '');
		  }
		);
	}else{
		
		if( $('#patientProvider-PatientCareInsurance-kind_ins_legally').is(':checked') || 
				 $('#patientProvider-PatientCareInsurance-kind_ins_private').is(':checked') || 
				 $('#patientProvider-PatientCareInsurance-kind_ins_no').is(':checked') ||
				 $('#patientProvider-PatientCareInsurance-kind_ins_others').is(':checked') 
				 ){
					$('.kind_ins_view_extrafields').each(function() {
						$(this).attr('style', '');
					  }
					);
			}else {
				$('.kind_ins_view_extrafields').each(function() {
					$(this).attr('style', 'display:none;');
				  }
				);
			} 
	}
	
}
//.


function attacBoxAccordion(_parentObj) 
{
	if ($("#tab_container_provider #box-PatientHealthInsurance .entries .entry").length > 1 
			&& $("#tab_container_provider #box-PatientHealthInsurance .entries .entry").find(".wrapper_selector").length < 1) 
	{
		
		
		$("#tab_container_provider #box-PatientHealthInsurance .entries").accordion("destroy"); 
		
		$("#tab_container_provider #box-PatientHealthInsurance .entries .entry").each(function(){
			
			var _legend = $('div.entryline:eq(0)', this);
			
			_legend.addClass('accordion_head');
			
			$(_legend).remove();
			
			$( this ).wrapInner( "<div class='accordion_body wrapper_selector'></div>" );
			
			$(_legend).prependTo(this);
			
		});
		
		
		$("#tab_container_provider #box-PatientHealthInsurance .entries").accordion({
			active: 0,
			collapsible: true,
			autoHeight: true,
			header: '> .entry > .accordion_head'
		});
		
		
		$("#tab_container_provider #box-PatientHealthInsurance .entries .entry").css({
			'min-height' : 'auto',
			'border' : 'none',
			'padding' : '0',
			'margin' : 0
		});
		
		$("#tab_container_provider #box-PatientHealthInsurance .entries").css({
			'width' : 'auto',
			'padding' : '2px'
		});
		
	
	}
}






function attachBoxSortable()
{
	//provider_left  will be 201
	//provider_right will be 202
	
	
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
			
			var _url = appbase +"patientnew/versorger?" + "id=" + window.idpd;
			
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
			
			var _url = appbase +"patientnew/versorger?" + "id=" + window.idpd;
			
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



