
$(document).ready(function(){ 
	create_datepicker();
	
}); /* $(document).ready END  */
var cnt = 0;
function new_maintenancestage_row(_this,_event){
	cnt++;
	var parent = $(_this).parents("tr");
	var cloned_td = parent.clone(false);
	
	cloned_td.find('.ui-datepicker-trigger').remove();
	cloned_td.find('input, select ').each(function(){
		
		var first_input = $(this).attr("name");
		firstrow_input = first_input.slice( first_input.lastIndexOf("["));
		
		$(this)
		.attr("name", String("form_data[new_"+cnt+"]"+firstrow_input))
		.removeAttr("id")
		.prop("checked",false)
		.removeClass('hasDatepicker');
		
		if($(this).attr('type') != "checkbox"){
			$(this).val('');	
		}
	})

	$(cloned_td).insertAfter(parent);
	create_datepicker();
}




function delete_maintenancestage_row(_this,_event){

	
	jConfirm(translate('confirmdeleterecord: Mark line as deleted'), translate('confirmdeletetitle'), function(r) {
		if(r)
		{
			var parent = $(_this).parents("tr");
			parent.addClass('deleted');
			parent.find('input').each(function(){
				var first_input = $(this).attr("name");
				firstrow_input = first_input.slice( first_input.lastIndexOf("["));
				if(firstrow_input == "[isdelete]"){
					$(this).val(1);
				}
			});
			parent.find('button.delete_row').remove();
			parent.find('button.new_row').remove();
		}
	});
	
}


function create_datepicker(){
	$('.idate').datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});
	
}