;
/**
 * patientcourse.js
 * @date 01.11.2018
 * @author @cla
 * 
 * i've moved the mess from the html in this js... 
 * because i don't want to rewrite the `string  concatenate wonder`  listcourseSession.html = placeholderPatientCourseAddInline ...  
 *  ISPC-2486 Ancuta 20.11.2019
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


function isInteger(k, edit)
{
    var box = '';
    if(edit == 1) {
	box = 'edit_value';
    } else {
	box = 'sym_value';
    }
	
    var s = document.getElementById(box+k).value;
    var chars = "0123456789";

    if(s>10) {
	document.getElementById(box+k).value = "";
	return false;
    }

    var i;
    s = s.toString();
    for (i = 0; i < s.length; i++) {
	var c = s.charAt(i);
		if (chars.indexOf(c)==-1) {
	    document.getElementById(box+k).value = "";
	    return false;
	}
    }
    return true;
}


function chkmask(ltr,cnt)
{
	var cnts = 0;
	for( var i=0; i<ltrjs.length;i++) {
		if(ltrjs[i] == ltr) {
			cnts++;
		}
	}

	if(cnts<1){
		document.getElementById('course_type'+cnt).value="";
	} else {
		$('#course_title'+cnt).focus();
	}
}


$(function() {
	/*$('textarea').elastic(); *///@cla remove .. do NOT add css and js like this, you break all other stuff

	$('.divcheckbox').click(function(){
		var id = $(this).attr('id');

		if(this.checked==true) {
			$('.block_'+id).attr('checked', true);
		} else {
			$('.block_'+id).attr('checked', false);
		}
	});
});



 function save_filter(chkbx){

	var shortcut_details = {};
	var cnt = 0;
	
	$('.course_filter').each(function(){
		var name =  $(this).attr('name');
		if($(this).is(':checked')){
			var value  = "1";
		}  else{
			var value   = "0";
		}
		shortcut_details[name] = value;
		cnt++;
	});
	
	
	var array_shortcuts = JSON.stringify(shortcut_details);
	ajaxCallserver({
		url : 'ajax/saveuserfilter',
		method : 'POST',
		data : {
			details: array_shortcuts
		}
	});
}
 
 
function checkboxurl(chkbx,isfilter)
{
	if(!chkbx.checked) {
		var ltrs = document.getElementsByName(chkbx.name);

		for(var j=0;j<ltrs.length;j++) {
			ltrs[j].checked = false;
		}
	} else {
		var ltrs = document.getElementsByName(chkbx.name);

		for(var j=0;j<ltrs.length;j++) {
			ltrs[j].checked = true;
		}
	}
	
	if(isfilter==1) {
		check3(chkbx);
	} else {
		check2();
	}
}

function check2()
{
	$(".parentdiv").hide();
	//var cletters = []; //@cla removed.. this is not used
	var chkurl = "";
	var cnt = 0;
	var hide_all = 0;
	
	for (var i in ltrjs ) {
		if(ltrjs[i] != '_owned'){
			$("."+ltrjs[i]).hide();
		}

		var ltrs = document.getElementsByName(ltrjs[i]);

		for(var j=0;j<(ltrs.length-1);j++) {
			if($('#wrong').is(':checked')){
				$('.wrongfilter:not(.source_entry)').show();
				$('.wrongfilter *:not(.colleft2_inpwid):not(.source_entry)').each(function(){
					$(this).show();
					cnt++;
				});
			}

			if(ltrs[j].name == '_owned' && ltrs[j].checked==true) {
				hide_all = (hide_all+1);
			}

			if(ltrs[j].name == '_shared' && ltrs[j].checked==true) {
				hide_all = (hide_all+1);
			}

			if(ltrs[j].checked == true ) {
				$("."+ltrs[j].name).parent().parent().parent().show();
				$("."+ltrs[j].name).show();
				cnt++;
			} else if(ltrs[j].name != '_owned') {
				$("."+ltrs[j].name).hide();
			}
		}
	}

	if(cnt==0) {
		$(".parentdiv").show();
		for (var i in ltrjs ) {
			$("."+ltrjs[i]).show();
		}
	}

	if(hide_all>=2) {
		$(".parentdiv").hide();
	}
}

function check3(chks) {
	var sel_chks = 1;
	//var cletters=[];//@cla remover.. this is not used
	var chkurl = "";
	var cnt = 0;

	for(var i in ltrjs) {
		var ltrs = document.getElementsByName(ltrjs[i]);

		for(var j=0;j<(ltrs.length-1);j++) {
			if(ltrs[j].checked==true) {
				sel_chks = (sel_chks+1);
			}
		}
	}
	
	if(sel_chks ==1){
		$(".parentdiv").hide();
	}
	
	if(chks.checked) {
		$(".wrongfilter:not(.source_entry)").show();
		if($('#wrong').is(':checked')){
			$('.wrongfilter:not(.source_entry)').show();
			$('.wrongfilter *:not(.colleft2_inpwid):not(source_entry)').each(function(){
				$(this).show();
			});
		}
	} else {
		check2();
	}
}




var delaytrimer;


function keyupdelay(obj, cntr) {
	//obj is _this... it's all you need
	clearTimeout(delaytrimer);
	delaytrimer = setTimeout(function applydelay(){
		upper(cntr);
		chkmask(obj.value, cntr);
		changeinput(obj.value, cntr, obj);
		//ipad elastic annoying
		$('#komment'+cntr).elastic();
	},500);
}

function upper(v) {
	document.getElementById("course_type"+v).value = document.getElementById("course_type"+v).value.toUpperCase();
}


$(document).ready(function() {
	for(var k=0; k<hotkeysjs.length;k++) {
		var ads = k+1;
		shortcut.add('Ctrl+Alt+'+hotkeysjs[k],function(e) {
			if($('input[name='+String.fromCharCode(e.keyCode)+']').is(':checked')) {
				$('input[name='+String.fromCharCode(e.keyCode)+']').removeAttr('checked');
			} else {
				$('input[name='+String.fromCharCode(e.keyCode)+']').attr('checked','checked');
			}
	
			checkboxurl(document.getElementById($('input[name='+String.fromCharCode(e.keyCode)+']').attr('id')));
		});
	}
});

function printaction() {
	document.frmcourse.action = "patientcourse/printcourse?id=" + window.idpd;
	document.frmcourse.target = "_blank";
	document.frmcourse.submit();
}

function closepopup() {
	disablePopup();
}

function openPopup(chkbx,id, skip_modal) {
		var checkid = $(chkbx).attr('id');
		var ids = "";
		var quamma = "";

	for(var i=0;i<$('.block_'+checkid).length;i++) {
			ids = ids + quamma + ($('.block_'+checkid)[i].id);
			quamma = ",";
		}

	if(chkbx.checked) {
			$("<input type='hidden' name='comments["+id+"]' id='comment_"+id+"' value='' />").appendTo("#frmcourse");

			$('#contactArea').html('<iframe id="add_family_doc" frameborder="0" src="" scrolling="no" style="margin:0 auto;"></iframe>');


			centerPopup({sr:'about:blank',ht:250,wt:450});

			//$('#contactArea').html('<div>' + translate('wrongcomment') + '<div align="right"><a id="popupContactClose" style="cursor:pointer;" onclick="uncheckbox(\''+checkid+'\');closepopup()">x</a></div></div><div><textarea name="comment" id="comment"></textarea></div><div><button name="" onClick="saveComment(\''+id+'\',\''+ids+'\',\''+checkid+'\');">' + translate('submit') + '</button></div>');
			//ISPC-2571 Lore 18.08.2020
			$('#contactArea').html('<div>' + translate('wrongcomment_formular') + '<div align="right"><a id="popupContactClose" style="cursor:pointer;" onclick="uncheckbox(\''+checkid+'\');closepopup()">x</a></div></div><div><textarea name="comment" id="comment" style="width: 455px;height: 40px;"></textarea></div><div><button name="" onClick="saveComment(\''+id+'\',\''+ids+'\',\''+checkid+'\');">' + translate('submit') + '</button></div>');

			loadPopup();
		} else {

			$("#comment_"+id).remove();

			ajaxCallserver({url:'patientcourse/savewrongcd?ids='+ids+'&val=0&blockcnt='+checkid});
		}
	}

function saveComment(id,ids,blockid)
 {
	var val = "";
	var modal_mode = '0';

	if($("#comment").val().length>0) {
		
	  val="Dieser Eintrag wurde als gelöscht markiert. Begründung : " + $("#comment").val();

		$("#comment_"+id).val(val);

		ajaxCallserver({url:'patientcourse/savewrongcd', method:'POST', data:{comment:val,val:1,ids:ids,blockcnt:blockid}});
		disablePopup();

	   var arr_medis = ["M", "N", "I", "Q", "BP"];
		var arr_diag = ["D","H","HS"];
	   var arr_xbdt_actions = ["LE"];

		$('.courses_types_'+blockid).each(function(){
			if(jQuery.inArray($(this).val(), arr_medis)>=0){

				if(modal_mode == '0'){
					if($(this).val() == 'Q' && $(this).attr('rel') != 0){
						modal_mode = '1';
				   } else if($(this).val() == 'Q' && $(this).attr('rel') == 0) {
					   modal_mode = '3';
					} else if($(this).val() != 'Q') {
						modal_mode = '1';
					}
				}
			}
			
			if(jQuery.inArray($(this).val(), arr_diag)>=0) {
				if(modal_mode == 0){
					modal_mode = '2';
				}
			}
			
			if(jQuery.inArray($(this).val(), arr_xbdt_actions)>=0 && module_le) {
				if(modal_mode == 0){
					modal_mode = '4';
				}
			}
		});

		switch (modal_mode) {
			case '1':
				//modal medis with ok
				$('#delete_medis').show();
				$('#modal_redir_type').val('M');

				$('#modal_delete').dialog('open');
				break;
			case '2':
				//modal diag with ok
				$('#delete_diag').show();
				$('#modal_redir_type').val('D');

				$('#modal_delete').dialog('open');
				break;

			   case '3':
				   //ispc 1864 12) if a user deletes a Q entry . the BTM ammount MUST not be affected!
				   //modal btm dialog deleting medi with ok	
					//$('#btm_medi_revert').val(ids);
					//$('#delete_btm_med').dialog('open');
					
				   break;
				
			case '4':
				//modal diag with ok
				$('#delete_xbdt_action').show();
				$('#modal_redir_type').val('LE');

				$('#modal_delete').dialog('open');
				break;
			default:
		}
	}
	else
	{
		   jAlert(translate('entercomment'));
	}
   }

function diag_button(){
	window.location = appbase+'patient/patdiagnoedit?id=' + window.idpd;
	$('#modal_delete').dialog('close');
}

function medi_button(){
	window.location = appbase+'patient/patientmedication?id=' + window.idpd;
	$('#modal_delete').dialog('close');
}

var callBackWrong = function(params){

	if(params.val==1)
	{
	    $("#wrongcomment_"+params.id).show();
		$("#wrongcomment_"+params.id).html(params.comment);
		$('#maindiv_'+params.id).addClass('wrongfilter');

		$('#maindiv_'+params.id).addClass('greyclass');
		$('.wrc_'+params.id).find('font').attr('color','#cccccc');
		$('.wrt_'+params.id).find('font').attr('color','#cccccc');
	}
	else
	{
	    $('#maindiv_'+params.id).removeClass('greyclass');
		$('#maindiv_'+params.id).removeClass('wrongfilter');

		$('#wrongcomment_'+params.id).hide();



		for(var i=0;i<$('.wrc_'+params.id).length;i++)
		{
	       var crid = ($('.wrc_'+params.id)[i].id);
		       var crtid = ($('.wrt_'+params.id)[i].id);
		       var oldcolor = $('#old_'+crid.substr(('wrc_').length)).val();

		       $('#'+crid).find('font').attr('color','#'+oldcolor);
		       $('#'+crtid).find('font').attr('color','#'+oldcolor);
	    }
	}
	
	if( ! $.isEmptyObject(params.extra_params)) {
		
		if( params.extra_params.hasOwnProperty('refresh')) {

			//params.extra_params.refresh is intended to reload only parts of the page
			switch( params.extra_params.refresh) {
				case "vital_signs_icons":{
					refresh_weight_chart_block();//refresh the graph for the vitalsigns
				}
				break;
			}
		}
	}
};

function uncheckbox(chk)
{
       $('#'+chk).attr('checked',false);
}

function appendto(rowId,inputValue){
	var modulepriv = window.modulepriv;
	var module_hb = window.module_hb;
	var modulepriv_bavaria = window.modulepriv_bavaria;
	var modulepriv_g = window.modulepriv_g; //ISPC-2651 Elena 20.08.2020
	var module_noauto = window.module_noauto;
	var module_le = window.module_le;
	var module_sd = window.module_sd;
	var module_al = window.module_al;
	var module_xs = window.module_xs;
	var module_xe = window.module_xe;
	var module_vo_ve = window.module_vo_ve;
	
	var module_pk = window.module_pk;
	var module_xn = window.module_xn;
	var module203_rlp_special_sh = window.module203_rlp_special_sh;
	var module209_demstepcare_special_sh= window.module209_demstepcare_special_sh;
	var module_ml = window.module_ml;

	var module_companion_xt = window.module_companion_xt;		//ISPC-2902 Lore 27.04.2021

	var text = '';
	
	 //we get all inputs values every time something changes so if user skip a box.. we have the rest 2 and so on
	 if(($('#name'+rowId).val() != "Name" && $('#dosage'+rowId).val() != "Dosierung") || ($('#todo'+rowId).val() != "TODO" && $('#till'+rowId).val() != "Till When" && $('#user'+rowId).val() != "0") || ($('#diagnosis'+rowId).val() != "Beschreibung" ))
	 {
		//ISPC - 2366
	 	if($('#course_type'+rowId).val() == "M" || $('#course_type'+rowId).val() == "N" || $('#course_type'+rowId).val() == "I")
	 	{
	 		text = $('#name'+rowId).val() != '' ? $('#name'+rowId).val() : '';
			text += $('#dosage'+rowId).val() != '' ? '|' + $('#dosage'+rowId).val() : '|';
			text += $('#komment'+rowId).val() != '' ? '|' + $('#komment'+rowId).val() : '';
			text += inputExtra != '' ? '|' + inputExtra : '|';//ISPC-2329
			text += inputExtraAtc != '' ? '|' + inputExtraAtc : '|';//ISPC-2554 pct.3
			text += inputExtraPzn != '' ? '|' + inputExtraPzn : '|';//ISPC-2329 Ancuta 03.04.2020
			text += inputExtraDBF_id != '' ? '|' + inputExtraDBF_id : '|';//ISPC-2329 Ancuta 03.04.2020
			text += inputExtraDosage_form != '' ? '|' + inputExtraDosage_form : '|';//ISPC-2554 Carmen 14.05.2020
			text += inputExtraUnit != '' ? '|' + inputExtraUnit : '|';//ISPC-2554 Carmen 14.05.2020
			//text = $('#name'+rowId).val()+' | '+$('#dosage'+rowId).val()+' | '+$('#komment'+rowId).val();
	    } 
		else if($('#course_type'+rowId).val() == "BP" )
		{
			text = $('#name'+rowId).val() != '' ? $('#name'+rowId).val() : '';
			text += $('#komment'+rowId).val() != '' ? '|' + $('#komment'+rowId).val() : '';
			
			//text = $('#name'+rowId).val()+' | '+$('#komment'+rowId).val();
		} 
	 	else if($('#course_type'+rowId).val() == "W")
	 	{
			if($('#group'+rowId).is(':checked'))
			{
				var group = '1';
			} 
			else 
			{
				var group = '0';
			}
			text = $('#todo'+rowId).val()+' |---------| '+$('#user'+rowId).val()+' |---------| '+ $('#till' + rowId).val() + ' |---------| ' + group;

		}
	 	else if ($('#course_type' + rowId).val() == "P") 
	 	{
	 		text = $('#name'+rowId).val() != '' ? $('#name'+rowId).val() : '';
			text += $('#dosage'+rowId).val() != '' ? '|' + $('#dosage'+rowId).val() : '';
			text += $('#komment'+rowId).val() != '' ? '|' + $('#komment'+rowId).val() : '';
			
			//text = $('#medi_name' + rowId).val() + ' | ' + $('#dosage' + rowId).val() + '  | ' + $('#comments' + rowId).val();
		}  
	 	else if ($('#course_type' + rowId).val() == "PB") 
		{
			if ($('#komment' + rowId).val() == "Inhalt" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
			{//set time default value if left empty
				komment = "";
			} 
			else 
			{
				komment = $('#komment' + rowId).val();
			}
			
			text =  komment + ' | ' + $('#date' + rowId).val() + ' ' + $('#hourtime' + rowId).val();
		} 
	 	else if ($('#course_type' + rowId).val() == "KX") 
		{
			if ($('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
			{//set time default value if left empty
				komment = "";
			} 
			else 
			{
				komment = $('#komment' + rowId).val();
			}
			
			text =  komment + ' | ' + $('#date' + rowId).val() + ' ' + $('#hourtime' + rowId).val();
		} 
	 	else if ($('#course_type' + rowId).val() == "L") 
	 	{
				
			if ($('#lrownumber' + rowId).val() == "Leistugen" || $('#lrownumber' + rowId).val() == "" || $('#lrownumber' + rowId).val() == " ") 
			{//set time default value if left empty
		  		komment = "";
			} 
			else 
			{
		    	komment = $('#lrownumber' + rowId).val();
	        }
	     	text =  komment ;
		}
		else if (
				($('#course_type' + rowId).val() == "U" 
				|| $('#course_type' + rowId).val() == "V") 
				|| (($('#course_type' + rowId).val() == "VO" || $('#course_type' + rowId).val() == "VE") && module_vo_ve == "1")
				|| ($('#course_type' + rowId).val() == "XT" && (modulepriv == "1" || modulepriv_bavaria == "1"))
				|| ($('#course_type' + rowId).val() == "G" && (modulepriv_g == "1")) //ISPC-2651

				|| ($('#course_type' + rowId).val() == "LS" && module_lysocare == "1")
				|| ($('#course_type' + rowId).val() == "SD" &&  module_sd == "1") 
				|| ($('#course_type' + rowId).val() == "HB" && module_hb == "1") 
				|| ($('#course_type' + rowId).val() == "AA" && (modulepriv == "1" || modulepriv_bavaria == "1"))
				|| ($('#course_type' + rowId).val() == "XS" && module_xs == "1") 
				|| ($('#course_type' + rowId).val() == "XE" && module_xe == "1")
				
				|| ($('#course_type' + rowId).val() == "PK" && module_pk == "1") 
				|| ($('#course_type' + rowId).val() == "XN" && module_xn == "1") 
				|| ($('#course_type' + rowId).val() == "ME")
				
				|| ($('#course_type' + rowId).val() == "RK" && module203_rlp_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "RI" && module203_rlp_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "RO" && module203_rlp_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "RD" && module203_rlp_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "ML" && module_ml == "1") 
				|| ($('#course_type' + rowId).val() == "DD" && module209_demstepcare_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "DC" && module209_demstepcare_special_sh == "1" ) 
				|| ($('#course_type' + rowId).val() == "XM" && module_xm == "1") //TODO-2942
				|| ($('#course_type' + rowId).val() == "XH" && module_xh == "1") //TODO-2942
				|| ($('#course_type' + rowId).val() == "XG" && module_xg == "1") //TODO-2942
				|| ($('#course_type' + rowId).val() == "WT" && module_wt == "1")	//TODO-3897 Lore 15.03.2021
				) 
		{

			if ($('#course_type' + rowId).val() == "U" && modulepriv != "1") 
			{
			    //appended the select value to time
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = $('#uSelect' + rowId).val() + " | 10";
			    }
				else 
				{
					time = $('#uSelect' + rowId).val() + " | " + $('#time' + rowId).val();
			     }
			} 
			else if ($('#course_type' + rowId).val() == "U" && modulepriv == "1") 
			{ //LNR client only
				//appended the select value to time
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = $('#uSelect' + rowId).val() + " | 15";
			    } 
				else 
				{
					time = $('#uSelect' + rowId).val() + " | " + $('#time' + rowId).val();
			    }
			} 
			else if (($('#course_type' + rowId).val() == "XT" && module_noauto ==  "1") ||
				    ($('#course_type' + rowId).val() == "G" && modulepriv_g ==  "1") || //ISPC-2651 Elena 20.08.2020
					 ($('#course_type' + rowId).val() == "LS") ||
					 ($('#course_type' + rowId).val() == "WT") ||		//TODO-3897 Lore 15.03.2021
					 ($('#course_type' + rowId).val() == "SD" && module_noauto ==  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && module_noauto ==  "1")) 
			{ //module no auto comment active for xt shortcuts
				//appended the select value to time
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = " ";
				} 
				else 
				{
					time = $('#time' + rowId).val();
				}
			} 
			else if (($('#course_type' + rowId).val() == "XT" && modulepriv == "1" && module_noauto !=  "1") ||
					($('#course_type' + rowId).val() == "G" && modulepriv_g == "1" ) || //ISPC-2651 Elena 20.08.2020
					 ($('#course_type' + rowId).val() == "SD" && modulepriv == "1" && module_noauto !=  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && modulepriv == "1" && module_noauto !=  "1") ) 
			{//LNR CLient Z=>XT(telefon) shortcut
				//appended the select value to time
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = "12";
			    } 
				else 
				{
					time = $('#time' + rowId).val();
			     }
		    }
			else if (($('#course_type' + rowId).val() == "XT" && modulepriv_bavaria == "1"  && module_noauto !=  "1") ||
					($('#course_type' + rowId).val() == "G" && modulepriv_g == "1"  ) || //ISPC-2651 Elena 20.08.2020
					 ($('#course_type' + rowId).val() == "SD" && modulepriv_bavaria == "1"  && module_noauto !=  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && modulepriv_bavaria == "1"  && module_noauto !=  "1")) 
			{//LNR CLient Z=>XT(telefon) shortcut
				//appended the select value to time
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = "5";
			    } 
				else 
				{
					time = $('#time' + rowId).val();
			     }
		    }
	   		// NEW     Administration shortcut - like Koordination
			else if ($('#course_type' + rowId).val() == "AA" && modulepriv_bavaria == "1") 
			{//TP CLient Z=>AA(administration) shortcut
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = "5";
			    } 
				else 
				{
					time = $('#time' + rowId).val();
			    }
			}

			if ($('#course_type' + rowId).val() == "V"
					|| (module_vo_ve == "1" && ($('#course_type' + rowId).val() == "VO" || $('#course_type' + rowId).val() == "VE"))
					|| ($('#course_type' + rowId).val() == "XG" && module_xg == "1")
				) 
			{
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = "8";
			    }
				else 
				{
					time = $('#time' + rowId).val();
			     }
			}

			if ($('#course_type' + rowId).val() == "U" && modulepriv != "1") 
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set kommentar default value if left empty
					komment = "Situation stabil, heute kein Besuch notwendig, Kontakt f. Folgetag vereinbart";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			} 
			else if ($('#course_type' + rowId).val() == "U" && modulepriv == "1") 
			{ //LNR Client Only
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set kommentar default value if left empty
					komment = "Beratung";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}
			else if (($('#course_type' + rowId).val() == "XT" && module_noauto ==  "1") || 
					 ($('#course_type' + rowId).val() == "LS") ||
					 ($('#course_type' + rowId).val() == "WT") ||		//TODO-3897 Lore 15.03.2021					 
					 ($('#course_type' + rowId).val() == "G" && modulepriv_g == "1") || //ISPC-2651 Elena 20.08.2020
					 ($('#course_type' + rowId).val() == "SD" && module_noauto ==  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && module_noauto ==  "1") ) 
			{ //module no auto comment active for xt shortcuts
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set kommentar default value if left empty
					komment = " ";
				}
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			} 
			else if (($('#course_type' + rowId).val() == "XT" && modulepriv == "1" && module_noauto !=  "1") || 
					 ($('#course_type' + rowId).val() == "SD" && modulepriv == "1" && module_noauto !=  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && modulepriv == "1" && module_noauto !=  "1") ) 
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set kommentar default value if left empty
					komment = "Situation stabil, heute kein Besuch notwendig, Kontakt f. Folgetag vereinbart";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}

			else if (($('#course_type' + rowId).val() == "XT" && modulepriv_bavaria == "1" && module_noauto !=  "1") || 
					 ($('#course_type' + rowId).val() == "SD" && modulepriv_bavaria == "1" && module_noauto !=  "1") ||
					 ($('#course_type' + rowId).val() == "HB" && modulepriv_bavaria == "1" && module_noauto !=  "1") )
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set kommentar default value if left empty
					komment = "Telefonat bzgl. der aktuellen Situation";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}
			else if ($('#course_type' + rowId).val() == "AA" && modulepriv_bavaria == "1")
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set time default value if left empty
					komment = "Administration Pflege";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}

			if ($('#course_type' + rowId).val() == "V"
					|| (module_vo_ve == "1" && ($('#course_type' + rowId).val() == "VO" || $('#course_type' + rowId).val() == "VE"))
				) 
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set time default value if left empty
					komment = "Koordinationsleistung";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}
			
			if (($('#course_type' + rowId).val() == "XG" && module_xg == "1")
			) 
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set comment default value if left empty
					komment = "Teambesprechung";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
			}
			
			if ($('#course_type' + rowId).val() == "XS" 
				|| $('#course_type' + rowId).val() == "XE"
					|| $('#course_type' + rowId).val() == "PK"
						|| $('#course_type' + rowId).val() == "XN"
							|| $('#course_type' + rowId).val() == "ME"
								
								|| $('#course_type' + rowId).val() == "RK"
									|| $('#course_type' + rowId).val() == "RI"
										|| $('#course_type' + rowId).val() == "RO"
											|| $('#course_type' + rowId).val() == "RD"
												|| $('#course_type' + rowId).val() == "ML"
													|| $('#course_type' + rowId).val() == "DD"
														|| $('#course_type' + rowId).val() == "DC"											
															|| $('#course_type' + rowId).val() == "XM"
																|| $('#course_type' + rowId).val() == "XH"
				
			) 
			{
				if ($('#komment' + rowId).val() == "Grund / Anlass" || $('#komment' + rowId).val() == "" || $('#komment' + rowId).val() == " ") 
				{//set time default value if left empty
					komment = "";
				} 
				else 
				{
					komment = $('#komment' + rowId).val();
				}
				if ($('#time' + rowId).val() == "Zeit (in Minuten)" || $('#time' + rowId).val() == "" || $('#time' + rowId).val() == " ") 
				{//set time default value if left empty
					time = "";
				} 
				else 
				{
					time = $('#time' + rowId).val();
				}
			
			}
			
			
			text =  time + ' | ' + komment + ' | ' + $('#date' + rowId).val() + ' ' + $('#hourtime' + rowId).val();
			
			if ($('#course_type' + rowId).val() == "LS") {
				text += ' | ' + $('#uCategory' + rowId).val();
				text += ' | ' + $('#uCategory_2_' + rowId).val();
				text += ' | ' + $('#uCategory_3_' + rowId).val();
			}
			
			//TODO-3897 Lore 15.03.2021
			if ($('#course_type' + rowId).val() == "WT") {
				text += ' | ' + $('#uCategory' + rowId).val();
				text += ' | ' + $('#uCategory_2_' + rowId).val();
				text += ' | ' + $('#uCategory_3_' + rowId).val();
			}
			
			
		} 
		else if ($('#course_type' + rowId).val() == "D" || $('#course_type' + rowId).val() == "H" || $('#course_type' + rowId).val() == "HS") 
		{
			icdId = $('#hidd_icdnumber' + rowId).val();
			if($('#icdnumber' + rowId).val() == "ICD") 
			{
				icdField = "";
		    } 
			else 
			{
				icdField = $('#icdnumber' + rowId).val();
		    }

			text = icdId + ' | ' + icdField + ' | ' + $('#diagnosis' + rowId).val();
		} 
		else if ($('#course_type' + rowId).val() == "AL" && module_al == "1")  
		{
			
						
			if($('#actionid' + rowId).val() == "ID") {action_id_field = "";	} 
			else {action_id_field = $('#actionid' + rowId).val();}
			
			if($('#description' + rowId).val() == "Beschreibung"){description = "";} 
			else{ description = $('#description' + rowId).val(); }

			text = action_id_field + ' | ' + description + ' | ' + $('#al_date' + rowId).val();

		}
		else if ($('#course_type' + rowId).val() == "LE" && module_le == "1")  
		{
					
			
			action_id = $('#hidd_actionid' + rowId).val();
			
			if($('#actionid' + rowId).val() == "ID") 
			{
				action_id_field = "";
			} 
			else 
			{
				action_id_field = $('#actionid' + rowId).val();
			}

			text = action_id + ' |____| ' + action_id_field + ' |____| ' + $('#actionname' + rowId).val()+ ' |____| ' + $('#actionuser' + rowId).val()+ ' |____| ' + $('#date' + rowId).val() + ' ' + $('#hourtime' + rowId).val();;
		} 
		else if ($('#course_type' + rowId).val() == "SB") 
		{
			text = $('#sb_user' + rowId).val() + ' |__| ' +  $('#sb_order' + rowId).val() ;				
	    }
	    
		$('#course_title' + rowId).val(text);
		
	 }
	 else 
	 {
		$('#course_title' + rowId).val('');
	 }
 }

//_this is the caller
function changeinput(shortcut, id, _this){
	
	var modulepriv = window.modulepriv;
	var module_hb = window.module_hb;
	var module_le = window.module_le;
	var modulepriv_bavaria = window.modulepriv_bavaria;
	var modulepriv_g = window.modulepriv_g; //ISPC-  Maria:: Migration CISPC to ISPC 20.08.2020
	var module_sd = window.module_sd;
	var module_al = window.module_al;
	var module_xs = window.module_xs;
	var module_xe = window.module_xe;
	var module_vo_ve = window.module_vo_ve;
	
	var shortcut_options = window.shortcut_options;		//ISPC-2902 Lore 29.04.2021

	idplus = id +1;
	idminus = id -1;
	var pid = $('#patientid').val() || window.idpd;
	var total_cnt = $('.LeftList01').length;
	var has_hs_fields = false;
	inputExtra = '';
	inputExtraAtc = ''; //ISPC-2554 pct.3
	inputExtraPzn = ''; //ISPC-2329 Ancuta 03.04.2020
	inputExtraDBF_id = ''; //ISPC-2329 Ancuta 03.04.2020
	
	$('input[name="course_type[]"]').each(function(){
		if($(this).val() == 'HS' && $(this).attr('id') != 'course_type'+id) {
			has_hs_fields = true;
		}
	});
	
	//reset error for newly changed line
//	$('#error'+id).remove();
	$('.verlauf_error_div').hide();
	$('#course_title'+id).val('');
	
	if((shortcut == "M" || shortcut == "I" || shortcut == "N" || shortcut == "m" || shortcut == "i" || shortcut == "n"))
	{ //medications
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}
		//ISPC - 2366
		//newInputs = '<div id="hidd'+id+'"><input type="text" id="name'+id+'"class="caret course_med_name" name="name[]" value="Name" onfocus="if(this.value == \'Name\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Name\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="dosage'+id+'" name="dosage[]" class="caret"  value="Dosierung" style="width:150px; text-align:left;" onfocus="if(this.value == \'Dosierung\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Dosierung\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="komment'+id+'" name="komment[]" class="caret"  value="Kommentar" style="width:210px; text-align:left;" onfocus="if(this.value == \'Kommentar\') { this.value=\'\'};" onblur="if(this.value == \'\'){ this.value=\'Kommentar\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/></div>';
		newInputs = '<div id="hidd'+id+'" class="hid-div" ><input type="text" id="name'+id+'"class="caret course_med_name" name="name[]" placeholder="Name" value="" onfocus="if(this.value == \'Name\') { this.value=\'\'}" onblur="if(this.value != \'\'){ appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="dosage'+id+'" name="dosage[]" class="caret"  placeholder="Dosierung" value="" style="width:150px; text-align:left;" onfocus="if(this.value == \'Dosierung\') { this.value=\'\'}" onblur="if(this.value != \'\'){ appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="komment'+id+'" name="komment[]" class="caret"  placeholder="Kommentar" value="" style="width:210px; text-align:left;" onfocus="if(this.value == \'Kommentar\') { this.value=\'\'};" onblur="if(this.value != \'\'){ appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/></div>';
		$('#course_title'+id).hide();
		$('#listcoursesession_course_title'+id).append(newInputs);
		$('#listcoursesession_course_title'+id).show();

		if (window.window_show_mmi == 1) {
			
			var healthinsuranceik = window.window_kassen_no;
			var client = window.window_clientid;
	
			$('.course_med_name').live('change', function() {
				var input_row = parseInt($(this).attr('id').substr(('name').length));
			}).liveSearch({
				url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
				id: 'livesearch_admission_medications',
				aditionalWidth: '300',
				noResultsDelay: '900',
				typeDelay: '900',
				returnRowId: function (input) {return parseInt($(input).attr('id').substr(('name').length));}
			});
		}


		if(jQuery.inArray(shortcut, ltrjs))
		{
			$('#name'+id).focus();
		}

	}
	else if((shortcut == "BP" || shortcut == "BP"))
	{ //medications
		
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}

		//newInputs = '<div id="hidd'+id+'"><input type="text" id="name'+id+'"class="caret course_med_name_treatment_care" name="name[]" value="Name" onfocus="if(this.value == \'Name\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Name\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="komment'+id+'" name="komment[]" class="caret"  value="Kommentar" style="width:210px; text-align:left;" onfocus="if(this.value == \'Kommentar\') { this.value=\'\'};" onblur="if(this.value == \'\'){ this.value=\'Kommentar\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/></div>';
		newInputs = '<div id="hidd'+id+'" class="hid-div" ><input type="text" id="name'+id+'"class="caret course_med_name_treatment_care" name="name[]" placeholder="Name" value="" onfocus="if(this.value == \'Name\') { this.value=\'\'}" onblur="if(this.value != \'\'){ appendto(\''+id+'\',this.value)}  insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" /><input type="text" id="komment'+id+'" name="komment[]" class="caret"  placeholder="Kommentar" value="" style="width:210px; text-align:left;" onfocus="if(this.value == \'Kommentar\') { this.value=\'\'};" onblur="if(this.value != \'\'){ appendto(\''+id+'\',this.value)} insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/></div>';
		$('#course_title'+id).hide();
		$('#listcoursesession_course_title'+id).append(newInputs);
		$('#listcoursesession_course_title'+id).show();
		
		if (window.window_show_mmi == 1) {
			var healthinsuranceik = window.window_kassen_no;
			var client = window.window_clientid;
			
			$('.course_med_name_treatment_care').live('change', function() {
				var input_row = parseInt($(this).attr('id').substr(('name').length));
			}).liveSearch({
	//				url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
				url: 'ajax/medicationstreatmentcare?q=',
				id: 'livesearch_admission_medications',
				aditionalWidth: '300',
				noResultsDelay: '900',
	
					typeDelay: '900',
				returnRowId: function (input) {return parseInt($(input).attr('id').substr(('name').length));}
			});
		}


		if(jQuery.inArray(shortcut, ltrjs))
		{
			$('#name'+id).focus();
		}

	} 
	 
	else if(shortcut == "W" || shortcut=="w") 
	{ // To Do

		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}
		


		var usersSelect = '<div class="sel_holder sel_holder_todos"><select name="user'+id+'" id="user'+id+'" multiple="multiple" size="1" onchange="appendto('+id+',this.value); insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" style="width:200px; height: 36px; text-align:left; float:left;" class="todo_selectbox">' + window.window_json_encode_sel_str + '</select></div>';					
		newInputs = '<div id="hidd'+id+'" class="hid-div" >\n\
			<textarea id="todo'+id+'"class="caret course_todo" name="todo[]" value="TODO" onfocus="if(this.value == \'TODO\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'TODO\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');">TODO</textarea>\n\
			'+usersSelect+'\n\
			<input type="text" id="till'+id+'" name="till[]" class="caret course_todo_date" style="width:100px; text-align:left; float:left;heigth:16px; "  value="' + window.window_date + '"  onfocus="appendto(\''+id+'\',this.value);" onchange="if(this.value == \'\'){ this.value=\'' + window.window_date + '\' } else { appendto(\''+id+'\',this.value);} insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
		</div>';

		/*
		<textarea id="selected'+id+'"  style="width:196px; height: 40px;" cols="" rows="3" class="todo_specialo_tezxtae"></textarea></div>\n\
*/
		$('#listcoursesession_course_title'+id).append(newInputs);



		var result = [];
		/*
		$('#user'+id).live('change',function(){
			
			var items = [];
			$('#user'+id+' option:selected').each(function(){ items.push($(this).text()); });
			var result = items.join('; ');
			$('#selected'+id).val(result);
			
		});
		*/

		$('.todo_selectbox', $('#listcoursesession_course_title'+id)).chosen({
			placeholder_text_single: translate('please select'),
			placeholder_text_multiple : translate('please select'),
			multiple:1,
			width:'250px',
			style: "padding-top:10px",
			"search_contains": true,
			no_results_text: translate('noresultfound')
		});
		
		
		$('#todo'+id).elastic();
		$('#till' + id).mask("99.99.9999");
		$('#course_title'+id).hide();
		$('#till'+id).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(date) {
				appendto(id,date.value);
				insertsession(id,pid,id);
				$(this).focus();
				return false;
			}
		});
		
		$('#listcoursesession_course_title'+id).show();
		appendto(id, window.window_date);
		if(jQuery.inArray(shortcut, ltrjs)){
			$('#todo'+id).focus();
		}

	} 
	else if( 
			   shortcut == "U"  || shortcut == "u" 
			|| shortcut == "V"  || shortcut == "v"
			|| ((shortcut == "VO" || shortcut == "VE") && module_vo_ve == "1")
			|| ((shortcut == "XT" || shortcut == "xt") && (modulepriv == "1" || modulepriv_bavaria == "1" ))
			|| ((shortcut == "G" || shortcut == "g") && (modulepriv_g == "1"  )) //ISPC-2651
			|| ((shortcut == "LS" || shortcut == "ls") && module_lysocare == "1")
			|| ((shortcut == "SD" || shortcut == "sd") && module_sd == "1")
			|| ((shortcut == "HB" || shortcut == "hb") && module_hb == "1" )
			|| ((shortcut == "AA" || shortcut == "aa") && (modulepriv == "1" || modulepriv_bavaria == "1" ))
			|| ((shortcut == "XS" || shortcut == "xs") && module_xs == "1" )
			|| ((shortcut == "XE" || shortcut == "xe") && module_xe == "1" )
			
			|| ((shortcut == "PK" || shortcut == "pk") && module_pk == "1" )
			|| ((shortcut == "XN" || shortcut == "xn") && module_xn == "1" )
			|| ((shortcut == "ME" || shortcut == "me"))
			
			|| ((shortcut == "RK" || shortcut == "rk")  && module203_rlp_special_sh == "1"  )
			|| ((shortcut == "RI" || shortcut == "ri")  && module203_rlp_special_sh == "1"  )
			|| ((shortcut == "RO" || shortcut == "ro")  && module203_rlp_special_sh == "1"  )
			|| ((shortcut == "RD" || shortcut == "rd")  && module203_rlp_special_sh == "1" )
			|| ((shortcut == "ML" || shortcut == "ml")  && module_ml == "1" )
			|| ((shortcut == "DD" || shortcut == "dd")  && module209_demstepcare_special_sh == "1"  )
			|| ((shortcut == "DC" || shortcut == "dc")  && module209_demstepcare_special_sh == "1" )
			|| ((shortcut == "XM" || shortcut == "xm") && module_xm == "1" ) //TODO-2942
			|| ((shortcut == "XH" || shortcut == "xh") && module_xh == "1" ) //TODO-2942
			|| ((shortcut == "XG" || shortcut == "xg") && module_xg == "1" ) //TODO-2942
			|| ((shortcut == "WT" || shortcut == "wt") && module_wt == "1" )	//TODO-3897 Lore 15.03.2021
					
			)
	{ //Koordination and Telefon
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}

/*		$("#time"+id).live("keyup input paste", function(){
			setTimeout(jQuery.proxy(function() {
				this.val(this.val().replace(/[^0-9]/g, ''));
			}, $(this)), 0);
		});*/

		//ISPC-2770 Lore 17.12.2020
		if( shortcut == "XT" || shortcut == "xt" 
			|| shortcut == "U"  || shortcut == "u" 
			|| shortcut == "V"  || shortcut == "v"){
			
			$("#time"+id).live("keyup input paste click", function(){
				setTimeout(jQuery.proxy(function() {
					this.val(this.val().replace(/[^0-9]/g, ''));
				}, $(this)), 0);
				
				//bigger than 0
				if($(this).val() == "0")$(this).val("");

				//small than 360
				if ($(this).val() > 360){
					var str = $(this).val();
					var res = str.substring(0, 3);
					//console.log(res);
					if(res > 360){
						//alert('Only integers between 1-360 !');
						jAlert(translate('Only integers between 1-360 !'), 'Alert');
						$(this).val("");
						appendto(id, "");
						insertsession(id,pid,total_cnt);
					} else {
						$(this).val(res);
					}
				} 
			});
			
			$("#komment"+id).live("keyup input paste click", function(){
				setTimeout(jQuery.proxy(function() {
					this.val(this.val().replace("|", '/'));
				}, $(this)), 0);
				
			});
			
		} else {
			$("#time"+id).live("keyup input paste", function(){
				setTimeout(jQuery.proxy(function() {
					this.val(this.val().replace(/[^0-9]/g, ''));
				}, $(this)), 0);
			});
		}
		//.
		
		if(shortcut == "U" || shortcut == "u")
		{
			var uSelect = '<select id="uSelect'+id+'" name="uSelect[]" style="" class="course_uselect" onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onblur="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"><option value="mit Betroffenen">mit Betroffenen</option><option value="mit Leistungserbringer">mit Leistungserbringer</option></select>';
			var comment_class = 'course_comment_small';
		} 
		//ISPC-2902 Lore 27.04.2021
		else if ( (shortcut == "XT" || shortcut == "xt") && module_companion_xt == '1' ) {
			//var uSelect = '<select id="uSelect'+id+'" name="uSelect[]" style="" class="course_uselect" onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onblur="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"> <option value="-">----</option> 	<option value="mit Patienten"> mit Patienten mata </option>  <option value="mit Angehörigen"> mit Angehörigen</option>  <option value="mit Professionellen"> mit Professionellen</option> </select>';

			var uSelect = '<select id="uSelect'+id+'" name="uSelect[]" style="" class="course_uselect" onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onblur="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');">  '+ window.window_xt_sel_str +' </select>';
			var comment_class = 'course_comment_small';
		} 
		//.	
		else 
		{
			var uSelect = '';
			var comment_class = 'course_comment';

		}
		var module_lysocare_select = '';
		if (shortcut == "LS" || shortcut == "ls") {
			//hardcoded select
			module_lysocare_select = '';
            var module_lysocare_select = '<br/><select id="uCategory'+id+'" name="uCategory[]" style="" class="course_uselect" onchange="appendto(\''+id+'\',this.value);  insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
                <option value="-">----</option>\n\
            	<option value="Terminvereinbarung / Abstimmung Infusionstermin">Terminvereinbarung / Abstimmung Infusionstermin</option>\n\
                <option value="Lieferung">Lieferung</option>\n\
                <option value="Generelle Frage zu Produkt bzw. Therapie">Generelle Frage zu Produkt bzw. Therapie</option>\n\
                <option value="Wohlbefinden">Wohlbefinden</option>\n\
                <option value="Fragen zum Lysocare Vermittlungsservice">Fragen zum Lysocare Vermittlungsservice</option>\n\
                <option value="Veränderungen der Stammdaten">Veränderungen der Stammdaten</option>\n\
                <option value="Versorgungsforschung">Versorgungsforschung</option>\n\
                <option value="Sonstiges">Sonstiges</option>\n\
	            <option value="Anfrage bzw. Organisation der Übernahme der Versorgung">Anfrage bzw. Organisation der Übernahme der Versorgung</option>\n\
	            <option value="Rezeptmanagement">Rezeptmanagement</option>\n\
	            <option value="Einholen von Dokumenten">Einholen von Dokumenten</option>\n\
	            <option value="Update Therapieverlauf">Update Therapieverlauf</option>\n\
            </select>\n\
            <select id="uCategory_2_'+id+'" name="uCategory2[]"  style="" class="course_uselect" onchange="appendto('+id+', this.value); insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
	            <option value="-">----</option>\n\
	            <option value="Inbound">Inbound</option>\n\
	            <option value="Outbound">Outbound</option>\n\
	        </select>\n\
	        <select id="uCategory_3_'+id+'" name="uCategory3[]"  style="" class="course_uselect" onchange="appendto('+id+', this.value); insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
	        	<option value="-">----</option>\n\
	            <option value="eingeschriebener Patient">eingeschriebener Patient</option>\n\
	            <option value="eingeschriebener Arzt">eingeschriebener Arzt</option>\n\
	            <option value="interessierter Patient">interessierter Patient</option>\n\
	            <option value="interessierter Arzt">interessierter Arzt</option>\n\
	            <option value="Apotheke">Apotheke</option>\n\
	            <option value="eingeschriebener Pflegedienst">eingeschriebener Pflegedienst</option>\n\
	            <option value="potenzieller Pflegedienst">potenzieller Pflegedienst</option>\n\
	            <option value="Angehöriger">Angehöriger</option>\n\
	            <option value="Patientenorganisation">Patientenorganisation</option>\n\
	        </select>';
		}
		
		//TODO-3897 Lore 15.03.2021
		var module_wt_select = '';
		if (shortcut == "WT" || shortcut == "wt") {
			//hardcoded select
			module_wt_select = '';
            var module_wt_select = '<br/><select id="uCategory'+id+'" name="uCategory[]" style="" class="course_uselect" onchange="appendto(\''+id+'\',this.value);  insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
                <option value="-">----</option>\n\
            	<option value="Terminvereinbarung">Terminvereinbarung</option>\n\
                <option value="Abstimmung der Lieferung">Abstimmung der Lieferung</option>\n\
                <option value="Generelle Fragen zum Produkt/zur Therapie">Generelle Fragen zum Produkt/zur Therapie</option>\n\
                <option value="Wohlbefinden/Abfrage AZ">Wohlbefinden/Abfrage AZ</option>\n\
                <option value="Veränderung der Stammdaten">Veränderung der Stammdaten</option>\n\
                <option value="Rezeptmanagement">Rezeptmanagement</option>\n\
                <option value="Einholen von Dokumenten ">Einholen von Dokumenten </option>\n\
	            <option value="Update Therapieverlauf">Update Therapieverlauf</option>\n\
	            <option value="Erinnerungsservice">Erinnerungsservice</option>\n\
	            <option value="Einholen von Laborwerten">Einholen von Laborwerten</option>\n\
                <option value="Sonstiges">Sonstiges</option>\n\
            </select>\n\
            <select id="uCategory_2_'+id+'" name="uCategory2[]"  style="" class="course_uselect" onchange="appendto('+id+', this.value); insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
	            <option value="-">----</option>\n\
	            <option value="Inbound">Inbound</option>\n\
	            <option value="Outbound">Outbound</option>\n\
	        </select>\n\
	        <select id="uCategory_3_'+id+'" name="uCategory3[]"  style="" class="course_uselect" onchange="appendto('+id+', this.value); insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >\n\
	        	<option value="-">----</option>\n\
	            <option value="Eingeschriebener Patient ">Eingeschriebener Patient</option>\n\
	            <option value="Eingeschriebener Arzt ">Eingeschriebener Arzt </option>\n\
	            <option value="Interessierter Patient">Interessierter Patient</option>\n\
	            <option value="Interessierter Arzt">Interessierter Arzt</option>\n\
	            <option value="Apotheke">Apotheke</option>\n\
	            <option value="Angehöriger interessierter Patient ">Angehöriger interessierter Patient </option>\n\
	            <option value="Angehöriger eingeschriebener Patient">Angehöriger eingeschriebener Patient</option>\n\
	            <option value="Sonstige">Sonstige</option>\n\
	        </select>';
		}
		//.
		
		
		var newInputs = '<div id="hidd'+id+'" class="hid-div" >\n\
			'+uSelect+'\n\
			<input type="text" id="time'+id+'"class="caret course_time" name="time[]" value="Zeit (in Minuten)" onfocus="if(this.value == \'Zeit (in Minuten)\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Zeit (in Minuten)\' } else { appendto (\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onkeydown="appendto(\''+id+'\',this.value);"/>\n\
			<textarea name="komment[]" id="komment'+id+'" class="'+comment_class+'" onfocus="if(this.value == \'Grund / Anlass\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Grund / Anlass\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >Grund / Anlass</textarea> \n\
			<input type="text" id="date'+id+'" name="date[]" class="caret course_date" value="'+window.window_date+'" onfocus="appendto(\''+id+'\',this.value);"  onblur="if(this.value == \'\'){ this.value=\''+window.window_date+'\' } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\')"   onchange="if(this.value == \'\'){ this.value=\''+window.window_date+'\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
			<input type="text" id="hourtime'+id+'" name="hourtime[]" class="timepick course_timepicker"  value="'+window.window_hourminute+'"   onfocus="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onblur="if(this.value == \'\'){ this.value=\''+window.window_hourminute+'\' }"       onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
			'+module_lysocare_select+module_wt_select+'\n\
		</div>';
		$('#listcoursesession_course_title'+id).append(newInputs);

		$('#komment'+id).elastic();
		
		$('#date' + id).mask("99.99.9999");
		$('#date'+id).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(date) {
				appendto(id,date.value);
				insertsession(id,pid,total_cnt);
				$(this).focus();
				return false;
			}
		});

		$('#hourtime' + id).timepicker({
			onSelect : function(time_value) {
				if($('#date'+id).val()==""){
					$('#date'+id).val(window.window_date);
				}
				appendto(id, time_value);
				insertsession(id, pid, total_cnt);
				$(this).focus();
				return false;
			},
			minutes : {
				interval : 5
			},
			showPeriodLabels : false,
			rows : 4,
			hourText : 'Stunde',
			minuteText : 'Minute'
		});

		$('#course_title' + id).hide();

		$('#listcoursesession_course_title' + id).show();
		appendto(id, window.window_date);
		if (jQuery.inArray(shortcut, ltrjs)) {
			$('#time' + id).focus();
		}

	} 
	else if((shortcut == "PB" || shortcut == "pb")) 
	{
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}

		$("#time"+id).live("keyup input paste", function(){
			setTimeout(jQuery.proxy(function() {
				this.val(this.val().replace(/[^0-9]/g, ''));
			}, $(this)), 0);
		});

		var uSelect = '';
		var comment_class = 'course_comment_pb';

		var newInputs = '<div id="hidd'+id+'" class="hid-div" >\n\
			'+uSelect+'\n\
			<textarea name="komment[]" id="komment'+id+'" class="'+comment_class+'" onfocus="if(this.value == \'Inhalt\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Inhalt\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" >Inhalt</textarea> \n\
			<input type="text" id="date'+id+'" name="date[]" class="caret course_date" value="'+window.window_date+'" onfocus="appendto(\''+id+'\',this.value);"  onblur="if(this.value == \'\'){ this.value=\''+window.window_date+'\' }"   change="if(this.value == \'\'){ this.value=\'Date\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
			<input type="text" id="hourtime'+id+'" name="hourtime[]" class="timepick course_timepicker"  value="'+window.window_hourminute+'"    onblur="if(this.value == \'\'){ this.value=\''+window.window_hourminute+'\' }"       onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
		</div>';
		$('#listcoursesession_course_title'+id).append(newInputs);

		$('#komment'+id).elastic();
		$('#date'+id).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(date) {
				appendto(id,date.value);
				insertsession(id,pid,total_cnt);
				$(this).focus();
				return false;
			}
		});

		$('#hourtime' + id).timepicker({
			onSelect : function(time_value) {
				if($('#date'+id).val()==""){
					$('#date'+id).val(window.window_date);
				}
				appendto(id, time_value);
				insertsession(id, pid, total_cnt);
				$(this).focus();
				return false;
			},
			minutes : {
				interval : 5
			},
			showPeriodLabels : false,
			rows : 4,
			hourText : 'Stunde',
			minuteText : 'Minute'
		});

		$('#course_title' + id).hide();

		$('#listcoursesession_course_title' + id).show();
		appendto(id, window.window_date);
		if (jQuery.inArray(shortcut, ltrjs)) {
			$('#time' + id).focus();
		}

	}
	else if((shortcut == "KX" || shortcut == "kx")) 
	{
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}

		$("#time"+id).live("keyup input paste", function(){
			setTimeout(jQuery.proxy(function() {
				this.val(this.val().replace(/[^0-9]/g, ''));
			}, $(this)), 0);
		});

		var uSelect = '';
		var comment_class = 'course_comment_pb';

		var newInputs = '<div id="hidd'+id+'" class="hid-div" >\n\
			'+uSelect+'\n\
			<textarea name="komment[]" id="komment'+id+'" class="'+comment_class+'" onfocus="if(this.value == \'\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" ></textarea> \n\
			<input type="text" id="date'+id+'" name="date[]" class="caret course_date" value="'+window.window_date+'" onfocus="appendto(\''+id+'\',this.value);"  onblur="if(this.value == \'\'){ this.value=\''+window.window_date+'\' }"   change="if(this.value == \'\'){ this.value=\'Date\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
			<input type="text" id="hourtime'+id+'" name="hourtime[]" class="timepick course_timepicker"  value="'+window.window_hourminute+'"    onblur="if(this.value == \'\'){ this.value=\''+window.window_hourminute+'\' }"       onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>\n\
		</div>';
		$('#listcoursesession_course_title'+id).append(newInputs);

		$('#komment'+id).elastic();
		$('#date'+id).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(date) {
				appendto(id,date.value);
				insertsession(id,pid,total_cnt);
				$(this).focus();
				return false;
			}
		});

		$('#hourtime' + id).timepicker({
			onSelect : function(time_value) {
				if($('#date'+id).val()==""){
					$('#date'+id).val(window.window_date);
				}
				appendto(id, time_value);
				insertsession(id, pid, total_cnt);
				$(this).focus();
				return false;
			},
			minutes : {
				interval : 5
			},
			showPeriodLabels : false,
			rows : 4,
			hourText : 'Stunde',
			minuteText : 'Minute'
		});

		$('#course_title' + id).hide();

		$('#listcoursesession_course_title' + id).show();
		appendto(id, window.window_date);
		if (jQuery.inArray(shortcut, ltrjs)) {
			$('#time' + id).focus();
		}

	}
	else if (shortcut == "D" || shortcut == "H" || shortcut == "d" || shortcut == "h" || ((shortcut == "HS" || shortcut == "hs") && has_hs_fields === false) ) 
	{ //Diagnosis and Hauptdiagnosen
		
		if ($('#hidd' + id).length > 0) 
		{
			$('#hidd' + id).hide().remove();
			$('#course_title' + id).val('');
		}
		
		var $error_div = '';
		if(shortcut == "HS" || shortcut == "hs")
		{
			if(window.$disable_hs_insert == '1') {
				var $error_div = '<div id="error' + id + '" class="verlauf_error_div" style="display:block;clear: both;">' + translate('hs_limit_patient_reached') + '</div>';
			} else {
				var $error_div = '<div id="error' + id + '" class="verlauf_error_div" style="display:none;clear: both;">' + translate('hs_limit_patient_reached')+ '</div>';
			}
		}
		
		newInputs = '<div id="hidd'+id+'" class="hid-div" ><input name="icdnumber['
				+ id
				+ ']" id="icdnumber'
				+ id
				+ '" class="livesearchicdinp caret" value="ICD"  style="width:45px;" type="text" onfocus="if(this.value == \'ICD\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'ICD\' } else { appendto(\''
				+ id
				+ '\',this.value) } insertsession(\''
				+ id
				+ '\',\''
				+ pid
				+ '\',\''
				+ total_cnt
				+ '\');" /><div id="icddiagnodropdown'+id+'"  class="icdlivesearchdropdown" style="position: absolute; display:none;"></div><input name="diagnosis['
				+ id
				+ ']" id="diagnosis'
				+ id
				+ '" value="Beschreibung" size="33" class="livesearchinp caret course_diag_name" type="text"  onfocus="if(this.value == \'Beschreibung\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Beschreibung\' } else { appendto(\''
				+ id
				+ '\',this.value) } insertsession(\''
				+ id
				+ '\',\''
				+ pid
				+ '\',\''
				+ total_cnt
				+ '\');" />     <input name="hidd_icdnumber['+id+']" value="" id="hidd_icdnumber'+id+'" type="hidden"> <input name="hidd_diagnosis['+id+']" value="" id="hidd_diagnosis'+id+'" type="hidden"><input name="hidd_tab['+id+']" value="" id="hidd_tab" type="hidden"></div> <div id="diagnodropdown'+id+'" class="samtablistDiognoDrp livesearchdropdown" style="position: absolute; display:none;"></div>'+$error_div+'';

		$('#listcoursesession_course_title' + id).append(newInputs);
		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).show();
		$('#icdnumber'+id).focus();

		//new version
		$('.livesearchicdinp').live('change', function() {
			var input_row = parseInt($(this).attr('id').substr(('icdnumber').length));
					reset_diagnosis(input_row);
		}).liveSearch({
					url : 'ajax/diagnosis?mode=icdnumber&q=',
					id : 'livesearch_admission_diagnosis',
					aditionalWidth : '560',
					noResultsDelay : '900',
					typeDelay : '900',
					returnRowId : function(input) {
				return parseInt($(input).attr('id').substr(('icdnumber').length));
					}
				});

		//livesearch diagnosis description ls
		$('.livesearchinp').live('change', function() {
			var input_row = parseInt($(this).attr('id').substr(('diagnosis').length));
					reset_diagnosis(input_row);
		}).liveSearch({
					url : 'ajax/diagnosis?q=',
					id : 'livesearch_admission_diagnosis',
					aditionalWidth : '0',
					noResultsDelay : '900',
					typeDelay : '900',
					returnRowId : function(input) {
				return parseInt($(input).attr('id').substr(('diagnosis').length));
					}
				});

		var has_hs_fields = true; //remove shortcut if user is trying to add more than one hs entry
		
		
	} 
	else if ((shortcut == "HS" || shortcut == "hs") && has_hs_fields === true) 
	{
		$('#course_type' + id).val('').focus();
		$('.verlauf_error_div').show();
		$('#course_title' + id).show();
		$('#hidd' + id).hide().remove();
		$('#course_title' + id).val('');
		
	} 
	else if (shortcut == "AL" && module_al == "1"){
				
		if ($('#hidd' + id).length > 0) 
		{
			$('#hidd' + id).hide().remove();
			$('#course_title' + id).val('');
		}
		
		newInputs = '<div id="hidd'+id+'" class="hid-div" >';
		newInputs +='<input name="actionid['+ id+ ']" id="actionid'+id+'" class="live_search_al_action_id caret" value="ID"  style="width:45px;" type="text" onfocus="if(this.value == \'ID\') { this.value=\'\'}" 	onblur="if(this.value == \'\'){ this.value=\'ID\' } else { appendto(\''+ id+ '\',this.value) } trigger_insertsession(\''+ id+ '\',\''+ pid+ '\',\''+ total_cnt+ '\');"  autocomplete="off" />';
		newInputs +='<input name="description['+ id+ ']" id="description'+ id+ '" value="Beschreibung" style="width:450px!important" class="live_search_al_description caret course_action_name" type="text"  onfocus="if(this.value == \'Beschreibung\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Beschreibung\' } else { appendto(\''+ id+ '\',this.value) } trigger_insertsession(\''+ id+ '\',\''	+ pid + '\',\''	+ total_cnt	+ '\');" />';
		newInputs +='<input name="date[]" id="al_date'+id+'" style="float: left; height: 17px; width: 65px;" type="text" class="caret al_course_date" value="'+window.window_date+'" onblur="if(this.value == \'\'){ this.value=\''+window.window_date+'\' }"   onchange="if(this.value == \'\'){ this.value=\''+window.window_date+'\' } else { appendto(\''+id+'\',this.value) } trigger_insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>';
					
		newInputs +='<input type="hidden" name="hidd_actionid['+ id+ ']" id="hidd_actionid'+id+'" value="" />';
		newInputs +='<input type="hidden" name="hidd_description['+ id+ ']" id="hidd_description'+ id+ '" value="" />';
		newInputs +='<input type="hidden" name="hidd_date['+ id+ ']" id="hidd_date'+id+'" value="'+window.window_date+'" style="height:16px;" type="text" />';
		newInputs +='</div>';
		
		
		$('#listcoursesession_course_title' + id).append(newInputs);
		
		datapicker_al(id);
		liveSearch_al();
		
		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).show();
		$('#actionid'+id).focus();

		
	}
	else if (shortcut == "LE" && module_le == "1") 
  	{ //XBDT ACTIONS
		
		if ($('#hidd' + id).length > 0) 
		{
			$('#hidd' + id).hide().remove();
			$('#course_title' + id).val('');
		}
  	
		var le_users_select = '<select name="actionuser['+id+']" id="actionuser'+id+'" onblur="appendto('+id+',this.value);" onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" style="width:120px; text-align:left; float:left;" class="caret">' + window.window_users_le_le_sel_str + '</select>';
		
		
		newInputs = '<div id="hidd'+id+'" class="hid-div" >';
		newInputs +='<input name="actionid['+ id+ ']" id="actionid'+id+'" class="live_search_action_id caret" value="ID"  style="width:65px;" type="text" onfocus="if(this.value == \'ID\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'ID\' } else { appendto(\''+ id+ '\',this.value) } insertsession(\''+ id+ '\',\''+ pid+ '\',\''+ total_cnt+ '\');"  autocomplete="off" />';
//			newInputs +='<div id="icddiagnodropdown'+id+'"  class="icdlivesearchdropdown" style="position: absolute; display:none;"></div>';
		newInputs +='<div id="action_le_livesearch'+id+'"  class="action_le_livesearch" style="position: absolute; display:none;"></div>';
		newInputs +='<input name="actionname['+ id+ ']" id="actionname'+ id+ '" value="Leistungen" size="33" class="live_search_action_name caret course_action_name" type="text"  onfocus="if(this.value == \'Leistungen\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Leistungen\' } else { appendto(\''+ id+ '\',this.value) } insertsession(\''+ id+ '\',\''	+ pid + '\',\''	+ total_cnt	+ '\');" />';
		newInputs +='<input name="hidd_actionid['+id+']" value="" id="hidd_actionid'+id+'" type="hidden">';
		newInputs +='<input name="hidd_actionname['+id+']" value="" id="hidd_actionname'+id+'" type="hidden">';
		
		//user select 
		newInputs += le_users_select;
		// date field
		newInputs +='<input type="text" id="date'+id+'" name="date[]" class="caret course_date" value="'+window.window_date+'" onfocus="appendto(\''+id+'\',this.value);"  onblur="if(this.value == \'\'){ this.value=\''+window.window_date+'\' } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\')"   onchange="if(this.value == \'\'){ this.value=\''+window.window_date+'\' } else { appendto(\''+id+'\',this.value) } insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>';
		newInputs +='<input type="text" id="hourtime'+id+'" name="hourtime[]" class="timepick course_timepicker"  value="'+window.window_hourminute+'"   onfocus="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');" onblur="if(this.value == \'\'){ this.value=\''+window.window_hourminute+'\' }"       onchange="insertsession(\''+id+'\',\''+pid+'\',\''+total_cnt+'\');"/>';
		
		newInputs +='</div>';
//			newInputs +='<div id="diagnodropdown'+id+'" class="samtablistDiognoDrp livesearchdropdown" style="position: absolute; display:none;"></div>';
		
		$('#listcoursesession_course_title' + id).append(newInputs);
		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).show();
		$('#actionid'+id).focus();

		$('.live_search_action_id').live('change', function() {
			var input_row = parseInt($(this).attr('id').substr(('actionid').length));
			reset_actions(input_row);
		}).liveSearch({
			url : 'ajax/xbdtactions?mode=action_id&q=',
			id : 'livesearch_admission_diagnosis',
			aditionalWidth : '560',
			noResultsDelay : '900',
			typeDelay : '900',
			returnRowId : function(input) {
				return parseInt($(input).attr('id').substr(('actionid').length));
			}
		});
		
		$('.live_search_action_name').live('change', function() {
			var input_row = parseInt($(this).attr('id').substr(('actionname').length));
			reset_actions(input_row);
		}).liveSearch({
			url : 'ajax/xbdtactions?q=',
			id : 'livesearch_admission_diagnosis',
			aditionalWidth : '0',
			noResultsDelay : '900',
			typeDelay : '900',
			returnRowId : function(input) {
				return parseInt($(input).attr('id').substr(('actionname').length));
			}
		});
		
		$('#date' + id).mask("99.99.9999");
		$('#date'+id).datepicker({
			dateFormat: 'dd.mm.yy',
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: '',
			onSelect: function(date) {
				appendto(id,date.value);
				insertsession(id,pid,total_cnt);
				$(this).focus();
				return false;
			}
		});

		$('#hourtime' + id).timepicker({
			onSelect : function(time_value) {
				if($('#date'+id).val()==""){
					$('#date'+id).val(window.window_date);
				}
				appendto(id, time_value);
				insertsession(id, pid, total_cnt);
				$(this).focus();
				return false;
			},
			minutes : {
				interval : 5
			},
			showPeriodLabels : false,
			rows : 4,
			hourText : 'Stunde',
			minuteText : 'Minute'
		});

		$('#course_title' + id).hide();

		$('#listcoursesession_course_title' + id).show();
		

		
	}
	else if (shortcut == "P" || shortcut == "p") 
	{
		$('#hidd' + id).remove();

		var medications_list = window.window_medications_list;

		//create medication dropdown!
		var dropdown_list = '<select id="medications-' + id
				+ '" class="medications_dd" rel="' + id
				+ '" name="verlauf_edit[' + idminus
				+ ']" onchange="load_medi($(this)); insertsession(\'' + id
				+ '\',\'' + pid + '\',\'' + total_cnt + '\');">';

		$.each(medications_list, function(i, item) {

			if (i == '999999999') {
				dropdown_list += '<option value="0">' + item + '</option>';
			} else {
				dropdown_list += '<optgroup label="'+jsTranslate[i]+'">';
				$.each(item, function(key, value) {
					dropdown_list += '<option value="'+key+'">' + value
							+ '</option>';
				});
				dropdown_list += '</optgroup>';
			}
		});
		dropdown_list += '</select>';

		newInputs = '<div id="hidd'+id+'" class="hid-div" >'
				+ dropdown_list
				+ '<label for="delete'+id+'" style="float:none;padding-left:2px;"><input type="checkbox" id="delete'+id+'" name="delete[]" value="1" style="float:none; margin-top:3px;" />absetzen</label><br />';
				+ '<div id="medication-content-'+id+'" style="display:none;">';
		newInputs += '<input type="hidden" id="medi_id'+id+'" name="id_medication['+idminus+']" value=""/>';
		newInputs += '<input type="hidden" id="drid'+id+'" name="drid['+idminus+']" value=""/>';
		newInputs += '<input type="text" id="medi_change'+id+'"  class="med_data" name="medication_change['+idminus+']" value="" style="width:62px; text-align:left; float:left; padding:5px; margin-right:3px;" />';
		newInputs += '<input type="hidden" id="medi_replace'+id+'"  name="replace_with['+idminus+']" value=""/>';
		/*newInputs += '<input type="text" id="medi_name'
				+ id
				+ '" name="medication['
				+ idminus
				+ ']" class="course_medi_name_edit" value="" onblur="insertsession(\''
				+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';
		newInputs += '<input type="text" id="dosage'
				+ id
				+ '" name="dosage['
				+ idminus
				+ ']" value="" style="width:80px; text-align:left; padding:5px; margin-left:5px; float:left;" onblur="insertsession(\''
				+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';
		newInputs += '<input type="text" id="comments'
				+ id
				+ '" name="comments['
				+ idminus
				+ ']" value="" style="width:150px; text-align:left; padding:5px; margin-left:5px; float:left;" onblur="insertsession(\''
				+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';*/
		newInputs += '<input type="text" id="medi_name'
			+ id
			+ '" name="medication['
			+ idminus
			+ ']" class="course_medi_name_edit" placeholder="Name" value="" onblur="insertsession(\''
			+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';
		newInputs += '<input type="text" id="dosage'
			+ id
			+ '" name="dosage['
			+ idminus
			+ ']" placeholder="Dosierung" value="" style="width:80px; text-align:left; padding:5px; margin-left:5px; float:left;" onblur="insertsession(\''
			+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';
		newInputs += '<input type="text" id="comments'
			+ id
			+ '" name="comments['
			+ idminus
			+ ']" placeholder="Kommentar" value="" style="width:150px; text-align:left; padding:5px; margin-left:5px; float:left;" onblur="insertsession(\''
			+ id + '\',\'' + pid + '\',\'' + total_cnt + '\');" />';
				/*
		newInputs += '<label for="delete'+id+'" style="line-height: 32px; margin-left: 3px;">absetzen</label><input type="checkbox" id="delete'+id+'" name="delete[]" value="1" style="float:left; margin-left: 5px;line-height: 26px;margin-top: 10px;" /><br />';
		*/
		newInputs += '</div></div>';

		$('#listcoursesession_course_title' + id).append(newInputs);

		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).show();
	} 
	else if (shortcut == "S" || shortcut == "s") 
	{
		$('#hidd' + id).remove();

		var symptoms = window.window_symptoms;
		var symptoms_select = window.window_symptoms_select;

		//create sym dropdown!
		var dropdown_list = '<table class="datatable" id="verlauf">';
		$.each(symptoms, function(i, item) {
							if (i != '999999999') {
				dropdown_list += '<tr><td colspan="3"><b>' + i + '</b></td></tr>';
				$.each(item, function(key, value) {
					dropdown_list += '<tr>';
					dropdown_list += '<td>';
					dropdown_list += '<span>'
							+ value
							+ '</span>';
					dropdown_list += '<input type="hidden" value="'+key+'" id="symptom-'+id+'" name="symptom['+idminus+'][]" />';
					dropdown_list += '</td>';
					dropdown_list += '<td>';
					if (symptoms_select == "a") {
						dropdown_list += '<select  id="sym_value'+key+'-'+id+'" name="sym_value['+idminus+'][]"  class="sym_val" ><option value=""></option><option value="0">kein</option><option value="4">leicht</option><option value="7">mittel</option><option value="10">schwer</option> </select>';
					} else {
						dropdown_list += '<input type="text" id="sym_value'
								+ key
								+ '-'
								+ id
								+ '" name="sym_value['
								+ idminus
								+ '][]" value="" class="sym_val" onkeyup="isInteger(\''
								+ key
								+ '-'
								+ id
								+ '\')" style="width:25px; margin-left:5px; text-align:left; float:left; padding: auto;" />';
					}

					dropdown_list += '</td>';
					dropdown_list += '<td>';
					dropdown_list += '<input type="text" id="sym_coment'+id+'" name="sym_coment['+idminus+'][]" value="" class="sym_coment" style="width:235px; margin-left:5px; text-align:left; float:left; padding: auto;" />';
					dropdown_list += '</td>';
					dropdown_list += '</tr>';
				});
							}
						});
		dropdown_list += '</table>';

		newInputs = '<div id="hidd'+id+'" class="hid-div" >' + dropdown_list + '';
		newInputs += '</div>';

		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).append(newInputs);
		$('#listcoursesession_course_title' + id).show();

	} 
	else if (shortcut == "SB" || shortcut == "sb") 
	{ //  Sanitatshausbestellung -> medical supply order 

		if ($('#hidd' + id).length > 0) 
		{
			$('#hidd' + id).hide().remove();
			$('#course_title' + id).val('');
		}

		var sb_usersSelect = '<select name="sb_user'
				+ id
				+ '" id="sb_user'
				+ id
				+ '" onblur="appendto('
				+ id
				+ ',this.value);" onchange="insertsession(\''
				+ id
				+ '\',\''
				+ pid
				+ '\',\''
				+ total_cnt
				+ '\');" style="width:150px; text-align:left; float:left;" class="caret">'+ window.window_sel_sb_str + '</select>';
		newInputs = '<div id="hidd'+id+'" class="hid-div" >'
				+ sb_usersSelect
				+ '<textarea id="sb_order'
				+ id
				+ '"class="caret course_sb_order" name="sb_order[]" value="Sanitätshausbestellung" onfocus="if(this.value == \'Sanitätshausbestellung\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Sanitätshausbestellung\' } else { appendto(\''
				+ id + '\',this.value) } insertsession(\'' + id + '\',\''
				+ pid + '\',\'' + total_cnt
				+ '\');">Sanitätshausbestellung</textarea></div>';

		$('#listcoursesession_course_title' + id).append(newInputs);

		$('#sb_order' + id).elastic();

		$('#course_title' + id).hide();

		$('#listcoursesession_course_title' + id).show();

		appendto(id, window.window_date);

		if (jQuery.inArray(shortcut, ltrjs)) 
		{
			$('#sb_order' + id).focus();
		}
		
	}
	else if(shortcut == "L" || shortcut == "l") 
	{
		if($('#hidd'+id).length > 0)
		{
			$('#hidd'+id).hide().remove();
			$('#course_title'+id).val('');
		}
		
		newInputs = ' <div id="hidd'+id+'" class="hid-div" ><textarea name="services['+ id+ ']" id="lrownumber'+ id+ '" class="serviceslivesearvh caret course_sb_order" style=" margin: 3px 0; display: block;   height: 26px;   overflow: hidden;" onfocus="if(this.value == \'Leistungen\') { this.value=\'\'}" onblur="if(this.value == \'\'){ this.value=\'Leistungen\' } else { appendto(\''+ id	+ '\',this.value) } insertsession(\''+ id+ '\',\''	+ pid	+ '\',\''+ total_cnt+ '\');" >Leistungen</textarea></div>';
       
//			$('#lrownumber'+id).elastic();
		
		$('#course_title' + id).hide();
		$('#course_title' + id).hide();
		$('#listcoursesession_course_title' + id).append(newInputs);
		$('#listcoursesession_course_title' + id).show();
		$('#lrownumber'+id).elastic(); // TODO-1595< line moved under append
		//new version
		if(moduleprivl == "1")
 		{
     		$('.serviceslivesearvh').live('change', function() {
	 			var input_row = parseInt($(this).attr('id').substr(('lrownumber').length));
   			//reset_diagnosis(input_row);
     		}).liveSearch({
	 		url : 'ajax/services?q=',
	 		id : 'livesearch_services',
	 		aditionalWidth : '560',
	 		noResultsDelay : '900',
	 		typeDelay : '900',
	 		returnRowId : function(input) {
	        	return parseInt($(input).attr('id').substr(('lrownumber').length));
	   		}
     		});
 		}
		
	}
	else 
	{
		$('#course_title' + id).show();
		$('#hidd' + id).hide().remove();
		$('#course_title' + id).val('');
	}
	
	
	//disable write untill you select the shortcut
    if (shortcut == null || shortcut == '') {
        $(_this).parents('div.shortcutRow')
        .find('textarea.defaultTextarea').attr('disabled', true).attr('placeholder', translate("Please select action first"));
    } else {
        $(_this).parents('div.shortcutRow')
        .find('textarea.defaultTextarea').attr('disabled', false).attr('placeholder', '').end()
        .find('.RightList01 input[type=text], .RightList01 textarea').filter(':input:visible:first').focus();
        
        //TODO-2376
        $(_this).parents('div.shortcutRow').addClass(shortcut);
        
        //TODO-2376
        $(_this).parents('div.ListOuter01').addClass('shortcutRow '+shortcut);
//        $(_this).parents('div.ListOuter01').className('ListOuter01 addComment '+shortcut);
//        $(_this).parents('div.ListOuter01').removeAttribute('class');
//        $(_this).parents('div.ListOuter01').addClass('ListOuter01 addComment '+shortcut);
    }
}

function reset_actions(input_row) {
	if ($('#hidd_tab' + input_row).val() != 'text') {
		$('#hidd_actionid' + input_row).val('');
		$('#hidd_actionname' + input_row).val('');
	}
}
function select_actions(did, row) {
	$('#actionid' + row).val($('#action_id_' + did).val());
	$('#actionname' + row).val($('#action_name_' + did).val());
	$('#hidd_actionid' + row).val($('#action_row_id_' + did).val());
	$('#hidd_actionname' + row).val($('#action_row_id_' + did).val());
	$('#actionid' + row).blur(); //why you no lost focus?
	$('#actionname' + row).focus();
}

function reset_diagnosis(input_row) {
	if ($('#hidd_tab' + input_row).val() != 'text') {
		$('#hidd_icdnumber' + input_row).val('');
		$('#hidd_diagnosis' + input_row).val('');
		$('#hidd_tab' + input_row).val('text');
	}
}
function selectDiagnosis(did, row) {
	$('#icdnumber' + row).val($('#diag_icd_' + did).val());
	$('#diagnosis' + row).val($('#diag_de_' + did).val());
	$('#hidd_icdnumber' + row).val($('#diag_id_' + did).val());
	$('#hidd_diagnosis' + row).val($('#diag_id_' + did).val());
	$('#hidd_tab' + row).val('dig');
	$('#icdnumber' + row).blur(); //why you no lost focus?
	$('#diagnosis' + row).focus();
}

function selectServices(did, row) {
	$('#lrownumber' + row).val($('#diag_icd_' + did).val());
//		$('#diagnosis' + row).val($('#diag_de_' + did).val());
	$('#hidd_icdnumber' + row).val($('#diag_id_' + did).val());
//	$('#hidd_diagnosis' + row).val($('#diag_id_' + did).val());
//		$('#hidd_tab' + row).val('dig');
//		$('#icdnumber' + row).blur(); //why you no lost focus?
	$('#lrownumber' + row).focus();
}

function checkFields(formname) {
	$('.caret').each(function() {
		if ($(this).val() != "Name" && $(this).val != "Kommentar" && $(this).val() != "Dosierung" && $(this).val() != "TODO" && $(this).val() != "Beschreibung") {
					if (checkdischargednew(formname)) {
				var submitstr = "document." + formname + ".submit()";
						eval(submitstr);
					}
				} else {
			jConfirm('Empty fields, do you want to continue', '', function(r) {
								if (r && checkdischargednew(formname)) {
					var submitstr = "document." + formname + ".submit()";
									eval(submitstr);
									//return true;
								}
							});
					return false;
				}
			});
	return false;
}

function closediaearch(ele) {
	id = $(ele).attr('id');

	$('#diagnodropdown' + id).hide();
	$('#icddiagnodropdown' + id).hide();
	$('.focused').removeClass('focused');
}

$(document).ready(function() {
	var pid = $('#patientid').val() || window.idpd;

	
	
	/*
	$("[id^=user]").live('change',function(){
		var row_id = $(this).attr('rel');
		
		
		var items = [];
		$('#user'+row_id+' option:selected').each(function(){ items.push($(this).text()); });
		var result = items.join('; ');
		$('#selected'+row_id).val(result);
		
	})
	*/
	
	// USER Filter settings
	
	if (typeof isMobileVersion == 'undefined' || isMobileVersion != 1)
	{
		if(filterkeysjs.length > 0){
			$.each(filterkeysjs,function(i, item) {
				if($('input[name='+item+']').is(':checked')) {
					$('input[name='+item+']').removeAttr('checked');
				} else {
					$('input[name='+item+']').attr('checked','checked');
				}
				
			$('.verlauf_li_filter').addClass("filter_available");
			
			$('.filter_display_img').hide();
			checkboxurl(document.getElementById($('input[name='+item+']').attr('id')));
			});
		} else{
			$('.verlauf_filter_apply').hide();
			$('.verlauf_filter_remove').hide();
		}
	
	}
	
	//calls for elastic, datepicker, timepicker
	$('.course_comment').elastic();

	$('.course_date').mask("99.99.9999");
	$('.course_date').datepicker({
			dateFormat : 'dd.mm.yy',
			showOn : "both",
			buttonImage : $('#calImg').attr('src'),
			buttonImageOnly : true,
			changeMonth : true,
			changeYear : true,
			nextText : '',
			prevText : '',
			onSelect : function(date) {
			appendto($(this).attr('id').replace("date", ""), $(this).val());
			insertsession($(this).attr('id').replace("date", ""), pid, $('.LeftList01').length);
			$(this).focus();
			return false;
		}
	});
	$('.course_todo_date').mask("99.99.9999");
	$('.course_todo_date').datepicker({
			dateFormat : 'dd.mm.yy',
			showOn : "both",
			buttonImage : $('#calImg').attr('src'),
			buttonImageOnly : true,
			changeMonth : true,
			changeYear : true,
			nextText : '',
			prevText : '',
			onSelect : function(date) {
			appendto($(this).attr('id').replace("till", ""), $(this).val());
			insertsession($(this).attr('id').replace("till", ""), pid, $('.LeftList01').length);
			$(this).focus();
			return false;
		}
	});
	$('.course_timepicker').timepicker({
		onSelect : function(time_value) {

			if($('#date'+$(this).attr('id')).val()==""){
				$('#date'+$(this).attr('id')).val(window.window_date);
			}
			appendto($(this).attr('id').replace("hourtime",""), $(this).val());
			insertsession($(this).attr('id').replace("hourtime",""),pid,$('.LeftList01').length);
				$(this).focus();
				return false;
			},
			minutes : {
				interval : 5
			},
			showPeriodLabels : false,
			rows : 4,
			hourText : 'Stunde',
			minuteText : 'Minute'
		});

	$('.livesearchicdinp').bind('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('icdnumber').length));
		reset_diagnosis(input_row);
	}).liveSearch({
			url : 'ajax/diagnosis?mode=icdnumber&q=',
			id : 'livesearch_admission_diagnosis',
			aditionalWidth : '560',
			noResultsDelay : '900',
			typeDelay : '900',
			returnRowId : function(input) {
			return parseInt($(input).attr('id').substr(('icdnumber').length));
		}
	});

	//livesearch diagnosis description ls
	$('.livesearchinp').live('change', function() {
				var input_row = parseInt($(this).attr('id').substr(('diagnosis').length));
				reset_diagnosis(input_row);
			}).liveSearch({
				url : 'ajax/diagnosis?q=',
				id : 'livesearch_admission_diagnosis',
				aditionalWidth : '0',
				noResultsDelay : '900',
				typeDelay : '900',
				returnRowId : function(input) {
					return parseInt($(input).attr('id').substr(('diagnosis').length));
				}
			});

	if(moduleprivl == "1")
    {
	    $('.serviceslivesearvh').bind('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('lrownumber').length));
		//reset_diagnosis(input_row);
	    }).liveSearch({
		url : 'ajax/services?q=',
		id : 'livesearch_services',
		aditionalWidth : '560',
		noResultsDelay : '900',
		typeDelay : '900',
		returnRowId : function(input) {
			return parseInt($(input).attr('id').substr(('lrownumber').length));
		   }
	    });
      }

	if(module_al == "1"){
		
		liveSearch_al();
		$('.al_course_date').each(function(){
			datapicker_al(parseInt($(this).attr('id').substr(('al_date').length)));
		});	
		
	}
	if(module_le == "1")
	{

	$('.live_search_action_id').bind('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('actionid').length));
		reset_actions(input_row);
	}).liveSearch({
		url : 'ajax/xbdtactions?mode=action_id&q=',
		id : 'livesearch_admission_diagnosis',
		aditionalWidth : '560',
		noResultsDelay : '900',
		typeDelay : '900',
		returnRowId : function(input) {
			return parseInt($(input).attr('id').substr(('actionid').length));
		}
	});

	//livesearch actions name
	$('.live_search_action_name').bind('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('actionname').length));
		reset_actions(input_row);
	}).liveSearch({
		url : 'ajax/xbdtactions?q=',
		id : 'livesearch_admission_diagnosis',
		aditionalWidth : '0',
		noResultsDelay : '900',
		typeDelay : '900',
		returnRowId : function(input) {
			return parseInt($(input).attr('id').substr(('actionname').length));
		}
	});
		
	}
	
	
	
					// new display for filters
					//top
					$('.filter_block').hide();
					$('#filter_block_int').val('0');

					$('#verlauf_filter_options').click(function() {
						if ($('#filter_block_int').val() == '0') {
							$('.filter_block').show();
							$('#filter_block_int').val('1');
						} else {
							$('.filter_block').hide();
							$('#filter_block_int').val('0');
						}
					});

	
	// New filter settings -TOP
	$('#filter_remove').val('0');
	$('#filter_apply').val('0');
	$('#verlauf_filter_apply').hide();
	
	$('#verlauf_filter_remove').click(function() {
		if ($('#filter_remove').val() == '0') {
			$('#filter_remove').val('1');
			$('#filter_apply').val('0');
			
			$('.verlauf_filter_remove').hide();
			$('.verlauf_filter_apply').show();
			
			$('.verlauf_li_filter').removeClass("filter_available");
			$.each(filterkeysjs,function(i, item) {
				if($('input[name='+item+']').is(':checked')) {
					$('input[name='+item+']').removeAttr('checked');
				} 
			checkboxurl(document.getElementById($('input[name='+item+']').attr('id')));
			});
		}
	});
	
	$('#verlauf_filter_apply').click(function() {
		if ($('#filter_apply').val() == '0') {
			$('#filter_apply').val('1');
			$('#filter_remove').val('0');
			
			$('.verlauf_filter_remove').show();
			$('.verlauf_filter_apply').hide();
			
			
			$('.verlauf_li_filter').addClass("filter_available");
			$.each(filterkeysjs,function(i, item) {
				if($('input[name='+item+']').is(':checked')) {
					$('input[name='+item+']').removeAttr('checked');
				} else {
					$('input[name='+item+']').attr('checked','checked');
				}
			checkboxurl(document.getElementById($('input[name='+item+']').attr('id')));
			});
		}
	});
	
	
	
	
					//bottom
					$('.filter_block_bt').hide();
					$('#filter_block_bt_int').val('0');

					$('#verlauf_filter_options_bottom').click(function() {
						if ($('#filter_block_bt_int').val() == '0') {
							$('.filter_block_bt').show();
							$('#filter_block_bt_int').val('1');
						} else {
							$('.filter_block_bt').hide();
							$('#filter_block_bt_int').val('0');
						}

					});


	
	
	// New filter settings -BOTTOM
	$('#filter_remove_b').val('0');
	$('#filter_apply_b').val('0');
	$('#verlauf_filter_apply_bottom').hide();
	
	$('#verlauf_filter_remove_bottom').click(function() {
		
		if ($('#filter_remove_b').val() == '0') {
			$('#filter_remove_b').val('1');
			$('#filter_apply_b').val('0');
			
			$('.verlauf_filter_remove').hide();
			$('.verlauf_filter_apply').show();
			
			$('.verlauf_li_filter').removeClass("filter_available");
			$.each(filterkeysjs,function(i, item) {
				if($('input[name='+item+']').is(':checked')) {
					$('input[name='+item+']').removeAttr('checked');
				} 
			checkboxurl(document.getElementById($('input[name='+item+']').attr('id')));
			});
		}
	});
	
	
	$('#verlauf_filter_apply_bottom').click(function() {
		if ($('#filter_apply_b').val() == '0') {
			$('#filter_apply_b').val('1');
			$('#filter_remove_b').val('0');
			
			$('.verlauf_filter_remove').show();
			$('.verlauf_filter_apply').hide();
			
			
			$('.verlauf_li_filter').addClass("filter_available");
			$.each(filterkeysjs,function(i, item) {
				if($('input[name='+item+']').is(':checked')) {
					$('input[name='+item+']').removeAttr('checked');
				} else {
					$('input[name='+item+']').attr('checked','checked');
				}
			checkboxurl(document.getElementById($('input[name='+item+']').attr('id')));
			});
		}
	});

					//ajax loader facebook
	jQuery.ias({
		container : '#ListBox',
		history : false,
		item : '.master_row',
		pagination : 'div.navigation',
		next : 'div.navigation a.next_link_button',
		loader : '<div class="row clearer parentdiv iefirst" id="maindiv_loading" style="width: 100%; text-align:center;">' + translate('fb_verlauf_loading') + '</div>',
		onRenderComplete : function(items) {
			//here we filter verlauf data

			//1. get checked shortcuts
			var selected_shortcuts = new Array();

			$('.listcoursechecks_main_div input:checked').each(function() {
				check2();
			});
		}
	});

	$('#modal_delete').dialog({
		autoOpen : false,
		modal : true,
		resizable : false,
		width : 500,
		buttons : [
		           {
		        	   text :translate('yesconfirm'),
			click: function() {
				switch ($('#modal_redir_type').val()) {
				case 'D':
					window.location = appbase + 'patient/patdiagnoedit?id=' + window.idpd;
					break;
					case 'LE':
						window.location = appbase + 'patientformnew/xbdtactions?id=' + window.idpd;
						break;
				case 'M':
					window.location = appbase + 'patient/patientmedication?id=' + window.idpd;
					break;

				default:
					break;
				}
				$(this).dialog("close");
			}
		           },
		           {
		        	   text: translate('noconfirm'),
			click : function() {
				$(this).dialog("close");
			}
			}
		],
		close : function() {
			//reset message divs on close
			$('#modal_delete p').each(function() {
						$(this).hide();
					});

			$('#modal_delete').dialog('removebutton','Medications');
			$('#modal_delete').dialog('removebutton','Diagnosis');
		}
	});

	$('#delete_btm_med').dialog({
										autoOpen : false,
										modal : true,
										resizable : false,
										width : 500,
										buttons : [{
											text: translate('yesconfirm'),
											click : function() {
				revert_medication($('#btm_medi_revert').val());
												$(this).dialog("close");
											}},
											{
												text: translate('noconfirm'),
												click: function() {
												$(this).dialog("close");
											}
										}]
									});

	if (window.window_show_mmi == 1) {
		
		var healthinsuranceik = window.window_kassen_no;
		var client = window.window_clientid;
		$('.course_med_name').live('change', function() {
			var input_row = parseInt($(this).attr('id').substr(('name').length));
		}).liveSearch({
		url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
			id: 'livesearch_admission_medications',
			aditionalWidth: '300',
			noResultsDelay: '900',
			typeDelay: '900',
			returnRowId: function (input) {return parseInt($(input).attr('id').substr(('name').length));}
		});
	}
	$('.course_med_name_treatment_care').live('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('name').length));
	}).liveSearch({
//			url: 'pharmaindex/getproductsmedils?ik_no='+healthinsuranceik+'&sm=0&client='+client+'&searchtext=',
		url: 'ajax/medicationstreatmentcare?q=',
		id: 'livesearch_admission_medications',
		aditionalWidth: '300',
		noResultsDelay: '900',

			typeDelay: '900',
		returnRowId: function (input) {return parseInt($(input).attr('id').substr(('name').length));}
	});
	
	
	
	$('.todo_selectbox').chosen({
		placeholder_text_single: translate('please select'),
		placeholder_text_multiple : translate('please select'),
		multiple:1,
		width:'250px',
		style: "padding-top:10px",
		"search_contains": true,
		no_results_text: translate('noresultfound')
	});
	
	//transform the select
	chosenizeSelectShortcut($('.selectShortcut'));
	
});

function revert_medication(ids) {
	$.ajax({
		type : 'POST',
		url : 'ajax/revertbtmmedication',
		data : {
			course : ids
		},
		success : function(data) {
			var data_obj = jQuery.parseJSON(data);

			if (data_obj.error) {
				alert(data_obj.error);
			}
		}
	});
}

function load_medi(that) {
	var medi_data = that.val().split('-');
	var element_row = that.attr('rel');

	//clear inputs
	$('#medi_name' + element_row).val('');
	$('#dosage' + element_row).val('');
	$('#comments' + element_row).val('');
	$('#ListNew').block({
		message : "<h1>" + jsTranslate['loadingpleasewait'] + "</h1>",
		css : {
			border : 'none',
			padding : '15px',
			backgroundColor : '#000',
			'-webkit-border-radius' : '10px',
			'-moz-border-radius' : '10px',
			opacity : .5,
			color : '#fff'
		}
	});

	$.ajax({
				type : "POST",
		url : "patientcourse/requestmedicationdata?id=" + window.idpd + "&mid=" + medi_data[0] + "&mmid=" + medi_data[1],async : true,success : function(response) {
					var obj = $.parseJSON(response);

			$('#medi_name' + element_row).val('' + obj.medi_name + '');
			$('#medi_change' + element_row).val('' + obj.medi_change + '');
			$('#medi_replace' + element_row).val('' + obj.medi_replace + '');
					$('#dosage' + element_row).val('' + obj.dosage + '');
			$('#comments' + element_row).val('' + obj.comments + '');
					$('#delete' + element_row).val('' + obj.id + '');
			$('#delete' + element_row).attr('name','delete[' + obj.id + ']');
			$('#medi_id' + element_row).val('' + obj.medication_master_id + '');
					$('#drid' + element_row).val('' + obj.id + '');
					$('#medication-content-' + element_row).show('slow');
					$('#ListNew').unblock();
			selectdignosis(window.idpd, new Number(element_row + 1));

					$('#medi_change' + element_row).datepicker({
						dateFormat : 'dd.mm.yy',
						showOn : "both",
						buttonImage : $('#calImg').attr('src'),
						buttonImageOnly : true,
						changeMonth : true,
						changeYear : true,
						nextText : '',
						prevText : ''
					});
					$('#medi_change' + element_row).mask("99.99.9999");
				}
			});
}

function selectMedications(mid, row, mmi_handler)
{
	$('#name'+row).val($('#medi_me_'+mid).val());
	//TODO-3365 Carmen 24.08.2020
	if(js_pharmaindex_settings.takinghint == 'yes')
	{
	//ISPC 2554 Carmen 16.06.2020
	//if(module_takinghint == '1')
	//{
		$('#komment'+row).val($('#medi_TAKINGHINT_'+mid).val());
	//}
	}
	else
	{
		$('#komment'+row).val('');
	}
	//--
	
	//TODO-3365 Carmen 24.08.2020
	if(js_pharmaindex_settings.drug == 'yes')
	{
		inputExtra =  $('#medi_wirkstoffe_'+mid).val();//ISPC-2329
	}
	else
	{
		inputExtra =  '';//ISPC-2329
	}
	
	//TODO-3365 Carmen 24.08.2020
	if(js_pharmaindex_settings.atc == 'yes')
	{
		inputExtraAtc =  $('#medi_ATC_'+mid).val();//ISPC-2554 pct.3
	}
	else
	{
		inputExtraAtc =  '';
	}
	
	inputExtraPzn =  $('#medi_PZN_'+mid).val();//ISPC-2329 Ancuta 03.04.2020
	inputExtraDBF_id =  $('#medi_DBF_ID_'+mid).val();//ISPC-2329 Ancuta 03.04.2020
	
	//TODO-3365 Carmen 24.08.2020
	if(js_pharmaindex_settings.dosage_form == 'yes')
	{
		inputExtraDosage_form =  $('#medi_DOSAGEFORMID_'+mid).val();//ISPC-2554 Carmen 14.05.2020
	}
	else
	{
		inputExtraDosage_form =  '';
	}
	//TODO-3365 Carmen 24.08.2020
	if(js_pharmaindex_settings.unit == 'yes')
	{
		inputExtraUnit =  $('#medi_UNIT_'+mid).val();//ISPC-2554 Carmen 14.05.2020
	}
	else
	{
		inputExtraUnit =  '';
	}

	inputExtraPzn =  $('#medi_PZN_'+mid).val();//ISPC-2329 Ancuta 03.04.2020
	inputExtraDBF_id =  $('#medi_DBF_ID_'+mid).val();//ISPC-2329 Ancuta 03.04.2020
	
	appendto(row, $('#medi_me_'+mid).val());
	
}