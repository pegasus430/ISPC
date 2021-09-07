//ISPC-2247
var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	//ISPC-2430 Carmen 26.11.2019
	var medication_submit = true;

	var medication_type = $('#med_type').val();
	
	
	if($('#title').val() == '')
	{
		var htmldiv = '<span style="color: red; margin-left: 10px;">'+translate('Title needed!')+'</span>';
		$('.titlediv').append(htmldiv);
		$(window).scrollTop(0);		
		return false;
	}
	
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
			$(this).closest('div').parent().parent().find(':input:not([type="hidden"])').not(this).each(function(){

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


$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	if (show_mmi == "1") {
		//used in mmi dialog
		var active_recipe_row = null;
		$(document).off('click',".mmi_search_button").on('click', '.mmi_search_button', function(){
			//var receipt_field = $(this).prev().prev();
			var receipt_field = $(this);
			active_recipe_row = receipt_field;
		});
	}

		// DEFINE MEDICATION LIVESEARCH - WITH MMI OR NOT
		if (show_mmi == "1") {
		//INSTALL MEDIINDEX-WIDGET
		var pi = new pharmaindex();
		pi.input_medname = ".med";
		pi.input_rowparent = "div";
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
			
			var input_row = active_recipe_row.data('row');
			
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
					//pzn = 0;//ISPC-2329 Ancuta 03.04.2020
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
			var pharmformcode = '';
			var unit = '';
			var takinghint = '';//ISPC-2554 Carmen 16.06.2020
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
			
		    $('#set-'+input_row+'-pzn').val(pzn.toString().escapeValue());
			
			$('#set-'+input_row+'-source').val(source.toString().escapeValue());
			$('#set-'+input_row+'-dbf_id').val(dbf_id.toString().escapeValue());
			//TODO-3365 Carmen 21.08.2020
		    if(js_pharmaindex_settings.atc == 'yes')
			{
		    	$('#set-'+input_row+'-atc').val(atcstring); //ISPC-2554 pct.3 Carmen 06.04.2020
			}
		    else
		    {
		    	$('#set-'+input_row+'-atc').val(''); //ISPC-2554 pct.3 Carmen 06.04.2020
		    }
		    //--
			
			//ISPC 2554 Carmen 13.05.2020
		    var dosmatch =  false;
		    var unitmatch = false;
		    
		    $('input[name ="set['+input_row+'][frequency][]"]').removeAttr('checked');
		    $('input[name ="set['+input_row+'][dosage][]"]').val('');
		    $('input[name ="set['+input_row+'][med_dosage_form][]"]').removeAttr('checked');
		    $('input[name ="set['+input_row+'][med_dosage_form_mmi][]"]').removeAttr('checked');
		    $('input[name ="set['+input_row+'][med_dosage_form_custom]"]').removeAttr('checked');
		    $('input[name ="set['+input_row+'][med_dosage_form_custom_text]"]').val('');
		    
		    //TODO-3365 Carmen 21.08.2020
		    if(js_pharmaindex_settings.dosage_form == 'yes')
			{
			   	$.each( js_clientdosageform, function( key, value ) {
			   	  if(value.mmi_code == pharmformcode && pharmformcode != '')
			   	  {
			   		$('#set-'+input_row+'-med_dosage_form_mmi-'+value.id).attr('checked','checked');
			   		dosmatch = true;
			   	  }
			   	});
			   	if(dosmatch === false && pharmformcode != '')
	    	   	{
			   		$('#set-'+input_row+'-med_dosage_form_mmi-'+'mmi_'+pharmformcode).attr('checked','checked');
			   	}	
			}
		    //--
		   	
		    //TODO-3365 Carmen 21.08.2020
		    if(js_pharmaindex_settings.unit == 'yes')
			{
			   	$.each( js_clientunit, function( key, value ) {
			   		
			  	   	  if(value.unit.toLowerCase() == unit.toLowerCase())
			  	   	  {
			  	   		$('#set-'+input_row+'-unit').val(value.id); //ISPC-2554
			  	   		unitmatch = true;
			  	   	  }
			  	   	});
			  	   	if(unitmatch === false && unit != '')
			  	   	{
			  	   		$('#set-'+input_row+'-unit').val('mmi_'+unit.toLowerCase());
			  	   	}
			  	   	else if(unitmatch === false && unit == '')
			  	   	{
			  	   		$('#set-'+input_row+'-unit').val('');
			  	   	}
				//--
			}
		    //--
			
		    //TODO-3365 Carmen 21.08.2020
		    if(js_pharmaindex_settings.drug == 'yes')
			{
				if(drug){
					//$('#set-'+input_row+'-drug').val(drug);
					//ISPC-2554 Carmen 06.08.2020
					drugdrug = drugdrugarr.join(', ');
					
					$('#set-'+input_row+'-drug').val(drugdrug);
					//--
				}else{
					$('#set-'+input_row+'-drug').val('');
				}
			}
		    else
		    {
		    	$('#set-'+input_row+'-drug').val('');
		    }
		    //--
			$('#set-'+input_row+'-medication').val(recipe);
			$('#set-'+input_row+'-hidd_medication').val('');
			//TODO-3365 Carmen 21.08.2020
		    if(js_pharmaindex_settings.takinghint == 'yes')
			{
			//ISPC-2554 Carmen 16.06.2020
		    /*if(module_takinghint == '1')
	   	    {*/
		    	$('#set-'+input_row+'-comments').val(takinghint);
	   	    //}
		    //--
			}
		    else
		    {
		    	$('#set-'+input_row+'-comments').val('');
		    }
		    //--
			//$('#edited_'+input_row).val('1');
			
		};

		pi.install();
		
		$('.livesearchmedinp').live('change', function() {
			var receipt_field = $(this);
			active_recipe_row = receipt_field;
			//var input_row = $(this).data('row');
			var input_row = active_recipe_row.data('row');
			reset_medications(input_row);
			
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
			$('.livesearchmedinp').live('change', function() {
			var receipt_field = $(this);
			active_recipe_row = receipt_field;
			//var input_row = $(this).data('row');
			var input_row = active_recipe_row.data('row');
			reset_medications(input_row);
	
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
	

	// ADD NEW SET ROW
	$(document).on('click', '.addbutton', function(){
		var setcount = $("#setcount").val();
		var med_freq = JSON.parse(medication_frequency);
		var med_dos = JSON.parse(medication_dosage);
		var med_typ = JSON.parse(medication_types);
		var med_dos_mmi = JSON.parse(medication_dosageform_mmi); //ISPC-2554 pct.1 Carmen 06.04.2020
		//console.log($.isEmptyObject(med_dos_mmi));
		
		//ISPC-2247 pct.1 Lore 06.05.2020
		var medi_type = $('#med_type').val();
		var time_scheme = JSON.parse(sets_time_scheme); 
		var scheme = time_scheme[medi_type];
		//console.log(scheme);
		//.
		
		var htmldiv = '<div class="rowdiv"  style="width: 100%; border: 1px solid #000; float: left;">';
			htmldiv += '<div  style="width: 15%; float: left; border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
				htmldiv += '<div style="width: 100%;">'+translate('label_drug')+'</div>';
				htmldiv += '<div style="width: 80%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][drug]" id="set-'+setcount+'-drug" value="" class="form_drug" style="width: 95%;" type="text">';			
				htmldiv += '</div>';
				if (show_mmi == "1") {
					htmldiv += '<div style="width: 25%;">';
						htmldiv += '<input name="set['+setcount+'][add_mmi]" id="set-'+setcount+'-add_mmi" value="MMI" class="mmi_search_button" data-row="'+setcount+'" readonly="readonly" style="width: 90%;" type="text">';
					htmldiv += '</div>';
				}
				htmldiv += '<div style="clear: both;"></div>';
				htmldiv += '<div style="width: 100%;">'+translate('label_medication')+'</div>';
				htmldiv += '<div style="width: 100%;">';
					htmldiv += '<input name="set['+setcount+'][hidd_medication]" value="" readonly="readonly" id="set-'+setcount+'-hidd_medication" type="hidden">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 100%;">';
					htmldiv += '<input name="set['+setcount+'][pzn]" value="" readonly="readonly" id="set-'+setcount+'-pzn" type="hidden">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 100%;">';
					htmldiv += '<input name="set['+setcount+'][source]" value="" readonly="readonly" id="set-'+setcount+'-source" type="hidden">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 100%;">';
					htmldiv += '<input name="set['+setcount+'][dbf_id]" value="" readonly="readonly" id="set-'+setcount+'-dbf_id" type="hidden">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 100%;">';
				htmldiv += '<input name="set['+setcount+'][atc]" value="" readonly="readonly" id="set-'+setcount+'-atc" type="hidden">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 100%;">';
				htmldiv += '<input name="set['+setcount+'][unit]" value="" readonly="readonly" id="set-'+setcount+'-unit" type="hidden">';
				htmldiv += '</div>';
				/*htmldiv += '<div style="width: 100%;">';
					htmldiv += '<input name="set['+setcount+'][comment]" value="" readonly="readonly" id="set-'+setcount+'-comment" type="hidden">';
				htmldiv += '</div>';*/
				htmldiv += '<div style="width: 80%;">';
					htmldiv += '<input name="set['+setcount+'][medication]" id="set-'+setcount+'-medication" value=""  class="livesearchmedinp med" data-row="'+setcount+'0" style="width: 95%;" autocomplete="off" type="text">';
				htmldiv += '</div>';			
			htmldiv += '</div>';
			htmldiv += '<div style="width: 10%; float: left; border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
				htmldiv += '<div style="width: 100%; float: left; padding-left: 5px; padding-top: 5px;" id="dosage'+setcount+'">';
					htmldiv += '<div style="width: 100%;">'+translate('label_dosage')+'</div>';
					htmldiv += '<div style="width: 100%; float: left;">';
						htmldiv += '<input name="dosagecount'+setcount+'" value="1" readonly="readonly" id="new_dosage'+setcount+'" type="hidden">';
					htmldiv += '</div>';
					
					//ISPC-2247 pct.1 Lore 06.05.2020
					if( !$.isEmptyObject(scheme))
					{
						for(var x in scheme ){
							htmldiv += '<div style="width: 100%; color: green;">'+scheme[x]+'</div>';
							htmldiv += '<div style="width: 100%; float: left;">';
							htmldiv += '<input name="set['+setcount+'][dosage]['+x+']" id="set-'+setcount+'-dosage-'+x+'" value="" class="form_dosage" style="width: 85%;" type="text">';
							htmldiv += '</div>';
						}
					} else {
							htmldiv += '<div style="width: 100%; float: left;">';
							htmldiv += '<input name="set['+setcount+'][dosage][0]" id="set-'+setcount+'-dosage-0" value="" class="form_dosage" style="width: 85%;" type="text">';
							htmldiv += '</div>';	
					}

					
			htmldiv += '</div>';
				
				//ISPC-2247 pct.1 Lore 06.05.2020
				if( $.isEmptyObject(scheme))
				{
					htmldiv += '<div style="width: 100%; float: left;">';
						htmldiv += '<img src="' + appbase + 'images/btttt_plus.png" class="add_dosage" data-row="'+setcount+'">';
					htmldiv += '</div>';
				}
/*				htmldiv += '<div style="width: 100%; float: left;">';
				htmldiv += '<img src="' + appbase + 'images/btttt_plus.png" class="add_dosage" data-row="'+setcount+'">';
				htmldiv += '</div>';*/
			
			htmldiv += '</div>';
			htmldiv += '<div style="width: 14%; float: left; border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
				htmldiv += '<div style="width: 100%;">'+translate('label_frequency')+'</div>';
				htmldiv += '<div style="width: 100%;">';
					for(var freq_i in med_freq)
					{
						htmldiv += '<label style="width: 100%; display: block;">';
							htmldiv += '<input name="set['+setcount+'][frequency][]" id="set-'+setcount+'-frequency-'+freq_i+'" value="'+freq_i+'" type="checkbox">'+med_freq[freq_i];
						htmldiv += '</label>';	
						
					}
					
				htmldiv += '</div>';
				
				//Darreichungsform
				htmldiv += '<div style="width: 14%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][frequency_custom]" value="'+setcount+'" type="hidden"><input name="set['+setcount+'][frequency_custom]" id="set-'+setcount+'-frequency_custom" value="1" type="checkbox">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 80%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][frequency_custom_text]" id="set-'+setcount+'-frequency_custom_text" value="" style="width: 95%;" type="text">';
				htmldiv += '</div>';
				htmldiv += '<div style="clear: both;"></div>';
			htmldiv += '</div>';
			htmldiv += '<div style="width: 30%; float: left; border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
			htmldiv += '<div style="width: 100%;">'+translate('label_med_dosage_form')+'</div>';
			htmldiv += '<div style="width: 100%;">';
			//ISPC-2554 pct.1 Carmen 06.04.2020
			if(show_mmi == '1' && !$.isEmptyObject(med_dos_mmi))
			{
				htmldiv += '<div style="width: 100%; display: block; font-weight: bold;">'+translate("client dosageform list");
				htmldiv += '</div>';
			}
			//--
				for(var dos_f_i in med_dos)
				{
					htmldiv += '<label style="width: 100%; display: block;">';
						htmldiv += '<input name="set['+setcount+'][med_dosage_form][]" id="set-'+setcount+'-med_dosage_form-'+dos_f_i+'" value="'+dos_f_i+'" type="checkbox">'+med_dos[dos_f_i];
					htmldiv += '</label>';	
					
				}
				//ISPC-2554 pct.1 Carmen 06.04.2020
				if(show_mmi == '1' && !$.isEmptyObject(med_dos_mmi))
				{
					htmldiv += '<div style="width: 100%; display: block; font-weight: bold;">'+translate("mmi dosageform list");
					htmldiv += '</div>';	
					for(var dos_f_mmi_i in med_dos_mmi)
					{
						htmldiv += '<label style="width: 100%; display: block;">';
							htmldiv += '<input name="set['+setcount+'][med_dosage_form_mmi][]" id="set-'+setcount+'-med_dosage_form_mmi-'+med_dos_mmi[dos_f_mmi_i][0]+'" value="'+med_dos_mmi[dos_f_mmi_i][0]+'" type="checkbox">'+med_dos_mmi[dos_f_mmi_i][1];
						htmldiv += '</label>';	
						
					}
				}
				//--	
				htmldiv += '</div>';
				htmldiv += '<div style="width: 15%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][med_dosage_form_custom]" value="'+setcount+'" type="hidden"><input name="set['+setcount+'][med_dosage_form_custom]" id="set-'+setcount+'-med_dosage_form_custom" value="1" type="checkbox">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 80%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][med_dosage_form_custom_text]" id="set-'+setcount+'-med_dosage_form_custom_text" value="" style="width: 95%;" type="text">';
				htmldiv += '</div>';
				htmldiv += '<div style="clear: both;"></div>';
			htmldiv += '</div>';

			//Applikationsweg
			htmldiv += '<div style="width: 12%; float: left; border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
			htmldiv += '<div style="width: 100%;">'+translate('label_medication_type')+'</div>';
			htmldiv += '<div style="width: 100%;">';
				for(var mtype_i in med_typ)
				{
					htmldiv += '<label style="width: 100%; display: block;">';
						htmldiv += '<input name="set['+setcount+'][type][]" id="set-'+setcount+'-type-'+mtype_i+'" value="'+mtype_i+'" type="checkbox">'+med_typ[mtype_i];
					htmldiv += '</label>';	
					
				}
				htmldiv += '</div>';
				htmldiv += '<div style="width: 15%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][type_custom]" value="'+setcount+'" type="hidden"><input name="set['+setcount+'][type_custom]" id="set-'+setcount+'-type_custom" value="1" type="checkbox">';
				htmldiv += '</div>';
				htmldiv += '<div style="width: 80%; float: left;">';
					htmldiv += '<input name="set['+setcount+'][type_custom_text]" id="set-'+setcount+'-type_custom_text" value="" style="width: 95%;" type="text">';
				htmldiv += '</div>';
				htmldiv += '<div style="clear: both;"></div>';
			htmldiv += '</div>';
			
			//Kommentare
			htmldiv += '<div style="width: 10%; float: left;  border-right: 1px solid #000; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
				htmldiv += '<div style="width: 100%;">'+translate('label_comments')+'</div>';
				htmldiv += '<div style="width: 100%;">';
				htmldiv += '<textarea name="set['+setcount+'][comments]" id="set-'+setcount+'-comments" rows="'+textarearows+'" style="width: 90%;" cols=""></textarea>';
				htmldiv += '</div>';
			htmldiv += '</div>';		
			
			htmldiv += '<div style="width: 3%; float: left; height: '+newsetheight+'px; padding-left: 5px; padding-top: 5px;">';
				htmldiv += '<div style="width: 100%; float: left;">';
					htmldiv += '<img src="' + appbase + 'images/btttt_minus.png" class="delrow" data-row="'+setcount+'">';
				htmldiv += '</div>';
			htmldiv += '</div>';
		htmldiv += '</div>';
		htmldiv += '<div style="clear: both;"></div>';
		$('#set').append(htmldiv);
		
		$('#set-'+setcount+'-medication').bind('keyup keydown change paste',function(){
			
			if (show_mmi == "1") {
			
				$(this).live('change', function() {
					var receipt_field = $(this);
					active_recipe_row = receipt_field;
					//var input_row = $(this).data('row');
					var input_row = active_recipe_row.data('row');
					reset_medications(input_row);
					
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
					var receipt_field = $(this);
					active_recipe_row = receipt_field;
					//var input_row = $(this).data('row');
					var input_row = active_recipe_row.data('row');
					reset_medications(input_row);
			
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
		
		$("#setcount").val(parseInt(setcount)+1);	
		
			
			
			
	});
	
	//ADD NEW DOSAGE LINE 
	
	$(document).on("click",".add_dosage", function(){
		
	//$(".add_dosage").live("click", function() {
		var row = $(this).data("row");
		var doscount = $("#new_dosage"+row).val();
		
		if(parseInt(textarearows)-(parseInt(doscount)+1) <= 4)
		{			
			newsetheight = parseInt(newsetheight)+25;
			$(this).parent().parent().css({"height": newsetheight + "px"});
			$(this).parent().parent().siblings().css({"height": newsetheight + "px"});
		}
		var htmldiv = '<div style="width: 100%; float: left;"><input type="text" name="set['+row+'][dosage]['+doscount+']" value="" style="width: 85%;" class="form_dosage" /></div>';
		$('#dosage'+row).append(htmldiv);
		$("#new_dosage"+row).val(parseInt(doscount)+1);
	});

	
	// DELETE ROW IN SET
	$(document).on("click",".delrow", function(){
		//console.log($(this));
		$(this).closest('.rowdiv').css( "background", "yellow" ).remove();
		$("#setcount").val($("#setcount").val()-1);
		
	});
	
	$(document).on("change",".frequency_custom, .med_dosage_form_custom, .type_custom", function(){		
			var row = $(this).data("row");
			if(!$(this).is(":checked"))
			{
				$($('#'+$(this).attr('id')+'_text')).val('');
				$(this).siblings().val($(this).val());
				$(this).val('');
			}
	
		});
	
	$(document).on('click', ':input', function(){
		if($(this).css('background-color') === 'rgb(243, 187, 191)')
		{
			$(this).css('background-color', '#f2f2f2');
		}
	});
	
});/*-- END  $(document).ready ----------- --*/

function reset_medications(input_row)
{
		if($('#set-'+input_row+'-hidd_medication').val()){
			
				$('#set-'+input_row+'-hidd_medication').val('');
		}
}

function selectMedications(mid, row, mmi_handler)   
{
	if($.isNumeric(row))
	{
	}		
	else
	{
		var details = row.split("-");
		
		var mrow = details[1];
				
		var table_search_results = $("#medinosis_drop_table");
		var pzn = $('#medi_PZN_'+mid , table_search_results).val() || 0;
		var source = $('#medi_TYPE_'+mid , table_search_results).val() || "custom";
		var dbf_id = $('#medi_DBF_ID_'+mid , table_search_results).val() || "";
		//ISPC-2554 pct.3 Carmen 06.04.2020
		var atc = $('#medi_ATC_'+mid , table_search_results).val() || "";
		//--
		
		var pharmformcode = $('#medi_DOSAGEFORMID_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 13.05.2020
		var unit = $('#medi_UNIT_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 13.05.2020
		var takinghint = $('#medi_TAKINGHINT_'+mid , table_search_results).val() || ""; //ISPC 2554 Carmen 16.06.2020
		
		$('#'+row).val($('#medi_me_'+mid).val());
		//TODO-3365 Carmen 21.08.2020
	    if(js_pharmaindex_settings.drug == 'yes')
		{
	    	$('#set-'+mrow+'-drug').val($('#medi_wirkstoffe_'+mid).val());
		}
	    else
	    {
	    	$('#set-'+mrow+'-drug').val('');
	    }
	    //--
	  //TODO-3365 Carmen 21.08.2020
	    if(js_pharmaindex_settings.takinghint == 'yes')
		{
		//ISPC 2554 Carmen 16.06.2020
		/*if(module_takinghint == '1')
  	    {*/
			$('#set-'+mrow+'-comments').val(takinghint);
  	    //}
		//--
		}
	    else
	    {
	    	$('#set-'+mrow+'-comments').val('');
	    }
	    //--
		if(!mmi_handler)
		{
			$('#set-'+mrow+'-comments').val($('#medi_comment_'+mid).val());
			$('#set-'+mrow+'-hidd_medication').val($('#medi_id_'+mid).val());
		}
		
	    $('#set-'+mrow+'-pzn').val(pzn);
		
		$('#set-'+mrow+'-source').val(source);
		$('#set-'+mrow+'-dbf_id').val(dbf_id);	
		//TODO-3365 Carmen 21.08.2020
	    if(js_pharmaindex_settings.atc == 'yes')
		{
	    	$('#set-'+mrow+'-atc').val(atc); //ISPC-2554
		}
	    else
	    {
	    	$('#set-'+mrow+'-atc').val(''); //ISPC-2554
	    }
	    //--
		
		//ISPC 2554 Carmen 13.05.2020
	    var dosmatch =  false;
	    var unitmatch = false;
	    
	    $('input[name ="set['+mrow+'][frequency][]"]').removeAttr('checked');
	    $('input[name ="set['+mrow+'][dosage][]"]').val('');
	    $('input[name ="set['+mrow+'][med_dosage_form][]"]').removeAttr('checked');
	    $('input[name ="set['+mrow+'][med_dosage_form_mmi][]"]').removeAttr('checked');
	    $('input[name ="set['+mrow+'][med_dosage_form_custom]"]').removeAttr('checked');
	    $('input[name ="set['+mrow+'][med_dosage_form_custom_text]"]').val('');
	    
	  //TODO-3365 Carmen 21.08.2020
	    if(js_pharmaindex_settings.dosage_form == 'yes')
		{
		   	$.each( js_clientdosageform, function( key, value ) {
		   	  if(value.mmi_code == pharmformcode && pharmformcode != '')
		   	  {
		   		$('#set-'+mrow+'-med_dosage_form_mmi-'+value.id).attr('checked','checked');
		   		dosmatch = true;
		   	  }
		   	});
		   	if(dosmatch === false && pharmformcode != '')
		   	{
		   		$('#set-'+mrow+'-med_dosage_form_mmi-'+'mmi_'+pharmformcode).attr('checked','checked');
		   	}
		}
	    //--
	    //TODO-3365 Carmen 21.08.2020
	    if(js_pharmaindex_settings.unit == 'yes')
		{
		   	$.each( js_clientunit, function( key, value ) {
		   		
	  	   	  if(value.unit.toLowerCase() == unit.toLowerCase())
	  	   	  {
	  	   		$('#set-'+mrow+'-unit').val(value.id); //ISPC-2554
	  	   		unitmatch = true;
	  	   	  }
	  	   	});
	  	   	if(unitmatch === false && unit != '')
	  	   	{
	  	   		$('#set-'+mrow+'-unit').val('mmi_'+unit.toLowerCase());
	  	   	}
	  	   	else if(unitmatch === false && unit == '')
	  	   	{
	  	   		$('#set-'+mrow+'-unit').val('');
	  	   	}
			//--
		}
	    //--
						    
	}
}