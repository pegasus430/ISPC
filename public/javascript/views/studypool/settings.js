/* settings.js */

var changes = false;
var submited = false;

var new_groups = 0;

var qq_uploader; 
var qq_max_filesize = 1024*1024*5;

var dontLeave = function(e) {
	if (changes === true && submited === false) {
		return translate('no_save_leave_alert');
	}
}

$(document).ready(function() {
	
//	window.onbeforeunload = dontLeave;	
	
	$("#specified_user")
	.chosen({
		multiple:0,
		inherit_select_classes:true,
		width: "260px",
		"search_contains": true,
		no_results_text: translate('noresultfound'),
		placeholder_text_single: translate('please select'),
	});
	
	uploader_create('qq_file_uploader', ['docx']);
	
	status_changed($("input[name='status']:checked"));
	
	
	
	
});



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

	
	qq_uploader = new qq.FineUploader({
		debug: false,
		multiple : multiple_files,
		element: document.getElementById( holderId ),
		template: 'qq-template',
		
		request: {
			customHeaders: {},
			endpoint: appbase+'studypool/fileupload',
			filenameParam: "qqfilename",
			forceMultipart: true,
			inputName: "qqfile",
			method: "POST",
			params: {
				'action':'upload_file_attachment',
				'date': new Date(),
				'multiple': multiple_files,
			},
			paramsInBody: true,
			totalFileSizeName: "qqtotalfilesize",
			uuidName: "qquuid",
		},
		
		
		deleteFile: {            
			enabled: true, // defaults to false
			method: "POST",
			endpoint: appbase+'studypool/fileupload',
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
			onComplete: function(id, fileName, responseJSON){	
				
//				$('#btnsubmit').removeAttr("disabled");
				
				if (responseJSON.success == true){
					//update					
					$('#'+holderId +' li[qq-file-id="'+id+'"] input.qquuid').val(this.getUuid(id));
				}
			},
			onSubmit: function(id, fileName){
//				$('#btnsubmit').attr("disabled", "true");
//				$("#fileuploads").val("0");	 
			},
		}
		
	});
}


function status_changed(_this)
{
	if($(_this).val() == 'enabled') {
		$('#form_settings').show();
	} else {
		$('#form_settings').hide();
	}

}
