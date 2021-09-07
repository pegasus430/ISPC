; /*patientfileupload.js*/




$(document).ready(function() {
	  uploader_create(
	    		"qq_file_uploader_placeholder", 
	    		['*', 'pdf', 'docx', 'doc', 'xml', 'xls', 'csv'],
	    		null,
	    		true
	    		);
});





//extensions = array[];
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
  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_patient_files';
		
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
	  	
		var _action_name =  $(holderElement).data('action_name') || 'upload_patient_files';
		 
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
              'action'    : 'upload_patient_files',
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
		      
		      action_name =  $(el).data('action_name') || 'upload_patient_files';
		      
		      
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


//ISPC-2642 Ancuta 10-11.08.2020
function tag_uploader_create( holderId, allowed_extensions , max_filesize, multiple_files, tag_name)
{
 
	var _cid = window._cid || 0;
	//defaults
	var _max_filesize = 102400000;
	var _allowed_extensions = ['pdf','docx'];
	var _multiple_files = false;
	var _tag_name = false;
	
	if ( ! $.isNumeric(max_filesize) ) {
		max_filesize = _max_filesize;
	}
	if ( ! $.isArray(allowed_extensions) ) {
		allowed_extensions = _allowed_extensions;
	}
	if (  typeof multiple_files !== "boolean"  ) {
		multiple_files = _multiple_files;
	}
	
	if ( ! tag_name ) {
		tag_name = _tag_name;
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
 
	

	
	
	
	
	
//	$('#'+$(holderElement).attr('id')+'>div.qq-uploader-selector>div.qq-upload-button').find('div.button_name').html('fsdfdsfsdfsdfsd'+tag_name);
	
	var sessionParams = function sessionParams() {
		
		var _action_name =  $(holderElement).data('action_name') || 'upload_patient_files';
		
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
		
		var _action_name =  $(holderElement).data('action_name') || 'upload_patient_files';
		
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
				'action'    : 'upload_patient_files',
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
//					$('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).hide();
//					$('li[qq-file-id="'+id+'"] input.qquuid_file2tag', $(holderElement)).val(file.uuid+'_'+tag_name);
					$('li[qq-file-id="'+id+'"] input.qquuid_file2tag', $(holderElement)).val(tag_name);
					
					
					$('li[qq-file-id="'+id+'"] span.filesize', $(holderElement)).hide();
					$('li[qq-file-id="'+id+'"] span.infolabel', $(holderElement)).hide();
					$('li[qq-file-id="'+id+'"] div.qq-progress-bar', $(holderElement)).hide();
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
				
				action_name =  $(el).data('action_name') || 'upload_patient_files';
				
				
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
//					$('li[qq-file-id="'+id+'"] input.qquuid_file_title', $(holderElement)).hide();
//					$('li[qq-file-id="'+id+'"] input.qquuid_file2tag', $(holderElement)).val(this.getUuid(id)+'_'+tag_name);
					$('li[qq-file-id="'+id+'"] input.qquuid_file2tag', $(holderElement)).val(tag_name);
					
					
					
					$('li[qq-file-id="'+id+'"] span.filesize', $(holderElement)).hide();
					$('li[qq-file-id="'+id+'"] span.infolabel', $(holderElement)).hide();
					$('li[qq-file-id="'+id+'"] div.qq-progress-bar', $(holderElement)).hide();
					
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
	
	
	
	var tag_name_low = tag_name.toLowerCase();
	var btn = '<div class="button"><span>'+tag_name+'</span><button class="plus"></button></div>'
	var btn = '<button class="plus"></button>'

	var tag_id = tag_ids[tag_name];
	var filter_tag = '<a href="javascript:void(0);" class="tag" rel="'+tag_id+'">'+tag_name+'</a>';
	$('div#tagFile_upload_'+tag_name_low+'.tagFile_upload> div.qq-uploader-selector.qq-uploader.bigupload').append(filter_tag);
	
	$('div#tagFile_upload_'+tag_name_low+'.tagFile_upload > div.qq-uploader-selector.qq-uploader.bigupload > div.qq-upload-button-selector.qq-upload-button > div.button_name').html('');
	$('div#tagFile_upload_'+tag_name_low+'.tagFile_upload > div.qq-uploader-selector.qq-uploader.bigupload > div.qq-upload-button-selector.qq-upload-button').addClass('clearButton');
	$('div#tagFile_upload_'+tag_name_low+'.tagFile_upload > div.qq-uploader-selector.qq-uploader.bigupload > div.qq-upload-button-selector.qq-upload-button').addClass('newPlusButton');
	
	
	$('div#tagFile_upload_'+tag_name_low+'.tagFile_upload > div.qq-uploader-selector.qq-uploader.bigupload > div.qq-upload-button-selector.qq-upload-button').unbind('click');
	
	
	return qq_uploader;
}