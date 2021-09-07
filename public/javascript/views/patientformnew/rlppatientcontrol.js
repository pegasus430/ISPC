/* rlppatientcontrol */
//console.log("rlppatientcontrol");


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
	
	
});

function selected_month_change()
{
	var selected_month = $("#selected_month").val();
	window.location.href= appbase + 'patientformnew/rlppatientcontrol?id=' +  idpd + "&selected_month=" + selected_month;
}

/*

function row_total_visits(_this_row) {
	
	var total_visits = 0;
	$(".hasValue", $(_this_row)).each(function(){
		total_visits += Number($(this).text());
	});
	
	$("td:last", $(_this_row)).text(total_visits);
}


function selected_month_change()
{
	var selected_month = $("#selected_month").val();
	window.location.href= appbase + 'patientformnew/rlppatientcontrol?id=' +  idpd + "&selected_month=" + selected_month;
}


function append_new_group()
{
	changes = true;
	
	var new_groupid = "new_" + String(new_groups);
	new_groups ++;
	
	var cloned_group = $('tr', $("#blank_data_group")).clone();
	
	cloned_group.attr('data-groupid', new_groupid );
	
	$("#data_table").append(cloned_group);	

	
}

function add_action_dialog(_this , mode)
{
	
	var groupid = null;
	var actionid = null;
	
	if( mode == 'edit' ) {
		//edit mode
		var parent_tr = $(_this).parents(".tr_hasValue");
		
		groupid = parent_tr.data('groupid');
		actionid = parent_tr.data('actionid') || '';
		
		var startdate = parent_tr.data('startdate') || '';
		var interval = parent_tr.data('interval');
		var interval_options = parent_tr.data('interval_options') || '';
		
//		$( "#add_action_actions" , $('#add_action_dialog')).val( actionid ).trigger("chosen:updated");
		$( "#add_action_startdate" , $('#add_action_dialog')).val( startdate );
		$( "input.add_action_interval" , $('#add_action_dialog')).val( [interval] );
		onclick_action_interval($( "input.add_action_interval:checked" , $('#add_action_dialog')));
		
		switch(interval) {
			case "every_x_days":{
				$("input.every_x_days", $('#add_action_dialog')).val(interval_options);
			}break;
			case "selected_days_of_the_week":{
				var checked_options = [];
				interval_options += "";				
				if(~interval_options.indexOf(",")){
					checked_options = interval_options.split(",");
				} else {
					checked_options.push(interval_options);
				}
				$("input.selected_days_of_the_week", $('#add_action_dialog')).val(checked_options);
			}break;
		}
		
		//remove one action
		var remove_action_button =
		{
			text: translate('rlppatientcontrol_lang')['remove_action'],
			click: function() {
				
				$('tr.tr_hasValue[data-groupid="'+groupid+'"][data-actionid="'+actionid+'"]').remove();
				$( this ).dialog( "close" );
			}
         };
		
	}
	else {
		
		var remove_action_button =
		{
			text:  translate('rlppatientcontrol_lang')['noconfirm'],
			click: function() {
				$( this ).dialog( "close" );
			}
         };
		
	}

	
	
	
	$('#add_action_dialog')
	.data("groupid", groupid)
	.dialog({
		autoOpen: true,
		modal: true,
		open: function(){
			//update action selectox with disabled actions from this group
			set_disabled_actions($(_this).parents("tr").data('groupid') , actionid);
			
			$(".every_x_days", this).keydown(keydown_only_int);
			
		},
		width:'500',
		height:'430',
		title: translate('rlppatientcontrol_lang')['add_action_dialog_title'],
		
		buttons: [
		          
		          remove_action_button,

		          {
					text: translate('rlppatientcontrol_lang')['edit_action'],
					click: function() {
						
						changes = true;
						
						add_action_line(_this, this);
						
						$( this ).dialog( "close" );
						
					}
		          },
		          
		          
//		          
//		          {
//					text: translate('noconfirm'),
//					click: function() {
//						$( this ).dialog( "close" );
//					}
//		          },
			]
	});
}

//on add_action_dialog open
function set_disabled_actions(groupid , actionid)
{
	//first enable all actions
	$( "#add_action_actions option" , $('#add_action_dialog')).attr("disabled", false);
		
	//then disable actions in this groupid 
	var user_options = []; 
	
	$('tr.tr_hasValue[data-groupid="'+groupid+'"]').each(function(){
		if (actionid != $(this).data("actionid"))
			user_options.push($(this).data("actionid") + "");
	});
	
	if ($(user_options).length){
		var options = $("#add_action_actions option", $('#add_action_dialog')).filter(function() {    
		    return $.inArray($(this).val() + "", user_options) > -1;
		}).attr("disabled", true);
	}
	
	//finaly update selectbox
	$( "#add_action_actions" , $('#add_action_dialog')).val(actionid).trigger("chosen:updated");
}





//add new action
function add_action_line(_this_icon , _this_dialog)
{

	var mode, parent_tr, parent_groupid, empty_tr;
	
	if ($(_this_dialog).data('groupid') !== null) {
		//we are in edit mode
		mode = "edit";
		parent_tr =  $(_this_icon).parents("tr");
		parent_groupid = $(_this_dialog).data('groupid');
		
	} else {
		//this is a new row append
		mode = "append";
		parent_tr =  $(_this_icon).parents("tr.action_row");
		parent_groupid = parent_tr.data("groupid");
	}	
		
	var add_action_actions =  $(".add_action_actions", _this_dialog).val();
	var add_action_actions_text =  $(".add_action_actions option:selected", _this_dialog).text();
	var add_action_startdate =  $("input.add_action_startdate", _this_dialog).val();
	var add_action_interval =  $("input.add_action_interval:checked", _this_dialog).val();
	var add_action_interval_options;
		
	switch(add_action_interval) {
		case "daily":{
			add_action_interval_options = null;
		}
		break;
		case "every_x_days":{
			add_action_interval_options = $("input.every_x_days", _this_dialog).val()+"" || "1";
		}
		break;
		case "selected_days_of_the_week":{
			 
			add_action_interval_options = [];
			$("input.selected_days_of_the_week:checked", _this_dialog).each(function(){				
				add_action_interval_options.push($(this).val() + "");
			});			
		}
		break;
	}
	
	if (mode == "edit") {		
		empty_tr = parent_tr;	
		empty_tr
			.data('groupid', parent_groupid  )
			.data('actionid', add_action_actions )
			.data('startdate', add_action_startdate )
			.data('interval', add_action_interval )
			.data('interval_options', add_action_interval_options );
		
	} else if(mode == "append"){
		empty_tr = $('tr.action_row', $("#blank_data_group")).clone();
		empty_tr
			.attr('data-groupid', parent_groupid  )
			.attr('data-actionid', add_action_actions )
			.attr('data-startdate', add_action_startdate )
			.attr('data-interval', add_action_interval )
			.attr('data-interval_options', add_action_interval_options );
		
	}
	
	empty_tr
		.removeClass("action_row")
		.addClass("tr_hasValue");
	
	
	var xdays_counter = 0;
	
	$('td', empty_tr).each(function(){
		var data_day = $(this).data('day') || 0;
		var data_weekday = $(this).data('weekday') +"" || "0";
		if(typeof data_day !== 'undefined') {
			
			//clear all cells
			$(this).text('').removeClass("hasValue");
			
			switch(add_action_interval) {
				case "daily":{
					//each day starting from this point in time
					if( ! compareDates( add_action_startdate, "dd.mm.yyyy" , data_day, "dd.mm.yyyy" )) {
						$(this).text(1).addClass("hasValue");
					}
				}
				break;
				case "every_x_days":{
					if( ! compareDates( add_action_startdate, "dd.mm.yyyy" , data_day, "dd.mm.yyyy" )) {
						if (xdays_counter % Number(add_action_interval_options) == 0) {
							$(this).text(1).addClass("hasValue");
						}
						xdays_counter++;
					}
				}
				break;
				case "selected_days_of_the_week":{
					if( ! compareDates( add_action_startdate, "dd.mm.yyyy" , data_day, "dd.mm.yyyy" )) {
						if ($.inArray( data_weekday, add_action_interval_options ) != -1) {
							$(this).text(1).addClass("hasValue");
						}
					}
				}break;
			
			}
		}
	});
	
	
	//first column edit action text
	$('td:first', empty_tr)
		.html(add_action_actions_text)
		.addClass("first_column_action");

	
	if (mode == "edit") {		
		//nothing to insert
	} else if(mode == "append"){
		//append our new action row
		$( empty_tr ).insertAfter( parent_tr);
	}

	//set last colum with the counter
	row_total_visits(empty_tr);
	
	row_selected_hours(parent_groupid);
	
}


//double-clicked the action, you want to edit
function edit_add_action_line( _this )
{
	add_action_dialog(_this , 'edit');
}


//onclick radio button
function onclick_action_interval(_this) 
{
	var interval = $(_this).val();
	
	$(".interval_options").hide();
	$(".interval_options."+interval).show();
}


//onclick select_hour glyph
function add_hour_dialog(_this)
{
	$('#add_hour_dialog').dialog({
		autoOpen: true,
		modal: true,
		title: translate('rlppatientcontrol_lang')['add_hour_title'],
		buttons: [
		          {
					  
					text: translate('rlppatientcontrol_lang')['noconfirm'],
					click: function() {
						$( this ).dialog( "close" );
					}
				  },
  
		          {
					text: translate('rlppatientcontrol_lang')['edit_action'],
					click: function() {

						changes = true;
						
						add_hour_group($(_this).parents("tr.action_row"), $(".add_hour_dialog_date").val());
						
						$( this ).dialog( "close" );
						
					}
		          },
		          
		          
			]
	});
}

//add_hour_dialog save
function add_hour_group(parent_tr, hour) 
{
	//set attribute data-selected_hour
	$(parent_tr).data('selected_hour', hour);
	
	//update action row hours
	row_selected_hours($(parent_tr).data('groupid'));	
}

//update the actions rown by adding the hours
function row_selected_hours(groupid)
{
	//first clear all
	$('tr.action_row[data-groupid="'+groupid+'"] td')
	.not(':first').not(':last')
	.text("");
	
	var selected_hour = $('tr.action_row[data-groupid="'+groupid+'"]').data('selected_hour') || "";
		
	$('tr.tr_hasValue[data-groupid="'+groupid+'"]').each(function(){
		$('td', this).each(function(i){
			if($(this).hasClass('hasValue')) {
				$('tr.action_row[data-groupid="'+groupid+'"] td:eq('+i+')').text(selected_hour);
			}
		});
	});
}

//delete one group with confirm dialog
function remove_groupid_dialog( _this)
{
	$('#remove_groupid_dialog').dialog({
		autoOpen: true,
		modal: true,
		title: translate('rlppatientcontrol_lang')['remove_groupid_dialog_title'],
		buttons: [
		          {
					text: translate('noconfirm'),
					click: function() {	$( this ).dialog( "close" );	}
		          },
		          
		          {
					text: translate('yesconfirm'),
					click: function() {
						//remove all group rows
						changes = true;
						var groupid = $(_this).parents("tr.action_row").data("groupid");
						$('tr[data-groupid="'+groupid+'"]').remove();	
						$( this ).dialog( "close" );
					}
		          },
		          
			]
	});

}



*/
function clear_hidden_inputs()
{
	$("input[name^='data[']").remove(); 
}
//function to create hidden inputs from our custom obj
function create_hidden_inputs(obj) 
{	
	var name = '';	
	$.each( obj, function( groupid, actions ) {		
		$.each( actions, function( actionid, values ) {
			$.each( values, function( key, val ) {
				
				if (key=="data") {
					$.each( val, function( day_key, day_val ) {
						name = 'form['+actionid+']['+day_key+']';
						$('<input />')
						.attr('type', 'hidden')
						.attr('name', name)
						.attr('value', day_val)
						.appendTo('#form_rlp');
					});
				}else {
					name = 'form['+actionid+']';
					$('<input />')
					.attr('type', 'hidden')
					.attr('name', name)
					.attr('value', val)
					.appendTo('#form_rlp');
				}
			});
		});
	});
	
	return true;
}
// create hidden inputs from out table and submit the form
function save_form(_this) {
	var result = []; // this is for console.log
	var result_obj = {};
 	
	$("#data_table .tr_hasValue").each(function(){
		
		var new_group = {};
//		new_group.groupid =  $(this).data('actionid');
		new_group.actionid =  $(this).data('actionid');
	
		var data = {};
		$("td input.products", this).each(function(){
			var day = $(this).data('day');
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


 
