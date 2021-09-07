/**
 * btmbuchhistory.js
 */


//used as sprintf( xxx %1% xxx)
	if (!String.prototype.format) {
	  String.prototype.format = function() {
	    var args = arguments;
	    return this.replace(/\%(\d+)\%/g, function(match, number) { 
	      return typeof args[number] != 'undefined'
	        ? args[number]
	        : match
	      ;
	    });
	  };
	}
	
	var vwlist; // this will be the datatable object

	$(document).ready(function() {
		
		window.vwlist = drawDatatable();


	});
	
	var timer_redraw;
	
	function redrawDatatable( keep_page ){
		
		var resetPaging = true;
		
		if (keep_page === true) {
			resetPaging = false;
		}
		
		window.clearTimeout(timer_redraw);
			
		timer_redraw = window.setTimeout(function () {
			window.vwlist.ajax.reload(null, resetPaging);
		},800);
		
		
	}
	
	function getTimestamp()
	{
		return new Date().getTime();
	}
	
	function drawDatatable() {
		var btm_datatable = $('#btm_history_dtable').DataTable({
			// ADD language
			"language": {
				"url": appbase+"/javascript/data_tables/de_language.json"// + "?-" + getTimestamp()
			},
			sDom: 
				'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
				't'+
				'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"ip<"#bottom_export">>',
			
				
			"lengthMenu": [[50, 150, 300], [50, 150, 300]],
			
						
			"pageLength": 50,
			
			//"bLengthChange": false,
			
			"bFilter": false,
			"bSort" : false,
			
			"autoWidth": true,

			"pagingType": "full_numbers",

			

			"scrollX": true,
			"scrollCollapse": true,
				
			'serverSide': true,
			"bProcessing": true,
			
			'processing': true,
			"bServerSide": true,
		    
		    "sAjaxSource": window.location.href,
		    "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
		      
		    	if (oSettings.jqXHR) {
					oSettings.jqXHR.abort();
				}
		    
		    	
				$("form#filter input[name^='filteron']:checked").each(function(){
					aoData.push({ "name": "filteron[]", "value": this.value });
				});
				
				$("form#filter input[name^='tablefilter']:checked").each(function(){
					aoData.push({ "name": "tablefilter[]", "value": this.value });
				});

				aoData.push({ "name": "length", "value": oSettings._iDisplayLength });
				aoData.push({ "name": "start", "value":  oSettings._iDisplayStart });
				aoData.push({ "name": "ajax_btm_history", "value": "1" });
				aoData.push({ "name": "selectuser", "value": $("#selectuser").val() });
				aoData.push({ "name": "selectyear", "value": $("#selectyear").val() });
				
				
				
		    	oSettings.jqXHR = $.ajax( {
			        "dataType": 'json',
			        "type": "POST",
			        "url": sSource,
		       		"data": aoData,
		       		"success": fnCallback
		      } );
		    },
					
			
			columns: [
			          { data: "id", className: "", name:"id", title:"#" , "width": "10px" },
			          
			          { data: "type", visible: false, className: "", name:"debugcolumn", title:"debug"  },
			           
			           { data: "entrydate", className: "", name:"entrydate", title:translate('entrydate') +"<br>"  + translate('btmbuchhistory_lang')['action_initiation'] , "width":"20%"  },
			           { data: "medication", className: "", name:"medikation", title: translate('medikation')  , "width":"20%"  },
			           { data: "qty", className: "", name:"count", title: translate('count') , "width":"10px"   },
			           { data: "action", className: "", name:"action", title: translate('action') , "width":"10%"  },
			           { data: "process", className: "", name:"process", title: translate('process') },
			          
			           { data: "btm_correction", visible: false, className: "", name:"btm_correction", title: translate('btmbuchhistory_lang')['btm_correction'], "width": "10px" }
			          
					 ],
			//order: [[ 0, "asc" ]],		 
			
			"fnDrawCallback": function( oSettings ) {
				 
				
				btm_datatable.column(0, {}).nodes().each( function (cell, i) {
			            cell.innerHTML = oSettings._iDisplayStart + i+1;
			     });  
				
			
				
				btm_datatable.columns.adjust();
			} ,
				
			"initComplete": function( settings, json ) {
			    $('#bottom_export').append($("#print_pdf_btn_div").html()).addClass("clearer");
			    
			    
			    if (btm_special_user === true) {
			    	var column = btm_datatable.column( "btm_correction:name" );
			    	if (column) {
			    		column.visible( true );
			    	}
			    }
			    //debug column
			    if ( typeof(DEBUGMODE) !== 'undefined' &&  DEBUGMODE === true) {
			    	var column = btm_datatable.column( "debugcolumn:name" );
			    	if (column) {
			    		column.visible( true );
			    	}
			    }
			    
			  },
			
			 
			"stateSave": false,
			/*
			"stateLoadCallback": function (settings)
			{
				 o = false;
				
				 $.ajax( { 
					url: appbase + 'user/loadtablepref',
					method: "POST",
				    "async": true,
				    data: { page: "btmbuchhistory" },
				    "dataType": "json",
				    "success": function (json) {
				    	if (typeof json !== 'undefined' && json !== null
					    		
					    ){
					    	
				    		//&& typeof json.columns !== 'undefined' && json.columns !== null
				    		//&& typeof json.colOrder !== 'undefined' && json.colOrder !== null
				    		
				    		o = json;
				    		
					    }
				    	
				    	
				    }
				 });
				 
				 return o; 
			},
				
			"stateSaveCallback": function (settings, data) {
				
				data.page = "btmbuchhistory";
				data.results = data.length;
				
			    // Send an Ajax request to the server with the state object
			    $.ajax( {
			      "url": appbase + "ajax/saveuserpageresults",
			      "data": data,
			      "dataType": "json",
			      "type": "POST",
			      "success": function () {}
			    } );
			}
			
			*/
						
		});
		
		return btm_datatable;
	}
	
	

	function correction(_this)
	{
		
		var rowIdx = vwlist.cell(  $(_this).closest('td') ).index().row;
		var rows = vwlist.rows( rowIdx ).data();
		var data = rows[0];
		
		if (typeof data !== 'object') {
			return false;
		} 
		
		//console.log( data); 
		//return;

		var correction_dialog_text = translate('btmbuchhistory_lang')["correction dialog text"];
		correction_dialog_text = correction_dialog_text.format(correction_dialog_text, data.process, data.eventdate, data.eventuser, data.medication, data.qty );

		
		$('#correction_dialog').dialog({
			
			dialogClass: "correction_dialog",
		    autoOpen: false,
		    closeOnEscape: true,
		    open: function () {
		    	
		    	$(".correction_info", this).html(correction_dialog_text);
		    	$("#amount", this).val("");
		    	$("#comment", this).val("");
		    	
		    },
		    
		    beforeClose: function () {
		        //return false; // don't allow to close
		    },
		    
		    close: function (event, ui) {
				//dialog was closed
		    },

		    buttons: [{
		        text: translate('btmbuchhistory_lang')["correction save"],
		        click: function () {

		        	var amount = $("#amount", $(this).dialog()).val();
		        	var comment = $("#comment", $(this).dialog()).val();
		        	
		        	var _this_button = this;
		        	
		        	if (validatePositiveInteger(amount) && checkclientchanged() ) {
		        		//submit with ajax the change?
		        				
		        		var _post_data = {
				        	"table": data.table_name,
			       			"id": data.row_id ,
			       			"amount" : amount , 
			       			"comment" : comment , 
			       			"old_amount" : data.amount,
			       			"old_methodid": data.methodid,
			       			"old_medicationid": data.medicationid,
			       			"old_done_date": data.eventdate,
			       			"action":"correction",
			       			"datatables_data" : data
			       		};
		        				
		        		validatePositiveStock(_post_data);
		        		
		        				
		        	} else {
		        		setTimeout(function () {alert(translate('btmbuchhistory_lang')["only positive amount"]);},50);
		        	}
		        	
		        	
		        },

		    },
		    //cancel button
		    {
		        text: translate('cancel'),
		        click: function () {
		        	$(this).dialog("close");
		        },

		    }
		    
		    ],
		        
		    modal: true,
		    title: translate('btmbuchhistory_lang')["correction dialog tile"],
		    minWidth: 550,
		    minHeight: 250,
		});
		
		
		//$this->view->translate('btmbuchhistory_lang')['correction add event'];
		
		$('#correction_dialog').dialog('open');
		return;

	}
	
	function validatePositiveInteger( testNumber )
	{    

		return testNumber.match(/^\d+$/) && parseInt(testNumber) >= 0;
		
	}
	
	
	function validatePositiveStock(_post_data) 
	{

		if (typeof _post_data !== 'object') {
			return false;
		} 
		_post_data.action = "correction_validatePositiveStock";
		
		var vresult = false;

		var correction_event_negative_amount_text = translate('btmbuchhistory_lang')['correction_event_negative_amount_text'];

		$.ajax({
	        "dataType": 'json',
	        "type": "POST",
	        "url": appbase + "medication/btmbuchhistory",
	        "async" : false,
	        "data": _post_data,
       		"success": function( data ){ 

       			if (data.result === true) {
       				vresult = true;
       			} else {
       				vresult = false;
       				correction_event_negative_amount_text = correction_event_negative_amount_text.format(correction_event_negative_amount_text, data.break_stock_row.break_date );

       			}
       		},
       		"error" : function(xhr, ajaxOptions, thrownError){
       			vresult = false;
       		}
      	});
		
		if ( vresult ) {
			
			_post_data.action = "correction";
			perform_ajax( _post_data );
			
		} else {
			//do a jconfirm, user asumes responsability for breaking the "stock" amount
			
			jConfirm(correction_event_negative_amount_text, translate('btmbuchhistory_lang')['correction_event_negative_amount_title'], function(r) {
				if(r) {	
					vresult = true;
					//perform the ajax here
					_post_data.action = "correction";
					
					//negative stock rule broken, add to comment
					if (_post_data.comment && _post_data.comment.length !== 0) {
						_post_data.comment += "\n";
					}				
					_post_data.comment += translate('btmbuchhistory_lang')['correction_event_negative_amount_broken_comment'];
					
					perform_ajax( _post_data );
					
				} else {
					
					vresult = false;
					$("#correction_dialog").dialog("close");
					
				}
			});
		}
		
		return ;
		
	}
	
	
	function perform_ajax( _post_data ) 
	{
		$.ajax({
	        "dataType": 'json',
	        "type": "POST",
	        "url": appbase + "medication/btmbuchhistory",
       		
	        "data": _post_data,
	        
       		"success": function( data ){ 
       			//send me a changeevent-receipt?
       			$("#correction_dialog").dialog("close");
       			redrawDatatable( true );
       		},
       		"error" : function(xhr, ajaxOptions, thrownError){
       			//console.log(xhr, ajaxOptions, thrownError);
       		}
      	});
		
	}
	

	function print_pdf_with_filters()
	{
		//console.log(arguments.callee.toString());
		
		$('#filter').append('<input type="hidden" id="print_pdf" name="print_pdf" value="print_pdf_with_filters" />');
		$("#filter").submit();
		$('#print_pdf').remove();
	}

	//for the checkboxes
	function toggleall( _this , _inputName)
	{

		$("form#filter input[name^='"+_inputName+"']").	prop('checked', $(_this).is(':checked'));
		
		redrawDatatable();

		
	}
	
	