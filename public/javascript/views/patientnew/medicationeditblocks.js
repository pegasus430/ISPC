var show_mmi = window.show_mmi;
var new_fields = window.new_fields;
var client_pumpe_autocalculus = window.client_pumpe_autocalculus;		

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
		var url = appbase + 'patientnew/medicationdeletededit?id=' + idpd;
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
				
				$(_this).next("tr").show();
				
				$(_this).addClass('rotate180').addClass('no_bottom_border');
			} else {
				
				$(_this).next("tr").hide();
				
				$(_this).removeClass('rotate180').removeClass('no_bottom_border');
			}
			
		} else {
			$(_this).next("tr").toggle();
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
		$("tr.child_row_toggler", parent_table).each(function(){
			$("td:eq(0)", this).attr("colspan", dosage_count+4);
			
		});
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
		sch_pump_block ='<div class="pumpeblock" id="pumpeblock'+pumpecount+'">';
		
		if(pumpecount == 1){
			sch_pump_block +='<div class="med_block_header">';
		} else {
			sch_pump_block +='<div class="med_block_header inner">';
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
		
		sch_pump_block +='<table id="isschmerzpumpe_pumpeblock'+pumpecount+'" class="datatable medication_edit_table" data-medication_type="isschmerzpumpe"><thead>';
		sch_pump_block +='<tr>';
		sch_pump_block +='<th width="160px" style="min-width:160px">' + translate("medication_name") + ' / ' + translate("medication_drug")+ '</th>';
		if(new_fields == "1"){
 			sch_pump_block +='<th>' + translate("medication_unit") + ' / ' + translate("medication_concentration") + '</th>';
		}
		if(pumpe_type == "pump"){
			sch_pump_block +='<th>' + translate("medication_dosage24h") + '</th>';
		} 
		else
		{
			sch_pump_block +='<th>' + translate("medication_dosage") + '</th>';
		}
		sch_pump_block +='<th>' + translate("medication_indication") + '</th>';
		sch_pump_block +='<th>' + translate("medication_prescribed_by") + ' / ' + translate("medication_change_date") + ' / ' + translate("medication_importance") + '</th>';
		sch_pump_block +='<th></th>';
		sch_pump_block +='</tr>';
		sch_pump_block +='</thead>';

		sch_pump_block +='<tbody>';
		sch_pump_block +='</tbody>';
		sch_pump_block +='</table>';
		
		sch_pump_block +='<input type="hidden" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][id]" value="" />';
		sch_pump_block +='<input type="hidden" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][pumpe_type]" value="'+pumpe_type+'" />';
		sch_pump_block +='<input type="hidden" value="1" id="new_sh_line_isschmerzpumpe_'+pumpe_type+'_'+pumpecount+'">'; 
		
		sch_pump_block +='<a  href="javascript:void(0)" data-pumpe_type="'+pumpe_type+'" data-pumpe_number = "'+pumpecount+'"  data-pumpe_status = "custom" rel="isschmerzpumpe" class="medication_schm_add_link">' + translate("new medication line") + '</a>';

		sch_pump_block +='<table class="datatable_sch">';
		sch_pump_block +='<tr><td colspan="2"><label>' + translate("Kommentar") + '</label></td></tr>';
		sch_pump_block +='<tr><td colspan="2"><textarea class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][description]" ></textarea></td></tr>';
		sch_pump_block +='<tr><td width="16%"><label>' + translate("medication_type") + '</label></td><td><select class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][pumpe_medication_type]"><option value=""> </option><option value="i.v.">i.v.</option><option value="s.c.">s.c.</option></select></td></tr>';
		sch_pump_block +='<tr><td><label>' + translate("Flussrate") + '</label></td><td><input type="text" class="xxl_input extra_calculation" style="max-width:50px;"  data-ec_field="flussrate"   data-medication_type="isschmerzpumpe" data-pumpe_number="'+pumpecount+'" id="flussrate_isschmerzpumpe_'+pumpecount+'" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][flussrate]" value="" /></td></tr>';
		sch_pump_block +='<tr><td width="16%"><label>' + translate("medication_carrier_solution") + '</label></td><td ><input type="text" class="xxl_input extra_calculation"  style="max-width:50px;"   data-medication_type="isschmerzpumpe"  data-ec_field="carriersolution"  data-pumpe_number="'+pumpecount+'" id="carriersolution_isschmerzpumpe_'+pumpecount+'"  name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][carrier_solution]" value="" /><span class="carriersolution_extra_text" id="carriersolution_extra_text_'+pumpecount+'"></span></td></tr>';
		if(pumpe_type =="pca"){
			sch_pump_block +='<tr><td width="16%"><label>' + translate("Bolus") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][bolus]" value="" /></td></tr>';
			sch_pump_block +='<tr><td><label>' + translate("Max Bolus") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][max_bolus]" value="" /></td></tr>';
			sch_pump_block +='<tr><td><label>' + translate("Sperrzeit") + '</label></td><td><input type="text" class="xxl_input" name="medication_block[isschmerzpumpe]['+pumpecount+'][cocktail][sperrzeit]" value=""  /></td></tr>';
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
		var tr_start = '<tr id="tr'+medication_type+pumpe_number+medcount+'">';
		var tr_end = '</tr>';
		
		// DEFINE td 
		var td_start ='<td class="border_bottom_solid">';
		var td_end ='</td>';
		

		// Create MMI button
		if (show_mmi == "1") {
			var mmi_medication_style = 'width:75%!important;';
			var mmi_button_search = '&nbsp;<input type="button" name="mmi_search" id="mmi_search_'+medication_type+'_'+medcount+'_'+pumpe_type+'_'+pumpe_number+'" value="' + translate('mmi_button') + '" class="mmi_search_button" />';
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
		var usrstr = "";
		for(var i in jsusers)
		{
			usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
		}
		var userdrop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][verordnetvon]['+medcount+']" data-medication_type="'+medication_type+'" class="verordnetvon_select sch_referral change_status"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'" rel="'+medication_type+'">'+usrstr+'</select>';
		
		
		// #########################################################
		// Create  Einheit dropdown :: (unit)
		var unit_medication = '<option value="0"></option>';
		if(js_unit){
			for(var uniti in js_unit)
			{
				unit_medication +='<option value="'+uniti+'">'+js_unit[uniti]+'</option>';
			}
		}
		var unit_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][unit]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+unit_medication+'</select>';
		

		// #########################################################
		// Create  Darreichungsform dropdown :: (dosage from)
		
		var dosage_from_medication = '<option value="0"></option>';
		if(js_dosage_form){
			//ISPC-2554 pct.1 Carmen 03.04.2020
			dosage_from_medication +='<optgroup label='+translate("client dosageform list")+'>';
			//--
			for(var dosageformi in js_dosage_form)
			{
				dosage_from_medication +='<option value="'+dosageformi+'">'+js_dosage_form[dosageformi]+'</option>';
			}
		}
		//ISPC-2554 pct.1 Carmen 03.04.2020			
		if(js_dosageform_mmi){			
			dosage_from_medication +='<optgroup label='+translate("mmi dosageform list")+'>';
			for(var dosageformi in js_dosageform_mmi)
			{						
				dosage_from_medication +='<option value="'+js_dosageform_mmi[dosageformi][0]+'">'+js_dosageform_mmi[dosageformi][1]+'</option>';					
			}
		}
		//--
		var dosage_form_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][dosage_form]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_dosage_form sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+dosage_from_medication+'</select>';
		
		
		// #########################################################
		// Create  Indikation dropdown :: (type)
		var indication_medication_str = '<option value="0"></option>';
		
		if(js_indication){
			
			$.each(js_indication, function(id,in_value){
				indication_medication_str +='<option value="'+id+'"  style="background: #'+in_value.color+'" data-indication_color="#'+in_value.color+'" >'+in_value.name+'</option>';
			})
		}
		
		var indication_drop = '<select name="medication_block['+medication_type+']['+pumpe_number+'][indication]['+medcount+']"  data-medication_type="'+medication_type+'" class="indication_color_select sch_referral change_status"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'">'+indication_medication_str+'</select>';
		
		
		// #################################
		// CREATE TABLE ROW - WITH NEW MEDICATION LINE
		// Create - medication  name  		
		var med_name_label ='<label>' + translate("medication_name") + '</label>';
		var med_name_input ='<input type="text" name="medication_block['+medication_type+']['+pumpe_number+'][medication]['+medcount+']" value="" id="medication_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  autocomplete="off"   data-medication_type="'+medication_type+'" class="livesearchmedinp meds_'+medication_type+'_'+pumpe_number+'_line_'+medcount+' sch_referral  change_status xxl_input livesearchmedinp_fixedwidth onchange_reset_pzn"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   rel="'+medication_type+'"/>';
		
		// Create - medication  hidden inputs
		var med_hidd ='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+'][hidd_medication]['+medcount+']" id="hidd_medication_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  value="" />';
		var med_drid ='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+'][drid]['+medcount+']" id="drid_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  value="" />';
		
		// hidden inputs for PZN
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][pzn]" class="medication_pzn" id="'+medication_type+'_medication_pzn-'+pumpe_number+'_'+medcount+'" value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][source]" class="medication_source" id="'+medication_type+'_medication_source-'+pumpe_number+'_'+medcount+'"  value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][dbf_id]" class="medication_dbf_id" id="'+medication_type+'_medication_dbf_id-'+pumpe_number+'_'+medcount+'"  value="" >';
		//ISPC-2554 pct.3 Carmen 26.03.2020
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+pumpe_number+']['+medcount+'][atc]" class="medication_atc" id="'+medication_type+'_medication_atc-'+pumpe_number+'_'+medcount+'"  value="" >';
		//--
		// Create - medication  drug
		var med_drug_label ='<label>' + translate("medication_drug") + '</label>';
		var med_drug_input ='<input type="text" name="medication_block['+medication_type+']['+pumpe_number+'][drug]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="drug_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  class="medication_drug sch_referral change_status onchange_reset_pzn"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"/>';
		
		// Create - medication  unit
		var med_unit ='<label>' + translate("medication_unit") +':</label>'+unit_drop;
 
		// Create - medication  dosage form
		var med_dosage_form ='<label>' + translate("medication_dosageform") + ':</label>'+dosage_form_drop;
		//var med_dosage_form ='';

		// Create - medication  concentration
		var med_concentration_label ='<label>' + translate("medication_concentration") + '</label>';
		var med_concentration_input ='<input style="width:85px" type="text" name="medication_block['+medication_type+']['+pumpe_number+'][concentration]['+medcount+']" value=""  data-ec_field="concentration"    data-medication_type="'+medication_type+'" class="xsmall_input medication_concentration sch_referral change_status extra_calculation"  id="concentration_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-pumpe_med="'+medcount+'"   data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  rel="'+medication_type+'"/>';
 
 
		// Create - medication  dosaje OLD STRUCTURE
		var med_simple_dosage_label ='<label>' + translate("medication_dosage_h") + ':</label>';
		var med_simple_dosage_input ='<input type="text" name="medication_block['+medication_type+']['+pumpe_number+'][dosage]['+medcount+']"  value="" data-ec_field="dosage"   data-medication_type="'+medication_type+'"  class="dosage_input sch_referral change_status calculate_sh_d24h extra_calculation"   data-pumpe_med="'+medcount+'"  data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"  data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"    id="dosage_sh_'+medication_type+'_'+pumpe_number+'_'+medcount+'"  />';
		
		var med_simple_dosage24h_label ='<label>' + translate("medication_dosage24h") + ':</label>';
		var med_simple_dosage24h_input ='<input type="text" name="medication_block['+medication_type+']['+pumpe_number+'][dosage_24h]['+medcount+']"  value="" data-ec_field="dosage_24h"  data-medication_type="'+medication_type+'"  class="dosage_input sch_referral change_status  calculate_sh_d extra_calculation"   data-pumpe_med="'+medcount+'" data-pumpe_number="'+pumpe_number+'" title="'+medication_type+'_'+pumpe_number+'_'+medcount+'"   data-row_info="'+medication_type+'_'+pumpe_number+'_'+medcount+'"    rel="'+medication_type+'"   id="dosage_sh_24h_'+medication_type+'_'+pumpe_number+'_'+medcount+'"    />';
		
		
		// Create - medication  indication
		var med_indication ='<label>' + translate("medication_indication") + ':</label>'+indication_drop;
		
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
		
		//SPECIAL DISPLAY FOR MEDICATION
		var  full_tr = "";
		
		full_tr += tr_start;
		
		// TD :: medication name, mmi button and drug
		full_tr += td_start;
		full_tr += med_name_label+med_name_input;
		full_tr += mmi_button_search ;
		full_tr += med_hidd;
		full_tr += med_drid;
		full_tr += med_drug_label+med_drug_input;
		full_tr += td_end;

		if(new_fields == "1")
		{
		// TD ::medication unit / dosagefrom  / concentration
			full_tr += td_start
				+med_unit
//				+med_dosage_form
				+med_concentration_label
				+med_concentration_input
				+td_end ;
		}
 
// 		if(pumpe_type =="pump"){
			full_tr += td_start;
			full_tr += med_simple_dosage_label+med_simple_dosage_input;
			full_tr += med_simple_dosage24h_label+med_simple_dosage24h_input;
			full_tr += td_end;
// 		} 
// 		else
// 		{
// 			full_tr += td_start+med_simple_dosage_label+med_simple_dosage_input+td_end;
// 		}

		// TD ::medication indications and comment
		full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
		
		// TD ::medication Verordnet von , date and importance
		full_tr +=td_start+med_prescribed_by_label+med_prescribed_by_input+med_date+med_importance_label+med_importance_input+td_end;
		
		full_tr += td_start+med_delete_link+td_end;
		
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
	
		var new_line_count = parseInt(medcount) + 1 ;
		$("#new_sh_line_"+medication_type+"_"+pumpe_type+"_"+pumpe_number+"").val(new_line_count);
		
	
		// increment line 
		medcount++;
	}
	
	
	
	
	
	
	

	function create_new_line(medication_type,medcount)
	{
	
		var timed_bocks_arr = jQuery.parseJSON (timed_bocks);
		
		// DEFINE tr 
		var tr_start = '<tr id="tr'+medication_type+medcount+'" class="selector_medication_tr_1">';
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
			var mmi_button_search = '&nbsp;<input type="button" name="mmi_search" id="mmi_search_'+medication_type+'_'+medcount+'" value="' + translate('mmi_button') + '" class="mmi_search_button" />';
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
		var js_dosage_intervals_length = 0;
		
		if (individual_medication_time == "0") {
			var time_interval_holder = $('input.time_interval_style', $("table#not_individual_medication_time .dosage_intervals_holder") );
		} else {
			var time_interval_holder = $('input.time_interval_style', $("table#" + medication_type + "_med_table .dosage_intervals_holder") );
		}
		
		
		$(time_interval_holder).each(function(i){
			
			var time_val = $(this).val();
			if (time_val == "") {
				time_val = '- : -';
			}
			dosaje_str += td_start;
			dosaje_str += '<div class="dosage_firstrow_div">'
				+ '<label class="dosage_label">'+time_val+'</label>'
				+ '<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']['+time_val+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage_concentration cdc_'+medication_type+'_'+medcount+'"  data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'"   title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"   id="dosage_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"   />'
				+ '<span class="over_the_input selector_dosage_text"></span>'
				+ '</div>';
			
			dosaje_str += '<div class="dosage_secondrow_div">'
				+ '<input type="text" name="medication_block['+medication_type+'][dosage_concentration]['+medcount+']['+time_val+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage" data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"  id="dosage_concentration_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"  />'
				+ '<span class="over_the_input selector_concentration_text"></span>'
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
		var usrstr = "";
		for(var i in jsusers)
		{
			usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
		}
		var userdrop = '<select name="medication_block['+medication_type+'][verordnetvon]['+medcount+']"  data-medication_type="'+medication_type+'" class="verordnetvon_select referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+usrstr+'</select>';
		
		
		// #########################################################
		// Create  Einheit dropdown :: (unit)
		var unit_medication = '<option value="0"></option>';
		if(js_unit){
			for(var uniti in js_unit)
			{
				unit_medication +='<option value="'+uniti+'">'+js_unit[uniti]+'</option>';
			}
		}
		var unit_drop = '<select name="medication_block['+medication_type+'][unit]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit referral change_status small_input selector_medication_unit" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+unit_medication+'</select>';
		

		// #########################################################
		// Create  Darreichungsform dropdown :: (dosage from)
		
		var dosage_from_medication = '<option value="0"></option>';
		if(js_dosage_form){
			//ISPC-2554 pct.1 Carmen 03.04.2020
			if(show_mmi == '1')
			{
				dosage_from_medication +='<optgroup label='+translate("client dosageform list")+'>';
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
				dosage_from_medication +='<optgroup label='+translate("mmi dosageform list")+'>';
				for(var dosageformi in js_dosageform_mmi)
				{						
					dosage_from_medication +='<option value="'+js_dosageform_mmi[dosageformi][0]+'">'+js_dosageform_mmi[dosageformi][1]+'</option>';					
				}
			}
		}
		//--
		var dosage_form_drop = '<select name="medication_block['+medication_type+'][dosage_form]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_dosage_form referral change_status small_input selector_medication_dosage_form" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+dosage_from_medication+'</select>';
		
		
		// #########################################################
		// Create  Applikationsweg dropdown :: (type)
		var type_medication = '<option value="0"></option>';
		
		if(js_type){
			
			for(var typei in js_type)
			{
				type_medication +='<option value="'+typei+'">'+js_type[typei]+'</option>';
			}
		}
		var type_drop = '<select name="medication_block['+medication_type+'][type]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+type_medication+'</select>';
		

		
		// #########################################################
		// Create  ESKALATION dropdown :: (escalation)
		var escalation_medication = '<option value="0"></option>';
		
		if(js_escalation){
			
			for(var escalationi in js_escalation)
			{
				escalation_medication +='<option value="'+escalationi+'">'+js_escalation[escalationi]+'</option>';
			}
		}
		var escalation_drop = '<select name="medication_block['+medication_type+'][escalation]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_escalation referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+escalation_medication+'</select>';
				
		
		
		
		
		// #########################################################
		// Create  Indikation dropdown :: (type)
		var indication_medication_str = '<option value="0"></option>';
		
		if(js_indication){
			
			$.each(js_indication, function(id,in_value){
				indication_medication_str +='<option value="'+id+'"  style="background: #'+in_value.color+'" data-indication_color="#'+in_value.color+'" >'+in_value.name+'</option>';
			})
		}
		
		var indication_drop = '<select name="medication_block['+medication_type+'][indication]['+medcount+']"  data-medication_type="'+medication_type+'" class="indication_color_select referral  change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'">'+indication_medication_str+'</select>';
		
		
		// #################################
		// CREATE TABLE ROW - WITH NEW MEDICATION LINE
		// Create - medication  name  		
		var med_name_label ='<label>' + translate("medication_name") + '</label>';
		var med_name_input ='<input type="text" name="medication_block['+medication_type+'][medication]['+medcount+']" value="" id="medication_'+medication_type+'_'+medcount+'"  autocomplete="off"   data-medication_type="'+medication_type+'" class="livesearchmedinp meds_'+medication_type+'_line_'+medcount+' referral  change_status livesearchmedinp_fixedwidth onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  hidden inputs
		var med_hidd ='<input name="medication_block['+medication_type+'][hidd_medication]['+medcount+']" value="" id="hidd_medication_'+medication_type+'_'+medcount+'" type="hidden">';
		var med_drid ='<input type="hidden" id="drid_'+medication_type+'_['+medcount+']" name="medication_block['+medication_type+'][drid]['+medcount+']" value="" />';
		
		//hidden for PZN
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][pzn]" class="medication_pzn" id="'+medication_type+'_medication_pzn-'+medcount+'" value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][source]" class="medication_source" id="'+medication_type+'_medication_source-'+medcount+'"  value="" >';
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][dbf_id]" class="medication_dbf_id" id="'+medication_type+'_medication_dbf_id-'+medcount+'"  value="" >';
		//ISPC-2554 pct.3 Carmen 26.03.2020	
		med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][atc]" class="medication_atc" id="'+medication_type+'_medication_atc-'+medcount+'"  value="" >';
		//--	
		// Create - medication  drug
		var med_drug_label ='<label>' + translate("medication_drug") + '</label>';
		var med_drug_input ='<input type="text" name="medication_block['+medication_type+'][drug]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="drug_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  unit
		var med_unit ='<label>' + translate("medication_unit") + ':</label>'+unit_drop;
		
		// Create - medication  type
		var med_type ='<label>' + translate("medication_type") + ':</label>'+type_drop;
		
		// Create - medication  escalation
		var med_escalation ='<label>' + translate("medication_escalation") + ':</label>'+escalation_drop;
		
		
		// Create - medication  dosage form
		var med_dosage_form ='<label>' + translate("medication_dosageform") + ':</label>'+dosage_form_drop;
		//var med_dosage_form ='';

		// Create - medication  concentration
		var med_concentration_label ='<label>' + translate("medication_concentration") + '</label>';
		var med_concentration_input ='<input style="width:85px" type="text" name="medication_block['+medication_type+'][concentration]['+medcount+']" value=""   data-medication_type="'+medication_type+'" class="small_input medication_concentration referral  change_status concentration_calculation" data-dosage_row_info="'+medication_type+'_'+medcount+'"  title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"     id="concentration_'+medication_type+'_'+medcount+'"   />';
		
		
		
		
		// Create - medication  dosaje NEW STRUCTURE
		var med_dosage_tds = dosaje_str;
		
		// Create - medication  dosaje OLD STRUCTURE
		var med_simple_dosage_label ='<label>' + translate("medication_dosage") + ':</label>';
		var med_simple_dosage_input ='<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']"  value="" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>';
		var med_simple_dosage_lysocare = '';
		if(module_lysocare){
			 med_simple_dosage_lysocare = '<label>' + translate("Dosage according to the product information");
			 med_simple_dosage_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][dosage_product]['+medcount+']"  value="yes" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Ja</label>';
			 med_simple_dosage_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][dosage_product]['+medcount+']"  value="no" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Nein</label>';
			 med_simple_dosage_lysocare += '</label>';
		}
		
		// Create - medication  indication
		var med_indication ='<label>' + translate("medication_indication") + ':</label>'+indication_drop;
		
		var med_indication_label = '<label>' + translate("medication_indication") + ':</label>';
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
			var med_comment_input ='<input name="medication_block['+medication_type+'][comments]['+medcount+']"  data-medication_type="'+medication_type+'" class="referral  change_status xxl_input" title="'+medication_type+'_'+medcount+'" rows="" cols=""  rel="'+medication_type+'" />';
		}
		else
		{
			var med_comment_label ='<label>' + translate("medication_comments") + ':</label>';
			var med_comment_input ='<textarea name="medication_block['+medication_type+'][comments]['+medcount+']"  data-medication_type="'+medication_type+'" class="med_com_textarea referral  change_status" title="'+medication_type+'_'+medcount+'" rows="" cols=""  rel="'+medication_type+'" ></textarea>';
		}
		
		// Create - medication  prescribed by -
		var med_prescribed_by_label ='<label>' + translate("medication_prescribed_by") + ':</label>';
		var med_prescribed_by_input =userdrop;
		
		
		// Create - medication  date - not needed
		var med_date ='';
		
		// Create - medication  importance / sort
		var med_importance_label ='<label>' + translate("medication_importance") + ':</label>';
		var med_importance_input ='<input type="text" name="medication_block['+medication_type+'][importance]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_importance referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';

		// Create -delete 
		var med_delete_link ='<a class="delete_new_row" href="javascript:void(0)" onClick="remove_new_line(\'#tr'+medication_type+medcount+'\')"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ><img width="13px" alt="delete" src="' + res_path + '/images/action_delete.png"></a>';
		
		// Create - medication days interval
		var med_days_interval_label ='<label>' + translate("medication_days_interval") + ':</label>';
		var med_days_interval_input ='<input type="text" name="medication_block['+medication_type+'][days_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_days_interval referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';
		var med_days_interval_technical_lysocare = '';
		if(module_lysocare){
			med_days_interval_technical_lysocare = '<label>' + translate("Interval according to technical information");
			med_days_interval_technical_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][days_interval_technical]['+medcount+']"  value="yes" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Ja</label>';
			med_days_interval_technical_lysocare += '<label><input type="radio" name="medication_block['+medication_type+'][days_interval_technical]['+medcount+']"  value="no" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>Nein</label>';
			med_days_interval_technical_lysocare += '</label>';
		}
		
		// Create - medication date in put
		var med_administration_date_label ='<label>' + translate("medication_administration_date") + ':</label>';
		var med_administration_date_input ='<input type="text" name="medication_block['+medication_type+'][administration_date]['+medcount+']"  id="adminisration_date_'+medcount+'"  data-medication_type="'+medication_type+'"  class="small_input medication_adminisration_date referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';
		
		// Create - interval question div
		var med_has_interval_q = '<div class="interval_question"><input type="checkbox" value="1"  name="medication_block['+medication_type+'][has_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  data-medcount="'+medcount+'"  id="has_interval_'+medication_type+'_'+medcount+'"  /><label>' + translate("is interval medi?") + '</label></div>'
		var med_has_interval_start = '<div class="interval_block" style="display: none;" id="int_set_'+medication_type+'_'+medcount+'"   >'
		var med_has_interval_end = '</div>'
			
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
		var med_kcal_input ='<input type="text" name="medication_block['+medication_type+'][kcal]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="kcal_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		// Create - medication  volume
		var med_volume_label ='<label>' + translate("medication_volume") + '</label>';
		var med_volume_input ='<input type="text" name="medication_block['+medication_type+'][volume]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="volume_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
		
		//SPECIAL DISPLAY FOR MEDICATION
		
		if(medication_type == "treatment_care")
		{
			var  full_tr = "";
			full_tr += tr_start;
			// TD :: medication name
			full_tr += td_start+med_name_input+med_hidd+med_drid+td_end;
			
			// medication comment
			full_tr += td_start+med_comment_input+td_end;
			
			// medication  	Verordnet von
			full_tr += td_start+med_prescribed_by_input+td_end;
			
			// medication  date
			full_tr += td_start+td_end;
			// medication  importance
			full_tr += td_start+med_importance_input+td_end;
			
			full_tr += td_start+med_delete_link+td_end;
			full_tr += tr_end;
		} 
		else if(medication_type == "scheduled")
		{
			var  full_tr = "";
			full_tr += tr_start;
			// TD :: medication name
			full_tr += td_start;
			full_tr += med_name_label+med_name_input;
			full_tr += mmi_button_search;
			full_tr += med_hidd+med_drid;
			full_tr += med_drug_label+med_drug_input;
			
			full_tr += td_end;
			
			
			// TD :: medication dosage
			full_tr += td_start+med_simple_dosage_label+med_simple_dosage_input + med_simple_dosage_lysocare + td_end;
			
			
			// TD ::medication indications and comment
			full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
			

			// medication interval days
			full_tr += td_start+med_days_interval_label+med_days_interval_input + med_days_interval_technical_lysocare + td_end;
			
			// medication  administration date
			full_tr += td_start+med_administration_date_label+med_administration_date_input+td_end;

			
			// TD ::medication Verordnet von , date and importance
			full_tr +=td_start+med_prescribed_by_label+med_prescribed_by_input+med_date+med_importance_label+med_importance_input+td_end;
			
			
			full_tr += td_start+med_delete_link+td_end;
			full_tr += tr_end;
		} 
		else
		{
			
			
			
			
			
			var  full_tr = "";
			
			full_tr += tr_start;
			
			// TD :: medication name, mmi button and drug
			if(medication_type != "isnutrition"){
				full_tr += td_start;
				full_tr += med_name_label+med_name_input;
				full_tr += mmi_button_search;
				full_tr += med_hidd;
				full_tr += med_drid;
				full_tr += med_drug_label+med_drug_input;
				 if (medication_type == "isintubated") {
					 full_tr += med_packaging_label+med_packaging_drop;
				 }
				full_tr += td_end;
			} else {
				full_tr += td_start;
				full_tr += med_name_label+med_name_input;
				full_tr += med_hidd+med_drid;
				full_tr += med_drug_label+med_drug_input;
				full_tr += td_end;
			}
			
			if(new_fields == "1")
			{
				// TD ::medication unit and type
				//full_tr += td_start+"XXXX"+med_unit+med_type+td_end
				
				// TD ::medication dosage form and concentration
				//full_tr += td_start+med_dosage_form+med_concentration_label+med_concentration_input+td_end
				
				
				// TD ::concentration and pro and type
				//child_row
//				full_tr += '<td class="border_bottom_solid" style="border-right:none">'+med_concentration_label+med_concentration_input+ pro_label +med_type+ '</td>';
				
				// TD ::unit and dosage
				//child_row
//				full_tr += '<td class="border_bottom_solid" style="border-left:none">'+med_unit+med_dosage_form+ '</td>';
				
			}
		
			if( !~$.inArray( medication_type, timed_bocks_arr ) ){
				//reset colspan medication not in timed 
				js_dosage_intervals_length = 1;
			} 
			
			
			var child_row_toggler = '<!-- child row toggler  -->' 
			+ '<tr id="tr' + medication_type + medcount + '_child_toggle" onclick="medi_child_toggle(this);" class="child_row child_row_toggler selector_medication_tr_2" >'
			+ '<td class="medi_child_toggle_td border_bottom_solid medi_child_toggle_td_arrow" colspan="' + Number(4 + js_dosage_intervals_length) +'" >';
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
			+ '<tr  id="tr' + medication_type + medcount + '_child"  style="display:none;"  class="child_row child_row_holder selector_medication_tr_3">'
			+ '<td class="border_bottom_solid" colspan="'+Number(1 + js_dosage_intervals_length) +'">'

			if(new_fields == "1"){
				child_row +=''
				+ '<div class="div_float_left_extra">' + med_concentration_label + med_concentration_input + "</div>"
				+ '<div class="div_float_left_extra">' + med_unit + "</div>"
				+ '<div style="float:left; padding-top:20px">' + pro_label + "</div>"
				+ '<div class="div_float_left_extra">' + med_dosage_form  + "</div>"
				+ '<div class="div_float_left_extra">' + med_type + "</div>";
				
//				if (medication_type == 'isbedarfs' || medication_type == 'iscrisis') {
//				child_row +=''
//					+ '<div class="div_float_left_extra">' + med_escalation + "</div>";
//				}
				
				
			} 
			child_row += '</td>';

			if(medication_type == "actual" && allow_normal_scheduled == "1") {
				
				//child_row
//				full_tr +=td_start+med_has_interval_q+med_has_interval_start+med_days_interval_label+med_days_interval_input+med_administration_date_label+med_administration_date_input+med_has_interval_end+td_end;
				child_row +=  '<td class="border_bottom_solid" colspan="2">'
					+ med_has_interval_q
					+ med_has_interval_start
					+ med_days_interval_label
					+ med_days_interval_input
					+ med_administration_date_label
					+ med_administration_date_input
					+ med_has_interval_end
					+ '</td>'
					+ '<td class="border_bottom_solid">&nbsp;</td>';
			} else {
				child_row +=  '<td class="border_bottom_solid" colspan="3">';
				
				if (medication_type == 'isbedarfs' || medication_type == 'iscrisis') {
					child_row +=''
						+ '<div class="div_float_left_extra">' + med_escalation + "</div>";
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
        		+ '<label>' + translate("Interval for dosage") + '</label>'
        		+ '<input type="text" name="medication_block['+medication_type+']['+medcount+'][dosage_interval]"  data-medication_type="'+medication_type+'"  class="dosage_interval selector_medication_edited" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />'
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
				 full_tr += med_kcal_label+med_kcal_input;
				 full_tr += med_volume_label+med_volume_input;
				 full_tr +=  med_comment_label+med_comment_input;
				 full_tr += td_end;
			 } else {
				 full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
			 }
			
			
			// TD ::medication Verordnet von , date and importance
			full_tr +=td_start+med_prescribed_by_label+med_prescribed_by_input+med_date+med_importance_label+med_importance_input+td_end;
			
			if(medication_type == "actual" && allow_normal_scheduled == "1") {
				
				//child_row
//				full_tr +=td_start+med_has_interval_q+med_has_interval_start+med_days_interval_label+med_days_interval_input+med_administration_date_label+med_administration_date_input+med_has_interval_end+td_end;
			}
			
			full_tr += td_start+med_delete_link+td_end;
			
			
			full_tr +=tr_end;
			
			full_tr += child_row_toggler + child_row ;
			
		}
 
		$('#'+medication_type+'_med_table').append(full_tr);
		
		
		$('#adminisration_date_'+medcount).datepicker({
			dateFormat: 'dd.mm.yy',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: ''
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
				//--
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
				//--
				//TODO-3365 Carmen 21.08.2020
				if(js_pharmaindex_settings.takinghint == 'yes')
				{
			    /*//ISPC-2554 Carmen 16.06.2020
			    if(module_takinghint == '1')
   	   	     	{*/	
			    	$('#comments_'+medication_type+'_'+row).val(takinghint);
			    //}
			    //--
				}
				else
				{
					$('#comments_'+medication_type+'_'+row).val('');
				}
				//--
			  //ISPC 2554 Carmen 11.05.2020
			    var child_tr = parent_tr.next().next();
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
				//--
	    	   	
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
				//--
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
		
		
		if(client_pumpe_autocalculus && new_fields == "1") {
			
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
							dosage = dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
						}
						var has_comma_2h = dosage_24.toString().indexOf(".");
						
						if(has_comma_2h != "-1"){
							dosage_24 = dosage_24.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
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
										dosage = dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");				
									}
									var has_comma_2h = dosage_24.toString().indexOf(".");
									
									if(has_comma_2h != "-1"){
										dosage_24 = dosage_24.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");   					
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
						dosage24h = dosage24h.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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
						dosage24h = dosage24h.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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

						input_dosage = input_dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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

						input_dosage = input_dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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
						dosage24h = dosage24h.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_24h_'+medication_type+"_"+pumpe_number+"_"+med_line).val(dosage24h).removeClass("flash_red");	
						$(this).removeClass("flash_red");
					}
					else if( isNumeric(input_dosage) ){
						var dosage24h = input_dosage * 24;
						dosage24h = dosage24h.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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
						input_dosage = input_dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
						$('#dosage_sh_'+medication_type+"_"+pumpe_number+"_"+med_line).val(input_dosage).removeClass("flash_red");	
						$(this).removeClass("flash_red");
					} 
					else if (isNumeric(input_dosage_24h)){
						
						input_dosage = input_dosage.toFixed(3).toString().replace(".",",").replace(/0+$/,'').replace(/,$/, "");  
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
		
		
		
		
		if (show_mmi == "1") {
			//used in mmi dialog
			var active_recipe_row = null;
			$(document).off('click',".mmi_search_button").on('click', '.mmi_search_button', function(){
				//var receipt_field = $(this).prev().prev();
				var receipt_field = $(this);
				active_recipe_row = receipt_field;
			});
		}

		// change indication select background 
  		$('.indication_color_select').live('change', function() {
	  		var that = $(this).find('option:selected'); 
  			$(this).attr("style",that.attr("style"));
  			
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
						//pzn = 0;
						dbf_id = PRODUCT.ID;
						source = arguments[2]['source'];
						
						//ISPC-2329 Ancuta 03.04.2020
						//We now take the PZN for the FIRST item in PACKAGE_LIST
						var PACKAGE_ITEM_0 = arguments[2]['PRODUCT']['PACKAGE_LIST'][0];
						pzn = PACKAGE_ITEM_0.PZN;
						//dbf_id = PACKAGE_ITEM_0.ID; // ?? 
						
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
	 					$('.medication_atc', parent_tr).val(atcstring); //ISPC-2554 pct.3 Carmen 26.03.2020
	 				}
	 				else
	 				{
	 					$('.medication_atc', parent_tr).val(''); //ISPC-2554 pct.3 Carmen 26.03.2020
	 				}
	 				//--
	 				//TODO-3365 Carmen 21.08.2020
					if(js_pharmaindex_settings.takinghint == 'yes')
					{
	    	   	     /*//ISPC-2554 Carmen 16.06.2020
	    	   	     if (module_takinghint == '1') {*/
	    	   	    	parent_tr.find('.med_com_textarea').map(function(){
	    	   	    		$(this).val(takinghint);
	    	   	    	});
	    	   	     //}
	    	   	     //--
	    	   	     }
					else
					{
						parent_tr.find('.med_com_textarea').map(function(){
	    	   	    		$(this).val('');
	    	   	    	});
					}
	    	   	     //--
	    	   	     //ISPC-2554 Carmen 11.05.2020
		    	   	    var dosmatch =  false;
		    	   	    var unitmatch = false;
		    	   	    var child_tr = parent_tr.next().next();
		    	   	//TODO-3365 21.08.2020
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
						//--

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
				    	   	//--
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
					//--
					
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
					//--
				 
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
			onSelect: function(){
				var medication_type = $(this).attr("rel");
				if ($(this).hasClass('referral')) 
				{
					var input_row = parseInt($(this).attr('title').substr((medication_type+'_').length)); 			
				}
				$('#edited_'+medication_type+'_'+input_row).val('1');
				
			}
		});
		$(".date_input").mask("99.99.9999");

		$('.medication_adminisration_date').datepicker({
			dateFormat: 'dd.mm.yy',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(){
				var medication_type = $(this).attr("rel");
				if ($(this).hasClass('referral')) 
				{
					var input_row = parseInt($(this).attr('title').substr((medication_type+'_').length)); 			
				}
				$('#edited_'+medication_type+'_'+input_row).val('1');
			}
		});
		$(".medication_adminisration_date").mask("99.99.9999");


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
			$firts_row = $(this).parents('tr').prev('tr').prev('tr.selector_medication_tr_1');
			if ($firts_row) {
				$firts_row.find('.selector_dosage_text').each(function(){
					$(this).text(_val);
				});
			}
		});
		$('.selector_medication_dosage_form').live('change keyup', function() {
			var _val = $("option:selected", this).text().substr(0,3);
			$firts_row = $(this).parents('tr').prev('tr').prev('tr.selector_medication_tr_1');
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
		$("table.datatable .dosage_intervals_holder").each(function(){
			dosage_intervals_timepicker(this);
		});
		
		$("table#not_individual_medication_time .dosage_intervals_holder").each(function(){
			dosage_intervals_timepicker(this);
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