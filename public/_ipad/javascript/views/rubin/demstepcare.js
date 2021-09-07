/**
 * @auth Ancuta 28.08.2019 copy of bdi
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}
/*function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
	
	var multiple_files = true;
    //defaults
    var _max_filesize = 102400000;
    var _allowed_extensions = ['pdf','docx'];
    var _multiple_files = true;

    if ( ! $.isNumeric(max_filesize) ) {
        max_filesize = _max_filesize;
    }
    if ( ! $.isArray(allowed_extensions) ) {
        allowed_extensions = _allowed_extensions;
    }
    if ( ! typeof multiple_files === "boolean"  ) {
        multiple_files = _multiple_files;
    }

    var holderElement = document.getElementById(holderId);

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
                'date'		: function() {
                    return new Date();
                },
                'multiple'	: multiple_files,
                'upload_and_save' : true,
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
            typeError: "Ungültiges Dateiformat",
            sizeError: "Datei zu groß",
            minSizeError: "Datei zu klein",
            emptyError: "Datei hat keinen Inhalt",
            noFilesError: "Keine Datei ausgewählt",
            tooManyItemsError: "zu viele Dateien",
            maxHeightImageError: "Bilddimension zu groß",
            maxWidthImageError: "Bilddimension zu groß",
            minHeightImageError: "Bilddimension zu klein",
            minWidthImageError:  "Bilddimension zu klein",
            retryFailTooManyItems: "zu viele Dateien",
            onLeave: "",
            unsupportedBrowserIos8Safari: "Browser wird nicht unterstützt",
        },

        callbacks: {


            onSubmit: function(id, name) {
                var el = this._options.element;
                var parent = $(el).closest('div.extra_options');
 
                    //setParams
                    var params = {
                        'action'	: 'upload_file_attachment',
                        'id'		: window.idpd,
                        'tabname'	: holderId,
                        'date'		: function() {
                            return new Date();
                        },
                        'multiple'	: multiple_files,
                        'upload_and_save' : true
                    };
                    this.setParams(params, id);
                    return true;
            },

            onComplete: function(id, fileName, responseJSON){
                if (responseJSON.success == true){
                    //update
                    $('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
                }

                if (responseJSON.redirect == true){
                    if (typeof responseJSON.redirect_location != 'undefined') {

                    } else {
                      //  window.location.reload();//redirect to self
                    }
                }
            },
        }
    });
    return qq_uploader;
}
*/


function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
  var _cid = window._cid || 0;
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
  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_dms_patient_files';
		
		var _params = {
				'_method'	: 'SESSION',
				'action'	: _action_name,
				'date'		: function() {
					return new Date();
				},
				'cid'		: _cid,
				'id'		: window.idpd,
		};
		
		return _params;
  };
  
  var deleteParams = function deleteParams() {
	  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_dms_patient_files';
		 
		var _params = {
				'_method'	: 'DELETE',
				'action'	: _action_name,
				'date'		: new Date(),
				'cid'		: _cid,
				'id'		: window.idpd,
		};
		
		return _params;
  };
  
  var qq_uploader = new qq.FineUploader({
     
	  'debug': false,
	  'maxConnections' : 1,
      'multiple' : multiple_files,
      'element': holderElement,
      'template': 'qq-template',
      
      session : {
      	endpoint : appbase+'patient/uploadify2018',
      	params: sessionParams(),
      },
      
      'request': {
          'customHeaders': {},
          'endpoint': appbase+'patient/uploadify2018',
          'filenameParam': "qqfilename",
          'forceMultipart': true,
          'inputName': "qqfile",
          'method': "POST",
          'params': {
        	  // !! params are overwriten on submit this are for info
              'action'    : 'upload_dms_patient_files',
              'id'        : window.idpd,
              //'tabname'   : holderId,
              'action_name': 0,//holderId,
              'date'      : function() {
                  return new Date();
              },
              'multiple'  : multiple_files,
              //'file_date' : '',
              'upload_and_save' : false,
          },
          'paramsInBody': true,
          'totalFileSizeName': "qqtotalfilesize",
          'uuidName': "qquuid",
      },
      
      
      deleteFile: {            
          enabled: true, // defaults to false
          method: "POST",
          endpoint: appbase+'patient/uploadify2018',
          customHeaders: {},
          params: deleteParams()
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
    	  
        /*  
      	onDelete : function (id) {
      	},
      	
      	onSubmitDelete : function(id) {        		
      	},
      	*/
          onSubmit: function(id, name) {
        	  
        	  $('input[name=btnsubmit]').attr("disabled", true);

		      var el = this._options.element;
		      
		      var file_date = true;
		  
		      //setParams
		      tabname =  $(el).data('tabname') || null;
		      
		      action_name =  $(el).data('action_name') || 'upload_dms_patient_files';
		      
		      
		      var params = {
		  		'idcidpd'       : window.idcidpd,
		  		'id'            : window.idpd, // this is for patient files
		  		//'cid'           : _cid,// use this if you link clients
		          'action'        : action_name,
		          'tabname'       : tabname,
		          'date'          : function() {
		              return new Date();
		          },
		          'multiple'      : multiple_files,
		          'file_date'     : file_date,
		          'upload_and_save' : false
		      };
		      
		      this.setParams(params, id);
		      
		      return true;
              
          },
          
          onComplete: function(id, fileName, responseJSON){   
                           
          	$('input[name=btnsubmit]').attr("disabled", false);

              if (responseJSON.success == true){
            	  var _filename = fileName.split('.').slice(0, -1).join('.')
        			
                  $('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
                  $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_filename);
//                  getSize (id)
              } else if ('error' in responseJSON) {
            	  var _error = $.map(responseJSON.error, function(e){
            		    return e;
            	  }).join('; ');
            	  
            	  $('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).val(_error);
              }
              
              if (responseJSON.redirect == true){
                  if (typeof responseJSON.redirect_location != 'undefined') {
                      
                  } else {
//                  window.location.reload();//redirect to self
                  }
                  
              }
              
          },
      }
      
      
  });
  
  return qq_uploader;
}

$(document).ready(function() {
	var confdel = translate('confirmsingledeleterecord');
	var conftitle = translate('confirmdeletetitle');

//	uploader_create('demstepcare', ['pdf','docx','doc']);
	  uploader_create(
	    		"qq_file_uploader_placeholder", 
	    		['*', 'pdf', 'docx', 'doc', 'xml', 'xls', 'csv'],
	    		null,
	    		true
	    		);
	 $( ".form_date" ).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: "0"
			
		}).mask("99.99.9999");
	 
	// DELETE
		$(".delete").live('click', function() {
			$.confirmdeleteid = $(this).attr('data-doc');
			$.confirmdeletepid = $(this).attr('data-pid');
			jConfirm(confdel, conftitle, function(r) {
				if(r)
				{	
					location.href = appbase + 'rubin/demstepcare?doc_id='+$.confirmdeleteid+'&id='+$.confirmdeletepid+'&action=filedelete';
				}
			});
		});
});