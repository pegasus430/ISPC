//extensions = array[];
function uploader_create( holderId, allowed_extensions , max_filesize, multiple_files)
{
	//defaults
	var _max_filesize = 102400000;
	var _allowed_extensions = ['jpeg', 'jpg', 'gif', 'png'];
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
	
	var holderElement = document.getElementById(holderId);
	
	if (holderElement == null) {
		return;//holderId not found
	}
	
	qq_uploader = new qq.FineUploader({
		debug: false,
		multiple : multiple_files,
		element: holderElement,
		template: 'qq-template-gallery',
		
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
				'tabname'	: holderId,// form id --- 
				'action_name'	: holderId,// form id --- 
				'date'		: function() {
					return new Date();
				},
				'multiple'	: multiple_files,
				//'file_date' : '', // input with date when pic was taken
				//'upload_and_save' : 0, // save widouth submiting form 
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
				'action_name'	: holderId,// form id ---
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
        thumbnails: {
            placeholders: {
                 //waitingPath: '/source/placeholders/waiting-generic.png',
                 //notAvailablePath: '/source/placeholders/not_available-generic.png'
            }
        },
 
		callbacks: {
			
	
			onSubmit: function(id, name) {
				
// 				var el = this._options.element;
// 				var parent = $(el).closest('div.extra_options');
// 				var file_date = $('.file_date', parent).val();
// 				if( ! file_date) {

// 					//cancel upload
// 					setTimeout(function () {
// 						alert(translate('acp_box_lang')["Please first select file date"]);
// 						},50);
// 					this.cancelAll();
// 					this.clearStoredFiles();
					
// 					return false;
					
// 				} else {
// 					//setParams
// 					var params = {
// 						'action'	: 'upload_file_attachment',
// 						'id'		: window.idpd,
// 						'tabname'	: holderId,
// 						'date'		: function() {
// 							return new Date();
// 						},
// 						'multiple'	: multiple_files,
// 						'file_date'	: file_date,
// 						'upload_and_save' : true
// 					};
// 		            this.setParams(params, id);
// 		            return true;
// 				}
			},
			
			onComplete: function(id, fileName, responseJSON){	
								
				if (responseJSON.success == true){
					//update					
					//var imgsrc = $('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).parent().find('img').attr('src');
					//alert(imgsrc);
					
					$('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
					//$('li[qq-file-id="'+id+'"] input.qqusrc', $(holderElement)).val(imgsrc); //ISPC-2465 Carmen 14.10.2019
					$('li[qq-file-id="'+id+'"] input.qqusrc', $(holderElement)).val(responseJSON.qqusrc); //ISPC-2465 Carmen 14.10.2019
				}
			},
		}
		
	});
	
	return qq_uploader;
}
 
	
$(document).ready(function() {
	uploader_create('wounddocumentation_uploaded_img', ['jpeg', 'jpg', 'gif', 'png'],null,true);
});
