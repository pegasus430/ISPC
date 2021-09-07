;
/**
 * rpassessment.js
 * patientform
 * @date 05.08.2019
 * 
 * ISPC-2406
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

$(document).ready(function() {
	
	var _rpassessment_id_Editmode = $('#fid').val() || 0;
	$("#selector_manual_forms_editmode").checkFormularEditmode({'ajax':{'data' : {'pathname' : 'patientform/rpassessment', 'search' : 'rpassessment_id=' + _rpassessment_id_Editmode }}});


	
});



