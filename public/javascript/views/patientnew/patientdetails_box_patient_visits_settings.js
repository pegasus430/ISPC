;
/**
 * box_patient_visits_settings.js
 * @cla on 11.07.2018 
 */
//add this in a js that is only included if module or box
var tourplan_users_fetched = false;
var tourplan_selectbox = {};

function tourplan_fetch_users_callback(response){

			if (response === null || typeof response !== 'object'){
				return false;
			}

			var inactive_users = [];
			if (response.user_details != undefined)
			$.each(response.user_details, function(is, values) {
				$.each(values, function(k, v) {
					if( k == "isactive" && v == '1'){
						inactive_users.push(is); 
					}
				});
				
			});

			tourplan_users_fetched = true;
			var text = "<select name='tourenplan_select' class='tourenplan_select'>" + "\n";
				text += "<option value='0' data-type='none' selected>" + translate('please select') + "</option>";
			
			if (response.grups != undefined)
			$.each(response.grups, function(i, value) {			
				$disabled_option = "";
				if (value.groupname == null){ value.groupname  = translate('no_group'); $disabled_option = "disabled";} 
				text += '<option class="group-result group-main" value="' + i + '" data-type="grups" '+$disabled_option+'>' + value.groupname + '</option>'+ "\n";
				$.each(value, function(k, v) {
					if(k!="groupname"){
						if(jQuery.inArray( k, inactive_users ) != "-1"){
//							text += '<option class="opt" value="' + k + '" data-type="user" disabled>' + v + '(Inaktiv)</option>' + "\n";
						} else  {
							text += '<option class="opt" value="' + k + '" data-type="user">' + v + '</option>' + "\n";
						}
					}
				});
			});
							
			var pseudo1 = "";
			var pseudo2 = "";		
			if (response.pseudogrups != undefined)
			$.each(response.pseudogrups, function(i, value) {										
				if (i=="groupname"){ 
					pseudo1 = '<option class="group-result group-main" value="pseudogrups" data-type="pseudogrups" disabled>' + value + '</option>'+ "\n";
				}
				else{
					pseudo2 += '<option class="opt" value="' + i + '" data-type="pseudogrups">' + value + '</option>' + "\n";
				}									
			});
			if (pseudo2!=""){
				text += pseudo1 + pseudo2;
			}		
			
			text += "</select>";
			tourplan_selectbox = $.parseHTML( text );	
}//function tourplan_fetch_users_callback

function tourplan_fetch_users(){
	$.ajax({
		dataType: "json",
		url : appbase+'user/getjsondatav2',
		success : function(response) {		
			tourplan_fetch_users_callback(response);
		},
		error : function(){
			alert("please refresh page or contact admin");
		}
	});
}//function tourplan_fetch_users

var row_counter = 0;
function assign_tourenplan( visitor_id,  visitor_type,  days, is_disabled){
	row_counter++;
	
	var add_day = [];
	var input_disabled =  "";
	if (is_disabled =="1")  input_disabled =  " disabled=true ";
	
	add_day[1] = add_day[2] = add_day[3] = add_day[4] = add_day[5]= add_day[6] = add_day[7] = '<input type="text" style="width:18px; padding:1px; height: 26px;" '+input_disabled+'>';
	
	if ( days !== null && days !== undefined  && typeof days === 'object'){
		$.each(days, function(i, v){
			input_disabled = "";
			if (v != "0" && v!= ""){
				if (is_disabled =="1")  input_disabled =  " disabled=true ";
			
				add_day[i] = '<input value="'+v+'" type="text" style="width:18px; padding:1px; height: 26px;" '+input_disabled+'>';
			}
		});
	}
	

	var a_delete ='<a onclick="delete_tourenplan(\''+row_counter+'\')" href="javascript:void(0)" class="delete_row" ><img src="'+res_path+'/images/action_delete.png" /></a>';
	
	var _rowClass = row_counter % 2 === 0 ? 'odd' : 'even' ; 
	
	$('#table_visits_per_day tbody .add_new_row').before('<tr id="visits_row_'+row_counter+'" data-row_counter="'+row_counter+'" class="'+_rowClass+'">'
											+'<td id="visits_select_'+row_counter+'"></td>'
											+'<td class="x_cross" data-day="1" title="' + translate('Monday') + translate('visits_per_day') + '">'+ add_day[1] +'</td>'
											+'<td class="x_cross" data-day="2" title="' + translate('Tuesday') + translate('visits_per_day') + '">'+ add_day[2] +'</td>'
											+'<td class="x_cross" data-day="3" title="' + translate('Wednesday') + translate('visits_per_day') + '">'+ add_day[3] +'</td>'
											+'<td class="x_cross" data-day="4" title="' + translate('Thursday') + translate('visits_per_day') + '">'+ add_day[4] +'</td>'
											+'<td class="x_cross" data-day="5" title="' + translate('Friday') + translate('visits_per_day') + '">'+ add_day[5] +'</td>'
											+'<td class="x_cross" data-day="6" title="' + translate('Saturday') + translate('visits_per_day') + '">'+ add_day[6] +'</td>'
											+'<td class="x_cross" data-day="7" title="' + translate('Sunday') + translate('visits_per_day') + '">'+ add_day[7] +'</td>'
											+'<td title="' + translate('candelete')+ '">' + a_delete + '</td>'
											+'</tr>');
	
	var clone_select = $(tourplan_selectbox).clone().prop('id', 'visits_selectbox_'+row_counter );
	
	$('#table_visits_per_day #visits_select_'+row_counter).append( clone_select );	
	if(visitor_id != "0" && visitor_type != "0") {
		$('#visits_selectbox_'+row_counter +' option[value="'+visitor_id+'"][data-type="'+visitor_type+'"]').attr('selected',true);
		
		$('#visits_selectbox_'+row_counter )
			.attr('data-lastSelected', visitor_id)
			.attr('data-lastType', visitor_type);
		
		if (is_disabled == "1"){
			$('#visits_selectbox_'+row_counter ).attr('disabled', 'disabled');
			$('#table_visits_per_day #visits_select_'+row_counter).append( "<br><span>" + translate('user_cantvisit_or_inactive_label') + "</span>" );
		}
	}
	
	//remove users that at this moment are disabled
	if (is_disabled != "1" && visits_settings_disabled_users !== undefined && typeof visits_settings_disabled_users == 'object' && visits_settings_disabled_users.length >0 )
	{
		$.each(visits_settings_disabled_users, function(i,v){
			$('#visits_selectbox_'+row_counter+ " option[value='"+v+"']" ).remove();
		});		
	}
	
	
	$('#visits_selectbox_'+row_counter)
	.chosen({
		/*placeholder_text_single: "<?php echo $this->translate('please select'); ?>",*/
		multiple:0,
		inherit_select_classes: 1,
		"search_contains": true,
		/*no_results_text: "<?php echo $this->translate('noresultfound'); ?>",*/
		width: "140px"
	})
	.change(function(e, h){

		var newVal = $(this).val();
		var newId = $(this).attr('id');
		var data_type = $('option:selected', this).attr('data-type');
		
		var lastSelected =  $(this).attr('data-lastSelected');
		var lastType =  $(this).attr('data-lastType');
		
		if (lastSelected == undefined) {
			lastSelected = 0;
			lastType = "none";
			$(this).attr('data-lastSelected' , 0);
			$(this).attr('data-lastType' , 'none');
		}
		
		if (newVal!="0" )
		$("select[name='tourenplan_select']").each( function() {
			if ( $(this).attr('id') == newId) return true;			
			if ($(this).val() == newVal){
				newVal = lastSelected;
				return false;
			}
		});			
		
		if ( newVal == lastSelected ){
			//change back
			$('option[value="'+lastSelected+'"][data-type="'+lastType+'"]', this).attr('selected',true).trigger('chosen:updated');				
		}else{
			$(this).attr('data-lastSelected', newVal);
			$(this).attr('data-lastType', data_type);
			

			//make a post with new values
			//get all days
			var visit = {};
			var data_day = "";
			var visit_enabled = "";	
			var cnt = 0;
			$(".x_cross", $( this ).closest('tr')).each(function(){
				data_day = $( this ).attr("data-day");
				visit_enabled = $("input", this ) .val();
				visit[data_day] = visit_enabled;
				if (visit_enabled !="" && visit_enabled!="0")  cnt = 1; 
			});
			if(cnt > 0 ){
				//make the call
				var new_post = {};
				new_post['visit'] = visit;
				new_post['data_type'] = data_type;
				new_post['selectbox'] = newVal;
					
				if(lastSelected != 0){
					//set as isdeleted the old
					//oncomplete of this VISITOR-delete, perform assign of ne user	
					visits_ajax(0, lastType , lastSelected , 1 , new_post, row_counter);	
				}else{
											
					visits_ajax(visit, data_type , newVal , 0, null, row_counter);	
				}
				$("#visit_duration_table").show();
			}
			
			
		}		
	});		
	
	
	$("#visits_row_"+row_counter+ " .x_cross input")
		.focus(function() {
			$(this).attr("data-oldvalue", $(this).val());		
		})
		.blur(function() {

			var input_value =  $( this ).val();
			if (isNaN(input_value)) {
				$( this ).val($(this).attr("data-oldvalue"));
				return false;
			}
			//old value is the same
			if ( $(this).attr("data-oldvalue") == input_value ) return false;
			//no user selected			
			var data_type = $("select option:selected",$( this ).closest("tr")).attr('data-type');
			if (data_type == "none") return false;
			
			var selectbox = $("select", $( this ).closest("tr")).val();
			
			var visit_enabled = "";
			var data_day = $( this ).closest("td").attr("data-day");
			var visit = {};		
		
			visit[data_day] = input_value;
			
			var this_input_row_counter = $( this ).closest("tr").data('row_counter');
			visits_ajax(visit, data_type , selectbox , 0 , null, this_input_row_counter);	
			
			//show the visit duration table
			$("#visit_duration_table").show();
   		});
	
	
}//function assign_tourenplan


function visits_ajax(visit, data_type , selectbox, isdeleted, new_post, row_counter){
	
	if (selectbox == "0") return false;
	if (! checkclientchanged()) {
		return false;
	}
	if (new_post === null || isdeleted == 1){
		$("#table_visits_per_day #visits_row_"+row_counter)
			.after("<tr class='visits_ajax_loading'><td colspan='9'>&nbsp;" + translate('loadingpleasewait') + "</td></tr>")
			.find('*')
			.attr("disabled", true);
	}
		
	var nice_user = $("option[data-type='"+data_type+"'][value='"+selectbox+"']" , tourplan_selectbox).text();

	$.ajax({
		dataType: "json",
		method: "POST",
		type: "POST",
		url: appbase+'patient/updatepatientinfo?modname=VisitsSettings&patid=' + window.idpd,
		data: {
			'visit': visit,
			'visitor_type': data_type,
			'visitor_id': selectbox,
			'patid': window.idpd,
			'id': window.idpd, //"<?php echo $_REQUEST['id']; ?>",
			'modname': "VisitsSettings",
			'isdeleted': isdeleted,
			'formid': "grow43",
			'nice_user' : nice_user,
		},
		success:function(data){
			if (new_post!== null && typeof(new_post) == 'object'){
				
				$(".visits_ajax_loading").remove();
				visits_ajax(new_post.visit , new_post.data_type , new_post.selectbox , 0, null, row_counter);

			}else{
				$(".visits_ajax_loading").remove();
				$("#table_visits_per_day #visits_row_"+row_counter)
				.find('*')
				.attr("disabled", false);
			}
			
		},
		error : function(){}
		});
}//function visits_ajax


function delete_tourenplan(row_counter){

	var visitor_id = $('#visits_selectbox_'+row_counter).val();
	var visitor_type = $('#visits_selectbox_'+row_counter+' option:selected').attr('data-type');
		
	
	visits_ajax(0, visitor_type , visitor_id , 1);	
	$("#visits_row_"+row_counter).remove();

}

var visits_settings_disabled_users = {},
pat_visits_settings_visiting_users = {},
pat_visits_settings = {};

/* set saved touren grow43*/
$(document).ready(function(){
	
	visits_settings_disabled_users = patientVisitsSettings.pat_visits_settings_disabled_users;
	pat_visits_settings_visiting_users = patientVisitsSettings.pat_visits_settings_visiting_users;
	pat_visits_settings = patientVisitsSettings.pat_visits_settings;
	 
	
	if( $(pat_visits_settings_visiting_users).length > 0) {
		tourplan_fetch_users_callback( pat_visits_settings_visiting_users);
	}else{
		tourplan_fetch_users();
	}

	if( $(pat_visits_settings).length > 0  ) {		
		$.each(pat_visits_settings, function(visitor_id, types) {

			$.each(types, function(visitor_type, days) {
				
				if (visitor_type == 'is_disabled') return true;
				
				var _is_disabled = 0;
				
				if ('is_disabled' in types && types.is_disabled == "1") {
					//set this uservisit_plan as disabled and allow to deleteit
					_is_disabled = "1";
				}
				
				assign_tourenplan(visitor_id, visitor_type, days, _is_disabled ); 

			});
			
		});
		
		
		$("#visit_duration_table").show();
	}

});

