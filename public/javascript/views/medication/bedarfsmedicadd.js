$(document).ready(function() { /*------ Start $(document).ready --------------------*/	
	//ADD NEW MEDICAMENT LINE
	$(document).off('click',".medication_add_link").on('click',".medication_add_link", function(){
		var medcount = $("#new_line").val();
		create_new_line('isbedarfs',medcount);
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
					//pzn = 0; //ISPC-2329 Ancuta 03.04.2020
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
			
			//ISPC-2554 pct.3 Carmen 06.04.2020
			var atcstring = '';
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
			//--
							
            var parent_tr;
            if (parent_tr = $(active_recipe_row).closest("tr")) {
            	 $('.medication_pzn', parent_tr).val(pzn.toString().escapeValue());
    	   	     $('.medication_source', parent_tr).val(source.toString().escapeValue());
    	   	     $('.medication_dbf_id', parent_tr).val(dbf_id.toString().escapeValue());
    	   	     $('.medication_atc', parent_tr).val(atcstring); //ISPC-2554 pct.3 Carmen 06.04.2020
            }
   	     
			
			
			
				if(drug){
					//ISPC-2554 Carmen 05.08.2020
					//$('#drug_'+input_row).val(drug);
					drugdrug = drugdrugarr.join(', ');
					
					$('#drug_'+input_row).val(drugdrug);
					//--
				}else{
					$('#drug_'+input_row).val('');
				}
				
				$('#medication_'+input_row).val(recipe);
				$('#hidd_medication_'+input_row).val('');
				$('#edited_'+input_row).val('1');
			
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
	
		if(new_med == 1)
		{
			$("#new_line").val('1');
			var medcount = $("#new_line").val();
			create_new_line('isbedarfs',medcount);
		}
		
		$("#frmuser").submit(function(event){
			setTimeout(function () {$('input[type=submit]').attr('disabled', true);}, 150);
			setTimeout(function () {$('input[type=submit]').attr('disabled', false);}, 8000);
			
			if ( ! validateForm_edit()) {
				event.preventDefault();
				event.stopPropagation(); //stop going forward
				event.stopImmediatePropagation(); //stop going forward
				
				return false;
			}
			
			return true;

		});
	
		$(document).on('click', ':input', function(){
			
			if($(this).css('background-color') === 'rgb(243, 187, 191)')
			{
				$(this).css('background-color', '#f2f2f2');
			}
		});
		
});/*-- END  $(document).ready ----------- --*/

function create_new_line(medication_type,medcount)
{	
	// DEFINE tr 
	var tr_start = '<tr id="tr'+medication_type+medcount+'">';
	var tr_end = '</tr>';
	
	// DEFINE td 
	var td_start ='<td>';
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
	/*var dosaje_str = "";
	var js_dosage_intervals_length = 0;
	
	var time_interval_holder = $('input.time_interval_style', $("table#not_individual_medication_time .dosage_intervals_holder") );
	$(time_interval_holder).each(function(i){
		
		var time_val = $(this).val();
		if (time_val == "") {
			time_val = '- : -';
		}
		
		dosaje_str += td_start+'<div class="dosage_firstrow_div"><label class="dosage_label">'+time_val+'</label>';
		dosaje_str += '<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']['+time_val+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage_concentration cdc_'+medication_type+'_'+medcount+'"  data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'"   title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"   id="dosage_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"   /></div>';
		dosaje_str += '<div class="dosage_secondrow_div"><input type="text" name="medication_block['+medication_type+'][dosage_concentration]['+medcount+']['+time_val+']"  value="" data-medication_type="'+medication_type+'"  class="small_dosage_inptuts dosage_input referral change_status calculate_dosage" data-dosage_row_info="'+medication_type+'_'+medcount+'" data-dosage_column_info="'+medication_type+'_'+medcount+'_'+ time_val.replace(":","")+'" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"  id="dosage_concentration_'+medication_type+'_'+medcount+'_'+time_val.replace(":","")+'"  /></div>';
		dosaje_str += td_end;
		
		js_dosage_intervals_length ++;
	
	});
	
	if (js_dosage_intervals_length == 0 ){
		js_dosage_intervals_length = 1;
	}*/
	
	// #########################################################
	/* Create  Verordnet von  dropdown :: (prescribed by)
	var usrstr = "";
	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}
	var userdrop = '<select name="medication_block['+medication_type+'][verordnetvon]['+medcount+']"  data-medication_type="'+medication_type+'" class="verordnetvon_select referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+usrstr+'</select>';*/
	
	
	
	// #########################################################
	// Create  Einheit dropdown :: (unit)
	var unit_medication = '<option value="0"></option>';
	if(js_unit){
		for(var uniti in js_unit)
		{
			unit_medication +='<option value="'+uniti+'">'+js_unit[uniti]+'</option>';
		}
	}
	var unit_drop = '<select name="medication_block['+medication_type+'][unit]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_unit referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+unit_medication+'</select>';
	

	// #########################################################
	// Create  Darreichungsform dropdown :: (dosage from)
	
	var dosage_from_medication = '<option value="0"></option>';
	if(js_dosage_form){
		//ISPC-2554 pct.1 Carmen 06.04.2020
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
	//ISPC-2554 pct.1 Carmen 06.04.2020	
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
	var dosage_form_drop = '<select name="medication_block['+medication_type+'][dosage_form]['+medcount+']"  data-medication_type="'+medication_type+'" class="medication_dosage_form referral change_status small_input" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'">'+dosage_from_medication+'</select>';
	
	
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
	//ISPC-2554 pct.3 Carmen 06.04.2020	
	med_hidd +='<input type="hidden" name="medication_block['+medication_type+']['+medcount+'][atc]" class="medication_atc" id="'+medication_type+'_medication_atc-'+medcount+'"  value="" >';
	//--	
	// Create - medication  drug
	var med_drug_label ='<label>' + translate("medication_drug") + '</label>';
	var med_drug_input ='<input type="text" name="medication_block['+medication_type+'][drug]['+medcount+']" value=""   data-medication_type="'+medication_type+'" id="drug_'+medication_type+'_'+medcount+'"  class="medication_drug referral  change_status onchange_reset_pzn" title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"/>';
	
	// Create - medication  unit
	var med_unit ='<label>' + translate("medication_unit") + ':</label>'+unit_drop;
	
	// Create - medication  type
	var med_type ='<label>' + translate("medication_type") + ':</label>'+type_drop;
	
	
	// Create - medication  dosage form
	var med_dosage_form ='<label>' + translate("medication_dosageform") + ':</label>'+dosage_form_drop;
	//var med_dosage_form ='';

	// Create - medication  concentration
	var med_concentration_label ='<label>' + translate("medication_concentration") + '</label>';
	var med_concentration_input ='<input style="width:85px" type="text" name="medication_block['+medication_type+'][concentration]['+medcount+']" value=""   data-medication_type="'+medication_type+'" class="small_input medication_concentration referral  change_status concentration_calculation" data-dosage_row_info="'+medication_type+'_'+medcount+'"  title="'+medication_type+'_'+medcount+'" rel="'+medication_type+'"     id="concentration_'+medication_type+'_'+medcount+'"   />';
	
	
	
	
	// Create - medication  dosaje NEW STRUCTURE
	//var med_dosage_tds = dosaje_str;
	
	// Create - medication  dosaje OLD STRUCTURE
	var med_simple_dosage_label ='<label>' + translate("medication_dosage") + ':</label>';
	var med_simple_dosage_input ='<input type="text" name="medication_block['+medication_type+'][dosage]['+medcount+']"  value="" data-medication_type="'+medication_type+'"  class="dosage_input referral change_status" title="'+medication_type+'_'+medcount+'"  rel="'+medication_type+'"/>';
	
	
	// Create - medication  indication
	var med_indication ='<label>' + translate("medication_indication") + ':</label>'+indication_drop;
	
	var med_indication_label = '<label>' + translate("medication_indication") + ':</label>';
	var med_indication_input = indication_drop;
	
	
	
	// Create - medication  COMMENT - 
	var med_comment_label ='<label>' + translate("medication_comments") + ':</label>';
	var med_comment_input ='<textarea name="medication_block['+medication_type+'][comments]['+medcount+']"  data-medication_type="'+medication_type+'" class="med_com_textarea referral  change_status" title="'+medication_type+'_'+medcount+'" rows="" cols=""  rel="'+medication_type+'" ></textarea>';
	
	/* Create - medication  prescribed by -
	var med_prescribed_by_label ='<label>' + translate("medication_prescribed_by") + ':</label>';
	var med_prescribed_by_input =userdrop;
	
	// Create - medication  importance / sort
	var med_importance_label ='<label>' + translate("medication_importance") + ':</label>';
	var med_importance_input ='<input type="text" name="medication_block['+medication_type+'][importance]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_importance referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';*/

	// Create -delete 
	var med_delete_link ='<a class="delete_new_row" href="javascript:void(0)" onClick="remove_new_line(\'#tr'+medication_type+medcount+'\')"  data-medication_type="'+medication_type+'" rel="'+medcount+'" ><img width="13px" alt="delete" src="' + res_path + '/images/action_delete.png"></a>';
	
	// Create - medication days interval
	var med_days_interval_label ='<label>' + translate("medication_days_interval") + ':</label>';
	var med_days_interval_input ='<input type="text" name="medication_block['+medication_type+'][days_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  class="small_input medication_days_interval referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';

	
	// Create - medication date in put
	var med_administration_date_label ='<label>' + translate("medication_administration_date") + ':</label>';
	var med_administration_date_input ='<input type="text" name="medication_block['+medication_type+'][administration_date]['+medcount+']"  id="adminisration_date_'+medcount+'"  data-medication_type="'+medication_type+'"  class="small_input medication_adminisration_date referral change_status" title="'+medication_type+'_'+medcount+'"  value=""  rel="'+medication_type+'" />';
	
	// Create - interval question div
	var med_has_interval_q = '<div class="interval_question"><input type="checkbox" value="1"  name="medication_block['+medication_type+'][has_interval]['+medcount+']"  data-medication_type="'+medication_type+'"  data-medcount="'+medcount+'"  id="has_interval_'+medication_type+'_'+medcount+'"  /><label>' + translate("is interval medi?") + '</label></div>'
	var med_has_interval_start = '<div class="interval_block" style="display: none;" id="int_set_'+medication_type+'_'+medcount+'"   >'
	var med_has_interval_end = '</div>'
		
	var pro_label = '<label class="pro_label">' + translate('pro') + ' : </label>';
	
	
	//SPECIAL DISPLAY FOR MEDICATION
	var  full_tr = "";
	
	full_tr += tr_start;
	
	// TD :: medication name, mmi button and drug
	full_tr += td_start;
	full_tr += med_name_label+med_name_input;
	full_tr += mmi_button_search;
	full_tr += med_hidd;
	full_tr += med_drid;
	full_tr += med_drug_label+med_drug_input;
	
	full_tr += td_end;
	 
	js_dosage_intervals_length = 1;
	
	var child_row_toggler = '<!-- child row toggler  -->' 
	+ '<tr id="tr' + medication_type + medcount + '_child_toggle" onclick="medi_child_toggle(this);" class="child_row child_row_toggler" >'
	+ '<td class="medi_child_toggle_td border_bottom_solid medi_child_toggle_td_arrow" colspan="' + Number(3 + js_dosage_intervals_length) +'" >';
	child_row_toggler += ''
		+ translate('medication_type') + ','
		+ translate('medication_dosageform') + ','
		+ translate('medication_unit') + ','
		+ '		etc';
	child_row_toggler += ''	
		+ '</td>'
		+ '</tr>';
	
	var child_row = '<!-- child row -->' 
	+ '<tr  id="tr' + medication_type + medcount + '_child"  style="display:none;"  class="child_row child_row_holder">'
	+ '<td class="border_bottom_solid" colspan="'+Number(3 + js_dosage_intervals_length) +'">'

	child_row +=''
		+ '<div class="div_float_left_extra">' + med_concentration_label + med_concentration_input + "</div>"
		+ '<div class="div_float_left_extra">' + med_unit + "</div>"
		+ '<div style="float:left; padding-top:20px">' + pro_label + "</div>"
		+ '<div class="div_float_left_extra">' + med_dosage_form  + "</div>"
		+ '<div class="div_float_left_extra">' + med_type + "</div>";
	child_row += '</td>';	
	child_row +=  '<td class="border_bottom_solid" colspan="3"></td>';
	child_row += '</tr>';
	
	full_tr += td_start+med_simple_dosage_label+med_simple_dosage_input+td_end;

	// TD ::medication indications and comment
	full_tr += td_start+med_indication+med_comment_label+med_comment_input+td_end;
	
	// TD ::medication Verordnet von
	//full_tr +=td_start+med_prescribed_by_label+med_prescribed_by_input+med_importance_label+med_importance_input+td_end;
	
	full_tr += td_start+med_delete_link+td_end;
	
	
	full_tr +=tr_end;
	
	full_tr += child_row_toggler + child_row ;
		

	$('#'+medication_type+'_med_table').append(full_tr);
	
	$('#medication_'+medication_type+'_'+medcount).bind('keyup keydown change paste',function(){
		
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
	$("#new_line").val(new_line_count);
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

function remove_new_line(ids) {
	$(ids).remove();
	$(ids + "_child_toggle").remove();
	$(ids + "_child").remove();
}

function reset_medications(medication_type,input_row)
{
		if($('#hidd_medication_'+medication_type+'_'+input_row).val()){
			
			$('#hidd_medication_'+medication_type+'_'+input_row).val('');
			$('#drid_'+medication_type+'_'+input_row).val('');
		}
}

function delMedications(divid,did)
{

	if(did)
	{
		ajaxCallserver({
			url:'medication/bedarfsmedicationedit?delid='+did
		});
	}
	$(divid).remove();
	$(divid+'_child_toggle').remove();
	$(divid+'_child').remove();
}

function selectMedications(mid, row, mmi_handler)   
{
	if($.isNumeric(row))
	{
	}		
	else
	{
		var details = row.split("_");
		
		var row = details[2];
		var medication_type = details[1];
		
		
				
		var table_search_results = $("#medinosis_drop_table");
		var pzn = $('#medi_PZN_'+mid , table_search_results).val() || 0;
		var source = $('#medi_TYPE_'+mid , table_search_results).val() || "custom";
		var dbf_id = $('#medi_DBF_ID_'+mid , table_search_results).val() || "";
		//ISPC-2554 pct.3 Carmen 06.04.2020
		var atc = $('#medi_ATC_'+mid , table_search_results).val() || "";
		//--
		
		$('#medication_'+medication_type+'_'+row).val($('#medi_me_'+mid).val());
		$('#drug_'+medication_type+'_'+row).val($('#medi_wirkstoffe_'+mid).val());
		if(!mmi_handler)
		{
			$('#comments_'+medication_type+'_'+row).val($('#medi_comment_'+mid).val());
			$('#hidd_medication_'+medication_type+'_'+row).val($('#medi_id_'+mid).val());
		}
		var parent_tr = $("#tr"+medication_type+""+row);
		$('input.medication_pzn' , parent_tr).val(pzn);
		$('input.medication_source' , parent_tr).val(source);
		$('input.medication_dbf_id' , parent_tr).val(dbf_id);			     
		$('input.medication_atc' , parent_tr).val(atc); //ISPC-2554	
	}
}

//ISPC-2430 Carmen 28.11.2019
function validateForm_edit() {
		var medication_submit = true;
		
		var all_sch_rows = false;
		
		var sch_extra = true;
		
		var medication_type = 'isbedarfs';
		
		//ISPC-2430	Carmen 26.11.2019
		$( "input[name*='[medication]']").each(function(){
			if($(this).css('background-color') === 'rgb(243, 187, 191)')
			{
				$(this).css('background-color', '#f2f2f2');
			}
		});
		
		$( "input[name*='[medication]']").each(function(i){
			var empty_row = true;				
			if($(this).val() == '')
			{
				$(this).closest('tr').find(':input:not([type="hidden"])').not(this).each(function(){
					
					if(($(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $(this).val() != '' && $(this).val() != '0' && $(this).val() != 'MMI') || ($(this).attr('type') == 'radio' && $(this).is(':checked'))  || ($(this).attr('type') == 'checkbox' && $(this).is(':checked')))
					{
						empty_row = false;
						return false;
					}
				});

				$(this).closest('tr').next().next().find(':input:not([type="hidden"])').each(function(){
					
					if(($(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $(this).val() != '' && $(this).val() != '0' && $(this).val() != 'MMI') || ($(this).attr('type') == 'radio' && $(this).is(':checked'))  || ($(this).attr('type') == 'checkbox' && $(this).is(':checked')))
					{
						empty_row = false;
						return false;
					}
				});
				
				if(!empty_row)
				{
					medication_submit = false;
					$(this).css('background-color', 'rgb(243, 187, 191)');
					setTimeout(function () {alert( translate("name should be filled for line " + (i+1)) );}, 50);
				}
			}
		});
				
	return medication_submit;
	
}
