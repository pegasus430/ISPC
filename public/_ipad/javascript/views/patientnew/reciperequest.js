/* reciperequest.js */

function removeElem(ids){
	$(ids).remove();
}

$(function() {
	if($('#apothekeusers').val() !='' && $('#apothekeusers').val() !== null){
		var hasmed = 0;
		$('#medaddtable').find(":checkbox:checked").each(function(){
			if($(this).val() != '') {
				hasmed = 1;
			}
		});
		if(hasmed == 1) {
			$('#bestellen').attr('disabled',false);
		} else {
			$('#bestellen').attr('disabled',true);
		}
	} else {
		$('#bestellen').attr('disabled',true);
	}



	$('#apothekeusers').change(function(){
		if($(this).val() != '') {
			var hasmed = 0;
			$('#medaddtable').find(":checkbox:checked").each(function(){
				if($(this).val() != '') {
					hasmed = 1;
				}
			});
			if(hasmed == 1) {
				$('#bestellen').attr('disabled',false);
			} else {
				$('#bestellen').attr('disabled',true);
			}
		} else {
			$('#bestellen').attr('disabled',true);
		}

	});

	$('#medaddtable').find(':checkbox').change(function(){
		if($('#apothekeusers').val() != '') {
			var hasmed = 0;
			$('#medaddtable').find(":checkbox:checked").each(function(){
				if($(this).val() != '') {
					hasmed = 1;
				}
			});
			if(hasmed == 1) {
				$('#bestellen').attr('disabled',false);
			} else {
				$('#bestellen').attr('disabled',true);
			}
		} else {
			$('#bestellen').attr('disabled',true);
		}

	});

});


$(document).ready(function() {
	
		
	$('#assign_user_dialog').dialog({
		autoOpen: false,
		modal: true,
		title: translate('assignusertopatient'),
		buttons: [
		          {
					text: translate('yesconfirm'),
					click: function() {
						$('#assign_user_to_patient').val('1');
						$( this ).dialog( "close" );
						$('#frmpharmacyorder').submit();
					}
		          },
		          
		          {
					text: translate('noconfirm'),
					click: function() {
						$('#assign_user_to_patient').val('0');
						$( this ).dialog( "close" );
						$('#frmpharmacyorder').submit();
					}
		          },
			]
	});


	$('#bestellen').bind('click', function(e) {
		e.preventDefault() // prevents the form from being submitted
	
		var selected_user = $('#apothekeusers').val();
			
		if (selected_user === null 
				|| (selected_user.length == 1 && selected_user[0] == "0")) {
			jAlert(translate('Bitte Benutzer auswählen'), translate('confirmdeletetitle')); //ISPC - 2329 punctul d
			return false;// no user selected
		}
		
		if ( ! checkclientchanged()){
			return false;
		}
			
		var assigned_users = window.assigned_users;
		var not_assigned = $(selected_user).not(assigned_users).get();
	
		if(not_assigned.length == 0){
			$('#assign_user_to_patient').val('0');
			$('#frmpharmacyorder').submit();
		} else {
			//open modal to ask for assign
			$('#assign_user_dialog').dialog('open');
		}
	});
	
	$('#apothekeusers').chosen({
		placeholder_text_single: translate('please select'),
		placeholder_text_multiple : translate('please select'),
		multiple:1,
		width:'250px',
		"search_contains": true,
		no_results_text: translate('noresultfound')
	});

});
