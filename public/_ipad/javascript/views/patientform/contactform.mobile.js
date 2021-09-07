;
/**
 * contactform.mobile.js
 * @date 11.03.2019
 * @author @Ancuta
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

jQuery(document).ready(function ($) {
	
	/*
	 * todos block
	 */
	$( ".todo_date" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "focus",
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	})
	.mask("99.99.9999");
	
	$('.todo_selectbox' , $("#todos")).chosen({
		placeholder_text_single: translate('please select'),
		placeholder_text_multiple : translate('please select'),
		multiple:1,
		"search_contains": true,
		no_results_text: translate('noresultfound'),
		inherit_select_classes: true
	});
	
	
	/*
	 * add your onload fn here for your specific block
	 */
	//ISPC-2666 Lore 18.09.2020
	try {
		$('.livesearchFormEvents').livesearchCityZipcode();
		$('.livesearchFormEvents').livesearchHealthInsurance();					
	} catch (e) {
		//console.log(e);
	}
	
});

//ISPC-2667 Lore 22.09.2020
function show_kind_ins_extrafields(checkvals){

	if(checkvals == true){
		$('.kind_ins_view_extrafields').each(function() {
			$(this).attr('style', '');
		  }
		);
	}else{
		
		if( $('#PatientCareInsurance-kind_ins_legally').is(':checked') || 
				 $('#PatientCareInsurance-kind_ins_private').is(':checked') || 
				 $('#PatientCareInsurance-kind_ins_no').is(':checked') ||
				 $('#PatientCareInsurance-kind_ins_others').is(':checked') 
				 ){
					$('.kind_ins_view_extrafields').each(function() {
						$(this).attr('style', '');
					  }
					);
			}else {
				$('.kind_ins_view_extrafields').each(function() {
					$(this).attr('style', 'display:none;');
				  }
				);
			} 
	}	
}
//.

/*
 * todos block
 */
function todos_checkinput(element) 
{
	var _parent = $(element).closest("div.container.todo_container");	
	if ( $(".todo_text", _parent).val() != "" && $(".todo_selectbox", _parent).val() !== null) {
		$(_parent).next("div.container.todo_container").show();					
	}
	
	return;
}

/*
 * clientsymptoms block
 */
function clientsymptoms_create_new_symptom_item(_button)
{
	var _counter = $(_button).closest('.inputs').find('.container.double').length || 0,
	_clonedSymp = $(_button).closest('.inputs').find('.container.double').eq(0).clone(false); //... the first should be clientsymptoms[-1]
	
	_clonedSymp
	.css("display", "block")
	.find('input, select, textarea, label').each(function (){
		if (this.id)
			this.id = this.id.replace(/-1$/, _counter);
		
		if (this.name)
			this.name = this.name.replace('clientsymptoms[-1]', 'clientsymptoms['+_counter+']');
		
		$(this).removeAttr('disabled');
		
		var _for = $(this).attr('for') || '';
		if (_for != '') {
			$(this).attr('for', _for.replace(/-1$/, _counter));			
		}
	});
	
	$(_button).parent().before(_clonedSymp);
	
	return;
}
function clientsymptoms_remove_sym_line(_button) 
{
	$(_button).closest('.container.double').remove();	
	return false;
}



/*
 * symp_zapv_complex block
 */
function symp_zapv_complex_remove_item(_event) {
	$(_event.target).parent().hide('fast', function(){
		$(this).remove();
	});
	return false;
}
function symp_zapv_complex_add_item(_button) {
	
	var _clonedSymp = $(_button).parent().clone(true);
	
	_clonedSymp
	.find('select').val(0)
	.end()
	.find('button.btnAdd')
	.removeClass('btnAdd').addClass('btnDelete')
	.removeAttr('onclick').on('click', symp_zapv_complex_remove_item)
	;
	
	$(_button).closest('.container.double').find('.selector_symp_zapv_complex_last_value').before(_clonedSymp);
	
	return false;
}


/*
 * Used in Symptomatics blocks 
 */
function isInteger(k)
{
 	var s = document.getElementById('input_value'+k).value;
 	var chars = "0123456789";

 	if(s>10)
 	{
		document.getElementById('input_value'+k).value = "";
		return false;
 	}

 	var i;
 	s = s.toString();
	for (i = 0; i < s.length; i++)
	{
		var c = s.charAt(i);
		if (chars.indexOf(c)==-1)
		{
 			document.getElementById('input_value'+k).value = "";
 			return false;
		}
	}
	return true;
}


/*
 * Used in service_entry block - that it is not used any more!!! 
 */
function isIntegerServices(k)
{
 	var s = document.getElementById('inputvalue'+k).value;
 	var chars = "0123456789";

 	if(s>10)
 	{
		document.getElementById('inputvalue'+k).value = "";
		return false;
 	}

 	var i;
 	s = s.toString();
	for (i = 0; i < s.length; i++)
	{
		var c = s.charAt(i);
		if (chars.indexOf(c)==-1)
		{
 			document.getElementById('inputvalue'+k).value = "";
 			return false;
		}
	}
	return true;
}


function getSelected(dbvalue,fieldname)
{
	var fieldarray = document.getElementsByName(fieldname);
	for(i=0; i<fieldarray.length;i++)
	{
		if(fieldarray[i].value==dbvalue)
		{
			fieldarray[i].checked = true;
		}
	}
}

function showbox(val)
{
	if(val.value=="4")
	{
		$("#otherdivid").show();
	}else{
		$("#otherdivid").hide();
	}
}
