/* sendemail2membershistoy.js */


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
	
	function validatePositiveInteger( testNumber )
	{    
		return testNumber.match(/^\d+$/) && parseInt(testNumber) >= 0;
	}
	
	function getTimestamp()
	{
		return new Date().getTime();
	}

	var timer_redraw;
	var searchWaitInterval;
	var emailsDatatable; // this will be the datatable object
	
	function redrawDatatable( keep_page ){
		
		var resetPaging = true;
		
		if (keep_page === true) {
			resetPaging = false;
		}
		
		window.clearTimeout(timer_redraw);
			
		timer_redraw = window.setTimeout(function () {
			window.emailsDatatable.ajax.reload(null, resetPaging);
		},800);
		
		
	}
	
	
	
	function drawDatatable() {
		var email_datatable = $('#sendemail2members_histoy_dtable').DataTable({
			// ADD language
			"language": {
				"url": appbase+"/javascript/data_tables/de_language.json"// + "?-" + getTimestamp()
			},
			sDom: 
				'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"Clfr<"#top_filter">>'+
				't'+
				'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"ip<"#bottom_export">>',
			
				
			"lengthMenu": [[10, 50, 150, 300], [10, 50, 150, 300]],
			
						
			"pageLength": 50,
			
			//"bLengthChange": false,
			
			"bFilter": true,
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
		    	
				aoData.push({ "name": "length", "value": oSettings._iDisplayLength });
				aoData.push({ "name": "start", "value":  oSettings._iDisplayStart });
				aoData.push({ "name": "action", "value": "fetch_emails_list" });
				//aoData.push({ "name": "filter_by_member", "value": $("#filter_by_member").val() });

				
		    	oSettings.jqXHR = $.ajax( {
			        "dataType": 'json',
			        "type": "POST",
			        "url": sSource,
		       		"data": aoData,
		       		"success": fnCallback
		      });
		    },
					
			
			columns: [
			          {
			        	  "data":			null,
			        	  "orderable":		false,
			        	  "defaultContent":	"",
			        	  "className":		"",
			        	  "name":			"id",
			        	  "title":			"#" ,
			        	  "width":			"10px" 
			          },
			          
//			          { 
//			        	  "data": "debugcolumn",
//			        	  visible: false,
//			        	  className: "",
//			        	  name:"debugcolumn",
//			        	  title:"debugcolumn"
//			          },
			          
			          {
			        	  "data": "entrydate",
			        	  className: "",
			        	  name:"entrydate",
			        	  title:translate('entrydate'),
			        	  "width":"80px"
			          },
			          
			          { 
			        	  "data": "email_subject",
			        	  className: "",
			        	  name:"email_subject",
			        	  title: translate('email_subject'),
//			        	  "width":"220px"
			          },
			          
			          {
			        	  "data": "no_of_recipients",
			        	  className: "",
			        	  name:"no_of_recipients",
			        	  title: translate('sendemail2membershistoy_lang')['no_of_recipients'] ,
			        	  "width":"220px"
			          },
			          
					 ],
			//order: [[ 0, "asc" ]],		 
			
			"fnDrawCallback": function( oSettings ) {
				
				email_datatable.column(0, {}).nodes().each( function (cell, i) {
			            cell.innerHTML = oSettings._iDisplayStart + i+1;
			     });  
						
				
				email_datatable.columns.adjust();
			} ,
				
			"initComplete": function( settings, json ) {
				
				
				
				$('.dataTables_filter input')
				.unbind() 
				.bind("input", function (e) {
					var item = $(this);
					searchWait = 0;
					if (this.value.length >= 2 || this.value == "") {
						if(!searchWaitInterval) searchWaitInterval = setInterval(function(){
							if(searchWait>=3){
								clearInterval(searchWaitInterval);
								searchWaitInterval = '';
								searchTerm = $(item).val();
								$("body").addClass("loading");
								emailsDatatable.search(searchTerm).draw();						
								searchWait = 0;
							}
							searchWait++;;
						},200);
					}
					
				})
				.bind("keydown", function (e) {
					if (e.keyCode == 13){
						//Search when user presses Enter
						clearInterval(searchWaitInterval);
						$("body").addClass("loading");
						emailsDatatable.search($(this).val()).draw();
					}
				
				});
				
				var label = $('label', '.dataTables_filter');
				var label_text = translate('sendemail2membershistoy_lang')['filter by member'];
				label.prepend(label_text);
				

//			    $('#top_filter').append($("#filter_by_member").html()).addClass("top_filter");
			    
			    //debug column
//			    if ( typeof(DEBUGMODE) !== 'undefined' &&  DEBUGMODE === true) {
//			    	var column = email_datatable.column( "debugcolumn:name" );
//			    	if (column) {
//			    		column.visible( true );
//			    	}
//			    }
			    
			    
			    $('#sendemail2members_histoy_dtable tbody').on('click', 'tr:not(.smalltable)', function () {
			        //var tr = $(this).closest('tr');			    	
			        var tr = $(this);

			        var row = emailsDatatable.row( tr );
			 
			        if ( row.child.isShown() ) {
			            // This row is already open - close it
			            row.child.hide();
			            tr.removeClass('shown');
			        }
			        else {
			            // Open this row
			        	var child_class =  'smalltable';
			            row.child( format(row.data()) ).show();
			            if (tr.hasClass('even')) {
			            	child_class += ' even ';
			            }
			            if (tr.hasClass('odd')) {
			            	child_class += ' odd ';
			            }
			            $(row.child()).addClass(child_class);
			            
			            tr.addClass('shown');
			        }
			    } );
			    
			    
			    
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
		
		return email_datatable;
	}
	
	function format ( d ) {
	    // `d` is the original data object for the row
		
		if (d.email_attachment != null) {
			
		}
		
		var html =  '<table cellpadding="5" cellspacing="0" border="0" class="transparent" style="padding-left:50px;">'+
	        '<tr class="smalltable">'+
	            '<td width="130px">' + translate('sendemail2membershistoy_lang')['sent by'] + '</td>'+
	            '<td>' + d.email_sent_by + '</td>'+
	        '</tr>'+
	        '<tr class="smalltable">'+
	            '<td>' + translate('sendemail2membershistoy_lang')['email_content'] +'</td>'+
	            '<td>' + d.email_content + '</td>'+
	        '</tr>'+
	        '<tr class="smalltable">'+
	            '<td>' + translate('sendemail2membershistoy_lang')['sent to'] +'</td>'+
	            '<td>' + d.email_recipients.join('<br>') + '</td>'+
	        '</tr>';
	    
		if (d.email_attachment_id != null) 
		{
			
			var email_attachment_downloadlink = appbase;
			//add atacheent row with a link to download the file
			html += '<tr class="smalltable">'+
            '<td>' + translate('sendemail2membershistoy_lang')['email_attachment'] +'</td>'+
			
            '<td> <a href = "member/filedownload?doc_id='+ d.email_attachment_id +'&rev=1" >' + d.email_attachment_filename + '</a></td>'+
            '</tr>';
		}
		
		html +=  '</table>';
		
	    return html;
	}


	
	
	function filter_by_member( _this ) 
	{
		redrawDatatable();
	}

	


$(document).ready(function() {
		
	window.emailsDatatable = drawDatatable();
	
});



