function select_actions_al(row, id, action_id, description) {
	
	$('#actionid' + row).val(action_id);
	$('#description' + row).val(description);
	
	$('#hidd_actionid' + row).val(action_id);
	$('#hidd_actionname' + row).val(description);
	
	$('#description' + row).blur(); //why you no lost focus?
	//$('#description' + row).focus();
	
	
}
function trigger_insertsession(id, pid, total_cnt){
	var trigger = 0;

	
	if ($('#actionid'+id).val() !=  $('#hidd_actionid'+id).val() ) {
		$('#hidd_actionid'+id).val($('#actionid'+id).val());
		trigger = 1;
	}
	if ($('#description'+id).val() !=  $('#hidd_description'+id).val() ) {
		$('#hidd_description'+id).val($('#description'+id).val());
		trigger = 1;
	}
	if ($('#al_date'+id).val() !=  $('#hidd_date'+id).val() ) {
		$('#hidd_date'+id).val($('#al_date'+id).val());
		trigger = 1;
	}
	if (($('#hidd_actionid'+id).val()=='' && $('#hidd_description'+id).val()!='')
			||($('#hidd_actionid'+id).val()!='' && $('#hidd_description'+id).val()==''))
		{
			trigger = 0;
		}
	
	
	if( trigger == 1){
		insertsession(id, pid, total_cnt);
	}	
}


function liveSearch_al (){
	$('.live_search_al_action_id')
		.live('change', function() {
		})
		.liveSearch({
			url : 'ajax/settlementservices?mode=action_id&q=',
			id : 'livesearch_admission_diagnosis',
			aditionalWidth : '560',
			noResultsDelay : '900',
			typeDelay : '900',
			returnRowId : function(input) {
				return parseInt($(input).attr('id').substr(('actionid').length));
			}
		});
	
	
	$('.live_search_al_description')
	.live('change', function() {	
	})
	.liveSearch({
		url : 'ajax/settlementservices?mode=description&q=',
		id : 'livesearch_admission_diagnosis',
		aditionalWidth : '120',
		noResultsDelay : '900',
		typeDelay : '900',
		returnRowId : function(input) {
			return parseInt($(input).attr('id').substr(('description').length));
		}
	});	
}

function datapicker_al(id){

	$('#al_date'+id).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: '',
		onSelect: function(date) {
			$( '#al_date'+id ).trigger( "change" );
			$(this).focus();
			return false;
		}
	})
	.mask("99.99.9999");
	return false;
}