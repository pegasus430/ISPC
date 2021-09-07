/**
 *  patientdetails.js
 *  @date 2017.09.06
 */

$(document).ready(function() {
	
	/*
	 * init #grow6 - ACP
	 */
	if ($("#grow6").length == 1 ) {
		
		uploader_create('acp_file_healthcare_proxy', ['pdf','docx','doc']);
		uploader_create('acp_file_care_orders', ['pdf','docx','doc']);
		uploader_create('acp_file_living_will', ['pdf','docx','doc']);
		
	
		//datepicked for file_date
		$('#grow6 .file_date').each(function(){
			$(this).datepicker({dateFormat: 'dd.mm.yy',
				showOn: "both",
				buttonImage: $('#calImg').attr('src'),
				buttonImageOnly: true,
				changeMonth: true,
				changeYear: true,
				nextText: '',
				prevText: '',
				onSelect:function(){
					//upload the file if was allready selected?
				}
			});
		});
		
		//onchange contact person
		$('#grow6 .contactperson').each(function(){
			$(this).change(function(){
				var division_tab = $(this).closest('div.parent_container').data('division_tab');
				ajaxcall('Acp', 'contactperson_master_id', $(this).val(), window.idpd, 'grow6', {"division_tab":division_tab});
			});
		});
		
		//blur comments
		$('#grow6 .comments').each(function(){
			$(this).blur(function(){
				var division_tab = $(this).closest('div.parent_container').data('division_tab');				
				ajaxcall('Acp', 'comments', $(this).val(), window.idpd, 'grow6', {"division_tab":division_tab});
			});
		});
		
	} //eo #grow6
	

	
	
	
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
				'file_date' : '',
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
				var parent = $(el).closest('div.extra_options');
				var file_date = $('.file_date', parent).val();
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
					var params = {
						'action'	: 'upload_file_attachment',
						'id'		: window.idpd,
						'tabname'	: holderId,
						'date'		: function() {
							return new Date();
						},
						'multiple'	: multiple_files,
						'file_date'	: file_date,
						'upload_and_save' : true
					};
		            this.setParams(params, id);
		            return true;
				}
			},
			
			onComplete: function(id, fileName, responseJSON){	
								
				if (responseJSON.success == true){
					//update					
					$('li[qq-file-id="'+id+'"] input.qquuid', $(holderElement)).val(this.getUuid(id));
				}
								
				if (responseJSON.redirect == true){
					if (typeof responseJSON.redirect_location != 'undefined') {
						
					} else {
						window.location.reload();//redirect to self
					}
					
				}
				
			},
		}
		
	});
	
	return qq_uploader;
}