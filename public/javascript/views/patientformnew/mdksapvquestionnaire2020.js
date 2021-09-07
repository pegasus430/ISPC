////ISPC-2765,Elena,26.01.2021
$(document).ready(function(){
	$( ".datepick" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		nextText: '',
		prevText: ''
	});
	
	$("input[name=living_will_yes]").live('click', function(){
		if($("input[name=living_will_no]").is(":checked"))
		{
			$("input[name=living_will_no]").attr('checked', false);
		}
	});
	
	$("input[name=living_will_no]").live('click', function(){
		if($("input[name=living_will_yes]").is(":checked"))
		{
			$("input[name=living_will_yes]").attr('checked', false);
		}
	});
	
	$( ".ausscheidung" ).live("click", function(){
    	var index = $(this).attr('rel');
    	//alert(index);
    	$(this).val(index);
    });

	/*------------Family doctor-------------------

	$('#familydoc_option').live('click', function(){
		if($(this).val() == '0' ){
			var url = '<?php echo APP_BASE; ?>patient/familydocedit?id=<?php echo $this->enc_patid ?>&mod=modal';
			var dialog = $("#housarztDialog");
			if ($("#housarztDialog").length == 0) {
				dialog = $('<div id="housarztDialog" style="display:hidden"></div>').appendTo('body');
			}
			// load remote content
			dialog.load(
			url,
			{},
			function(responseText, textStatus, XMLHttpRequest) {
				dialog.dialog({
					width: 600,
					height: 360,
					modal:true,
					resizable: false,
					title: "Hausarzt bearbeiten",
					open: function(){
						if($("#familydoc_option").is(":checked")){
							$("#familydoc_option").removeAttr('checked');
						}

						//familydoc ls
						$('#familydoc_id').live('keydown', function() {
							$('#hidd_docid').val('');
						}).liveSearch({
							url: 'ajax/sfamilydoctor?q=',
							id: 'livesearch_stamdatten_familydoc',
							noResultsDelay: '1200',
							typeDelay: '1200'
						});
						$('#submit_btn').attr('alt', 'housarztDialog');
					},
					close: function(){
						$('#housarztDialog').remove();
					}
				});
			}
		);

		} else {
		}
	});

	/*------------ Pflegedienst -------------------

	$('#pflegedienst_option').live('click', function(){
		if($(this).val() == '0' ){
					var url = 'patient/pflegedienste?id=<?php echo $this->enc_patid ?>&mod=modal&pflid='+$('#pflidhidden').val();
					var dialog = $("#pflegeDialog");
					if ($("#pflegeDialog").length == 0) {
						dialog = $('<div id="pflegeDialog" style="display:hidden"></div>').appendTo('body');
					}
					// load remo$('.ui-dialog').remove();te content
					dialog.load(
					url,
					{},
					function(responseText, textStatus, XMLHttpRequest) {
						dialog.dialog({
							width: 600,
							height: 545,
							modal:true,
							resizable: false,

							title: "Pflegedienst",
							open: function(){
								if($("#pflegedienst_option").is(":checked")){
									$("#pflegedienst_option").removeAttr('checked');
								}
								//pflegedienste ls
								$('#pflegedienste').live('keydown', function() {
									$('#hidd_pflegeid').val('');
								}).liveSearch({
									url: 'ajax/pflegedienste?q=',
									id: 'livesearch_stammdaten_pflege',
									aditionalWidth: '120',
									noResultsDelay: '1200',
									typeDelay: '1200'
								});
								$('#submit_btn_pfl').attr('alt','pflegeDialog');
							},
							close: function(){
								$('#pflegeDialog').remove();
							}
						});
					});
		} else {

		}
	});

	/*------------ Hos[izdienst] -------------------

	$('#hospizdienst_option').live('click', function(){
		if($(this).val() == '0' ){
			var url = 'patient/hospiceassociation?id=<?php echo $this->enc_patid ?>&mod=modal';
			var dialog = $("#hospizdienstDialog");
			if ($("#hospizdienstDialog").length == 0) {
				dialog = $('<div id="hospizdienstDialog" style="display:hidden"></div>').appendTo('body');
			}

			// load remote content
			dialog.load(
			url,
			{},
			function(responseText, textStatus, XMLHttpRequest) {
				dialog.dialog({
					width: 600,
					height: 525,
					modal:true,
					resizable: false,

					title: "ambulanter Hospizdienst",
					open: function(){
						if($("#hospizdienst_option").is(":checked")){
							$("#hospizdienst_option").removeAttr('checked');
						}

						//pflegedienste ls
						$('#h_association').live('keydown', function() {
							$('#hidd_h_association_id').val('');
						}).liveSearch({
							url: 'ajax/hospiceassociations?q=',
							id: 'livesearch_stammdaten_hassoc',
							aditionalWidth: '120',
							noResultsDelay: '1200',
							typeDelay: '1200'
						});
						$('#submit_btn_hassoc').attr('alt','hospizdienstDialog');
					},
					close: function(){
						$('#hospizdienstDialog').remove();
					}
				});
			});

		} else {
		}
	});


	/*---------------------  Contact person      ----------------------------------------*/

	$("#contactperson_dialog").dialog({
		autoOpen: false,
		width: 600,
		height: 445,
		modal:true,
		resizable: false,
		title: "Private Pflegeperson",
		open: function(){
			$('#submit_contact').attr('alt','contactperson_dialog');
		}
	});

	/*$('#contactperson_option').click(function(){
		if($(this).val() == '0' ){
			if($(this).is(":checked")){
				$(this).removeAttr('checked');
			}

			$( "#contactperson_dialog" ).dialog('open');

		}
	});*/

	/*-------------------------------*/
	if($('#operativ_ja:checked').val() == 1){
	    $('.operativ_date').show();
	}
	$("input[name=operativ]").bind('click',function() {
	    if($(this).val() == 1){
			$('.operativ_date').show();
	    } else {
			$('#operativ_date').val("");
			$('.operativ_date').hide();
	    }
	});

	if($('#chemo_ja:checked').val() == 1){
	    $('.chemo_date').show();
	}
	$("input[name=chemo]").bind('click',function() {
	    if($(this).val() == 1){
			$('.chemo_date').show();
	    } else {
			$('#chemo_date').val("");
			$('.chemo_date').hide();
	    }
	});

	if($('#radiatio_ja:checked').val() == 1){
	    $('.radiatio_date').show();
	}
	$("input[name=radiatio]").bind('click',function() {
	    if($(this).val() == 1){
			$('.radiatio_date').show();
	    } else {
			$('#radiatio_date').val("");
			$('.radiatio_date').hide();
	    }
	});
	/*-------------------------------*/

	if($('#wound_treatment:checked').val() == 1){
	    $('.wound_treatment_description').show();
	}

	if($("#wound_treatment").is(":checked")){
		$(".wound_treatment_description").show("slow");
	} else {
		$('#wound_treatment_description').val("");
		$('.wound_treatment_description').hide();
	}


	$('#wound_treatment').live('click', function(){
		if($("#wound_treatment").is(":checked")){
			$(".wound_treatment_description").show("slow");
		} else {
			$('#wound_treatment_description').val("");
			$('.wound_treatment_description').hide();
		}
	});
	
	/*$('#med_fest').live('click', function(){
		if($("#med_fest").is(":checked")){
			//alert('test');
			$("#med_fest_text").show("slow");
		} else {
			$('#med_fest_text').hide("slow");
		}
	});
	
	$('#med_bedarf').live('click', function(){
		if($("#med_bedarf").is(":checked")){
			$("#med_bedarf_text").show("slow");
		} else {
			$('#med_bedarf_text').hide("slow");
		}
	});*/
	$('#familydoc_option').live('click', function(){
		if($(this).is(":checked"))
		{
			$(this).val("1");
		}
		else
		{
			$(this).val('0');
		}
				
	});
	$('#pflegedienst_option').live('click', function(){
		if($(this).is(":checked"))
		{
			$(this).val("1");
		}
		else
		{
			$(this).val('0');
		}		
	});
	$('#hospizdienst_option').live('click', function(){
		if($(this).is(":checked"))
		{
			$(this).val("1");
		}
		else
		{
			$(this).val('0');
		}
				
	});
	$('#contactperson_option').click(function(){
		if($(this).is(":checked"))
		{
			$(this).val("1");
		}
		else
		{
			$(this).val('0');
		}
	});

	/*-------------------------------------------------------------------------------------------------*/
	/*-------------------------------------------------------------------------------------------------*/
	
$('#btnsubmit').live('click', function(event){
	setTimeout(function () {$('input[type=submit], input[type=button]').attr('disabled', true);}, 150);
	setTimeout(function () {$('input[type=submit], input[type=button]').attr('disabled', false);}, 12000);
	//fix for ie8 ->event.preventDefault() is not working in ie8 so we use event.returnValue=false;
	if (event.preventDefault) {
		event.preventDefault();
	} else {
		event.returnValue = false;
	}
	
	$('#mdksapvq').submit();
	
});
/*
$('#med_fest').live('click', function(){
	return false;
});

$('#med_bedarf').live('click', function(){
	return false;
});
*/
$('#pdf_export').live('click', function(event){
	setTimeout(function () {$('input[type=submit], input[type=button]').attr('disabled', true);}, 150);
	setTimeout(function () {$('input[type=submit], input[type=button]').attr('disabled', false);}, 12000);	
});
	
$('#submit_btn').live('click', function(event){

	//fix for ie8 ->event.preventDefault() is not working in ie8 so we use event.returnValue=false;
	if (event.preventDefault) {
		event.preventDefault();
	} else {
		event.returnValue = false;
	}
	$('#familydoc_option').val('1').attr('checked', true).attr('onclick', 'return false;').attr('onkeydown', 'return false;');
	$('#hidd_docid_sel').val($('#hidd_docid').val());
	$('#first_name_sel').val($('#first_name').val());
	$('#last_name_sel').val($('#last_name').val());
	$('#street1_sel').val($('#street1').val());
	$('#zip_sel').val($('#zip').val());
	$('#city_sel').val($('#city').val());
	$('#phone_practice_sel').val($('#phone_practice').val());
	$('#phone_emergency_sel').val($('#phone_emergency').val());
	$('#fax_sel').val($('#fax').val());
	$('#housarztDialog').remove();
	
});

$('#submit_btn_pfl').live('click', function(event){
	//fix for ie8 ->event.preventDefault() is not working in ie8 so we use event.returnValue=false;
	if (event.preventDefault) {
		event.preventDefault();
	} else {
		event.returnValue = false;
	}
	$('#pflegedienst_option').val('1').attr('checked', true).attr('onclick', 'return false;').attr('onkeydown', 'return false;');
	$('#hidd_pflegeid_sel').val($('#hidd_pflegeid').val());
	$('#pflegedienste_sel').val($('#pflegedienste').val());
	$('#first_name_pfle_sel').val($('#first_name').val());
	$('#last_name_pfle_sel').val($('#last_name').val());
	$('#street1_pfle_sel').val($('#street1').val());
	$('#zip_pfle_sel').val($('#zip').val());
	$('#city_pfle_sel').val($('#city').val());
	$('#phone_practice_pfle_sel').val($('#phone_practice').val());
	$('#phone_emergency_pfle_sel').val($('#phone_emergency').val());
	$('#fax_pfle_sel').val($('#fax').val());
	$('#pflegeDialog').remove();
	
});

$('#submit_btn_hassoc').live('click', function(event){
	//fix for ie8 ->event.preventDefault() is not working in ie8 so we use event.returnValue=false;
	if (event.preventDefault) {
		event.preventDefault();
	} else {
		event.returnValue = false;
	}
	$('#hospizdienst_option').val('1').attr('checked', true).attr('onclick', 'return false;').attr('onkeydown', 'return false;');
	$('#hidd_h_association_id_sel').val($('#hidd_h_association_id').val());
	$('#h_association_sel').val($('#h_association').val());
	$('#h_association_comment_sel').val($('#h_association_comment').val());
	$('#first_name_hosp_sel').val($('#first_name').val());
	$('#last_name_hosp_sel').val($('#last_name').val());
	$('#street1_hosp_sel').val($('#street1').val());
	$('#zip_hosp_sel').val($('#zip').val());
	$('#city_hosp_sel').val($('#city').val());
	$('#phone_hosp_sel').val($('#phone').val());
	$('#phone_emergency_hosp_sel').val($('#phone_emergency').val());
	$('#fax_hosp_sel').val($('#fax').val());
	$('#hospizdienstDialog').remove();
	
});


$('#submit_contact').live('click', function(event){

	//fix for ie8 ->event.preventDefault() is not working in ie8 so we use event.returnValue=false;
	if (event.preventDefault) {
		event.preventDefault();
	} else {
		event.returnValue = false;
	}
	var curentDialogId = $(this).attr('alt');
	$().ajaxStart(function() {
		$('#ui-dialog-title-'+curentDialogId).html('<img src="<?php echo RES_FILE_PATH; ?>/images/ajax-title-loader.gif" width="14" />&nbsp;&nbsp;Loading...');
	}).ajaxStop(function() {
		$('#ui-dialog-title-'+curentDialogId).text("Private Pflegeperson");
	});
	
	$('#contactperson_option').val('1').attr('checked', true).attr('onclick', 'return false;').attr('onkeydown', 'return false;');
	$('#h_association_sel').val($('#h_association').val());
	$('#h_association_comment_sel').val($('#h_association_comment').val());
	$('#cnt_first_name_sel').val($('#cnt_first_name').val());
	$('#cnt_last_name_sel').val($('#cnt_last_name').val());
	$('#cnt_street1_sel').val($('#cnt_street1').val());
	$('#cnt_zip_sel').val($('#cnt_zip').val());
	$('#cnt_city_sel').val($('#cnt_city').val());
	$('#cnt_hatversorgungsvollmacht_sel').val($('#cnt_hatversorgungsvollmacht').val());
	$('#cnt_legalguardian_sel').val($('#cnt_legalguardian').val());
	$('#cnt_familydegree_id_sel').val($('#cnt_familydegree_id').val());
	$('#cnt_comment_sel').val($('#cnt_comment').val());
	$('#contactperson_dialog').remove();
	
});

	//ISPC - 2083
	$('#stampuser').live('change', function() {
		$('#user_stamp_block span').replaceWith('');
		$('#user_stamp_block textarea').replaceWith('');
	
		$.get(appbase + 'ajax/userstampinfo?stamp-info=' + $(this).val(), function(result) {
	
			if (result != 0) {
				var resultx = jQuery.parseJSON(result);
	
				var user_lanr = resultx.lanr;
				var user_bsnr = resultx.bsnr;
	
				$('#Veror63_NameBetrinInpt').val(user_bsnr);
				$('#Veror63_NamearztnInpt').val(user_lanr);
	
				$('#user_stamp_block span').replaceWith('');
				$('#user_stamp_block textarea').replaceWith('');
	
				var row1 = resultx.row1;
				var row2 = resultx.row2;
				var row3 = resultx.row3;
				var row4 = resultx.row4;
				var row5 = resultx.row5;
				var row6 = resultx.row6;
				var row7 = resultx.row7;
	
				//var user_stamp = '<span>' + row1 + '<br/>' + row2 + '<br/>' + row3 + '<br/>' + row4 + '<br/>' + row5 + '<br/>' + row6 + '<br/>' + row7 + '</span>';
				//var user_stamp_hidden = '<textarea name="stamp_block" style="display: none">' + row1 + '<br/>' + row2 + '<br/>' + row3 + '<br/>' + row4 + '<br/>' + row5 + '<br/>' + row6 + '<br/>' + row7 + '</textarea>';	
				
				//$('#user_stamp_block').append(user_stamp + user_stamp_hidden);
				
				var user_stamp_hidden = row1 + '<br/>' + row2 + '<br/>' + row3 + '<br/>' + row4 + '<br/>' + row5 + '<br/>' + row6 + '<br/>' + row7;
				$('<span></span>').text(user_stamp_hidden).appendTo($('#user_stamp_block'));
				$('<textarea name="stamp_block" style="display: none"></textarea>').text(user_stamp_hidden).appendTo($('#user_stamp_block'));				
	
			} else {
				$('.stamp_alert').show('fast').delay(1000).hide('slow');
			}
	
		});
		return false;
});
	/*-------------------------------------------------------------------------------------------------*/
	/*-------------------------------------------------------------------------------------------------*/
}); // end of $(document).ready()

function selectFamilyDoctor(doc_id)
{
	if($('#fdoc_fn_'+doc_id).val().length > 0 && $('#fdoc_ln_'+doc_id).val().length > 0){
		var input_text = $('#fdoc_ln_'+doc_id).val() +', '+ $('#fdoc_fn_'+doc_id).val()
	} else if($('#fdoc_ln_'+doc_id).length > 0) {
		var input_text = $('#fdoc_ln_'+doc_id).val();
	} else if($('#fdoc_dn_'+doc_id).length > 0) {
		var input_text = $('#fdoc_fn_'+doc_id).val();
	}

	$('#familydoc_id').val(input_text);
	$('#hidd_docid').val($('#fdoc_id_'+doc_id).val());

	$('#first_name').val($('#fdoc_fn_'+doc_id).val());
	$('#last_name').val($('#fdoc_ln_'+doc_id).val());
	$('#phone_practice').val($('#fdoc_ph_'+doc_id).val());
	$('#phone_private').val($('#fdoc_pr_'+doc_id).val());
	$('#street1').val($('#fdoc_st_'+doc_id).val());
	$('#zip').val($('#fdoc_zip_'+doc_id).val());
	$('#city').val($('#fdoc_ci_'+doc_id).val());
	$('#fax').val($('#fdoc_fax_'+doc_id).val());
}
function selectPflegedienst(pflid)
{
	$('#hidd_pflegeid').val($('#pflege_id_'+pflid).val());
	$('#pflegedienste').val($('#pflege_nu_'+pflid).val());
	$('#first_name').val($('#pflege_fn_'+pflid).val());
	$('#last_name').val($('#pflege_ln_'+pflid).val());
	$('#street1').val($('#pflege_st_'+pflid).val());
	$('#zip').val($('#pflege_zip_'+pflid).val());
	$('#city').val($('#pflege_ci_'+pflid).val());
	$('#phone_practice').val($('#pflege_ph_'+pflid).val());
	$('#phone_emergency').val($('#pflege_phem_'+pflid).val());
	$('#fax').val($('#fax'+pflid).val());
}

function selectHospiceAssociation(hassoc_id)
{
	$('#h_association_details').show();
	$('#hidd_h_association_id').val($('#hassoc_id_'+hassoc_id).val());
	$('#h_association').val($('#hassoc_ha_'+hassoc_id).val());
	$('#first_name').val($('#hassoc_fn_'+hassoc_id).val());
	$('#last_name').val($('#hassoc_ln_'+hassoc_id).val());
	$('#street1').val($('#hassoc_st_'+hassoc_id).val());
	$('#zip').val($('#hassoc_zip_'+hassoc_id).val());
	$('#city').val($('#hassoc_ci_'+hassoc_id).val());
	$('#phone').val($('#hassoc_ph_'+hassoc_id).val());
	$('#phone_emergency').val($('#hassoc_phem_'+hassoc_id).val());
	$('#fax').val($('#fax'+hassoc_id).val());

}