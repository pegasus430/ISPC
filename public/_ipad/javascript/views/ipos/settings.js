/* settings.js */

var changes = false;
var submited = false;

var new_groups = 0;

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
	
	status_changed($("input[name='status']:checked"));
	
	
	
	
});


function status_changed(_this)
{
	if($(_this).val() == 'enabled') {
		$('#form_settings').show();
	} else {
		$('#form_settings').hide();
	}

}
