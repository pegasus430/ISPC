;
/**
 * invoicesnew
 * invoicesnew.js
 * @date 04.02.2019
 * @author @cla
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

/*
$(document).ready(function() {

});
*/


function transmit_hl7_ft1(iid) {
	
	var __iids = iid || false,
	data = {},
	ajax = null;
	
	
	if (__iids) {
		//we have a single iid to transmit
		__iids = [__iids];
	} else {	
		
		//__iids must pe ppopulated with checked ones 
		__iids = $('.checkBox:checked[data-haspatientvisit="1"]').map(function(){return this.value;}).get();
		
		
		//ISPC-2459 Ancuta 26.11.2019
		__pv_iids = $('.checkBox:checked[data-haspatientvisit="1"]').map(function(){return this.value;}).get();
		__move_iids = $('.checkBox:checked[data-hasAllmovementnumbers="1"]').map(function(){return this.value;}).get();
//		
//		__iids = $.map(__pv_iids, function(el){
//			  return $.inArray(el, __move_iids) < 0 ? null : el;
//			});
		// -- 
		
	}
	
	data = {				
		"__action"	: "transmit_hl7_ft1",
		"iids"		: __iids,
	};
	
	ajax = $.ajax({

         'url'		: appbase + 'invoicenew/invoicesnew',
         'type'		: 'POST',
         'dataType'	: 'json',
         'data'		: data,

         error: function(jqXHR, textStatus, errorThrown) {
        	 //better luck next time
         },
         
         success: function(data, textStatus, jqXHR) {
        	 
        	 if (data.hasOwnProperty('result')
                 && data.result == true
                
        	 ) 
        	 {
        		 
        		 if (data.hasOwnProperty('acks') && data.acks !== null ) {
        			 
        			 $.map(data.acks, function(ack, iid) {
        				 var _parent_tr_id = $('input.checkBox[value="'+iid+'"]').parents("tr:eq(0)").attr('id');
        				 
        				 $( "#sub_" + _parent_tr_id).find("a.hl7_ft1_text").text(ack.hl7_ft1_text);
        				 
        				 
        				 $.notify({    					 
             				// options
             				title	: ack.hl7_ft1_title,
             				message	: ack.hl7_ft1_text
         				}, 
         				{
         					// settings
         					element			: $('.selector_formularNotifications'),
         					type			: ack.send_ok == 'yes' ? 'success' : 'error',
         					allow_dismiss	: true,
         					delay			: ack.send_ok == 'yes' ? 5000 : 0, // so it does not auto-closes
         					newest_on_top	: false,
         					position: 'relative',
         					placement		: {
         						from: 'nothing',
         						align: "center"
     						},
     						z_index: null,
     						icon_type: 'class',
         				});
        			 });
        		 }
        		 
        		 
        		 
        		 /**
        		  * this is for a general message
        		  */
        		 if (data.hasOwnProperty('message')) {
        			 $.notify(
             			{
             				// options
             				icon	: typeof data === 'object' && data.hasOwnProperty('icon') ? data.icon : 'glyphicon glyphicon-warning-sign',
             				title	: typeof data === 'object' && data.hasOwnProperty('title') ? data.title : null,
             				message	: typeof data === 'object' && data.hasOwnProperty('message') ? data.message : null,
         				}, 
         				{
         					// settings
         					element			: $('.selector_formularNotifications'),
         					type			: typeof data === 'object' && data.hasOwnProperty('type') ? data.type : 'success',
         					allow_dismiss	: true,
         					delay			: 500000, // so it does not auto-closes
         					newest_on_top	: false,
         					
         					position: 'relative',
         					placement		: {
         						from: 'nothing',
         						align: "center"
     						},
     						z_index: null,
     						icon_type: 'class',
         				});
    			 
        		}
    			 
        	 }
        	 
         }
	 });

	
}


/**
 * 
 * @param iid
 * @returns 
 * ancuta May 12, 2020
 */
function transmit_hl7_activation(iid) {
	
	var __iids = iid || false,
	data = {},
	ajax = null;
	
	
	if (__iids) {
		//we have a single iid to transmit
		__iids = [__iids];
	} else {	
		
		//__iids must pe ppopulated with checked ones 
		__iids = $('.checkBox:checked[data-haspatientvisit="1"]').map(function(){return this.value;}).get();
		
		
		//ISPC-2459 Ancuta 26.11.2019
		__pv_iids = $('.checkBox:checked[data-haspatientvisit="1"]').map(function(){return this.value;}).get();
		__move_iids = $('.checkBox:checked[data-hasAllmovementnumbers="1"]').map(function(){return this.value;}).get();
//		
//		__iids = $.map(__pv_iids, function(el){
//			  return $.inArray(el, __move_iids) < 0 ? null : el;
//			});
		// -- 
		
	}
	
	data = {				
			"__action"	: "transmit_hl7_activation",
			"iids"		: __iids,
	};
	
	ajax = $.ajax({
		
		'url'		: appbase + 'invoicenew/invoicesnew',
		'type'		: 'POST',
		'dataType'	: 'json',
		'data'		: data,
		
		error: function(jqXHR, textStatus, errorThrown) {
			//better luck next time
		},
		
		success: function(data, textStatus, jqXHR) {
			
			if (data.hasOwnProperty('result')
					&& data.result == true
					
			) 
			{
				
				if (data.hasOwnProperty('acks') && data.acks !== null ) {
					
					$.map(data.acks, function(ack, iid) {
						var _parent_tr_id = $('input.checkBox[value="'+iid+'"]').parents("tr:eq(0)").attr('id');
						
						$( "#sub_" + _parent_tr_id).find("a.hl7_ft1_text").text(ack.hl7_ft1_text);
						
						
						$.notify({    					 
							// options
							title	: ack.hl7_ft1_title,
							message	: ack.hl7_ft1_text
						}, 
						{
							// settings
							element			: $('.selector_formularNotifications'),
							type			: ack.send_ok == 'yes' ? 'success' : 'error',
									allow_dismiss	: true,
									delay			: ack.send_ok == 'yes' ? 5000 : 0, // so it does not auto-closes
											newest_on_top	: false,
											position: 'relative',
											placement		: {
												from: 'nothing',
												align: "center"
											},
											z_index: null,
											icon_type: 'class',
						});
					});
				}
				
				
				
				/**
				 * this is for a general message
				 */
				if (data.hasOwnProperty('message')) {
					$.notify(
							{
								// options
								icon	: typeof data === 'object' && data.hasOwnProperty('icon') ? data.icon : 'glyphicon glyphicon-warning-sign',
										title	: typeof data === 'object' && data.hasOwnProperty('title') ? data.title : null,
												message	: typeof data === 'object' && data.hasOwnProperty('message') ? data.message : null,
							}, 
							{
								// settings
								element			: $('.selector_formularNotifications'),
								type			: typeof data === 'object' && data.hasOwnProperty('type') ? data.type : 'success',
										allow_dismiss	: true,
										delay			: 500000, // so it does not auto-closes
										newest_on_top	: false,
										
										position: 'relative',
										placement		: {
											from: 'nothing',
											align: "center"
										},
										z_index: null,
										icon_type: 'class',
							});
					
				}
				
			}
			
		}
	});
	
	
}