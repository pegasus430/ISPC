
//ISPC-2329 pct.c)
$(".sh_selector_medication_unit").live('change', function () {

	var unit_text = $(this).find('option:selected').text();
	var concentration_input_id = $(this).closest("td").find('.medication_concentration').attr('id');
			
	var new_placeholder = ""+unit_text+" pro ml"
	  $('#'+concentration_input_id).attr('placeholder',new_placeholder);
	  $('#lbl_'+concentration_input_id).val(new_placeholder);			//ISPC-2684 Lore 15.10.2020

	//ISPC-2684  Lore 08.10.2020
	var dosage_id = $(this).closest("tr").find('.ph_dosage').attr('id');
	  $('#'+dosage_id).attr('placeholder',"Dosierung in ml/h");
	var dosage_24h_id = $(this).closest("tr").find('.ph_dosage_24h').attr('id');
	  $('#'+dosage_24h_id).attr('placeholder',"Dosierung in ml/24h");	
	  
	var dosage_unit_id = $(this).closest("tr").find('.unit_dosage').attr('id');
	var new_placeholder_unit = "Dosierung in "+unit_text+"/h";
	  $('#'+dosage_unit_id).attr('placeholder',new_placeholder_unit);
	  $('#lbl_'+dosage_unit_id).val(unit_text + '/h');

	
	var dosage_unit_24_id = $(this).closest("tr").find('.unit_dosage_24h').attr('id');
	var new_placeholder_unit_24 = "Dosierung in "+unit_text+"/24h";
	  $('#'+dosage_unit_24_id).attr('placeholder',new_placeholder_unit_24);
	  $('#lbl_'+dosage_unit_24_id).val(unit_text + '/24h');
	//.		
});
//..


$('.expand_details').live('click',function(){
	var med_id = $(this).data('expand_details_id');
	
	 
	if( $('.medication_row_'+med_id).hasClass('expanded')){
		$('.medication_row_'+med_id).removeClass('expanded')
		$('.medication_details_row_'+med_id).hide();
	} else{
		$('.medication_row_'+med_id).addClass('expanded')
		$('.medication_details_row_'+med_id).show();
		
	}
});


$('.ack_expand_details').live('click',function(){
	var med_id = $(this).data('expand_details_id');
	if( $('.ack_medication_row_'+med_id).hasClass('expanded')){
		$('.ack_medication_row_'+med_id).removeClass('expanded')
		$('.ack_medication_details_row_'+med_id).hide();
	} else{
		$('.ack_medication_row_'+med_id).addClass('expanded')
		$('.ack_medication_details_row_'+med_id).show();
		
	}
});



$('.offline_ack_expand_details').live('click',function(){
	var med_id = $(this).data('expand_details_id');
	console.log(med_id );
	
	if( $('.offline_ack_medication_row_'+med_id).hasClass('expanded')){
		$('.offline_ack_medication_row_'+med_id).removeClass('expanded')
		$('.offline_ack_medication_details_row_'+med_id).hide();
	} else{
		$('.offline_ack_medication_row_'+med_id).addClass('expanded')
		$('.offline_ack_medication_details_row_'+med_id).show();
		
	}
});

	//used as sprintf()
	if (!String.prototype.format) {
	  String.prototype.format = function() {
	    var args = arguments;
	    return this.replace(/\%(\d+)\%/g, function(match, number) { 
	      return typeof args[number] != 'undefined'
	        ? args[number]
	        : match
	      ;
	    });
	  };
	}

	
	function getmedicationdeletededit() {
		var url = appbase + 'patientmedication/deletededit?id=' + idpd;
		xhr = $.ajax({
			url : url,
			success : function(response) {
				$('#deleted_medication_list').html(response);
			}
		});
	}

	function isNumeric(value) {
		if (value != null && !value.toString().match(/^[0-9\.,]*$/)) return false;
		return true;
	}
	
	
	
	

	function flussarte_calucation(field,pumpe_nr,med_line)
	{
		//console.log(field)
		//console.log(pumpe_nr)
		//console.log(med_line)
	}	
	
	
	function tragerlosung_calculation(){
		
	}	
	
	
	function medi_child_toggle(_this , open_now) {

 
		//open_now PARAM is used to chnage all row to open or close
		if (open_now !== undefined && typeof(open_now) === "boolean"){
			if (open_now) {
				$(_this).next("tr.medi_child_toggle_td").show();
				
				$(_this).addClass('rotate180').addClass('no_bottom_border');
			} else {
				
				$(_this).next("tr").hide();
				
				$(_this).removeClass('rotate180').removeClass('no_bottom_border');
			}
			
		} else {
			
			
			$(_this).parent('tr').next('tr').toggle();
			$(_this).toggleClass('rotate180').toggleClass('no_bottom_border');
		}
	}
	
	
	function add_dosage_intervals_after (_this) 
	{
		
		var max_fields = 12;
		
		var td_holder = $(_this).closest(".dosage_intervals_holder");
		
		var count_fields = Number($(td_holder).find('input.time_interval_style').length);
		if(count_fields >= max_fields){
			return false;
		}
		
		//toggle all + signs
		
		if (count_fields == max_fields-1) {
			td_holder.find('a.add_button').attr("disabled", true); 
		} else {
			td_holder.find('a.add_button').attr("disabled", false); 
		}
		
		//toggle all - signs
		if (count_fields >= 4 ) { 
			td_holder.find('a.hidden_delete').show(); 
		} else {
			td_holder.find('a.hidden_delete').hide();
		}
		
		
		//var cloned = $(td_holder).find('input.time_interval_style:eq('+(count_fields-1)+')').closest("div").clone( );
		//var cloned = $(td_holder).find('input.time_interval_style:eq(0)').closest("div").clone( );
		var cloned = $(_this).closest('th').clone();	
		
		cloned.find("input").timepicker('destroy');
		cloned.find("input").val('').removeClass('hasTimepicker').removeAttr("id");					
		cloned.find("input.interval_id").remove();	
		cloned.find("input.custom").val("1");	
		
		//$(cloned).insertBefore($(_this).closest("div"));
		$(cloned).insertAfter($(_this).closest("th"));
	
		
		//renumber all input names
		dosage_reset_ints(_this);
		
		dosage_intervals_timepicker(_this);
		

		if (individual_medication_time == "0") {
			//insert in each table
			$(".dosage_intervals_holder_header", $("table.medication_edit_table")).each(function(){
	
				var parent_table = $(this).closest("table");
				
				//increment colspan of the thead dosage holder
				$(this).attr("colspan",(count_fields+1));
								
				dosage_column_insert($(td_holder).find('a.add_before_btn').last(), "insert_after", parent_table);
			});

		} else {
			//insert just in this table	
			//increment colspan of the thead dosage holder
			$('.dosage_intervals_holder_header', $(_this).closest('table.medication_edit_table')).attr("colspan",(count_fields+1));	
				
			dosage_column_insert($(td_holder).find('a.add_before_btn').last() , "insert_after");
		}	
		
		//remove extra add_after_button
		$(_this).remove();
		
		
	}
	
	function add_dosage_intervals_before (_this) 
	{
			
		var max_fields = 12;
		
		var td_holder = $(_this).closest(".dosage_intervals_holder");
		
		var count_fields = Number(td_holder.find('input.time_interval_style').length);
		
		if(count_fields >= max_fields){
			return false;
		}
	
		//toggle all + signs
		
		if (count_fields == max_fields-1) {
			td_holder.find('a.add_button').attr("disabled", true);  
		} else {
			td_holder.find('a.add_button').attr("disabled", false); 
		}
		
		//toggle all - signs
		if (count_fields >= 4 ) { 
			td_holder.find('a.hidden_delete').show(); 
		} else {
			td_holder.find('a.hidden_delete').hide();
		}
		
		//var cloned = $(_this).parent().clone();	
		var cloned = $("th:eq(0)" , td_holder).clone();		
		
		cloned.find("input").timepicker('destroy');
		cloned.find("input").val('').removeClass('hasTimepicker').removeAttr("id");	
		cloned.find("input.interval_id").remove();	
		cloned.find("input.custom").val("1");


		$(cloned).insertBefore($(_this).closest("th"));
		
		//renumber all input names
		dosage_reset_ints(_this);
			
		dosage_intervals_timepicker(_this);
				
		
		if (individual_medication_time == "0") {
			//insert in each table
			
			$(".dosage_intervals_holder_header", $("table.medication_edit_table")).each(function(){

				var parent_table = $(this).closest("table");
				
				//increment colspan of the thead dosage holder
				$(this).attr("colspan",(count_fields+1));
								
				dosage_column_insert(_this, "insert_before", parent_table);
			});
		} else { 
			//insert just in this table	
			dosage_column_insert(_this);
			
			//increment colspan of the thead dosage holder
			$('.dosage_intervals_holder_header', $(_this).closest('table.medication_edit_table')).attr("colspan",(count_fields+1));
			
		}
		
		return true;
	
	}
	
	
	function dosage_reset_ints(_this)
	{
		//renumber all input names
		var my_parent_table = $(_this).closest('table.medication_edit_table');
		var my_dosages = new Array(); 
		$('div.one_dosage_hour', $(".dosage_intervals_holder" , my_parent_table) ).each(function(i){

			$("input", this).each(function(){
				
				var input_name = $(this).attr("name");
				
				var arr = $(this).attr("name").split('][');
				if (arr[1] !== undefined) {
					arr[1] = i;
				}
				
				input_name = arr.join("][");	
								
				$(this).attr("name", input_name);			
				
			});
			
		});
		
		
	}
	
	function dosage_intervals_timepicker ( _this ) 
	{
		
		var parent_table = null;
		
		if (_this != undefined) {
			
			parent_table = $(_this).closest('table.medication_edit_table');
		}
		if (parent_table == null) {
			return false; //what?
		}
		
		var dosages = new Array();
		
		$('input.time_interval_style', $(".dosage_intervals_holder" , parent_table) ).each(function(i){
			dosages[i] = $(this).val();
		});
		

		
		
		var previous_h = '';
		var next_h = '';
		
		var dosages_limits = {};
		
		for (var i = 0; i < dosages.length; i++) {

			if (dosages[i-1] != undefined) {
				previous_h = dosages[i-1];
				if (previous_h == '') {
					if (dosages[i-2] != undefined) {
						previous_h = dosages[i-2];
					} else {
						previous_h = "00:00";
					}
				}
			} else {
				previous_h = "00:00";
			}

			if (dosages[i+1] != undefined) {
				next_h = dosages[i+1];
				if (next_h == '') {
					if (dosages[i+2] != undefined) {
						next_h = dosages[i+2];
					} else {
						next_h = "24:00"; //ISPC-2329 Carmen 13.01.2020
					}
				}
			} else {
				next_h = "24:00"; //ISPC-2329 Carmen 13.01.2020
			}
			
			dosages_limits[i] = {};
			
			var arr = previous_h.split(':');
			dosages_limits[i]['min_hour'] = arr.shift(); 
			dosages_limits[i]['min_minutes'] = arr.pop();
			
			var arr = next_h.split(':');
			dosages_limits[i]['max_hour'] = arr.shift(); 
			dosages_limits[i]['max_minutes'] = arr.pop();					
			
		}
		
		
		$('input.time_interval_style', $(".dosage_intervals_holder" , parent_table) ).each(function(i){
			
			if ($(this).hasClass('hasTimepicker')) {
			
		        $(this).timepicker('option', {
		        		hours: {starts: 0, ends: 24}, //ISPC-2329 Carmen 13.01.2020
		        		minTime: { hour: dosages_limits[i]['min_hour'], minute: dosages_limits[i]['min_minutes']},
		        	 	maxTime: { hour: dosages_limits[i]['max_hour'], minute: dosages_limits[i]['max_minutes']}
		        	});      
		  
			} else {

				//attach a timepicker to this input that should be also readonly
				$(this).timepicker({
		    		
					hours: {starts: 0, ends: 24}, //ISPC-2329 Carmen 13.01.2020
					minTime: { hour: dosages_limits[i]['min_hour'], minute: dosages_limits[i]['min_minutes'] },
			        maxTime: { hour: dosages_limits[i]['max_hour'], minute: dosages_limits[i]['max_minutes'] },
			        
			        defaultTime : dosages_limits[i]['min_hour']+":"+dosages_limits[i]['min_minutes'],
			        
			        showLeadingZero: true,

					onSelect : function(time_value) {
		    			//$(this).focus();
		    			//return false;
		    		},
		    		
		    		onClose : function() {
		    			dosage_intervals_timepicker(this); 
		    			
		    			var _this = this;
		    			if (individual_medication_time == "0") {
			    			//insert in each table
			    			
			    			$(".dosage_intervals_holder_header", $("table.medication_edit_table")).each(function(){
			    				var parent_table = $(this).closest("table");
			    				dosage_column_update(_this, parent_table);
			    			});

		    			} else {
		    				//just in this table	
		    				dosage_column_update(this);
		    			}

		    		},
		    		
		    		minutes : {
		    			interval : 5
		    		},
		    		
		    		showPeriodLabels : false,
		    		rows : 4,
		    		hourText : 'Stunde',
		    		minuteText : 'Minute'
		        });
				
				//attach onchange event to the input hour+minutes
				$(this).change(function(eventData, handler ){
					
					var my_parent_table = $(this).closest('table.medication_edit_table');
					var my_dosages = new Array(); 
					$('input.time_interval_style', $(".dosage_intervals_holder" , my_parent_table) ).each(function(i){
						my_dosages[i] = $(this).val();
					});
					
					for (var i = 1; i < my_dosages.length; i++) {
						
						var d0 = parseInt( my_dosages[i-1].replace(new RegExp(":", 'g'), '') , 10);
						var d1 = parseInt( my_dosages[i].replace(new RegExp(":", 'g'), ''), 10);
						
						if (isNaN(d0) || isNaN(d1)  || String(d0)== "" || String(d1) == "" || d0  >= d1) {
							
							setTimeout(function () {alert(translate("intervals must be consecutive"));}, 50);
							return false;
						}
					}
				});
				
			}
				
		
		});
		
		return true;	
	}
	
	function dosage_column_insert(_this , insert_after , parent_table) {

		// will be used to prepend column
		//it only works if 1 column exists in front.. else index + columns_infrom-1
		var dosage_div_index = $("div.one_dosage_hour", $(_this).closest('.dosage_intervals_holder')).index( $(_this).parent() ); 
		dosage_div_index = dosage_div_index +1; // Added 1 because new column was added at the begining
		var dosage_count = $("div.one_dosage_hour", $(_this).closest('.dosage_intervals_holder')).length;		
		
		
		if (parent_table == undefined) {
			var parent_table = $(_this).closest('table.medication_edit_table');
		}
		
		
		$("tr:not(.child_row)", parent_table).not(':eq(0)').each(function(){

			var cloned_td = $("td:eq("+dosage_div_index+")", this).clone();
			
			cloned_td.find('.mifo span').text('');
		
			cloned_td.find('.dosage_label').text('- : -');
			
			//change first input name
			var firstrow_input = cloned_td.find('.dosage_firstrow_div input.dosage_input').attr("name");
			if (firstrow_input != undefined) {
				firstrow_input = firstrow_input.slice(0, firstrow_input.lastIndexOf("["));
				firstrow_input += '[]';
			}			
			cloned_td.find('.dosage_firstrow_div input.dosage_input')
				.val('')
				.attr("name", String(firstrow_input))
				.attr("disabled", true)

			
			//change second input name
			var secondrow_input = cloned_td.find('.dosage_secondrow_div input.dosage_input').attr("name");
			if (secondrow_input != undefined) {
				secondrow_input = secondrow_input.slice(0, secondrow_input.lastIndexOf("["));
				secondrow_input += '[]';
			}
			cloned_td.find('.dosage_secondrow_div input.dosage_input')
				.val('')
				.attr("name", String(secondrow_input))
				.attr("disabled", true)
			
			if (insert_after !=undefined && insert_after == "insert_after"){
				$(cloned_td).insertAfter($("td:eq("+dosage_div_index+")", this));
			} else {
			
				$(cloned_td).insertBefore($("td:eq("+dosage_div_index+")", this));
			}
			
		});
		
		//increment colspan
		$("tr.child_row_holder", parent_table).each(function(){
			$("td:eq(0)", this).attr("colspan", dosage_count+2);			
		});
		
		
		//re-index dosages inputs
		recall_tabindex();
		
	}
	
	function dosage_column_update(_this , parent_table) {
		
		//it only works if 1 column exists in front.. else increment the var dosage_div_index as you see fit
		//var dosage_div_index = $("div.one_dosage_hour", $(_this).parent().parent()).index( $(_this).parent() ); 
		var dosage_div_index = $("th", $(_this).closest('.dosage_intervals_holder')).index( $(_this).closest('th') ); 
			dosage_div_index = dosage_div_index +1; // Added 1 because new column was added at the begining
			
		var dosage_div_value = $(_this).parent().find("input.time_interval_style").val();
		var dosage_div_value_no = parseInt( dosage_div_value.replace(new RegExp(":", 'g'), '') , 10);
		
		if (dosage_div_value == "") {
			dosage_div_value = '- : -';
		}
		
		if (parent_table == undefined) {
			var parent_table = $(_this).closest('table.medication_edit_table');
		}
		
		$("tr:not(.child_row)", parent_table).not(':eq(0)').each(function(){
		
			var this_td = $("td:eq("+Number(dosage_div_index+1)+")", this);
			
			this_td.find(".dosage_label").text(dosage_div_value);		
			
			//change first input name
			var firstrow_input = this_td.find('.dosage_firstrow_div input.dosage_input').attr("name");
			
			if (firstrow_input != undefined) {
				firstrow_input = firstrow_input.slice(0, firstrow_input.lastIndexOf("["));
				firstrow_input += '['+dosage_div_value+']';
			}
			//change first input data-dosage_column_info
			var dosage_column_info = this_td.find('.dosage_firstrow_div input.dosage_input').data("dosage_column_info");
			if (dosage_column_info !== undefined) {
				var arr = dosage_column_info.split('_');	
				arr.pop();
				dosage_column_info = arr.join("_");	
			}

			//change first input #id
			var id = this_td.find('.dosage_firstrow_div input.dosage_input').attr("id");
			if (id !== undefined) {
				var arr = id.split('_');	
				arr.pop();
				id = arr.join("_");	
			}
			this_td.find('.dosage_firstrow_div input.dosage_input')
				.attr("name", String(firstrow_input))
				.attr("disabled", false)
				.attr("id", id + "_"+dosage_div_value_no)
				.data("dosage_column_info" , dosage_column_info + "_"+dosage_div_value_no);
			
			//change second input name
			var secondrow_input = this_td.find('.dosage_secondrow_div input.dosage_input').attr("name");
			if (secondrow_input != undefined) {
				secondrow_input = secondrow_input.slice(0, secondrow_input.lastIndexOf("["));
				secondrow_input += '['+dosage_div_value+']';
			}
			
			//change second input #id
			var id = this_td.find('.dosage_secondrow_div input.dosage_input').attr("id");
			if (id !== undefined) {
				var arr = id.split('_');	
				arr.pop();
				id = arr.join("_");	
			}
			
			this_td.find('.dosage_secondrow_div input.dosage_input')
				.attr("name", String(secondrow_input))
				.attr("disabled", false)
				.attr("id", id + "_"+dosage_div_value_no)
				.data("dosage_column_info" , dosage_column_info + "_"+dosage_div_value_no);
						
		});
	}
	
	function dosage_column_delete(_this, parent_table) {
		
		var dosage_div_index = $("div.one_dosage_hour", $(_this).closest(".dosage_intervals_holder")).index( $(_this).parent() ); 
			dosage_div_index = dosage_div_index+1;
		var dosage_count = $("div.one_dosage_hour", $(_this).closest(".dosage_intervals_holder")).length;
		
		if (parent_table == undefined) {
			var parent_table = $(_this).closest('table.medication_edit_table');
		}
		
		$("tr:not(.child_row)", parent_table).not(':eq(0)').each(function(){
			
			var this_td = $("td:eq("+Number(dosage_div_index+1)+")", this);
			
			//verify it to contain the input
			//optionaly you could verify if label_text is the same as _this input value
			//if ( this_td.find("input.dosage_input").length ){
				//this_td.remove();
			//}
			this_td.remove();

		});
		
		//decrement colspan
		$("tr.child_row_toggler", parent_table).each(function(){
			$("td:eq(0)", this).attr("colspan", dosage_count+3);
			
		});
		$("tr.child_row_holder", parent_table).each(function(){
			$("td:eq(0)", this).attr("colspan", dosage_count+1);			
		});

		
		return false;
		
	}
	

	function delete_dosage_intervals(_this) { 
		
		var max_fields = 12;
		
		jConfirm(translate('confirmdeleterecord: Note that the time column will be deleted from all patient medication dosage'), translate('confirmdeletetitle'), function(r) {
			if(r)
			{
				var dosage_intervals_holder = $(_this).closest(".dosage_intervals_holder");
				
				var count_fields = Number(dosage_intervals_holder.find('input.time_interval_style').length);

				//minimum of 4 hours
				if(count_fields < 5){
					dosage_intervals_holder.find('a.hidden_delete').hide();
					return true;
				}
				
				//show all + signs
				dosage_intervals_holder.find('a.add_button').show();
				
				
				//hide all - signs
				if (count_fields <= 5 ) {
					dosage_intervals_holder.find('a.hidden_delete').hide();
				}
				
				//mark this id to be deleted
				var now_deleted_interval_id = Number($(_this).parent("div").find("input.interval_id").val());				
				var deleted_ids = dosage_intervals_holder.find("input.deleted_intervals_hidden_input").val();
				if (now_deleted_interval_id) {
					dosage_intervals_holder.find("input.deleted_intervals_hidden_input").val(deleted_ids + ',' + now_deleted_interval_id)
				}
				
				
				if (individual_medication_time == "0") {
    				//each table
	    			$(".dosage_intervals_holder_header", $("table.medication_edit_table")).each(function(){
	    				var parent_table = $(this).closest("table");
	    				dosage_column_delete(_this, parent_table);
	    				
	    				//increment colspan of the thead dosage holder
	    				$(this).attr("colspan",(count_fields-1));
	    			});
				} else {
    				//just in this table	
    				dosage_column_delete(_this);
    				//increment colspan of the thead dosage holder
    				$('.dosage_intervals_holder_header', $(_this).closest('table.medication_edit_table')).attr("colspan",(count_fields-1));
				}
    			
				
				
				dosage_intervals_timepicker(_this);
				
				//renumber all input names
				dosage_reset_ints(_this);
								
				
				var clone_add_after_button =  $(_this).closest('th').find('.add_after_button');
		
				$(_this).closest('th').remove();
				
				if(clone_add_after_button != undefined) {
					//clone_add_after_button
					$(clone_add_after_button).insertAfter($(' input.time_interval_style:last', dosage_intervals_holder));
					
				}
				
				return;
				

			}
		});
	}
	
	
	
	function create_new_pumpe(pumpe_type,pumpecount)
	{
		var sch_pump_block ='';
		sch_pump_block ='<div class="pumpeblock mk-container mk-edit" id="pumpeblock'+pumpecount+'">';
		
		if(pumpecount == 1){
			sch_pump_block +='<div class="med_block_header mk-top  mk-edit-title ">';
		} else {
			sch_pump_block +='<div class="med_block_header mk-top  mk-edit-title inner">';
		}
		/*
		if(pumpe_type =="pca"){
			sch_pump_block +='<h1>'+translate("pcamedicationtitle") + '</h1>';
		} else {
			sch_pump_block +='<h1>'+translate("pumpemedicationtitle") + '</h1>';
		}
		*/
		sch_pump_block +='<h1 id="section_isschmerzpumpe">'+translate("pumps")+'</h1>';
		
		sch_pump_block +='</div>';
		
		sch_pump_block +='<table id="isschmerzpumpe_pumpeblock'+pumpecount+'" class="medikation medication_edit_table" data-medication_type="isschmerzpumpe"><thead>';
		sch_pump_block +='<tr>';
		sch_pump_block +='<th class="sortnum">' + translate("medication_importance") + '</th>';
		sch_pump_block +='<th class="name">' + translate("medication_name") + ' / <span>' + translate("medication_drug")+ '</span></th>';
		sch_pump_block +='<th class="name">' + translate("medication_unit") + ' / <span>' + translate("medication_concentration") + '</span> </th>';
		if(pumpe_type == "pump"){
			sch_pump_block +='<th>' + translate("medication_dosage24h") + '</th>';
		} 
		else
		{
			sch_pump_block +='<th class="doseirung">' + translate("medication_dosage") + '</th>';
		}
		
		sch_pump_block +='<th>' + translate("medication_indication") + '</th>';
		sch_pump_block +='<th colspan="2">' + translate("medication_prescribed_by") + ' / ' + translate("medication_change_date") + '</th>';
		
		sch_pump_block +='</tr>';
		sch_pump_block +='</thead>';

		sch_pump_block +='<tbody>';
		sch_pump_block +='</tbody>';
		sch_pump_block +='</table>';
		
		sch_pump_block +='<input type="hidden" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][id]" value="" />';
		sch_pump_block +='<input type="hidden" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][pumpe_type]" value="'+pumpe_type+'" />';
		sch_pump_block +='<input type="hidden" value="1" id="new_sh_line_isschmerzpumpe_'+pumpe_type+'_'+pumpecount+'">'; 
		
		sch_pump_block +='<a  href="javascript:void(0)" data-pumpe_type="'+pumpe_type+'" data-pumpe_number = "'+pumpecount+'"  data-pumpe_status = "custom" rel="isschmerzpumpe" class="add_button_link  medication_schm_add_link">' + translate("new medication line") + '</a>';

		sch_pump_block +='<table class="medikation datatable_sch">';
		sch_pump_block +='<tr class="single"><td width="16%" ><label>' + translate("Kommentar") + '</label></td><td><textarea class="xxl_input editcomment" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][description]" ></textarea></td></tr>';
		// ISPC-2329 pct. M Lore 21.08.2019  add option = über Port
		sch_pump_block +='<tr class="single"><td><label>' + translate("medication_type") + '</label></td><td><select class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][pumpe_medication_type]"><option value=""> </option><option value="i.v.">i.v.</option><option value="s.c.">s.c.</option><option value="über Port">über Port</option></select></td></tr>';
		//sch_pump_block +='<tr class="single"><td><label>' + translate("Flussrate") + '</label></td><td><input type="text" class="xxl_input extra_calculation" style="max-width:50px;"  data-ec_field="flussrate"   data-medication_type="isschmerzpumpe" data-pumpe_number="'+pumpecount+'" id="flussrate_isschmerzpumpe_'+pumpecount+'" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][flussrate]" value="" /></td></tr>';
		//ISPC-2684 Lore 09.10.2020
		if (show_dosage_unit == "1") {
			sch_pump_block +='<tr class="single"><td><label>' + "Flussrate" + '</label></td><td><input type="text" class="xxl_input extra_calculation" style="max-width:50px;"  data-ec_field="flussrate"   data-medication_type="isschmerzpumpe" data-pumpe_number="'+pumpecount+'" id="flussrate_isschmerzpumpe_'+pumpecount+'" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][flussrate]" value="" /><select class="xxl_input" style="max-width:60px;" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][flussrate_type]"><option value="ml/h">ml/h</option><option value="mg/h">mg/h</option></select> </td></tr>';
		}else {
			sch_pump_block +='<tr class="single"><td><label>' + translate("Flussrate") + '</label></td><td><input type="text" class="xxl_input extra_calculation" style="max-width:50px;"  data-ec_field="flussrate"   data-medication_type="isschmerzpumpe" data-pumpe_number="'+pumpecount+'" id="flussrate_isschmerzpumpe_'+pumpecount+'" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][flussrate]" value="" /></td></tr>';
		}
		sch_pump_block +='<tr class="single"><td><label>' + translate("medication_carrier_solution") + '</label></td><td ><input type="text" class="xxl_input extra_calculation"  style="max-width:50px;"   data-medication_type="isschmerzpumpe"  data-ec_field="carriersolution"  data-pumpe_number="'+pumpecount+'" id="carriersolution_isschmerzpumpe_'+pumpecount+'"  name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][carrier_solution]" value="" /><span class="carriersolution_extra_text" id="carriersolution_extra_text_'+pumpecount+'"></span></td></tr>';
		if(pumpe_type =="pca"){
			sch_pump_block +='<tr class="single"><td><label>' + translate("Bolus") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][bolus]" value="" /></td></tr>';
			sch_pump_block +='<tr class="single"><td><label>' + translate("Max Bolus") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][max_bolus]" value="" /></td></tr>';
			sch_pump_block +='<tr class="single"><td><label>' + translate("Sperrzeit") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][sperrzeit]" value=""  /></td></tr>';
		}
		sch_pump_block +='</table>';
		sch_pump_block +='</div>';
		
		$('#empty_pumpe_block').remove();
		$('#blocks_isschmerzpumpe').append(sch_pump_block);
		
		
		//add default medi for this pump
		var medication_type = 'isschmerzpumpe';	
		var pumpe_number = pumpecount;
		//var pumpe_type = 'pumpe_type';	
		var medcount = $("#new_sh_line_"+medication_type+"_"+pumpe_type+"_"+pumpe_number).val();
		create_new_sh_line(medication_type,medcount,pumpe_number,pumpe_type);

		// increment line
		pumpecount++;
		$('#pumpe_count').val(pumpecount);
			
		
		
	}
	
	
	
	
	
	
	
	
	

	function create_new_sh_line(medication_type,medcount,pumpe_number, pumpe_type)
	{
		if(medcount == undefined) {
			medcount = 0;
		}

		
		// DEFINE tr 
		var tr_start = '<tr id="tr'+medication_type+pumpe_number+medcount+'" class="single">';
		var tr_end = '</tr>';
		
		// DEFINE td 
		var td_start ='<td class="border_bottom_solid">';
//Lore 19.10.2020 
		var td_40start ='<td class="border_bottom_solid" style="width:40%!important">';
		var td_20start ='<td class="border_bottom_solid" style="width:20%!important">';
		var td_15start ='<td class="border_bottom_solid" style="width:15%!important">';
		
		var td_end ='</td>';
		

		// Create MMI button
		if (show_mmi == "1") {
			var mmi_medication_style = 'width:75%!important;';
			var mmi_button_search = '&nbsp;<input type="button" name="mmi_search" id="mmi_search_'+medication_type+'_'+medcount+'_'+pumpe_type+'_'+pumpe_number+'" value="' + translate('mmi_button') + '" class="mmi_search_button  btnBlue btnMMI" />';
			var mmi_dosage_style = 'width:100px!important;';
			var mmi_selectuser_style = "width: 80px;";
		} else {
			var mmi_medication_style = '';
			var mmi_button_search = '';
			var mmi_dosage_style = '';
			var mmi_selectuser_style = '';
		}
			
		
		// #########################################################
		// Create  Verordnet von  dropdown :: (prescribed by)
		// ISPC-2329 pct.b
		var usrstr = "";
		for(var i in jsusers)
		{
			//usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
			var selecte_dr = "";
	        if (i == current_doctor) {
	        	selecte_dr = "selected"
	            //return;
	        }
	        usrstr +='<option value="'+i+'" '+selecte_dr+'>'+jsusers[i]+'</option>';
		}
		var userdrop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][verordnetvon]['+medcount+']" data-medication_type="'+medication_type+'" class="verordnetvon_select sch_referral change_status"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'" rel="'+medication_type+'">'+usrstr+'</select>';
		
		
		// #########################################################
		// Create  Einheit dropdown :: (unit)
		//var unit_medication = '<option value="0"></option>';
		//Lore 19.10.2020 
		var unit_medication = '<option value="0">i.E.</option>';
		if(js_unit){
			for(var uniti in js_unit)
			{
				unit_medication +='<option value="'+uniti+'" >'+js_unit[uniti]+'</option>';
			}
		}
		var unit_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][unit]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit sch_referral change_status sh_selector_medication_unit"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+unit_medication+'</select>';
		
		
		// #########################################################
		// Create  Darreichungsform dropdown :: (dosage from)
		
		var dosage_from_medication = '<option value="0"></option>';
		if(js_dosage_form){
			for(var dosageformi in js_dosage_form)
			{
				dosage_from_medication +='<option value="'+dosageformi+'">'+js_dosage_form[dosageformi]+'</option>';
			}
		}
		var dosage_form_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][dosage_form]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_dosage_form sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+dosage_from_medication+'</select>';
		
		
		// #########################################################
		// Create  Indikation dropdown :: (type)
		var indication_medication_str = '<option id="'+medication_type+'_'+medcount+'_0#FFFFFF" value="0" style="background: #FFFFFF!important" >Auswahl</option>';
		
		if(js_indication){
			
			$.each(js_indication, function(id,in_value){
				indication_medication_str +='<option id="'+medication_type+'_'+medcount+'_'+id.trim()+'#'+in_value.color+'" value="'+id+'"  style="background: #'+in_value.color+'" data-indication_color="#'+in_value.color+'" >'+in_value.name+'</option>';
			})
		}
		
		var indication_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][indication]['+medcount+']"  data-medication_type="'+medication_type+'" class="indication_color_select sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+indication_medication_str+'</select>';
		
		
		// #################################
		// CREATE TABLE ROW - WITH NEW MEDICATION LINE
		// Create - medication  name  		
		var med_name_label ='<label>' + translate("medication_name") + '</label>';
		var med_name_input ='<input type="text"  placeholder="' + translate("medication_name") + '" name="medication_block['+medication_type+']['+pumpe_number+'][medication]['+medcount+']" value="" id="medication_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  autocomplete="off"   data-medication_type="'+medication_type+'" class="livesearchmedinp meds_'+medication_type+'_'+pumpe_number+'_line_'+medcount+' sch_referral  change_status xxl_input livesearchmedinp_fixedwidth onchange_reset_pzn"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'"/>';
		
		// Create - medication  hidden inputs
		var med_hidd ='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+'][hidd_medication]['+medcount+']" id="hidd_medication_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  value="" />';
		var med_drid ='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+'][drid]['+medcount+']" id="drid_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  value="" />';
		
		// hidden inputs for PZN
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][pzn]" class="medication_pzn" id="'+medication_type+'_medication_pzn-'+pumpe_number+'_'+medcount+'" value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][source]" class="medication_source" id="'+medication_type+'_medication_source-'+pumpe_number+'_'+medcount+'"  value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][dbf_id]" class="medication_dbf_id" id="'+medication_type+'_medication_dbf_id-'+pumpe_number+'_'+medcount+'"  value="" >';

		// Create - medication  drug
		var med_drug_label ='<label>' + translate("medication_drug") + '</label>';
		var med_drug_input ='<br/><input type="text" placeholder="' + translate("medication_drug") + '" name="medication_block['+medication_type+']['+pumpe_number+'][drug]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="drug_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  class="medication_drug sch_referral change_status onchange_reset_pzn"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"/>';
		
		// Create - medication  unit
		var med_unit ='<label>' + translate("medication_unit") +':</label>'+unit_drop;
		var med_unit =unit_drop;
 
		// Create - medication  dosage form
		var med_dosage_form ='<label>' + translate("medication_dosageform") + ':</label>'+dosage_form_drop;
		//var med_dosage_form ='';

		// Create - medication  concentration
		var med_concentration_label ='<label>' + translate("medication_concentration") + '</label>';
		//var med_concentration_input ='<input style="width:85px" type="text" placeholder="' + translate("medication_concentration") + '" name="medication_block['+medication_type+']['+pumpe_number+'][concentration]['+medcount+']" value=""  data-ec_field="concentration"    data-medication_type="'+medication_type+'" class="xsmall_input medication_concentration sch_referral change_status extra_calculation"  id="concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-pumpe_med="'+medcount+'"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  rel="'+medication_type+'"/>';
		// ISPC-2329 pct.c)
		//var einheit_text = "EINHEIT"
		var einheit_text = "i.E."		//Lore 19.10.2020 
		//var med_concentration_input ='<input type="text" placeholder="'+einheit_text+' pro ml" name="medication_block['+medication_type+']['+pumpe_number+'][concentration]['+medcount+']" value=""  data-ec_field="concentration"    data-medication_type="'+medication_type+'" class="xsmall_input medication_concentration sch_referral change_status extra_calculation"  id="concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-pumpe_med="'+medcount+'"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  rel="'+medication_type+'"/>';
		//ISPC-2684 Lore 15.10.2020
		if (show_dosage_unit == "1") {
			var med_concentration_input ='<input type="text" style="width: 55%;" placeholder="'+einheit_text+' pro ml" name="medication_block['+medication_type+']['+pumpe_number+'][concentration]['+medcount+']" value=""  data-ec_field="concentration"    data-medication_type="'+medication_type+'" class="xsmall_input medication_concentration sch_referral change_status extra_calculation"  id="concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-pumpe_med="'+medcount+'"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  rel="'+medication_type+'"/>';
//Lore 19.10.2020 
			var med_concentration_label ='<input type="text" readonly style="width: 40%; background:transparent;border: none;font-size: 8px;"  value="'+einheit_text+' pro ml"  id="lbl_concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
		}else{
			var med_concentration_input ='<input type="text" placeholder="'+einheit_text+' pro ml" name="medication_block['+medication_type+']['+pumpe_number+'][concentration]['+medcount+']" value=""  data-ec_field="concentration"    data-medication_type="'+medication_type+'" class="xsmall_input medication_concentration sch_referral change_status extra_calculation"  id="concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-pumpe_med="'+medcount+'"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  rel="'+medication_type+'"/>';
		}
 
		// Create - medication  dosaje OLD STRUCTURE
		var med_simple_dosage_label ='<label>' + translate("medication_dosage_h") + ':</label>';
		var med_simple_dosage_input ='<input type="text" placeholder="' + translate("medication_dosage_h") + '" name="medication_block['+medication_type+']['+pumpe_number+'][dosage]['+medcount+']"  value="" data-ec_field="dosage"   data-medication_type="'+medication_type+'"  class="dosage_input sch_referral change_status calculate_sh_d24h extra_calculation  doseirung"   data-pumpe_med="'+medcount+'"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"    id="dosage_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
		
		var med_simple_dosage24h_label ='<label>' + translate("medication_dosage24h") + ':</label>';
		//ISPC-2329 pct.p) 
		var med_simple_dosage24h_input ='<input type="text"  placeholder="' + translate("medication_dosage24h") + '" name="medication_block['+medication_type+']['+pumpe_number+'][dosage_24h]['+medcount+']"  value="" data-ec_field="dosage_24h"  data-medication_type="'+medication_type+'"  class="dosage_input medication_dosage24_numeric sch_referral change_status  calculate_sh_d extra_calculation doseirung"   data-pumpe_med="'+medcount+'" data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"   id="dosage_sh_24h_'+medication_type+'_'+pumpe_number+'_'+medcount+'"    />';
		
		//ISPC-2684 Lore 05.10.2020
		if (show_dosage_unit == "1") {
			var med_simple_dosage_input ='<input type="text" placeholder="' + translate("medication_dosage_h") + '"  style="width: 55%;" name="medication_block['+medication_type+']['+pumpe_number+'][dosage]['+medcount+']"  value="" data-ec_field="dosage"   data-medication_type="'+medication_type+'"  class="dosage_input sch_referral change_status calculate_sh_d24h extra_calculation ph_dosage doseirung"   data-pumpe_med="'+medcount+'"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"    id="dosage_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
			var med_simple_dosage_label ='<input type="text" readonly style="width: 45%; background:transparent;border: none;"  value="ml/h"  id="lbl_dosage_sh_<?php echo $medication_type; ?>_<?php echo $pumpe_cnt;?>_<?php echo $cnt;?>"  />';
			var med_simple_dosage24h_input ='<input type="text"  placeholder="' + translate("medication_dosage24h") + '" style="width: 55%;" name="medication_block['+medication_type+']['+pumpe_number+'][dosage_24h]['+medcount+']"  value="" data-ec_field="dosage_24h"  data-medication_type="'+medication_type+'"  class="dosage_input medication_dosage24_numeric sch_referral change_status  calculate_sh_d extra_calculation ph_dosage_24h doseirung"   data-pumpe_med="'+medcount+'" data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"   id="dosage_sh_24h_'+medication_type+'_'+pumpe_number+'_'+medcount+'"    />';
			var med_simple_dosage24h_label ='<input type="text" readonly style="width: 45%; background:transparent;border: none;"  value="ml/24h"  id="lbl_dosage_sh_24h_<?php echo $medication_type; ?>_<?php echo $pumpe_cnt;?>_<?php echo $cnt;?>"  />';
		}
		var med_simple_dosage_unit_input ='<input type="text" placeholder="' + translate("medication_dosage_h_unit") + '"  style="width: 55%;" name="medication_block['+medication_type+']['+pumpe_number+'][unit_dosage]['+medcount+']"  value="" data-ec_field="unit_dosage"   data-medication_type="'+medication_type+'"  class="unit_dosage sch_referral change_status extra_calculation"   data-pumpe_med="'+medcount+'"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"    id="dosage_sh_unit_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
		var med_unit_dosage_label ='<input type="text" readonly placeholder="' + translate("medication_dosage_h_unit") + '"  style="width: 45%; background:transparent;border: none;"  value="'+einheit_text+'/h"  id="lbl_dosage_sh_unit_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
		var med_simple_dosage24h_unit_input ='<input type="text"  placeholder="' + translate("medication_dosage_24h_unit") + '" style="width: 55%;" name="medication_block['+medication_type+']['+pumpe_number+'][unit_dosage_24h]['+medcount+']"  value="" data-ec_field="unit_dosage_24h"  data-medication_type="'+medication_type+'"  class="unit_dosage_24h sch_referral change_status extra_calculation"   data-pumpe_med="'+medcount+'" data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"   id="dosage_sh_24h_unit_'+medication_type+'_'+pumpe_number+'_'+medcount+'"    />';
		var med_unit_dosage24h_label ='<input type="text" readonly placeholder="' + translate("medication_dosage_h_unit") + '"  style="width: 45%; background:transparent;border: none;"  value="'+einheit_text+'/24h"  id="lbl_dosage_sh_24h_unit_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';

		
		// Create - medication  indication
//		var med_indication ='<label>' + translate("medication_indication") + ':</label>'+indication_drop;
		var med_indication = indication_drop;
		
		// Create - medication  COMMENT - 
		var med_comment_label ='';
		var med_comment_input ='';

		
		// Create - medication  prescribed by -
		var med_prescribed_by_label ='<label>' + translate("medication_prescribed_by") + ':</label>';
		var med_prescribed_by_input = userdrop;
		
		
		// Create - medication  date - not needed
		var med_date ='';
		
		// Create - medication  importance / sort
		var med_importance_label ='<label>' + translate("medication_importance") + ':</label>';
		var med_importance_input ='<input type="text" name="medication_block['+medication_type+']['+pumpe_number+'][importance]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_importance sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    value=""  rel="'+medication_type+'" />';

		// Create - medication  importance / sort
		var med_delete_link ='<a class="delete_new_row" href="javascript:void(0)" onClick="remove_new_line(\'#tr'+medication_type+pumpe_number+medcount+'\')" data-pumpe_number="'+pumpe_number+'"  data-pumpe_type="'+pumpe_type+'"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ><img width="13px" alt="delete" src="' + res_path + '/images/action_delete.png"></a>';
		var med_delete_td ='<td class="delete delete_new_row" onClick="remove_new_line(\'#tr'+medication_type+pumpe_number+medcount+'\')"  data-pumpe_number="'+pumpe_number+'"  data-pumpe_type="'+pumpe_type+'"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ></td>';
		
		
		//SPECIAL DISPLAY FOR MEDICATION
		var  full_tr = "";
		
		full_tr += tr_start;
		
		// TD :: Sort (importance)
		full_tr += '<td class="sortnum">';
		full_tr += med_importance_input;
		full_tr += td_end;
		
		// TD :: medication name, mmi button and drug
		//Lore 19.10.2020 
		full_tr += '<td class="name" style="width: 21% !important;" ><div class="cn">';
		full_tr += med_name_input;
		full_tr += mmi_button_search ;
		full_tr += med_hidd;
		full_tr += med_drid;
		full_tr += med_drug_input;
		full_tr += '</div>';
		full_tr += td_end;

		// TD ::medication unit / concentration
/*		full_tr += td_start
				+med_unit
				+med_concentration_input
				+td_end ;*/
		//ISPC-2684 Lore 15.10.2020
		if (show_dosage_unit == "1") {
//Lore 19.10.2020 
			full_tr += td_20start
			+med_unit
			+med_concentration_input
			+med_concentration_label
			+td_end ;
		}else {
			full_tr += td_start
			+med_unit
			+med_concentration_input
			+td_end ;
		}
		// TD :: dosage / dosage 24h
		//Lore 19.10.2020 
		full_tr += td_20start;
		full_tr += med_simple_dosage_input;
		if (show_dosage_unit == "1") {
			full_tr += med_simple_dosage_label + med_simple_dosage_unit_input + med_unit_dosage_label;		//ISPC-2684 Lore 05.10.2020
		}
		full_tr += med_simple_dosage24h_input;
		if (show_dosage_unit == "1") {
			full_tr += med_simple_dosage24h_label+med_simple_dosage24h_unit_input + med_unit_dosage24h_label;		//ISPC-2684 Lore 05.10.2020
		}
		full_tr += td_end;

		// TD ::medication indication
		full_tr += td_start+med_indication+td_end;
		
		// TD ::medication Verordnet von  
		full_tr +=td_start+med_prescribed_by_input+med_date+td_end;
		
		// TD ::medication delete td  
		full_tr += med_delete_td;
		
		full_tr +=tr_end;

		$('#'+medication_type+'_pumpeblock'+pumpe_number).append(full_tr);
		
 		$('#medication_'+medication_type+'_'+pumpe_number+'_'+medcount).bind('keyup keydown change paste',function(){
 
 			if (show_mmi == "1") {
			
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_'+pumpe_number+'_').length));
					reset_medications(medication_type,input_row,pumpe_number);
				}).liveSearch({
					url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '300',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
						}
				});
 			} else {
				//livesearch medications ls
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_'+pumpe_number+'_').length));
					reset_medications(medication_type,input_row,pumpe_number);
				}).liveSearch({
					url: 'ajax/medications?q=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '300',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
					}
				});
	 		}
		});
 		
 		//ISPC-2329 Carmen 23.10.2019
 		$('.indication_color_select').chosen({
			placeholder_text_single: translate('please select'),
			placeholder_text_multiple : translate('please select'),
			multiple:0,
			width:'110px',
			style: "padding-top:10px",
			//"search_contains": true, //TODO-3242 Carmen 03.07.2020
			//disable_search: true,		// @Lore 31.10.2019+commented for TODO-3242 by Carmen 03.07.2020
			"enable_split_word_search": false, //TODO-3242 Carmen 03.07.2020
			no_results_text: translate('noresultfound')
		}).change(function () {
			var color = $(this).find(":selected").attr('id').substr(-7);
			$(this).parent().find('span').attr('style', 'background-color: '+color +' !important');
	    });
	
		var new_line_count = parseInt(medcount) + 1 ;
		$("#new_sh_line_"+medication_type+"_"+pumpe_type+"_"+pumpe_number+"").val(new_line_count);
		
		
		// increment line 
		medcount++;
	}
	
	
	
	
	
	
	

	function create_new_line(medication_type,medcount, dataObj)
	{
		if(typeof dataObj === 'undefined')
		{
			dataObj = {};
		}
	
		var timed_bocks_arr = jQuery.parseJSON (timed_bocks);
		
		// DEFINE tr 
		var tr_start = '<tr id="tr'+medication_type+medcount+'" class="selector_medication_tr_1 single">';
		var tr_end = '</tr>';
		
		// DEFINE td 
		if(medication_type =="treatment_care"){
			var td_start ='<td>';
		} 
		else
		{
			var td_start ='<td>';
		}
		var td_end ='</td>';
		
		// Create MMI button
		if (show_mmi == "1") {
			var mmi_medication_style = 'width:75%!important;';
			var mmi_button_search = '&nbsp;<input type="button" name="mmi_search" id="mmi_search_'+medication_type+'_'+medcount+'" value="' + translate('mmi_button') + '" class="mmi_search_button btnBlue btnMMI" />';
			var mmi_dosage_style = 'width:100px!important;';
			var mmi_selectuser_style = "width: 80px;";
		} else {
			var mmi_medication_style = '';
			var mmi_button_search = '';
			var mmi_dosage_style = '';
			var mmi_selectuser_style = '';
		}
			
	
		// Dosage columns dinamic version
		// #########################################################
		
		var dosaje_str = "";
		//ISPC - 2329 - punctul j
		var dosage_value_arr = [];
		var dosage_value = '';
		var dosage_form_value = '';
		var dosage_form_name = '';
		var dosage_interval_value = '';
		var medication_name_value = '';
		var drug_value = '';
		var type_value = '';
		var escalation_value = '';
		var indication_value = '';
		var comments_value = '';
		var unit_value = '';
		var concentration_value = '';
		var source_value = '';
		var chosen;
		//ISPC - 2329 - punctul j
		var js_dosage_intervals_length = 0;
		
		//ISPC-2554 pct.3 Carmen 10.04.2020
		var atc_value = '';
		//--
		
		if (individual_medication_time == "0") {
			var time_interval_holder = $('input.time_interval_style', $("table#not_individual_medication_time .dosage_intervals_holder") );
		} else {
			var time_interval_holder = $('input.time_interval_style', $("table#" + medication_type + "_med_table .dosage_intervals_holder") );
		}
		
		  
		if(typeof dataObj.dosage !== 'undefined')
		{
			dosage_value = dataObj.dosage;
			dosage_value_arr[0] = '! ALTE DOSIERUNG!';
			dosage_value_arr[1] = dataObj.dosage;
			dosage_value_arr[2] = '';
			dosage_value_arr[3] = '';
		}
		else
		{
			dosage_value_arr[0] = '';
			dosage_value_arr[1] = '';
			dosage_value_arr[2] = '';
			dosage_value_arr[3] = '';
		}
		
        // ISPC-2247 pct.1 Lore 06.05.2020 
		//console.log(dataObj); 
		if(typeof dataObj.dosage_0 !== 'undefined')
		{
			dosage_value_arr[0] = dataObj.dosage_0;
		}
		if(typeof dataObj.dosage_1 !== 'undefined')
		{
			dosage_value_arr[1] = dataObj.dosage_1;
		}
		if(typeof dataObj.dosage_2 !== 'undefined')
		{
			dosage_value_arr[2] = dataObj.dosage_2;
		}
		if(typeof dataObj.dosage_3 !== 'undefined')
		{
			dosage_value_arr[3] = dataObj.dosage_3;
		}
		//.
		
		if(!$.isEmptyObject(dataObj))
		{
			if(js_dosage_form_custom && typeof dataObj.dosage_form !== 'undefined'){
				dosage_form_name = js_dosage_form_custom[dataObj.dosage_form];
			}
			//ISPC-2554 pct.1 Carmen 08.04.2020
			if(js_dosageform_mmi && typeof dataObj.dosage_form !== 'undefined'){
				for(var dosageformi in js_dosageform_mmi)
				{
					if(js_dosageform_mmi[dosageformi][0] == dataObj.dosage_form)
					{				
						dosage_form_name = js_dosageform_mmi[dosageformi][1].substr(0, 3);
					}
				}
			}
			//--
		}
		
		if(typeof dataObj.dosage_form !== 'undefined')
		{
			dosage_form_value = dataObj.dosage_form;
			if(typeof dosage_form_name == 'undefined')
			{
				dosage_form_name = js_dosage_form[dataObj.dosage_form].substr(0,3);//ISPC-2554 pct.1 Carmen 08.04.2020
			}
		}
		
		if(typeof dataObj.dosage_interval !== 'undefined')
		{
			dosage_interval_value = dataObj.dosage_interval;
		}		
		
		if(typeof dataObj.medication !== 'undefined')
		{
			medication_name_value = dataObj.medication;
		}
		
		if(typeof dataObj.drug !== 'undefined')
		{
			drug_value = dataObj.drug;
		}
		
		if(typeof dataObj.type !== 'undefined')
		{
			type_value = dataObj.type;
		}
		
		if(typeof dataObj.escalation !== 'undefined')
		{
			escalation_value = dataObj.escalation;
		}
		
		if(typeof dataObj.comments !== 'undefined')
		{
			comments_value = dataObj.comments;
		}
		
		if(typeof dataObj.source !== 'undefined')
		{
			source_value = dataObj.source;
		}
		
		if(typeof dataObj.indication !== 'undefined')
		{
			indication_value = dataObj.indication;
		}
		
		if(typeof dataObj.unit !== 'undefined')
		{
			unit_value = dataObj.unit;
		}
		
		if(typeof dataObj.concentration !== 'undefined')
		{
			concentration_value = dataObj.concentration;
		}
		
		//ISPC-2554 pct.3 Carmen 10.04.2020
		if(typeof dataObj.atc !== 'undefined')
		{
			atc_value = dataObj.atc.replace(/"/g, "&#34;").replace(/'/g, "&#39;");
		}
		//--
		
		$(time_interval_holder).each(function(i){
			
			var time_val = $(this).val();
			if (time_val == "") {
				time_val = '- : -';
			}
			
			if(i > 3)					//ISPC-2247 pct.1 Lore 06.05.2020 era "i > 1" si pentru i=2,3 imi golea dosage_value_arr....
			{
				dosage_value_arr[i] = '';
			}
			
			dosaje_str += '<td class="dosierung">';
			dosaje_str += '<div class="dosage_firstrow_div item">'
				+ '<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']['+time_val+']"  value="'+dosage_value_arr[i]+'" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage_concentration cdc_'+medication_type+'_'+medcount+' dosierung"  data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'"   title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"   id="dosage_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"   />'
				+ '<span class="over_the_input selector_dosage_text"></span>'
				+ '</div>';
			
			dosaje_str += '<div class="dosage_secondrow_div item">'
				+ '<input type="text" name="medication_block['+medication_type+'][dosage_concentration]['+medcount+']['+time_val+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage dosierung" data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"  id="dosage_concentration_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"  />'
				+ '<span class="over_the_input selector_concentration_text">'+dosage_form_name+'</span>'
				+ '</div>';
			dosaje_str += td_end;
			
			js_dosage_intervals_length ++;
		
		});
		
		if (js_dosage_intervals_length == 0 ){
			js_dosage_intervals_length = 1;
		}
		
		
		
		// #########################################################
		// Create  Dosage columns :: 
/*		var dosaje_str = "";
		var js_dosage_intervals_length = 0;
		for(var di in js_dosage_intervals[medication_type])
		{
			dosaje_str += td_start+'<div class="dosage_firstrow_div"><label class="dosage_label">'+di+'</label>';
			dosaje_str += '<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']['+js_dosage_intervals[medication_type][di]+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage_concentration cdc_'+medication_type+'_'+medcount+'"  data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ di.replace(":","")+'"   title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"   id="dosage_'+medication_type+'_'+medcount+'_'+di.replace(":","")+'"   /></div>';
			if(new_fields == "1")
			{
				dosaje_str += '<div class="dosage_secondrow_div"><input type="text" name="medication_block['+medication_type+'][dosage_concentration]['+medcount+']['+js_dosage_intervals[medication_type][di]+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage" data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ di.replace(":","")+'" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"  id="dosage_concentration_'+medication_type+'_'+medcount+'_'+di.replace(":","")+'"  /></div>';
			}
			dosaje_str += td_end;
			
			js_dosage_intervals_length ++;
		}

		if (js_dosage_intervals_length == 0 ){
			js_dosage_intervals_length = 1;
		}*/
		
		// #########################################################
		// Create  Verordnet von  dropdown :: (prescribed by)
		// ISPC-2329 pct.b
		var usrstr = "";
		for(var i in jsusers)
		{
			//usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
			var selecte_dr = "";
	        if (i == current_doctor) {
	        	selecte_dr = "selected"
	            //return;
	        }     
	        usrstr +='<option value="'+i+'" '+selecte_dr+'>'+jsusers[i]+'</option>';       
		}
		var userdrop = '<select name="medication_block['+medication_type+'][verordnetvon]['+medcount+']"  data-medication_type="'+medication_type+'" class="verordnetvon_select referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+usrstr+'</select>';
		
		
		// #########################################################
		// Create  Einheit dropdown :: (unit)
		var unit_medication = '<option value="0"></option>';
		if(js_unit){
			
			for(var uniti in js_unit)
			{
				if(unit_value != "" && unit_value == uniti) {
					chosen = "selected=\'selected\'";
					
				}
				else
				{
					chosen = '';
				}
				unit_medication +='<option value="'+uniti+'"'+chosen+'>'+js_unit[uniti]+'</option>';
			}
		}
		var unit_drop = '<select name="medication_block['+medication_type+'][unit]['+medcount+']"   data-medication_type="'+medication_type+'" class="medication_unit referral change_status small_input selector_medication_unit" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+unit_medication+'</select>';
		

		// #########################################################
		// Create  Darreichungsform dropdown :: (dosage from)
		
		var dosage_from_medication = '<option value="0"></option>';
		
		if($.isEmptyObject(dataObj))
		{
			if(js_dosage_form){
				//ISPC-2554 pct.1 Carmen 03.04.2020
				if(show_mmi == '1')
				{
					dosage_from_medication +='<optgroup label='+translate('\"client dosageform list\"')+'>';
				}
				//--
				
				for(var dosageformi in js_dosage_form)
				{
					dosage_from_medication +='<option value="'+dosageformi+'">'+js_dosage_form[dosageformi]+'</option>';
				}
			}
			//ISPC-2554 pct.1 Carmen 03.04.2020
			if(show_mmi == '1')
			{
				if(js_dosageform_mmi){			
					dosage_from_medication +='<optgroup label='+translate('\"mmi dosageform list\"')+'>';
					for(var dosageformi in js_dosageform_mmi)
					{
						if(dosage_form_value != "" && dosage_form_value == js_dosageform_mmi[dosageformi][0]) {
							chosen = "selected=\'selected\'";
							
						}
						else
						{
							chosen = '';
						}
						dosage_from_medication +='<option value="'+js_dosageform_mmi[dosageformi][0]+'"'+ chosen +'>'+js_dosageform_mmi[dosageformi][1]+'</option>';					
					}
				}
			}
			//--
		}
		else
		{
			if(js_dosage_form){
				
				for(var dosageformi in js_dosage_form)
				{
					//if(dosage_form_value != "" && dataObj.dosage_form == dosageformi) {
					if(dosage_form_value != "" && dosage_form_value == dosageformi) {
						chosen = "selected=\'selected\'";
						
					}
					else
					{
						chosen = '';
					}
					dosage_from_medication +='<option value="'+dosageformi+'"'+ chosen +'>'+js_dosage_form[dosageformi]+'</option>';
				}
			}
			if(js_dosage_form_custom){
				
				for(var dosageformi in js_dosage_form_custom)
				{
					//if(dosage_form_value != "" && dataObj.dosage_form == dosageformi) {
					if(dosage_form_value != "" && dosage_form_value == dosageformi) {
						chosen = "selected=\'selected\'";
						
					}
					else
					{
						continue;
					}
					dosage_from_medication +='<option value="'+dosageformi+'"'+ chosen +'>'+js_dosage_form_custom[dosageformi]+'</option>';
				}
			}
			//ISPC-2554 pct.1 Carmen 03.04.2020
			if(show_mmi == '1')
			{
				if(js_dosageform_mmi){			
					dosage_from_medication +='<optgroup label='+translate("mmi dosageform list")+'>';
					for(var dosageformi in js_dosageform_mmi)
					{
						if(dosage_form_value != "" && dosage_form_value == js_dosageform_mmi[dosageformi][0]) {
							chosen = "selected=\'selected\'";
							
						}
						else
						{
							chosen = '';
						}
						dosage_from_medication +='<option value="'+js_dosageform_mmi[dosageformi][0]+'"'+ chosen +'>'+js_dosageform_mmi[dosageformi][1]+'</option>';					
					}
				}
			}
			//--
		}
		var dosage_form_drop = '<select name="medication_block['+medication_type+'][dosage_form]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_dosage_form referral change_status small_input selector_medication_dosage_form" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+dosage_from_medication+'</select>';
		
		
		// #########################################################
		// Create  Applikationsweg dropdown :: (type)
		var type_medication = '<option value="0"></option>';
		
		if($.isEmptyObject(dataObj))
		{
			if(js_type){
				
				for(var typei in js_type)
				{
					type_medication +='<option value="'+typei+'">'+js_type[typei]+'</option>';
				}
			}
		}
		else
		{
			if(js_type){
				
				for(var typei in js_type)
				{
					if(type_value != "" && type_value == typei) {
						chosen = "selected=\'selected\'";
						
					}
					else
					{
						chosen = '';
					}
					type_medication +='<option value="'+typei+'"'+chosen+'>'+js_type[typei]+'</option>';
				}
			}
			if(js_type_custom){
				
				for(var typei in js_type_custom)
				{
					if(type_value != "" && type_value == typei) {
						chosen = "selected=\'selected\'";
						
					}
					else
					{
						continue;
					}
					type_medication +='<option value="'+typei+'"'+chosen+'>'+js_type_custom[typei]+'</option>';
				}
			}
		}
		var type_drop = '<select name="medication_block['+medication_type+'][type]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+type_medication+'</select>';
		
		
		
		// #########################################################
		// Create  ESKALATION dropdown :: (escalation)
		var escalation_medication = '<option value="0"></option>';
		
		if(js_escalation){
			
			for(var escalationi in js_escalation)
			{
				if(escalation_value != "" && escalation_value == escalationi) {
					chosen = "selected=\'selected\'";
					
				}
				else
				{
					chosen = '';
				}
				escalation_medication +='<option value="'+escalationi+'"'+chosen+'>'+js_escalation[escalationi]+'</option>';
			}
		}
		var escalation_drop = '<select name="medication_block['+medication_type+'][escalation]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_escalation referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+escalation_medication+'</select>';
				
		
		
		
		
		// #########################################################
		// Create  Indikation dropdown :: (type)
		var indication_medication_str = '<option id="'+medication_type+'_'+medcount+'_0#FFFFFF" value="0" style="background: #FFFFFF!important" >Auswahl</option>';
		
		if(js_indication){
			
			var background = '';
			$.each(js_indication, function(id,in_value){
				if(indication_value != "" && indication_value == id.trim()) {
					chosen = "selected=\'selected\'";
					background = 'style="background: #'+in_value.color+'"';
			
				}
				else
				{
					chosen = '';
				}
				indication_medication_str +='<option id="'+medication_type+'_'+medcount+'_'+id.trim()+'#'+in_value.color+'" value="'+id+'"'+chosen+'  style="background: #'+in_value.color+'" data-indication_color="#'+in_value.color+'" >'+in_value.name+'</option>';
			})
		}
		
		var indication_drop = '<select '+background+' name="medication_block['+medication_type+'][indication]['+medcount+']"  data-medication_type="'+medication_type+'" class="indication_color_select referral  change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'">'+indication_medication_str+'</select>';
		
		
		// #################################
		// CREATE TABLE ROW - WITH NEW MEDICATION LINE
		// Create - medication  name  		
		var med_name_label ='<label>' + translate("medication_name") + '</label>';
		var med_name_input ='<input type="text" name="medication_block['+medication_type+'][medication]['+medcount+']"  placeholder='+ translate("medication_name") +'  value="'+medication_name_value+'" id="medication_'+medication_type+'_'+medcount+'"  autocomplete="off"   data-medication_type="'+medication_type+'" class="livesearchmedinp meds_'+medication_type+'_line_'+medcount+' referral  change_status livesearchmedinp_fixedwidth onchange_reset_pzn name" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  hidden inputs
		var med_hidd ='<input name="medication_block['+medication_type+'][hidd_medication]['+medcount+']" value="" id="hidd_medication_'+medication_type+'_'+medcount+'" type="hidden">';
		var med_drid ='<input type="hidden" id="drid_'+medication_type+'_['+medcount+']" name="medication_block['+medication_type+'][drid]['+medcount+']" value="" />';
		
		//hidden for PZN
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][pzn]" class="medication_pzn" id="'+medication_type+'_medication_pzn-'+medcount+'" value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][source]" class="medication_source" id="'+medication_type+'_medication_source-'+medcount+'"  value="'+source_value+'" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][dbf_id]" class="medication_dbf_id" id="'+medication_type+'_medication_dbf_id-'+medcount+'"  value="" >';
		//ISPC-2554 pct.3 Carmen 26.03.2020	
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][atc]" class="medication_atc" id="'+medication_type+'_medication_atc-'+medcount+'"  value="'+atc_value+'" >';
		//--
		// Create - medication  drug
		var med_drug_label ='<label>' + translate("medication_drug") + '</label>';
		var med_drug_input ='<br/><input type="text" name="medication_block['+medication_type+'][drug]['+medcount+']" value="'+drug_value+'"   placeholder='+ translate("medication_drug") +'   data-medication_type="'+medication_type+'" id="drug_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  unit
		var med_unit ='<label>' + translate("medication_unit") + ':</label>'+unit_drop;
		var med_unit_span ='<span>' + translate("medication_unit") + ':</span>'+unit_drop;
		
		// Create - medication  type
		var med_type ='<label>' + translate("medication_type") + ':</label>'+type_drop;
		var med_type_span ='<span>' + translate("medication_type") + ':</span>'+type_drop;
		
		// Create - medication  escalation
		var med_escalation ='<label>' + translate("medication_escalation") + ':</label>'+escalation_drop;
		var med_escalation_span ='<span>' + translate("medication_escalation") + ':</span>'+escalation_drop;
		
		
		// Create - medication  dosage form
		var med_dosage_form ='<label>' + translate("medication_dosageform") + ':</label>'+dosage_form_drop;
		var med_dosage_form_span ='<span>' + translate("medication_dosageform") + ':</span>'+dosage_form_drop;
		//var med_dosage_form ='';

		// Create - medication  concentration
		var med_concentration_label ='<label>' + translate("medication_concentration") + '</label>';
		var med_concentration_span ='<span>' + translate("medication_concentration") + '</span>';
		var med_concentration_input ='<input style="width:85px" type="text" name="medication_block['+medication_type+'][concentration]['+medcount+']" value="'+concentration_value+'"   data-medication_type="'+medication_type+'" class="small_input medication_concentration referral  change_status concentration_calculation" data-dosage_row_info="'+medication_type+'_'+medcount+'"  title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"     id="concentration_'+medication_type+'_'+medcount+'"   />';
		
		
		
		
		// Create - medication  dosaje NEW STRUCTURE
		var med_dosage_tds = dosaje_str;
		
		// Create - medication  dosaje OLD STRUCTURE
		var med_simple_dosage_label ='';
		var med_simple_dosage_input ='<input type="text" placeholder="' + translate("medication_dosage") + '" name="medication_block['+medication_type+'][dosage]['+medcount+']"  value="'+dosage_value+'" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>';
		var med_simple_dosage_lysocare = '';
		if(module_lysocare){
			 med_simple_dosage_lysocare = '<label>' + translate("Dosage according to the product information");
			 med_simple_dosage_lysocare += '<br/><label><input type="radio" name="medication_block['+medication_type+'][dosage_product]['+medcount+']"  value="yes" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Ja</label>';
			 med_simple_dosage_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][dosage_product]['+medcount+']"  value="no" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Nein</label>';
			 med_simple_dosage_lysocare += '</label>';
		}
		
		// Create - medication  indication
		var med_indication =indication_drop;
		
		var med_indication_label = '';
		var med_indication_input = indication_drop;
		
		
		
		// Create - medication  COMMENT - 
		if(medication_type == "isschmerzpumpe")
		{
			var med_comment_label ='';
			var med_comment_input ='';
		} 
		else if(medication_type == "treatment_care")
		{
			var med_comment_label ='<label>' + translate("medication_comments") + ':</label>';
			var med_comment_input ='<input name="medication_block['+medication_type+'][comments]['+medcount+']"  placeholder='+ translate("medication_comments") +'  data-medication_type="'+medication_type+'" class="referral  change_status xxl_input editcomment" title="'+medication_type+'_'+medcount+'" rows="" cols=""  rel="'+medication_type+'" />';
		}
		else
		{
			var med_comment_label ='';
			var med_comment_input ='<textarea name="medication_block['+medication_type+'][comments]['+medcount+']" placeholder='+ translate("medication_comments") +'    data-medication_type="'+medication_type+'" class="med_com_textarea referral  change_status editcomment" title="'+medication_type+'_'+medcount+'" rows="" cols=""  rel="'+medication_type+'" >'+comments_value+'</textarea>';
		}
		
		// Create - medication  prescribed by -
		var med_prescribed_by_label ='';
		var med_prescribed_by_input =userdrop;
		
		
		// Create - medication  date - not needed
		var med_date ='';
		
		// Create - medication  importance / sort
		var med_importance_label ='';
		var med_importance_input ='<input type="text" name="medication_block['+medication_type+'][importance]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_importance referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';

		// Create -delete 
		var med_delete_link ='<a class="delete_new_row" href="javascript:void(0)" onClick="remove_new_line(\'#tr'+medication_type+medcount+'\')"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ><img width="13px" alt="delete" src="' + res_path + '/images/action_delete.png"></a>';
		var med_delete_td ='<td class="delete delete_new_row" onClick="remove_new_line(\'#tr'+medication_type+medcount+'\')"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ></td>';
		
		// Create - toggle 
//		var med_toggle_child ='<td class="details delete_new_row" onClick="medi_child_toggle(\'#tr'+medication_type+medcount+'\')"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ></td>';
		var med_toggle_child ='<td id=" det' + medication_type + medcount + '_child_toggle" onclick="medi_child_toggle(this);" class="child_row child_row_toggler selector_medication_tr_2 details"> </td>';
		
		
		// Create - medication days interval
		var med_days_interval_label ='<label>' + translate("medication_days_interval") + ':</label>';
		var med_days_interval_span ='<span>' + translate("medication_days_interval") + ':</span>';
		var med_days_interval_input ='<input type="text" placeholder="' + translate("medication_days_interval") + '" name="medication_block['+medication_type+'][days_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_days_interval referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';
		var med_days_interval_technical_lysocare = '';
		if(module_lysocare){
			med_days_interval_technical_lysocare = '<label>' + translate("Interval according to technical information");
			med_days_interval_technical_lysocare += '<br/><label><input type="radio" name="medication_block['+medication_type+'][days_interval_technical]['+medcount+']"  value="yes" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Ja</label>';
			med_days_interval_technical_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][days_interval_technical]['+medcount+']"  value="no" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Nein</label>';
			med_days_interval_technical_lysocare += '</label>';
		}
		
		// Create - medication date in put
		var med_administration_date_label ='<span>' + translate("medication_administration_date") + ':</span>';
		var med_administration_date_input ='<input type="text" placeholder="' + translate("medication_administration_date") + '" name="medication_block['+medication_type+'][administration_date]['+medcount+']"  id="adminisration_date_'+medcount+'"  data-medication_type="'+medication_type+'"  class="small_input medication_adminisration_date referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';
		
		// Create - interval question div
		var med_has_interval_q = '<li><div class="interval_question"><input type="checkbox" value="1"  name="medication_block['+medication_type+'][has_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  data-medcount="'+medcount+'"  id="has_interval_'+medication_type+'_'+medcount+'"  /><label>' + translate("is interval medi?") + '</label></div></li>'
		var med_has_interval_start = '<li><div class="interval_block" style="display: none;" id="int_set_'+medication_type+'_'+medcount+'"   >'
		var med_has_interval_end = '</div></li>'
			
		var pro_label = '<label class="pro_label">' + translate('pro') + ' : </label>';
		

		
		
		// ISPC-2176
		// Create - medication  _packaging
		var med_packaging_label ='<label>' + translate("medication_packaging") + '</label>';
		var packaging_medication = "";
		if(js_packaging_array){
			for(var packagingi in js_packaging_array)
			{
				packaging_medication +='<option value="'+packagingi+'">'+js_packaging_array[packagingi]+'</option>';
			}
		}
		var med_packaging_drop = '<select name="medication_block['+medication_type+'][packaging]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_packaging referral change_status small_input selector_medication_packaging" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+packaging_medication+'</select>';
		
		
		
		// Create - medication  kcal
		var med_kcal_label ='<label>' + translate("medication_kcal") + '</label>';
		var med_kcal_span ='<span>' + translate("medication_kcal") + '</span>';
		var med_kcal_input ='<input type="text" name="medication_block['+medication_type+'][kcal]['+medcount+']" value=""   data-medication_type="'+medication_type+'" placeholder='+ translate("medication_kcal") +' id="kcal_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  volume
		var med_volume_label ='<label>' + translate("medication_volume") + '</label>';
		var med_volume_span ='<span>' + translate("medication_volume") + '</span>';
		var med_volume_input ='<input type="text" name="medication_block['+medication_type+'][volume]['+medcount+']" value=""   data-medication_type="'+medication_type+'" placeholder='+ translate("medication_volume") +'  id="volume_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		//SPECIAL DISPLAY FOR MEDICATION
		
		if(medication_type == "treatment_care")
		{
			var  full_tr = "";
			full_tr += tr_start;
			// medication  importance
			full_tr += '<td class="sortnum">'+med_importance_input+td_end;
			
			// TD :: medication name
			full_tr += '<td class="name"><div class="cn">'+med_name_input+med_hidd+med_drid+'</div>'+td_end;
			
			// medication comment
			full_tr += td_start+med_comment_input+td_end;
			
			// medication  	Verordnet von
			full_tr += td_start+med_prescribed_by_input+td_end;
			
			// medication  date
			full_tr += td_start+td_end;
			
			full_tr += med_delete_td;
			full_tr += tr_end;
		} 
		else if(medication_type == "scheduled")
		{
			var  full_tr = "";
			full_tr += tr_start;
			
			// TD :: sort (importance)			
			full_tr += '<td class="sortnum">';
			full_tr += med_importance_input;
			full_tr += td_end;
			
			
			// TD :: medication name
			full_tr += '<td class="name"><div class="cn">';
			full_tr += med_name_input;
			full_tr += mmi_button_search;
			full_tr += med_hidd+med_drid;
			full_tr += med_drug_input;
			full_tr += '</div>';
			full_tr += td_end;
			
			
			// TD :: medication dosage
			full_tr += td_start+med_simple_dosage_label+med_simple_dosage_input + med_simple_dosage_lysocare + td_end;
			
			
			// TD ::medication indications and comment
			full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
			

			// medication interval days
			full_tr += td_start+med_days_interval_input + med_days_interval_technical_lysocare + td_end;
			
			// medication  administration date
			full_tr += td_start+med_administration_date_input+td_end;

			
			// TD ::medication Verordnet von , date 
			full_tr +=td_start+med_prescribed_by_input+med_date +td_end;
			
			
			// TD ::Delete row
			full_tr += med_delete_td;
			
			full_tr += tr_end;
		} 
		else
		{
			
			var  full_tr = "";
			
			full_tr += tr_start;
			
			// TD :: sort
			full_tr += '<td class="sortnum">';
			full_tr += med_importance_input;
			full_tr += td_end;
			
			
			// TD :: medication name, mmi button and drug
			if(medication_type != "isnutrition"){
				full_tr +=  '<td class="name">';;
				full_tr += '<div class="cn">';
				full_tr += med_name_input;
				full_tr += mmi_button_search;
				full_tr += med_hidd;
				full_tr += med_drid;
				full_tr += med_drug_input;
				full_tr += '</div>';
				 if (medication_type == "isintubated") {
					 full_tr += med_packaging_label+med_packaging_drop;
				 }
				full_tr += td_end;
			} else {
				full_tr += td_start;
				full_tr += med_name_input;
				full_tr += med_hidd+med_drid;
				full_tr += med_drug_input;
				full_tr += td_end;
			}
			
		
			if( !~$.inArray( medication_type, timed_bocks_arr ) ){
				//reset colspan medication not in timed 
				js_dosage_intervals_length = 1;
			} 
			
			
			var child_row_toggler = '<!-- child row toggler  -->' 
			+ '<tr id="tr' + medication_type + medcount + '_child_toggle" onclick="medi_child_toggle(this);" class="child_row child_row_toggler selector_medication_tr_2" >'
			+ '<td class="medi_child_toggle_td border_bottom_solid medi_child_toggle_td_arrow" colspan="' + Number(6 + js_dosage_intervals_length) +'" >';
			if(new_fields == "1"){
				child_row_toggler += ''
				+ translate('medication_type') + ','
				+ translate('medication_dosageform') + ','
				+ translate('medication_unit') + ','
				+ '		etc';
				
			} else {
				child_row_toggler += ''
				+ translate("medication_days_interval") + ','
				+ translate("medication_administration_date");
			}
			child_row_toggler += ''	
				+ '</td>'
				+ '</tr>';
				
			
			
			var child_row = '<!-- child row -->' 
			+ '<tr  id="tr' + medication_type + medcount + '_child"  style="display:none;"  class="child_row child_row_holder selector_medication_tr_3 expandedItem">'
			+ '<td class="border_bottom_solid" colspan="'+Number(6 + js_dosage_intervals_length) +'">'

			child_row +='<h3>Applikationsweg, Darreichungsform, Einheit,etc.</h3>';
			child_row +='<div class="ei-content">';
			if(new_fields == "1"){
				child_row +='<ul class="item">'
				+ '<li>' + med_concentration_span + med_concentration_input + '<li>'
				+ '<li>' +  med_unit_span + '<li>'
				+ '</ul>'
				+ '<ul class="item">'
				+ '<li>' + med_dosage_form_span + '<li>'
				+ '<li>' +  med_type_span + '<li>'
				+ '</ul>';
			} 

			if((medication_type == "actual" || medication_type == "isivmed") && allow_normal_scheduled == "1") {
				
				//child_row
				child_row += '<ul class="item">' 
					+ med_has_interval_q
					+ med_has_interval_start
					+ med_days_interval_span
					+ med_days_interval_input
					+ med_administration_date_label
					+ med_administration_date_input
					+ med_has_interval_end 
					+ '</ul>';
			} else {
				child_row +=  '';
				
				if (medication_type == 'isbedarfs' || medication_type == 'iscrisis') {
					child_row +=''
						+ '<ul class="item"><li>' + med_escalation_span + "</li></ul>";
					}
				child_row +=  '</td>';
			}
			
			child_row += '</tr>';
			
			
			
			// if(medication_type == "actual" || medication_type == "isivmed"){
			var _med_simple_dosage_unit_form_text = '<span class="over_the_input" >'
	            + '<span class="selector_dosage_text"></span> '
	            + 'pro:'
	            + '<span class="selector_concentration_text"></span>'
	            + '</span>'
			
        	var _med_bedarf_dosage_interval = '<div>'
        		+ '<input type="text" name="medication_block['+medication_type+']['+medcount+'][dosage_interval]"  data-medication_type="'+medication_type+'"  class="dosage_interval selector_medication_edited" title="'+medication_type+'_'+medcount+'"  value="'+dosage_interval_value+'"  rel="'+medication_type+'" />'
        		+ '</div>';
   
			
			if($.inArray( medication_type, timed_bocks_arr ) >= 0){
				// TD ::medication dosades
				full_tr += med_dosage_tds;
			} 
			else
			{
				full_tr += td_start
					+ '<div style=\'position:relative\'>'
					+ med_simple_dosage_label
					+ med_simple_dosage_input
			 
					+ '</div>';
				
				if (medication_type == 'isbedarfs' || medication_type == 'iscrisis') {
					full_tr += _med_bedarf_dosage_interval;
				}
				
				full_tr += td_end;
				
			}
			
			// TD ::medication indications and comment
			 if (medication_type == "isintubated") {
				 full_tr += td_start;
				 full_tr += med_indication;
				 full_tr += med_kcal_input;
				 full_tr += med_volume_input;
				 full_tr += med_comment_input;
				 full_tr += td_end;
			 } else {
//				 full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
				 full_tr += '<td class="ind-com">'+med_indication+med_comment_input+td_end;
			 }
			
			
			// TD ::medication Verordnet von , date and importance
			full_tr +=td_start+med_prescribed_by_input+med_date+td_end;
			
			if((medication_type == "actual" || medication_type == "isivmed") && allow_normal_scheduled == "1") {
				
				//child_row
//				full_tr +=td_start+med_has_interval_q+med_has_interval_start+med_days_interval_label+med_days_interval_input+med_administration_date_label+med_administration_date_input+med_has_interval_end+td_end;
			}
			
			full_tr += med_delete_td;
			full_tr += med_toggle_child;
			
			full_tr +=tr_end;
			
			full_tr += child_row ;
			
		}
 
		$('#'+medication_type+'_med_table').append(full_tr);
		
		
		$('#adminisration_date_'+medcount).datepicker({
			dateFormat: 'dd.mm.yy',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: "0"
		});
		$('#adminisration_date_'+medcount).mask("99.99.9999");

		
 		
		$('#has_interval_'+medication_type+'_'+medcount).live('click', function(){
			var medication_type = $(this).data('medication_type');
			var medcount_line = $(this).data('medcount');
			//console.log("ssss");
			//console.log(medcount_line);
			
			if($(this ).prop( "checked" )){
				$("#int_set_"+medication_type+"_"+medcount_line).show();		
				
			}
			else
			{
				$("#int_set_"+medication_type+"_"+medcount_line).hide();
				// empty lines
				// interval				
			}
	


		});
	
		
 		$('#medication_'+medication_type+'_'+medcount).bind('keyup keydown change paste',function(){

			if(medication_type == "isnutrition"){
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
					reset_medications(medication_type,input_row);
				}).liveSearch({
					url: 'ajax/medicationsnutrition?q=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '400',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
					}
				});
			} 
			
			if(medication_type == "treatment_care"){
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
					reset_medications(medication_type,input_row);
				}).liveSearch({
					url: 'ajax/medicationstreatmentcare?q=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '400',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
					}
				});
			} 
			
			if (show_mmi == "1") {
			
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
					reset_medications(medication_type,input_row);
				}).liveSearch({
					url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '300',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
						}
				});
			} else {
				//livesearch medications ls
				$(this).live('change', function() {
					var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
					reset_medications(medication_type,input_row);
				}).liveSearch({
					url: 'ajax/medications?q=',
					id: 'livesearch_admission_medications',
					aditionalWidth: '300',
					noResultsDelay: '900',
					typeDelay: '900',
					returnRowId: function (input) {
						return $(input).attr('id'); // not integer, ex :: medicationactual1 
					}
				});
			}
		});

 		//ISPC-2329 Carmen 23.10.2019
 		$('.indication_color_select').chosen({
			placeholder_text_single: translate('please select'),
			placeholder_text_multiple : translate('please select'),
			multiple:0,
			width:'110px',
			style: "padding-top:10px",
			//"search_contains": true, //TODO-3242 Carmen 03.07.2020
			//disable_search: true,// TODO-2858 Ancuta 28.01.2020 commented for TODO-3242 by Carmen 03.07.2020
			"enable_split_word_search": false, //TODO-3242 Carmen 03.07.2020
			no_results_text: translate('noresultfound')
		}).change(function () {
			var color = $(this).find(":selected").attr('id').substr(-7);
			$(this).parent().find('span').attr('style', 'background-color: '+color +' !important');
	    });
 		
 		$('.chosen-single span').each(function() {
			var color_selected = $(this).parent().parent().parent().find("select").css("background-color");
			$(this).attr('style', 'background-color: '+color_selected+' !important');
		});
 		
		var new_line_count = parseInt(medcount) + 1 ;
		$("#new_line_"+medication_type).val(new_line_count);

		
		// increment line 
		medcount++;
		
		//reindex all dosages
		recall_tabindex();
	}
	
	
	
	
	 
	
	
	

	function selectMedications(mid, row, mmi_handler)   
	{
		if($.isNumeric(row))
		{
		}		
		else
		{
			var details = row.split("_");
			
			if(details.length == 4)
			{
				if($.isNumeric(details[2])){ // schmerzpumpe :like medication_isschmerzpumpe_2_3   
					var row = details[3];	
					var medication_type = details[1];
					var pumpe_number = details[2]; 
				}
				else
				{
					var row = details[3];	
					var medication_type = details[1]+'_'+details[2];// treatment_care
				}
			} else {
				var row = details[2];
				var medication_type = details[1];
			}
			
					
			var table_search_results = $("#medinosis_drop_table");
			var pzn = $('#medi_PZN_'+mid , table_search_results).val() || 0;
			var source = $('#medi_TYPE_'+mid , table_search_results).val() || "custom";
			var dbf_id = $('#medi_DBF_ID_'+mid , table_search_results).val() || "";
			
			//ISPC-2554 pct.3 Carmen 27.03.2020
			var atc = $('#medi_ATC_'+mid , table_search_results).val() || "";
			//--
			var pharmformcode = $('#medi_DOSAGEFORMID_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 11.05.2020
			var unit = $('#medi_UNIT_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 11.05.2020
			var takinghint = $('#medi_TAKINGHINT_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 16.06.2020
			
			
			if(pumpe_number)
			{

				$('#medication_'+medication_type+'_'+pumpe_number+'_'+row).val($('#medi_me_'+mid).val());
				//TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.drug == 'yes')
				{
					$('#drug_'+medication_type+'_'+pumpe_number+'_'+row).val($('#medi_wirkstoffe_'+mid).val());
				}
				else
				{
					$('#drug_'+medication_type+'_'+pumpe_number+'_'+row).val('');
				}
				if(!mmi_handler)
				{
					$('#comments_'+medication_type+'_'+pumpe_number+'_'+row).val($('#medi_comment_'+mid).val());
					$('#hidd_medication_'+medication_type+'_'+pumpe_number+'_'+row).val($('#medi_id_'+mid).val());
				} 
				else 
				{					
					$('#hidd_medication_'+medication_type+'_'+pumpe_number+'_'+row).val('');
					$('#edited_'+medication_type+'_'+pumpe_number+'_'+row).val('1');
				}
				
				var parent_tr = $("#tr" + medication_type + "" + pumpe_number + "" + row);
				$('input.medication_pzn' , parent_tr).val(pzn);
			    $('input.medication_source' , parent_tr).val(source);
			    $('input.medication_dbf_id' , parent_tr).val(dbf_id);	
			    //TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.atc == 'yes')
				{
					$('input.medication_atc' , parent_tr).val(atc); //ISPC-2554
				}
				else
				{
					$('input.medication_atc' , parent_tr).val(''); //ISPC-2554
				}
				//--

			}
			else
			{
				$('#medication_'+medication_type+'_'+row).val($('#medi_me_'+mid).val());
				//TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.drug == 'yes')
				{
					$('#drug_'+medication_type+'_'+row).val($('#medi_wirkstoffe_'+mid).val());
				}
				else
				{
					$('#drug_'+medication_type+'_'+row).val('');
				}
				//--
				if(!mmi_handler)
				{
					$('#comments_'+medication_type+'_'+row).val($('#medi_comment_'+mid).val());
					$('#hidd_medication_'+medication_type+'_'+row).val($('#medi_id_'+mid).val());
				}
				var parent_tr = $("#tr"+medication_type+""+row);
				$('input.medication_pzn' , parent_tr).val(pzn);
			    $('input.medication_source' , parent_tr).val(source);
			    $('input.medication_dbf_id' , parent_tr).val(dbf_id);	
			    //TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.atc == 'yes')
				{
					$('input.medication_atc' , parent_tr).val(atc); //ISPC-2554
				}
				else
				{
					$('input.medication_atc' , parent_tr).val(''); //ISPC-2554
				}
				//TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.takinghint == 'yes')
				{
			    //ISPC-2554 Carmen 16.06.2020
			    //if(module_takinghint == '1')
   	   	     	//{	
			    	$('#comments_'+medication_type+'_'+row).val(takinghint);
			    //}
				}
				else
				{
					$('#comments_'+medication_type+'_'+row).val('');
				}
			    //--
			  //ISPC 2554 Carmen 11.05.2020
			    var child_tr = parent_tr.next();
			    var dosmatch =  false;
			    var unitmatch = false;
			  //TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.dosage_form == 'yes')
				{
			    $.each( js_clientdosageform, function( key, value ) {
		    	   	  if(value.mmi_code == pharmformcode && pharmformcode != '')
		    	   	  {
		    	   		$('.medication_dosage_form', child_tr).val(value.id);
		    	   		var _val = value.dosage_form.substr(0,3);
		    	   		parent_tr.find('.selector_concentration_text').each(function(){
							$(this).text(_val);
						});
		    	   		dosmatch = true;
		    	   	  }
		    	   	});	    	   	
		    	   	if(dosmatch === false && pharmformcode != '')
		    	   	{	    	   		
		    	   		$('.medication_dosage_form', child_tr).val('mmi_'+pharmformcode);
		    	   		var _val = $("option:selected", $('.medication_dosage_form', child_tr)).text().substr(0,3);
		    	   		parent_tr.find('.selector_concentration_text').each(function(){
							$(this).text(_val);
						});
		    	   	}
		    	   	else if(dosmatch === false && pharmformcode == '')
		    	   	{	    	   		
		    	   		$('.medication_dosage_form', child_tr).val('0');
		    	   		parent_tr.find('.selector_concentration_text').each(function(){
							$(this).text('');
						});
		    	   	}
				}
				else
				{
					$('.medication_dosage_form', child_tr).val('0');
	    	   		parent_tr.find('.selector_concentration_text').each(function(){
						$(this).text('');
					});
				}
				//TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.unit == 'yes')
				{
	    	   	$.each( js_clientunit, function( key, value ) {
	    	   		
		    	   	  if(value.unit.toLowerCase() == unit.toLowerCase())
		    	   	  {
		    	   		$('.medication_unit', child_tr).val(value.id);
		    	   		var _val = value.unit;
		    	   		parent_tr.find('.selector_dosage_text').each(function(){
							$(this).text(_val);
						});
		    	   		unitmatch = true;
		    	   	  }
		    	   	});
		    	   	if(unitmatch === false && unit != '')
		    	   	{
		    	   		var unitadded = false; 
		    	   		$('.medication_unit option', child_tr).each( function() {
		    	   			if(this.value == 'mmi_'+unit.toLowerCase())
		    	   			{
		    	   				unitadded = true;
		    	   			}		    	   			
		    	   		});
		    	   		if(unitadded === false)
	    	   			{
	    	   				$('.medication_unit').append($('<option>').val('mmi_'+unit.toLowerCase()).text(unit.toLowerCase()));
	    	   			}
		    	   		$('.medication_unit', child_tr).val('mmi_'+unit.toLowerCase());
		    	   		var _val = $("option:selected", $('.medication_unit', child_tr)).text();
		    	   		parent_tr.find('.selector_dosage_text').each(function(){
							$(this).text(_val);
						});
		    	   	}
		    	   	else if(unitmatch === false && unit == '')
		    	   	{
		    	   		$('.medication_unit', child_tr).val('');
		    	   		parent_tr.find('.selector_dosage_text').each(function(){
							$(this).text('');
						});
		    	   	}
				}
				else
				{
					$('.medication_unit', child_tr).val('');
	    	   		parent_tr.find('.selector_dosage_text').each(function(){
						$(this).text('');
					});
				}
				//--
							    
				
			}
		}
	}
	
	
	
	
	
	function reset_medications(medication_type,input_row, pumpe_number)
	{
		
		if(medication_type == "isschmerzpumpe"){
			if($('#hidd_medication_'+medication_type+'_'+pumpe_number+'_'+input_row).val()){
				
				$('#hidd_medication_'+medication_type+'_'+pumpe_number+'_'+input_row).val('');
				$('#drid_'+medication_type+'_'+pumpe_number+'_'+input_row).val('');
			}
		} 
		else
		{
			if($('#hidd_medication_'+medication_type+'_'+input_row).val()){
				
				$('#hidd_medication_'+medication_type+'_'+input_row).val('');
				$('#drid_'+medication_type+'_'+input_row).val('');
			}
		}
	}
	
	
	
	
	function renew_medication(mid,pid,trid)
	{
		
		if(mid>0){
			/*
			 * ispc-2071
			 * + _isContactformText 
			 * + $.ajax.success
			 */
			
			var _isContactformText = '';
			if ($("#cfhiddennounce").length && $("#cfhiddencid").length) {
				_isContactformText =  '&_deleted_medis_contactform_cid=' + $("#cfhiddencid").val()
				+ "&_cfhiddennounce=" + $("#cfhiddennounce").val();
			}
			
			$.ajax({
				url:'patient/patientmedicationchange?mid='+mid+'&id='+pid+'&act=rnw&noredir=1'
					+ _isContactformText,
				async: false,
				
				success: function (data) {
					
					if (_isContactformText != '' && data != '') {
						try {
							
							var _data = JSON.parse(data);
							
							if ("callBack" in _data && "callBackParameters" in _data) {
									
								$.each(_data.callBackParameters, function (i, pc_id) {
									$('<input>', {
						                'type'  : "hidden",
						                'name'  : "deleted_medis_contactform_cid_patientcourse_id[]",
						                'value' : pc_id
						            }).prependTo("form#contact_form");
								});
							}
						} catch (e) {
							
						}
					}
				}
			});
		}
		if(acknowledge == "1"){
			if(approval_rights == 1){
				$("#"+trid).remove();
				// refresh -  all blocks  
				$.getmedicationblocks();
			} 
			else
			{
				// 		refresh deleted list
				getmedicationdeletededit();
			}
		} 
		else
		{
			$("#"+trid).remove();
			// refresh -  all blocks  
			$.getmedicationblocks();
		}
	}
	
	
	function remove_medication(mid,pid,trid)
	{

		if(mid>0)
		{
			/*
			 * ispc-2071
			 * + _isContactformText 
			 * + $.ajax.success
			 */
			var _isContactformText = '';
			if ($("#cfhiddennounce").length && $("#cfhiddencid").length) {
				_isContactformText =  '&_deleted_medis_contactform_cid=' + $("#cfhiddencid").val()
				+ "&_cfhiddennounce=" + $("#cfhiddennounce").val();
			}
			
			$.ajax({
				url:'patient/patientmedicationchange?mid='+mid+'&id='+pid+'&act=del&noredir=1' 
					+ _isContactformText,
				async: false, 
				
				success: function (data) {
					
					if (_isContactformText != '' && data != '') {
						
						try {							
							var _data = JSON.parse(data);
							
							if ("callBack" in _data && "callBackParameters" in _data) {
									
								$.each(_data.callBackParameters, function (i, pc_id) {
									$('<input>', {
						                'type'  : "hidden",
						                'name'  : "deleted_medis_contactform_cid_patientcourse_id[]",
						                'value' : pc_id
						            }).prependTo("form#contact_form");
								});
							}
						} catch (e) {
							
						}
					}
				}
			});
		}
		
		if(acknowledge == "1"){
			if(approval_rights == 1){
				var $tableident = $("#"+trid).closest('table');
				
				$("#"+trid).remove();
				$("#"+trid + "_child_toggle").remove();
				$("#"+trid + "_child").remove();
				
				 $tableident.find('tr').each(function(){
					if($(this).hasClass('acknowlege_row'))
					{
						var $ack_row = $(this).data('ack_id');
						
						if($ack_row == trid)
						{
							$(this).remove();
							
						}
						
					}
				 });
				getmedicationdeletededit();
			} 
			else
			{
				// refresh -  all blocks  
				$.getmedicationblocks();	
			}
		}
		else
		{
			
			$("#"+trid).remove();
			$("#"+trid + "_child_toggle").remove();
			$("#"+trid + "_child").remove();
			// refresh -  all blocks  
			getmedicationdeletededit();
			
		}
	}
	



	function remove_new_line(ids) {
		$(ids).remove();
		$(ids + "_child_toggle").remove();
		$(ids + "_child").remove();
	}
	
	
	
	
	
	
	
	
	var medicationeditblocks_ready = function() {
	//$(document).ready(function(){
 
 
		getmedicationdeletededit();
		
		
		//     ISPC-2127 deleted medis :: show hide 21.02.2018
		$('.deleted_meds_state').live('click',function(){
			
			$('.deleted_medication_edit_table').toggle();
			if ($(this).hasClass('hidden')){
				$(this).removeClass('hidden')
				$(this).addClass('shown')
			} else if ($(this).hasClass('shown')){
				$(this).removeClass('shown')
				$(this).addClass('hidden')
			}
		});

		
		
		// hide info buble in edit page
		$('.show_medication_info').hide();
		
		
		//if(client_pumpe_autocalculus && new_fields == "1") {
		if(client_pumpe_autocalculus && new_fields == "1" && show_dosage_unit == "0") {		//ISPC-2684 Lore 05.10.2020
	
			$('.extra_calculation').live("change keyup",function(eventObject){

				var pumpe_number = $(this).data("pumpe_number");
				var medication_type = $(this).data("medication_type");
				
				if($(this).data("pumpe_med")){
					var med_line = $(this).data("pumpe_med");
				}
				
				var field = $(this).data("ec_field");
				
				if($('#flussrate_isschmerzpumpe_'+pumpe_number).val()){
					var flussrate = $('#flussrate_isschmerzpumpe_'+pumpe_number).val();
					flussrate = + flussrate.replace(",",".");
				}	
				
				if( $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val()){
					var line_dosage = $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					line_dosage = + line_dosage.replace(",","."); 	
				}

				
				if($("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val()){
					var line_concentration = $("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					line_concentration = + line_concentration.replace(",","."); 	
				}
				
				if(field == "concentration"  )
				{
					var concentration = $(this).val();
					concentration = + concentration.replace(",","."); 	
					
					if(flussrate && isNumeric(flussrate) && isNumeric(concentration)  && concentration != ""  && flussrate != ""){ // calculate dosage
						
						var dosage = "";
						dosage = flussrate * concentration ;
						dosage_24 = dosage*24;

						var has_comma = dosage.toString().indexOf(".");
						
						if(has_comma != "-1"){
							dosage = dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
						}
						var has_comma_2h = dosage_24.toString().indexOf(".");
						
						if(has_comma_2h != "-1"){
							dosage_24 = dosage_24.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
						}

						$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage).removeClass("flash_red");
						$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage_24).removeClass("flash_red");
					}
					
					if( !flussrate  && line_dosage && (isNumeric(line_dosage) && isNumeric(concentration)  && concentration != ""  && line_dosage != "")){ // calculate flussrate - if flusarte is empty
						
						var new_flussrate = "";
						new_flussrate = line_dosage / concentration ;

						var has_comma = new_flussrate.toString().indexOf(".");
						
						if(has_comma != "-1"){
							new_flussrate = new_flussrate.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  					
						}

						$("#flussrate_"+medication_type+"_"+pumpe_number).val(new_flussrate);
					} 
					
					
					
					var carr = $('#carriersolution_isschmerzpumpe_'+pumpe_number).val();
					
					if(carr.length != 0 ){
						
						$('#carr_'+pumpe_number).append('<div class="alert" id="cal'+pumpe_number+'">' + translate("Warning: value was deleted!") + '</div>');
						$('#carriersolution_isschmerzpumpe_'+pumpe_number).addClass("flash_blue");
						$('#carriersolution_isschmerzpumpe_'+pumpe_number).val("");
						$('#carriersolution_extra_text_'+pumpe_number).text("");
						
			        	setTimeout(function () {
			        		$('#carriersolution_isschmerzpumpe_'+pumpe_number).removeClass("flash_blue");
			        		$("#cal"+pumpe_number).remove();
			        		}, 800);
					}
					
				} 
				else if(field == "flussrate")
				{
					//get all concentration from allmedis and change dosage
					
					var input_flussrate = $(this).val();
					input_flussrate = + input_flussrate.replace(",",".");
					
					if(isNumeric(input_flussrate)){
						$(this).removeClass("flash_red");
					}
					
					$("input[id^='concentration_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {

							var line_concentration = "";
					        var line_concentration = $(this).val();
					        line_concentration = + line_concentration.replace(",","."); 	

					         var new_line = $(this).data("pumpe_med");

								if(isNumeric(input_flussrate) && isNumeric(line_concentration)  && line_concentration != ""  && input_flussrate != ""){ // calculate dosage
									
									var dosage = "";
									var dosage_24 = "";
									dosage = input_flussrate * line_concentration ;
									dosage_24 = dosage*24;

									var has_comma = dosage.toString().indexOf(".");
									
									if(has_comma != "-1"){
										dosage = dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");				
									}
									var has_comma_2h = dosage_24.toString().indexOf(".");
									
									if(has_comma_2h != "-1"){
										dosage_24 = dosage_24.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
									}

									$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+new_line+"").val(String(dosage) );
									$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+new_line+"").removeClass("flash_red").addClass("flash_green");
									
									$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+new_line+"").val(String(dosage_24) );
									$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+new_line+"").removeClass("flash_red").addClass("flash_green");;
								
				
									//mark single drug as edited
									$('#edited_'+medication_type+'_'+pumpe_number+'_'+new_line).val('1');
									
									// set time out  - remove class
									setTimeout(function () {
										$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+new_line+"").removeClass("flash_green");
										$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+new_line+"").removeClass("flash_green")
									}, 800);
								} 
								else {
									//NaN
									$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+new_line+"").addClass("flash_red");
									$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+new_line+"").addClass("flash_red");
								}
					     });
					
					

					if($('#carriersolution_isschmerzpumpe_'+pumpe_number).val()){
						var input_carriersolution = $('#carriersolution_isschmerzpumpe_'+pumpe_number).val();
						input_carriersolution = + input_carriersolution.replace(",","."); 	
					}

					if (isNaN(input_flussrate) || isNaN(input_carriersolution) || input_flussrate==0 || input_carriersolution==0) {
						$(this).closest('table').find('.carriersolution_extra_text').text('');
						return;
					}
					
					var first_text = input_carriersolution / input_flussrate; //the first (max) = Gesamtvolumen / Flussrate
					first_text = first_text.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");
					
					var second_text = ''; 
					
					var carriersolution_extra_text = translate("carriersolution_extra_text");
					carriersolution_extra_text = carriersolution_extra_text.format(carriersolution_extra_text, first_text, second_text);

					$(this).closest('table').find('.carriersolution_extra_text').text(carriersolution_extra_text);
					
					return;

				}
				else if(field == "dosage")
				{
					var input_dosage = $(this).val();
					input_dosage = + input_dosage.replace(",",".");
					
					
					if(line_concentration && isNumeric(input_dosage) && isNumeric(line_concentration)  && line_concentration != ""  && input_dosage != ""){ // calculate flussrate
						
						var new_flussrate = "";
						new_flussrate = input_dosage / line_concentration ;

						var has_comma = new_flussrate.toString().indexOf(".");
						
						if(has_comma != "-1"){
							new_flussrate = new_flussrate.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
						}

						$("#flussrate_"+medication_type+"_"+pumpe_number).val(new_flussrate).removeClass("flash_red");
						
						var dosage24h = input_dosage *24;
						dosage24h = dosage24h.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val(dosage24h).removeClass("flash_red");	
						$(this).removeClass("flash_red");
						
						//console.log($("#flussrate_"+medication_type+"_"+pumpe_number).val());
						
						var inputsfls = $("#flussrate_"+medication_type+"_"+pumpe_number).val();
						if(inputsfls.length != 0 ){
						
						    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
						         var other_line = $(this).data("pumpe_med");
						         if(other_line != med_line){
// 						        	 $(this).val("");
	
						        	 $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
// 						        	 setTimeout(function () {$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red")}, 800);
						        	 
						         }
						     });
						}
						
					}
					else if( isNumeric(input_dosage) ){
						var dosage24h = input_dosage * 24;
						dosage24h = dosage24h.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val(dosage24h).removeClass("flash_red");	
						$(this).removeClass("flash_red");
						$("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
						
						var inputsfls = $("#flussrate_"+medication_type+"_"+pumpe_number).val();
						if(inputsfls.length != 0 ){
						
						    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
						         var other_line = $(this).data("pumpe_med");
						         if(other_line != med_line){
// 						        	 $(this).val("");
	
						        	 $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
// 						        	 setTimeout(function () {$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red")}, 800);
						        	 
						         }
						     });
						}
					}		
					else {
						//NaN
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val('').addClass("flash_red");	
						$("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
			        	/*
						setTimeout(function () {
							$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+med_line+"").removeClass("flash_red");
							$("#flussrate_"+medication_type+"_"+pumpe_number).removeClass("flash_red");
						}, 800);
			        	*/

					}
					
					
					/*
					if($("#carriersolution_"+medication_type+"_"+pumpe_number).val()){
						var carriersolution_value = $("#carriersolution_"+medication_type+"_"+pumpe_number).val();
						carriersolution_value = + carriersolution_value.replace(",","."); 	
						
						
					    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
					         var other_line = $(this).data("pumpe_med");
					         var input_dosage = $(this).val();
					         input_dosage = + input_dosage.replace(",",".");
					         
					         if(isNumeric(input_dosage) && isNumeric(carriersolution_value)  && carriersolution_value != ""  && input_dosage != ""){
					        	 
					        	 var new_concentration_line =  input_dosage / carriersolution_value;
					        	 //console.log('new concentration'+new_concentration_line);
						        	
						     		var has_comma = new_concentration_line.toString().indexOf(".");
									
									if(has_comma != "-1"){
										new_concentration_line = new_concentration_line.toFixed(3).toString().replace(".",",");   					
									}
						        	 $("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").val(new_concentration_line);
										
										
										
										$("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red").addClass("flash_green");;
										setTimeout(function () {
											$("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_green")
										}, 800);
					        	 
					         }

					     });
					}
					
				*/
						
				}
				else if(field == "dosage_24h")
				{
					var input_dosage_24h = $(this).val();
					input_dosage_24h = + input_dosage_24h.replace(",",".");
					
					var input_dosage =  input_dosage_24h / 24;
					
					
					// calculate flussrate
					if(line_concentration && isNumeric(input_dosage) && isNumeric(line_concentration)  && line_concentration != ""  && input_dosage != "")
					{ 				
						var new_flussrate = "";
						new_flussrate = input_dosage / line_concentration ;
					
						var has_comma = new_flussrate.toString().indexOf(".");
						
						if(has_comma != "-1"){
							new_flussrate = new_flussrate.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
						}

						$("#flussrate_"+medication_type+"_"+pumpe_number).val(new_flussrate).removeClass("flash_red");

						input_dosage = input_dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val(input_dosage).removeClass("flash_red");	
						$(this).removeClass("flash_red");
						
												
						var inputsfls = $("#flussrate_"+medication_type+"_"+pumpe_number).val();
						if(inputsfls.length != 0 ){
						
						    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
						         var other_line = $(this).data("pumpe_med");
						         if(other_line != med_line){
// 						        	 $(this).val("");
	
						        	 $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
// 						        	 setTimeout(function () {$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red")}, 800);
						        	 
						         }
						     });
						}
						
					} 
					else if (isNumeric(input_dosage_24h)){

						input_dosage = input_dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val(input_dosage).removeClass("flash_red");	
						$(this).removeClass("flash_red");
						$("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
						
						
						
						var inputsfls = $("#flussrate_"+medication_type+"_"+pumpe_number).val();
						if(inputsfls.length != 0 ){
						
						    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
						         var other_line = $(this).data("pumpe_med");
						         if(other_line != med_line){
// 						        	 $(this).val("");
	
						        	 $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+other_line+"").addClass("flash_red");
						        	 $("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
// 						        	 setTimeout(function () {$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red")}, 800);
						        	 
						         }
						     });
						}
						
					}
					 else {
							//NaN
							$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val('').addClass("flash_red");	
							$("#flussrate_"+medication_type+"_"+pumpe_number).addClass("flash_red");
				        	/*
							setTimeout(function () {
								$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").removeClass("flash_red");
								$("#flussrate_"+medication_type+"_"+pumpe_number).removeClass("flash_red");
							}, 800);
				        	*/

						}
					
				}
				else if(field == "carriersolution")
				{

					var input_carriersolution = $(this).val();
					input_carriersolution = + input_carriersolution.replace(",",".");
					
					if (isNaN(flussrate) || isNaN(input_carriersolution) || flussrate==0 || input_carriersolution==0) {
						$(this).next('.carriersolution_extra_text').text('');
						return;
					}
					
					var first_text = input_carriersolution / flussrate; //the first (max) = Gesamtvolumen / Flussrate
					first_text = first_text.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");
					
					var second_text = ''; 
					
					var carriersolution_extra_text = translate("carriersolution_extra_text");
					carriersolution_extra_text = carriersolution_extra_text.format(carriersolution_extra_text, first_text, second_text);

					$(this).next('.carriersolution_extra_text').text(carriersolution_extra_text);
					
					return;
					//DONT let the carriersolution "Gesamtvolumen" have an impact on the concentration and the auto calculation.

					/*
				    $("input[id^='dosage_sh_"+medication_type+"_"+pumpe_number+"']").each(function (i, el) {
				         var other_line = $(this).data("pumpe_med");
				         var input_dosage = $(this).val();
				         input_dosage = + input_dosage.replace(",",".");
				         
				         if(isNumeric(input_dosage) && isNumeric(input_carriersolution)  && input_carriersolution != ""  && input_dosage != ""){
				        	 var new_concentration_line =  input_dosage / input_carriersolution;
				        	 
				        	
				     		var has_comma = new_concentration_line.toString().indexOf(".");
							
							if(has_comma != "-1"){
								new_concentration_line = new_concentration_line.toFixed(3).toString().replace(".",",");   					
							}
				        	 $("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").val(new_concentration_line);
								
								
								
								$("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_red").addClass("flash_green");;
								setTimeout(function () {
									$("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+other_line+"").removeClass("flash_green")
								}, 800);
				        	 
				        	 
					         // add blinking class
				         }
				     });
					*/
				}	
			});
			
		}
		
		//ISPC-2684 Lore 05.10.2020
		if (show_dosage_unit == "1") {
			$('.extra_calculation').live("change keyup",function(eventObject){
				var pumpe_number = $(this).data("pumpe_number");
				var medication_type = $(this).data("medication_type");
				var field = $(this).data("ec_field");

				if($(this).data("pumpe_med")){
					var med_line = $(this).data("pumpe_med");
				}
				
				if($("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val() && field == "dosage"){
					var line_dosage = $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					
					var has_comma = line_dosage.toString().indexOf(",");
					if(has_comma != "-1"){
						line_dosage = + line_dosage.replace(",","."); 	
					}

					var dosage_sh_24h_val = line_dosage*24;
					dosage_sh_24h_val = dosage_sh_24h_val.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, ""); 
					$("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage_sh_24h_val);
				}
				
				if($("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val() && field == "dosage_24h"){
					var line_dosage_24 = $("#dosage_sh_24h_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					
					var has_comma = line_dosage_24.toString().indexOf(",");
					if(has_comma != "-1"){
						line_dosage_24 = + line_dosage_24.replace(",","."); 	
					}
					var dosage_sh_val = line_dosage_24/24;
					dosage_sh_val = dosage_sh_val.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, ""); 
					$("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage_sh_val);
				}
				
				if($("#dosage_sh_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val() && field == "unit_dosage"){
					var line_dosage_sh_unit = $("#dosage_sh_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					
					var has_comma = line_dosage_sh_unit.toString().indexOf(",");
					if(has_comma != "-1"){
						line_dosage_sh_unit = + line_dosage_sh_unit.replace(",","."); 	
					}
					var dosage_sh_24h_unit_val = line_dosage_sh_unit*24;
					dosage_sh_24h_unit_val = dosage_sh_24h_unit_val.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, ""); 
					$("#dosage_sh_24h_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage_sh_24h_unit_val);
				}
				
				if($("#dosage_sh_24h_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val() && field == "unit_dosage_24h"){
					var line_dosage_sh_24h_unit = $("#dosage_sh_24h_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();

					var has_comma = line_dosage_sh_24h_unit.toString().indexOf(",");
					if(has_comma != "-1"){
						line_dosage_sh_24h_unit = + line_dosage_sh_24h_unit.replace(",","."); 	
					}
					
					var dosage_sh_unit_val = line_dosage_sh_24h_unit/24;
					dosage_sh_unit_val = dosage_sh_unit_val.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, ""); 
					$("#dosage_sh_unit_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val(dosage_sh_unit_val);
				}
				
				if($("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val() && field == "concentration"){
					var line_concentration = $("#concentration_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					line_concentration = + line_concentration.replace(",","."); 
				}
            });
		}

		
		// ALLOW DOSAGE CALCULATION  - even if live calculation is disabled ( meaning if module : "MEDICATION :: Pumpe :: Live calculation"  is not selected) 
		if( !client_pumpe_autocalculus && new_fields == "1") {
			
			$('.extra_calculation').live("change keyup",function(eventObject){
				
				var pumpe_number = $(this).data("pumpe_number");
				var medication_type = $(this).data("medication_type");
				
				if($(this).data("pumpe_med")){
					var med_line = $(this).data("pumpe_med");
				}
				
				var field = $(this).data("ec_field");
 
				if( $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val()){
					var line_dosage = $("#dosage_sh_"+medication_type+"_"+pumpe_number+"_"+med_line+"").val();
					line_dosage = + line_dosage.replace(",","."); 	
				}
				
				if(field == "dosage")
				{
					var input_dosage = $(this).val();
					input_dosage = + input_dosage.replace(",",".");
					if( isNumeric(input_dosage)   && input_dosage != ""){ // calculate flussrate
						
						var dosage24h = input_dosage *24;
						dosage24h = dosage24h.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val(dosage24h).removeClass("flash_red");	
						$(this).removeClass("flash_red");
					}
					else if( isNumeric(input_dosage) ){
						var dosage24h = input_dosage * 24;
						dosage24h = dosage24h.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val(dosage24h).removeClass("flash_red");	
						$(this).removeClass("flash_red");
					}		
					else {
						//NaN
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val('').addClass("flash_red");	
		 
					}
					
				}
				else if(field == "dosage_24h")
				{
					var input_dosage_24h = $(this).val();
					input_dosage_24h = + input_dosage_24h.replace(",",".");

					var input_dosage =  input_dosage_24h / 24;
					
					// calculate flussrate
					if(  isNumeric(input_dosage) && input_dosage != "")
					{ 				
						input_dosage = input_dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val(input_dosage).removeClass("flash_red");	
						$(this).removeClass("flash_red");
					} 
					else if (isNumeric(input_dosage_24h)){
						
						input_dosage = input_dosage.toFixed(2).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val(input_dosage).removeClass("flash_red");	
						$(this).removeClass("flash_red");
						
					}
					else {
						//NaN
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val('').addClass("flash_red");	
						
					}
					
				}
			});
			
		}
		
		
		
		
		$(document).off('click',".has_interval").on('click', '.has_interval', function(){
			var medication_type = $(this).data('medication_type');
			var medcount_line = $(this).data('medcount');
			if($(this ).prop( "checked" )){
				$("#int_set_"+medication_type+"_"+medcount_line).show();		
				$('#edited_'+medication_type+'_'+medcount_line).val('1');
			}
			else
			{
				$("#int_set_"+medication_type+"_"+medcount_line).hide();
				$('#edited_'+medication_type+'_'+medcount_line).val('1');
				// empty lines
				// interval				
			}
	


		});
		
		//ISPC-2524 pct.2)  Lore 15.01.2020
		$(document).off('click',".change_actual_vs_bedarf").on('click', '.change_actual_vs_bedarf', function(){
			var medication_type = $(this).data('medication_type');
			var medcount_line = $(this).data('medcount');
			if($(this ).prop( "checked" )){
				$('#edited_'+medication_type+'_'+medcount_line).val('1');
			}
			else
			{
				$('#edited_'+medication_type+'_'+medcount_line).val('1');			
			}
		});	

		//ISPC-2524 pct.2)  Lore 15.01.2020
		$(document).off('click',".change_bedarf_vs_actual").on('click', '.change_bedarf_vs_actual', function(){
			var medication_type = $(this).data('medication_type');
			var medcount_line = $(this).data('medcount');
			if($(this ).prop( "checked" )){
				$('#edited_'+medication_type+'_'+medcount_line).val('1');
			}
			else
			{
				$('#edited_'+medication_type+'_'+medcount_line).val('1');			
			}
		});			
		
		
		
		if (show_mmi == "1") {
			//used in mmi dialog
			var active_recipe_row = null;
			$(document).off('click',".mmi_search_button").on('click', '.mmi_search_button', function(){
				//var receipt_field = $(this).prev().prev();
				var receipt_field = $(this);
				active_recipe_row = receipt_field;
			});
		}

		// ISPC-2329 pct.a) Lore 21.08.2019
		// change indication select background 
  		$('.indication_color_select').live('change', function() {
	  		var that = $(this).find('option:selected'); 
	  		//console.log(that);
	  		//alert(that.val());
	  		if(that.val()!=0){
	  			$(this).attr("style",that.attr("style"));
	  		} else{
	  			$(this).removeAttr("style")  			
	  		}
  			
  		});
		
		
  		// DEFINE MEDICATION LIVESEARCH - WITH MMI OR NOT
  		if (show_mmi == "1") {
			//INSTALL MEDIINDEX-WIDGET
			var pi = new pharmaindex();
			pi.input_medname = ".med";
			pi.input_rowparent = "tr";
			pi.input_receipe_butt = ".mmi_search_button";
			pi.input_to_recipe = ".to_recipe";
			pi.mode="recipe";
			pi.ikno = "#ikno_input";
			pi.use_suggestions = '0';
			pi.otcWarningSw = '0';
	
			//PATH FOR THE AJAX SCRIPTS
			pi.ajaxPath = "pharmaindex";
	
			//PATH FOR THE IMAGE FOLDER
			pi.imagePath="images/pharmaindex";
	
			/*
			pi.callback = function(recipe,drug){ //
				
				var medication_type = active_recipe_row.attr('rel');
				var input_row = parseInt(active_recipe_row.attr('id').substr(('medication_'+medication_type+'_').length));

				$(active_recipe_row).val(recipe);
				
				if(drug){
					$('#drug_'+medication_type+'_'+input_row).val(drug);
				}
				
				$('#hidd_medication_'+medication_type+'_'+input_row).val('');
				$('#edited_'+medication_type+'_'+input_row).val('1');
			};
			*/		

			pi.callback = function(recipe, drug, extra_object ){
				
				var input_row = (active_recipe_row.attr('id').substr(('mmi_search_').length));
				var res = input_row.split("_"); 
				var medication_type = res[0];
				
				var pzn = 0;
				var source = "custom";
				var dbf_id = 0;
				
				//extra_object
				if (arguments.length == 3 && typeof arguments[2] == 'object') {

					if (arguments[2]['source'] == "mmi_dialog_price" && typeof arguments[2]['PRICE_ITEM'] == 'object') {
						//this is a medication selected from the mmi pricelist
						var PRICE_ITEM = arguments[2]['PRICE_ITEM'];
						pzn = PRICE_ITEM.PZN;
						dbf_id = PRICE_ITEM.ID;
						source = arguments[2]['source'];
					} else if (arguments[2]['source'] == "mmi_dialog_product" && typeof arguments[2]['PRODUCT'] == 'object') {
						//this is a medication selected from the packages list
						//!!! we don't use the pzn from the package list here ...
						//PRODUCT.PACKAGE_LIST is [{}]
						/*
						var PACKAGE_ITEM_0 = arguments[2]['PRODUCT']['PACKAGE_LIST'][0];
						pzn = PACKAGE_ITEM_0.PZN;
						dbf_id = PACKAGE_ITEM_0.ID;
						*/
						var PRODUCT = arguments[2]['PRODUCT']
						pzn = 0;
						dbf_id = PRODUCT.ID;
						source = arguments[2]['source'];
					} else {
						//error
						pzn = 0;
						source = "custom";
						dbf_id = 0;
					}
				}
				
				//ISPC-2554 pct.3 Carmen 26.03.2020
				var atcstring = '';
				var pharmformcode = '';
				var unit = '';
				var takinghint = '';
				//ISPC 2554 Carmen 07.08.2020
				var drugdrugarr = [];
				var drugdrug = '';
				//--
				if(!$.isEmptyObject(extra_object) && extra_object.hasOwnProperty('PRODUCT'))
				{
					if(extra_object.PRODUCT.hasOwnProperty('ITEM_LIST') && !$.isEmptyObject(extra_object.PRODUCT.ITEM_LIST[0]))
					{
						if(extra_object.PRODUCT.ITEM_LIST[0].hasOwnProperty('ATCCODE_LIST') && !$.isEmptyObject(extra_object.PRODUCT.ITEM_LIST[0].ATCCODE_LIST[0]))
						{
				
				            var atclist = extra_object.PRODUCT.ITEM_LIST[0].ATCCODE_LIST[0];
				            var atcarr = {};
				            
				            atcarr['atc_code'] = atclist.CODE;
				            atcarr['atc_description'] = atclist.NAME;
				            atcarr['atc_groupe_code'] = atclist.PARENT.CODE;
				            atcarr['atc_groupe_description'] = atclist.PARENT.NAME;
				            
				            atcstring = JSON.stringify(atcarr);
						}
						if(extra_object.PRODUCT.ITEM_LIST[0].hasOwnProperty('PHARMFORMCODE') && extra_object.PRODUCT.ITEM_LIST[0].PHARMFORMCODE != "")
						{
							pharmformcode = extra_object.PRODUCT.ITEM_LIST[0].PHARMFORMCODE;
						}
						if(extra_object.PRODUCT.ITEM_LIST[0].hasOwnProperty('BASEMOLECULEUNITCODE') && extra_object.PRODUCT.ITEM_LIST[0].BASEMOLECULEUNITCODE != "")
						{
							unit = extra_object.PRODUCT.ITEM_LIST[0].BASEMOLECULEUNITCODE;
						}
						if(extra_object.PRODUCT.ITEM_LIST[0].hasOwnProperty('TAKINGHINT') && extra_object.PRODUCT.ITEM_LIST[0].TAKINGHINT != "")
						{
							takinghint = extra_object.PRODUCT.ITEM_LIST[0].TAKINGHINT;
						}
						//ISPC-2554 Carmen 07.08.2020
						if(extra_object.PRODUCT.ITEM_LIST[0].hasOwnProperty('COMPOSITIONELEMENTS_LIST') && extra_object.PRODUCT.ITEM_LIST[0].COMPOSITIONELEMENTS_LIST.length > 0)
						{
							for (i in extra_object.PRODUCT.ITEM_LIST[0].COMPOSITIONELEMENTS_LIST)
							{
								if(extra_object.PRODUCT.ITEM_LIST[0].COMPOSITIONELEMENTS_LIST[i].MOLECULETYPECODE == "A")
								{
									drugdrugarr.push(extra_object.PRODUCT.ITEM_LIST[0].COMPOSITIONELEMENTS_LIST[i].MOLECULENAME)
								}
							}
						}
						//--
					}
				}
				//--
								
	            var parent_tr;
	            if (parent_tr = $(active_recipe_row).closest("tr")) {
	            	 $('.medication_pzn', parent_tr).val(pzn.toString().escapeValue());
	    	   	     $('.medication_source', parent_tr).val(source.toString().escapeValue());
	    	   	     $('.medication_dbf_id', parent_tr).val(dbf_id.toString().escapeValue());
	    	   	
	    	   	//TODO-3365 Carmen 21.08.2020
 				if(js_pharmaindex_settings.atc == 'yes')
 				{
 					$('input.medication_atc' , parent_tr).val(atc); //ISPC-2554
 				}
 				else
 				{
 					$('input.medication_atc' , parent_tr).val(''); //ISPC-2554
 				}
 				//--
	    	   	//TODO-3365 Carmen 21.08.2020
 				if(js_pharmaindex_settings.takinghint == 'yes')
 				{
	    	   	//ISPC-2554 Carmen 16.06.2020
	    	   	    // if(module_takinghint == '1')
	    	   	     //{
	    	   	    	 $('.editcomment', parent_tr).val(takinghint);
	    	   	    // }
 				}
 				else
 				{
 					 $('.editcomment', parent_tr).val('');
 				}
	    	   	     //--
	    	   	     
	    	   	//ISPC-2554 Carmen 11.05.2020
		    	   	    var dosmatch =  false;
		    	   	    var unitmatch = false;
		    	   	    var child_tr = parent_tr.next();
		    	   	    //TODO-3365 Carmen 21.08.2020
		 				if(js_pharmaindex_settings.dosage_form == 'yes')
		 				{
		    	   	    $.each( js_clientdosageform, function( key, value ) {
			    	   	  if(value.mmi_code == pharmformcode && pharmformcode != '')
			    	   	  {
			    	   		$('.medication_dosage_form', child_tr).val(value.id);
			    	   		var _val = value.dosage_form.substr(0,3);
			    	   		parent_tr.find('.selector_concentration_text').each(function(){
								$(this).text(_val);
							});
			    	   		dosmatch = true;
			    	   	  }
			    	   	});	    	   	
			    	   	if(dosmatch === false && pharmformcode != '')
			    	   	{	    	   		
			    	   		$('.medication_dosage_form', child_tr).val('mmi_'+pharmformcode);
			    	   		var _val = $("option:selected", $('.medication_dosage_form', child_tr)).text().substr(0,3);
			    	   		parent_tr.find('.selector_concentration_text').each(function(){
								$(this).text(_val);
							});
			    	   	}
			    	   	else if(dosmatch === false && pharmformcode == '')
			    	   	{	    	   		
			    	   		$('.medication_dosage_form', child_tr).val('0');
			    	   		parent_tr.find('.selector_concentration_text').each(function(){
								$(this).text('');
							});
			    	   	}
		 				}
		 				else
		 				{
		 					$('.medication_dosage_form', child_tr).val('0');
			    	   		parent_tr.find('.selector_concentration_text').each(function(){
								$(this).text('');
							});
		 				}
		 				//TODO-3365 Carmen 21.08.2020
		 				if(js_pharmaindex_settings.unit == 'yes')
		 				{
			    	   	$.each( js_clientunit, function( key, value ) {
			    	   	  if(value.unit.toLowerCase() == unit.toLowerCase())
			    	   	  {
			    	   		$('.medication_unit', child_tr).val(value.id);
			    	   		var _val = value.unit;
			    	   		parent_tr.find('.selector_dosage_text').each(function(){
								$(this).text(_val);
							});
			    	   		unitmatch = true;
			    	   	  }
			    	   	});
			    	   	
			    	   	if(unitmatch === false && unit != '')
			    	   	{
			    	   		var unitadded = false; 
			    	   		$('.medication_unit option').each( function() {
			    	   			if(this.value == 'mmi_'+unit.toLowerCase())
			    	   			{
			    	   				unitadded = true;
			    	   			}		    	   			
			    	   		});
			    	   		if(unitadded === false)
		    	   			{
		    	   				$('.medication_unit').append($('<option>').val('mmi_'+unit.toLowerCase()).text(unit.toLowerCase()));
		    	   			}
			    	   		$('.medication_unit', child_tr).val('mmi_'+unit.toLowerCase());
			    	   		var _val = $("option:selected", $('.medication_unit', child_tr)).text();
			    	   		parent_tr.find('.selector_dosage_text').each(function(){
								$(this).text(_val);
							});
			    	   	}
			    	   	else if(unitmatch === false && unit == '')
			    	   	{
			    	   		$('.medication_unit', child_tr).val('');
			    	   		parent_tr.find('.selector_dosage_text').each(function(){
								$(this).text('');
							});
			    	   	}
		 				}
		 				else
		 				{
		 					$('.medication_unit', child_tr).val('');
			    	   		parent_tr.find('.selector_dosage_text').each(function(){
								$(this).text('');
							});
		 				}
			    	   	//--
	            }
	   	     
				
				if( medication_type != "isschmerzpumpe"){
					//TODO-3365 Carmen 21.08.2020
	 				if(js_pharmaindex_settings.drug == 'yes')
	 				{
					if(drug){
						//ISPC-2554 Carmen 05.08.2020
						//$('#drug_'+input_row).val(drug);
						drugdrug = drugdrugarr.join(', ');
						
						$('#drug_'+input_row).val(drugdrug);
						//--
					}else{
						$('#drug_'+input_row).val('');
					}
	 				}
	 				else
	 				{
	 					$('#drug_'+input_row).val('');
	 				}
					
					$('#medication_'+input_row).val(recipe);
					$('#hidd_medication_'+input_row).val('');
					$('#edited_'+input_row).val('1');
				} 
				else
				{

				 	input_row = res[1];
				 	var extra_pumpe_number = res[3];
				 
					$('#medication_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val(recipe);
					//TODO-3365 Carmen 21.08.2020
	 				if(js_pharmaindex_settings.drug == 'yes')
	 				{
				 	if(drug){
				 		//ISPC-2554 Carmen 05.08.2020
						//$('#drug_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val(drug);
				 		drugdrug = drugdrugarr.join(', ');
						
						$('#drug_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val(drugdrug);
						//--
					}else{
						$('#drug_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val('');
					}
	 				}
	 				else
	 				{
	 					$('#drug_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val('');
	 				}
				 
				 	$( "input[id^='hidd_medication_']" , $(active_recipe_row).parent()).val('');
				 	$( "input[id^='edited_']" , $(active_recipe_row).parent()).val('1');
				 	
				 	
					//$('#hidd_medication_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val('');
					//$('#edited_'+medication_type+'_'+extra_pumpe_number+'_'+input_row).val('1');
					
				}
			};
	
			pi.install();
			
			$('.livesearchmedinp').live('change', function() {
				var medication_type = $(this).attr('rel');
				var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
				reset_medications(medication_type,input_row);
			}).liveSearch({
				url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
				id: 'livesearch_admission_medications',
				aditionalWidth: '300',
				noResultsDelay: '900',
				typeDelay: '900',
				returnRowId: function (input) {
					var medication_type = $(this).attr('rel');
					return $(input).attr('id'); // not integer, ex :: medicationactual1 
					}
			});
  		} else {
			$('.livesearchmedinp').live('change', function() {
				var medication_type = $(this).attr('rel');
				var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
				reset_medications(medication_type,input_row);
			}).liveSearch({
				url: 'ajax/medications?q=',
				id: 'livesearch_admission_medications',
				aditionalWidth: '300',
				noResultsDelay: '900',
				typeDelay: '900',
				returnRowId: function (input) {
					return $(input).attr('id'); // not integer, ex :: medicationactual1 
				}
			});
		
  		}
		
		
		// DEFINE NUTRITION LIVESEARCH 
		$('.livesearchmedinp_nutrition').live('change', function() {
			var medication_type = $(this).attr('rel');
			var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
			reset_medications(medication_type,input_row);
		}).liveSearch({
			url: 'ajax/medicationsnutrition?q=',
			id: 'livesearch_admission_medications',
			aditionalWidth: '300',
			noResultsDelay: '900',
			typeDelay: '900',
			returnRowId: function (input) {
				return $(input).attr('id'); // not integer, ex :: medicationactual1 
			}
		});
		
		// DEFINE TREATMENT  CARE LIVESEARCH 
		$('.livesearchmedinp_treatment_care').live('change', function() {
			var medication_type = $(this).attr('rel');
			var input_row = parseInt($(this).attr('id').substr(('medication_'+medication_type+'_').length));
			reset_medications(medication_type,input_row);
		}).liveSearch({
			url: 'ajax/medicationstreatmentcare?q=',
			id: 'livesearch_admission_medications',
			aditionalWidth: '300',
			noResultsDelay: '900',
			typeDelay: '900',
			returnRowId: function (input) {
				return $(input).attr('id'); // not integer, ex :: medicationactual1 
			}
		});

		/* ----------------------------------------------------------    */
		$('.date_input').datepicker({
			dateFormat: 'dd.mm.yy',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: "0",
			onSelect: function(){
				
		        var todaysDate = new Date();
		        var selectedDate = new Date(Date.parse($(this).datepicker('getDate')));
		        
		        if (selectedDate > todaysDate) {
		            alert("Future Datum nicht erlaubt");
		            $(this).addClass('err_date_input');
		            $(this).val(formattedDate(todaysDate));
		        } else {
		        	$(this).removeClass('err_date_input');
		        }
		        
		        
		        
				var medication_type = $(this).attr("rel");
				if ($(this).hasClass('referral')) 
				{
					var input_row = parseInt($(this).attr('title').substr((medication_type+'_').length)); 			
				}
				$('#edited_'+medication_type+'_'+input_row).val('1');
				
			} 
		});
		$(".date_input").mask("99.99.9999");
		
		$(".date_input").live("change",function(){
			var selectedDate = new Date(Date.parse($(this).datepicker('getDate')));
			var todaysDate = new Date();
			
			if(selectedDate > todaysDate) {
	            alert("Future Datum nicht erlaubt");
	            $(this).addClass('err_date_input')
	            $(this).val(formattedDate(todaysDate));
	        } else{
	        	$(this).removeClass('err_date_input')
	        }
			
		        
		});

		
		$('.medication_adminisration_date').datepicker({
			dateFormat: 'dd.mm.yy',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			maxDate: "0",
			onSelect: function(){
				var medication_type = $(this).attr("rel");
				if ($(this).hasClass('referral')) 
				{
					var input_row = parseInt($(this).attr('title').substr((medication_type+'_').length)); 			
				}
				$('#edited_'+medication_type+'_'+input_row).val('1');
				
				
		        var todaysDate = new Date();
		        var selectedDate = new Date(Date.parse($(this).datepicker('getDate')));
		        
		        if (selectedDate > todaysDate) {
		            alert("Future Datum nicht erlaubt");
		            $(this).addClass('err_date_input');
		            $(this).val(formattedDate(todaysDate));
		        } else {
		        	$(this).removeClass('err_date_input');
		        }
		        
			}
		});
		$(".medication_adminisration_date").mask("99.99.9999");

		$(".medication_adminisration_date").live("change",function(){
			var selectedDate = new Date(Date.parse($(this).datepicker('getDate')));
			var todaysDate = new Date();
			
			if(selectedDate > todaysDate) {
	            alert("Future Datum nicht erlaubt");
	            $(this).addClass('err_date_input')
	            $(this).val(formattedDate(todaysDate));
	        } else{
	        	$(this).removeClass('err_date_input')
	        }
			
		        
		});
		/* ----------------------------------------------------------    */
		/*
		$('.calculate_sh_d24h').live('keyup change ', function(){
			
			return true;
			var row_info = $(this).data('row_info');
			dosage = $(this).val();
			dosage = + dosage.replace(",","."); 	
			
			if($.isNumeric(dosage))
			{
				if(dosage.length != '0'){
					var dosage24h = dosage *24;
					var has_comma = dosage24h.toString().indexOf(".");
					
					if(has_comma != "-1"){
						dosage24h = dosage24h.toFixed(3).toString().replace(".",",");   		
					}
					
					$('#dosage_sh_24h_'+row_info).val(dosage24h);	
				} else{
					$('#dosage_sh_24h_' + row_info).val();	
				}
			}
			else
			{
				$('#dosage_sh_24h_' + row_info).val();	
			}
		});

		$('.calculate_sh_d').live('keyup change ', function(){
			
			return true;
			
			var row_info = $(this).data('row_info');
			dosage = $(this).val();
			dosage = + dosage.replace(",","."); 	
			
			if($.isNumeric(dosage))
			{
				
				if(dosage.length != '0'){
					
					var dosageh = dosage / 24;
					var has_comma = dosageh.toString().indexOf(".");
					
					if(has_comma != "-1"){
						dosageh = dosageh.toFixed(3).toString().replace(".",",");   		
					}
					$('#dosage_sh_' + row_info).val(dosageh);	
				} else{
					
					$('#dosage_sh_' + row_info).val('');	
				}
			}
			else
			{
				$(this).parents("div.pumpeblock").find("input[data-ec_field='flussrate']").val('');
				$('#dosage_sh_' + row_info).val('');	
			}
		});
		*/

		
		
		/* -------------------------------------------------------------- */
		$('.calculate_dosage_concentration').live('keyup change ', function(){
			var row_info = $(this).data('dosage_row_info');
			var column_info = $(this).data('dosage_column_info');

			if($('#concentration_'+row_info).val() != undefined){
				concentration = $('#concentration_'+row_info).val();
				concentration = + concentration.replace(",","."); 	
				
				dosage = $(this).val();
				dosage = + dosage.replace(",","."); 	

				if($.isNumeric(dosage))
				{
					if(concentration.length != '0' && concentration != '' && concentration != '0' && dosage.length != '0'){
						var dosage_concentration = dosage / concentration;			
						
						var has_comma = dosage_concentration.toString().indexOf(".");
						
						if(has_comma != "-1"){
							dosage_concentration = dosage_concentration.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						}
						
						if (dosage_concentration == "0" && !~has_comma) {
							dosage_concentration = '';
						}
						
						$('#dosage_concentration_' + column_info).val(dosage_concentration);	
					} else{

						$('#dosage_concentration_' + column_info).val('');
					}
				}
				else
				{
					$('#dosage_concentration_' + column_info).val('');
				}
			}
		});
		
		$('.calculate_dosage').live('keyup change ', function(){
			var row_info = $(this).data('dosage_row_info');
			var column_info = $(this).data('dosage_column_info');
			
			if($('#concentration_'+row_info).val() != undefined){
				concentration = $('#concentration_'+row_info).val();
				concentration = + concentration.replace(",","."); 	
				dosage_concentration = $(this).val();
				dosage_concentration = + dosage_concentration.replace(",","."); 	
				
				if( concentration.length != '0' && concentration != '' && dosage_concentration.length != '0'){
				
				if($.isNumeric(dosage_concentration))
				{
					if( concentration.length != '0' && concentration != '' && dosage_concentration.length != '0'){
						var dosage = dosage_concentration * concentration;
						var has_comma = dosage.toString().indexOf(".");
						
						if(has_comma != "-1"){
							dosage = dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   	
						}
						
						if (dosage == "0" && !~has_comma) {
							dosage = '';
						}
						
						$('#dosage_' + column_info).val(dosage);
					} else{
						$('#dosage_' + column_info).val('');
					}
				}
				else
				{
					$('#dosage_' + column_info).val('');
				}
			}
			}
		});
		
		$('.concentration_calculation').live('keyup change ', function(){
			var row_info = $(this).data('dosage_row_info');
			var concentration = $(this).val();

			if(isNumeric($(this).val()))
			{
				$('.cdc_'+row_info).each(function(){
					
					var column_info  = $(this).data('dosage_column_info');
					
					concentration = $('#concentration_'+row_info).val();
					concentration = + concentration.replace(",","."); 	
					dosage = $(this).val();
					dosage = + dosage.replace(",","."); 	
					
					if(dosage.length != "0" && dosage !="" && concentration.length != '0' && concentration != '' && concentration != '0')
					{
						
						var dosage_concentration = dosage / concentration;
						
						var has_comma = dosage_concentration.toString().indexOf(".");
						
						if(has_comma != "-1"){
							dosage_concentration = dosage_concentration.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");
						}
										
						
						if (dosage_concentration == "0" && !~has_comma) {
							dosage_concentration = '';
						}
						
						
						$('#dosage_concentration_' + column_info).val(dosage_concentration);	
					} 
					else
					{
						$('#dosage_concentration_' + column_info).val("");	
					}
					
				 });
			}
		});
		
 

		
		
		
		
		
		
		/* ----------------------------------------------------------    */
		
		$('.referral').live('change keyup', function() {
			var medication_type = $(this).attr("rel");
			if ($(this).hasClass('referral')) 
			{
				var input_row = parseInt($(this).attr('title').substr((medication_type+'_').length)); 			
			}
			$('#edited_'+medication_type+'_'+input_row).val('1');
			//console.log($('#edited_'+medication_type+'_'+input_row).val());
		});
		
		/*	ISPC-2110 p.1 */
		$('.selector_medication_unit').live('change keyup', function() { 
			var _val = $("option:selected", this).text();
			$firts_row = $(this).parents('tr').prev('tr.selector_medication_tr_1');
			
			if ($firts_row) {
				$firts_row.find('.selector_dosage_text').each(function(){
					$(this).text(_val);
				});
			}
		});
		$('.selector_medication_dosage_form').live('change keyup', function() {
			var _val = $("option:selected", this).text();
			$firts_row = $(this).parents('tr').prev('tr.selector_medication_tr_1');
			if ($firts_row) {
				$firts_row.find('.selector_concentration_text').each(function(){
					$(this).text(_val);
				});
			}
		});
		$('.selector_medication_edited').live('change keyup', function() {			
			$(this).parents("tr").find("input[type=hidden][id^='edited_']").val(1);
		});
		
		
		
		
		
		
		//on manual edit of the medication name we ignore-reset the pzn, and name this medication as custom
		$ ('.onchange_reset_pzn').live("input propertychange", function (event) {
			var parent_tr = $(event.target).closest("tr");
			$(".medication_pzn, .medication_dbf_id", parent_tr). val("");
			$(".medication_source", parent_tr). val("custom");
		});
		
		$(document).off('change',".sch_referral").on('change',".sch_referral", function(){
			var medication_type = $(this).attr("rel");
			var pump_nr = $(this).data('pumpe_number');
			
			if ($(this).hasClass('sch_referral')) 
			{
				if(pump_nr){
					var input_row = parseInt($(this).attr('title').substr((medication_type+'_'+pump_nr+'_').length)); 			
				}
			}
			$('#edited_'+medication_type+'_'+pump_nr+'_'+input_row).val('1');
		});

		
		/* ----------------------------------------------------------    */
			$(document).off('click',".medication_add_link").on('click',".medication_add_link", function(){
			var medication_type = $(this).attr("rel");
			if(medication_type)
			{
				var medcount = $("#new_line_"+medication_type).val();
				create_new_line(medication_type,medcount);
			}
		});
		

		$(document).off('click',".medication_schm_add_link").on('click',".medication_schm_add_link", function(){
			var medication_type = $(this).attr("rel");
			
			if(medication_type)
			{
				var pumpe_number = $(this).data("pumpe_number");
				var pumpe_type = $(this).data("pumpe_type");
// 				var pumpe_type = $(this).data("pumpe_status");
				
				var medcount = $("#new_sh_line_"+medication_type+"_"+pumpe_type+"_"+pumpe_number).val();
				create_new_sh_line(medication_type,medcount,pumpe_number,pumpe_type);
			}
		});
		
		 
		

		$(".schm_add_block_link").on('click', function(){
			var pumpe_type = "pca";//$(this).val();	
			//console.log(number_of_pumps);
			if(pumpe_type != 0 && number_of_pumps < max_pumpe)
			{
				number_of_pumps++;
				create_new_pumpe(pumpe_type,number_of_pumps);
			}
			
			// reset select
			//$('.schm_add_block_link').prop('selectedIndex',0);
		});
		
		
		
		/* ----------------------------------------------------------    */
		$(document).off('click',".delete_medication").on('click',".delete_medication", function(){
			
			if ( ! checkclientchanged()) {
				return false;
			}			
			
			var trid = $(this).closest('tr').attr('id');
			var cnt = $("#"+trid).attr('alt');
			var drug_id = $(this).attr('rel');
			var pid = idpd; //"<?php echo $_GET['id']; ?>";
// 			console.log(this);
			remove_medication(drug_id,pid,trid);
		});
		
		
		$(document).off('click',".renew_ajx").on('click',".renew_ajx", function(){
			var trid = $(this).closest('tr').attr('id');
			var drug_id = $(this).attr('rel');
			var pid = idpd; //"<?php echo $_GET['id']; ?>";
			renew_medication(drug_id,pid,trid);
		});
		/* ----------------------------------------------------------    */
		
		$('.medication_concentration').live('keyup change ', function(event){
			
			var old_val = $(this).val();
			var new_val = old_val.replace(/[^0-9\.,]/g,'');
			if (old_val != new_val) {
				$(this).val(new_val);
			}

		});
			
		//ISPC-2329 pct.p)
		$('.medication_dosage24_numeric').live('keyup change ', function(event){
			var old_val = $(this).val();
			var new_val = old_val.replace(/[^0-9\.,]/g,'');
			if (old_val != new_val) {
				$(this).val(new_val);
			}
		});
		
		// get hash
		$('table.medication_edit_table').focusin(function(){
			
			var id = ($(this).attr("id"));
			var arr=id.split('_');
			var first=arr.shift(); 
			//var last=arr.pop();
			if (first != undefined && first != "") {
				window.location.hash = first;
			}  
			
		});
		
		
		if ( $('.error_master_div').length ){
			//scroll to errors
			$('html,body').animate({
				scrollTop: $('.error_master_div:eq(0)').offset().top
			});
		
		} else if( $( "#section_" + window.location.hash.substring(1) ).length )  {
			
			//scroll to a table
			
			$('html,body').animate({
				scrollTop: $( "#section_" + window.location.hash.substring(1) ).closest($(".medication_block")).offset().top
			});
		}


		//attach timepicker to dosages
		$("table.medikation .dosage_intervals_holder").each(function(){
			dosage_intervals_timepicker(this);
		});
		
		$("table#not_individual_medication_time .dosage_intervals_holder").each(function(){
			dosage_intervals_timepicker(this);
		});
		
		/* ######################## MEDICATION SETS  ######################## */
		$('.MedicSetButton').live('click', function(e){
			e.preventDefault();
			
			/*var pat = "<?php echo $_REQUEST['id'];?>";*/
			var set_type = $(this).data("med_block");
			var url = appbase+'patientmedication/sets?id='+pat+'&set_type='+set_type;
			xhr = $.ajax({
				url : url,
				success : function(response) {
					$('.medication_sets').html(response);
					$('.medication_sets').dialog('open');
				}
			});
		});

		
		$( ".medication_sets" ).dialog({
			autoOpen: false,
			resizable: false,
			title: translate('2247 - Medication sets '),
			height: 550,
			width: 900,
			modal: true,
			open: function() {
				//		<!-- ISPC-2329 pct.l) Lore 23.08.2019  -->
				if($('#nosets').val() == "1"){
					$(this).dialog( "option", "buttons", 
							[ 
							 
								{
									text: translate('cancel'), 
									"class": 'cancelButtonClass',
									click: function() {
										$( this ).dialog( "close" );
									}
								}
						]
					);	
				} else{
				$(this).dialog( "option", "buttons", 
						[ 
							{   
								text: translate('save'), 
								"class": 'cancelButtonClass disabledButtonClass',
								click: function() {
	    							$( this ).dialog( "close" );
									//ISPC - 2329 punctul j
									var dataArray = $('#add_set_medication').serializeArray();
									
									dataObj = {};
									var line = 9999;
									var medication_type = dataArray[0].value;
									
									$(dataArray).each(function(i, field){										
										
										if(field.name.substring(
												  field.name.lastIndexOf("[") + 1, 
												  field.name.lastIndexOf("]")
												) == 'add_medication')
											  {
										  if(line != 9999)
											  {
										  var medcount = $("#new_line_"+medication_type).val();
										  create_new_line(medication_type,medcount, dataObj);
											  }
										  line = field.name.substring(
												  field.name.indexOf("[") + 1, 
												  field.name.indexOf("]"));
										  dataObj = {};
											  }
									  else
										  {
									 
										  if(line == field.name.substring(
												  field.name.indexOf("[") + 1, 
												  field.name.indexOf("]")))
										 {
											  field_name = field.name.substring(
													  field.name.lastIndexOf("[") + 1, 
													  field.name.lastIndexOf("]")
													);
											 
											  dataObj[field_name] = field.value;
											  
										  
											  }
										  
										  
									  }
									});
									if(!$.isEmptyObject(dataObj))
									{
										 var medcount = $("#new_line_"+medication_type).val();
										 create_new_line(medication_type,medcount, dataObj);
									}
									//ISPC - 2329 punctul j
								}
							},
							{
								text: translate('cancel'), 
								"class": 'cancelButtonClass',
								click: function() {
									$( this ).dialog( "close" );
								}
							}
					]
				);
			}
			},
			close: function() {
				
			}

		});
		
		$('#MedicButton').live('click', function(e){
			e.preventDefault();
			
			/*var pat = "<?php echo $_REQUEST['id'];?>";*/
			//var set_type = $(this).data("med_block");
			$('#MedicButton').attr('disabled','disabled');
			
			var url = appbase+'patientmedication/print?id='+pat;
			xhr = $.ajax({
				url : url,
				type: 'POST',
				data: {bid : $('#bid').val()},
				dataType: 'json',
				success : function(response) {
					//console.log(response);
					$('#bid').val("");
					$(response).each(function(index, obj){
						var medcount = $("#new_line_isbedarfs").val();
						create_new_line('isbedarfs',medcount, obj);						
						//console.log(obj);
					});
					
				}
			});
		});
		
		//re-index dosages inputs
		recall_tabindex();			

	
	//});
	}
	
	
	function recall_tabindex() {
		
		var $cnt = 1;
		$(".medication_edit_table tr").each(function(k){
			
			
			$('.dosage_firstrow_div', this).each(function(i) {
				$cnt ++;
				$(this).find('input').attr('tabindex', $cnt);
			});
			
			$('.dosage_secondrow_div', this).each(function(i) {
				$cnt ++;
				$(this).find('input').attr('tabindex', $cnt);
			});
			
		});
	}
	
	/*------ Changes for ISPC-1848 F --------------------*/
	
	function formattedDate(date) {
		var d = new Date(date || Date.now()),
			month = '' + (d.getMonth() + 1),
			day = '' + d.getDate(),
			year = d.getFullYear();

		if (month.length < 2) month = '0' + month;
		if (day.length < 2) day = '0' + day;

		return [day, month, year].join('.');
	}
	
	function togglebutton(vals)
	{
		if(vals>0 || vals!="")
		{
			$('#MedicButton').removeAttr('disabled','disabled');
		}
		else
		{
			$('#MedicButton').attr('disabled','disabled');
		}
	}
	
	/*2020-06-12 medikation table expand buttons auto-float */
	/*ISPC-2329 Lore 16.06.2020*/
	function mediAutoFloat() {
		if ($( window ).width() > 400 ) {
			$( "table.medikation" ).each(function( index ) {
			if (($( window ).width() - 202) < $(this).width()) {
					
					$( this ).find( ".details.expand_details" ).css({position: "relative", overflow: "visible"});
								
					var leftpadding;
					leftpadding = $(window).width() - $(this).width() - 233 + $( window ).scrollLeft();
					
					if (leftpadding > 0) leftpadding = 0;
					else if (leftpadding < -555) {leftpadding = -555;}
	 
					$( this ).find( ".details.expand_details > button" ).css({
						position: "absolute",
						left: leftpadding,
						height: "100%",
						top: "0",
						display:"block"
						});
				} else {
					$( this ).find( ".details.expand_details > button" ).css({
						position: "static",
						left: "auto",
						height: "100%",
						top: "auto",
						display: "none" 
						});
				}	
			});
		}
	}

	 
	$(window).on("load resize scroll",function(e){
	    mediAutoFloat()
	});	
	/*//.*/
	