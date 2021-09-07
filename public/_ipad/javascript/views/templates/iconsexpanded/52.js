;
/**
 * 52.js = scheduled_medication_data
 * @date 01.04.2019
 * @author @cla
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}




jQuery(document).ready(function ($) {


	//give_scheduled_medication
	$(".give_scheduled_medication", $("#placeholder\\.patient\\.icons\\.new")).on('click',function(){
	 	
		var patient_id = window.idpd,
	 	drug_id = $(this).data("drug_id") || 0,
		int_current_date = (new Date()).toLocaleDateString('de-DE', { year: 'numeric', month: 'numeric', day: 'numeric' });
		
		jConfirm(translate('confirm_give_medication'), translate('confirm_give_medication_title'), function(r) {
		    if(r) {
		    	
				$.ajax({
					type: 'POST',
					url: 'ajax/givescheduledmed',
					data: {
						id: patient_id,
						drug_id: drug_id
					},
					success:function(response){
						
						$('#interval_set_button_'+drug_id).remove();
						$('#due_date_'+drug_id).html(int_current_date);
						setTimeout(function () {
							$('#sch_'+drug_id).remove();
						}, 800);
								
						//console.log(response);
						var response_obj = jQuery.parseJSON(response);
						if (response_obj['remove_icon'] == '1') {
							$('#sys_icon-52').remove();
							$('#content_sys_icon-52').remove();
						}
					}
				});
				
		    } else {
				//$('#status_readmit_data').dialog('close');
			}
		});
	});
	
	
});