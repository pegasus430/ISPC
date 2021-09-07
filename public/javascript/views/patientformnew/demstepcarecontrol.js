/* demstepcarecontrol */
//console.log("demstepcarecontrol");


var changes = false;
var submited = false;

var new_groups = 0;

//next 2 fn should be readily available from master js
var dontLeave = function(e) {
	if (changes === true && submited === false) {
		return translate('no_save_leave_alert');
	}
}

var keydown_only_int = function (e) 
{
	if( e.which == 13) {
		$(this).blur();
		return;
	}
	// Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
         // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
         // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
             // let it happen, don't do anything
             return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
}

$(document).ready(function() {
	
	window.onbeforeunload = dontLeave;
	
	$( "table#data_table" ).on( "change", "input:checkbox", function() {
		
		var tr_sum =  $(this).parents('tr').find('input:checkbox:checked').length;
		
		 $(this).parents('tr').find('td:last').text(tr_sum);
	});
	
	setTimeout(function(){
		$('.success').remove();
	}, 4500);
	
	
	
});

function selected_month_change()
{
	var selected_month = $("#selected_month").val();
	window.location.href= appbase + 'patientformnew/demstepcarecontrol?id=' +  idpd;
}
 
function selected_year_change()
{
	var selected_year = $("#selected_year").val();
//	window.location.href= appbase + 'patientformnew/demstepcarecontrol?id=' +  idpd + "&selected_year=" + selected_year;
	window.location.href= appbase + 'patientformnew/demstepcarecontrol?id=' +  idpd ;
}

function clear_hidden_inputs()
{
	$("input[name^='data[']").remove(); 
}
//function to create hidden inputs from our custom obj
//ISPC-2585 Ancuta 15-16.06.2020 - function changed to include quarter invoice
function create_hidden_inputs(obj) 
{	
	console.log(obj);
	
	var name = '';	
	$.each( obj, function( groupid, actions ) {		
		$.each( actions, function( actionid, values ) {
			$.each( values, function( key, val ) {
				console.log(key);
				if (key=="data") {
					$.each( val, function( day_key, day_val ) {
						if(actionid != 'quarter_invoice'){
							name = 'form['+actionid+']['+day_key+']';
							$('<input />')
							.attr('type', 'hidden')
							.attr('name', name)
							.attr('value', day_val)
							.appendTo('#form_rlp');
							
						} else {
							if(day_val != '0'){
								name = 'form[quarter_invoice]';
								$('<input />')
								.attr('type', 'hidden')
								.attr('name', name)
								.attr('value', day_val)
								.appendTo('#form_rlp');
							}
						}
					});
				}else {
//					name = 'form['+actionid+']';
//					$('<input />')
//					.attr('type', 'hidden')
//					.attr('name', name)
//					.attr('value', val)
//					.appendTo('#form_rlp');
				}
			});
		});
	});
	
	return true;
}
// create hidden inputs from out table and submit the form
function save_form(_this,option = false) {
	var result = []; // this is for console.log
	var result_obj = {};
 	
	// if generate invoice 
	// - check if quarter was selected
	//  - check assigned users
	
	
	$(".valsTable .tr_hasValue").each(function(){
		
		var new_group = {};
//		new_group.groupid =  $(this).data('actionid');
		new_group.actionid =  $(this).data('actionid');
	
		var data = {};
		$("td input.products", this).each(function(){
			var day = $(this).data('quarterly_date');
			if($(this).is(':checked')){
				data[day] = "1";
			} else{
				data[day] = "0";
			}
		});
		
		new_group.data = data;
		
		if( ! $.isPlainObject(result_obj[new_group.groupid])){
			result_obj[new_group.groupid] = {};
		}
		
		result_obj[new_group.groupid][new_group.actionid] = new_group;
		result.push(new_group);
		
	}); 
	
	
	var selected_quarter = 0;
	var new_group = {};
	new_group.actionid =  "quarter_invoice";
	
	var data = {};
	
	$(".valsTable .qInvoice").each(function(){
		
		$("th input.quarter_invoice", this).each(function(){
			var quarter = $(this).val();
			
			if($(this).is(':checked')){
				data[quarter] = quarter;
				selected_quarter++;
			} else{
				data[quarter] = "0";
			}
		});
		new_group.data = data;
		
		if( ! $.isPlainObject(result_obj[new_group.groupid])){
			result_obj[new_group.groupid] = {};
		}
		
		result_obj[new_group.groupid][new_group.actionid] = new_group;
		result.push(new_group);
		
	});
	
	if(option == 'generate_invoices'){
		if(selected_quarter == 0 ){
			alert(translate('dsc:Please select quarter to generate invoice !'));
			return false;
		} else {
			if(assigned_doctors == 0){
				alert(translate('dsc:Invoices can not be generated! Patient must have at least one DOCTOR assigned'));
				return false;
			}
			else if(assigned_doctors > 1){
				alert(translate('dsc:Invoices can not be generated! Patient must have ONLY one DOCTOR assigned'));
				return false;
			}
		}
	}
	
//	console.log(result_obj);
//	return false;
	//clear previous inputs 
	clear_hidden_inputs();
	
	create_hidden_inputs(result_obj);
	//append action type
	$('<input />')
	.attr('type', 'hidden')
	.attr('name', 'action')
	.attr('value', $(_this).attr('name'))
	.appendTo('#form_rlp');
 
	
	
	if (checkclientchanged('form_rlp')) {
		submited = true;
		
		
		if(_this.id  == "reset"){
			jConfirm(translate("confirm_reset_data"), translate("confirm_reset_title"), function(r) {
				if(r){
					$('#form_rlp').submit();
				}
			});
		} else{
			 $('#form_rlp').submit();
		}
		
	}
	
	

}


 
