;
/* uploadfiles.js */


var _xhr_folder = null;
var _cid = idcidpd;
var _ui_panel = null; //selected tab
//var _max_filesize = null; this is defined in view <script>
//var _selected_tab = null; this is defined in view <script>

function activate_accordion( _panel ) {

    $( "#accordion", $(_panel) ).accordion({
        active: false,
        collapsible: true,
        autoHeight: false,
        heightStyle: "fill",
        changestart: function(e, ui) {
            
        	//kill the old request
        	if (_xhr_folder != null) {
        		_xhr_folder.abort();
        	}
        	
        	_ui_panel = _panel;
        	
            //console.log(ui);

            if(ui.oldContent.length == 1){
                ui.oldContent.html("");
            }

            if (ui.newContent.length == 1) {
            	_xhr_folder = $.ajax({
                  url: appbase + 'misc/clientfileslist',
                  method: "GET",
                  data: {
                      "folder": ui.newContent.data("id"),
                      "cid": ui.newContent.data("cid"),
                      "associated_tab": ui.newContent.data("associated_tab") || 0,
                      //"cid" : _cid,
                  },
                  success: function(data) {
                      ui.newContent.html(data);  
                      //window.location = appbase+"misc/uploadfiles#folder_"+ui.newContent.data("id");
                  }
                });
            }               
        }
    });
    
    $( "#accordion", $(_panel)).accordion( "activate",0 );
}


function activate_deleted_accordion( _panel ) {
	
    $( "#del_accordion", $(_panel) ).accordion({
        active: false,
        collapsible: true,
        autoHeight: false,
        heightStyle: "fill"
    });
    $( "#del_accordion", $(_panel) ).accordion( "activate",0 );
    
}

function activate_qquploader(_panel) {	
	
    $('.qq_file_uploader_placeholder' , $(_panel)).each(function(){
        uploader_create(
        		this, 
        		['*', 'pdf', 'docx', 'doc', 'xml', 'xls', 'csv'],
        		window._max_filesize,
        		true
        		);
    });
}


//extensions = array[];
function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
	var _cid = window._cid;
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
    if (  typeof multiple_files !== "boolean"  ) {
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
    

	var sessionParams = function sessionParams() {
		
		var _action_name =  $(holderElement).data('action_name') || 'upload_client_files';
		
		var _params = {
			'_method'	: 'SESSION',
			'action'	: _action_name,
			'date'		: function() {
				return new Date();
			},
			'cid'		: _cid,
		};
			
		return _params;
	};
	    
    qq_uploader = new qq.FineUploader({
        debug: false,
        maxConnections : 1,
        multiple : multiple_files,
        element: holderElement,
        template: 'qq-template',
        
        session : {
        	endpoint : appbase+'misc/clientuploadify',
        	params: sessionParams()
        },
        
        request: {
            customHeaders: {},
            endpoint: appbase+'misc/clientuploadify',
            filenameParam: "qqfilename",
            forceMultipart: true,
            inputName: "qqfile",
            method: "POST",
            params: {
                // !! params are overwriten on submit this are for info
                'action'    : 'upload_client_files',
                //'id'        : window.idpd,
                //'tabname'   : holderId,
                'action_name': 0,//holderId,
                'date'      : function() {
                    return new Date();
                },
                'multiple'  : multiple_files,
                //'file_date' : '',
                'upload_and_save' : false,
            },
            paramsInBody: true,
            totalFileSizeName: "qqtotalfilesize",
            uuidName: "qquuid",
        },
        
        
        deleteFile: {            
            enabled: true, // defaults to false
            method: "POST",
            endpoint: appbase+'misc/clientuploadify',
            customHeaders: {},
            params: {
                'action':'upload_client_files',
                'date': new Date(),
                'cid' : _cid,
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
        	
        	onSessionRequestComplete : function(response, success, rawXhrOrXdr) {
        		if ( ! success) {
        			return;
        		}
        		
        		$.each(response, function(id, file) {
        			var _filename = file.name;
          			_filename = _filename.split('.').slice(0, -1).join('.');
          			
        			$('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(file.uuid);
          			$('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_filename);
        		});
        		
        	},
            
        	onDelete : function (id) {
        	},
        	
        	onSubmitDelete : function(id) {        		
        	},
        	
            onSubmit: function(id, name) {

            	$('input[name=btnsubmit]').attr("disabled", true);

                var el = this._options.element;
                
                var parent = $(el).closest($(el).data('parent')) || null;

                //var _cid = $('input[name=cid]', parent).val();
                
                //var file_date = $('.date', parent).val();
                var file_date = true;
                
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
                    action_name = tabname || 0;
                    
                    var params = {
                		'idcidpd'       : window.idcidpd,
                		'cid'           : _cid,
                        'action'        : 'upload_client_files',
                        //'id'            : window.idpd,
                        'tabname'       : tabname,
                        'action_name'   : action_name,
                        'date'          : function() {
                            return new Date();
                        },
                        'multiple'      : multiple_files,
                        'file_date'     : file_date,
                        'upload_and_save' : false
                    };
                    
                    this.setParams(params, id);
                    return true;
                }
            },
            
            onComplete: function(id, fileName, responseJSON){   
            	
            	$('input[name=btnsubmit]').attr("disabled", false);

                if (responseJSON.success == true){
                	var _filename = fileName.split('.').slice(0, -1).join('.');
          			
                    $('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
                    $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_filename);
//                    getSize (id)
                } else if ('error' in responseJSON) {
              	  var _error = $.map(responseJSON.error, function(e){
              		    return e;
              	  }).join('; ');
              	  
              	  $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_error);
                }
                
                                
                if (responseJSON.redirect == true){
                    if (typeof responseJSON.redirect_location != 'undefined') {
                        
                    } else {
//                    window.location.reload();//redirect to self
                    }
                    
                }
                
            },
        }
        
        
    });
    
    return qq_uploader;
}


$(document).ready(function() {
	
	$("#associated_clients_tabs").tabs({
		
		selected : window._selected_tab,
		
		show: function( event, ui ) {
			
			_cid = $(ui.tab).data('cid');
			
			_ui_panel = ui.panel;
			
			activate_accordion(_ui_panel);
			
			activate_deleted_accordion(_ui_panel);
			
			activate_qquploader(_ui_panel);
			
			$($.fn.dataTable.tables( true ) ).css('width', '100%');
		    $($.fn.dataTable.tables( true ) ).DataTable().columns.adjust();
		},
		
	});
	$("#associated_clients_tabs").show();
	
	
	
	$('.delete_link').live('click', function(event){
		
		event.preventDefault();
		event.stopPropagation();
		
		var doc_id = $(this).attr('rel');
		var _href = $(this).attr('href');
		
		jConfirm(translate('confirmdeleterecord'), '', function(r) {
			if (r) {
				window.location.href = appbase + _href; //"<?php echo APP_BASE.'misc/uploadfiles?delid="+doc_id+"';  ?>";
				return true;
			}
		});
		
		return false;
		
	})
	
	
	 
	$('.deleted_file_table').DataTable({
		// ADD language
		 "language": {
            "url": appbase+"javascript/data_tables/de_language.json"
         },
 		sDom: 't',
		processing: true,
		info: false,
		filter: false,
		paginate: false,
		serverSide: false,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
		columnDefs: [ 
				       	{ "iDataSort": 1, "targets": 0, "searchable": false, "orderable": true },
				       	{ "bVisible": false,"targets": 1, "searchable": false, "orderable": true },
				       	{ "targets": 2, "searchable": false, "orderable": true },
				       	{ "targets": 3, "searchable": false, "orderable": true },
				       	{ "targets": 4, "searchable": false, "orderable": true },
				],
				order: [[ 0, "asc" ]],
	}); 
	
});
	
	
	
	
	