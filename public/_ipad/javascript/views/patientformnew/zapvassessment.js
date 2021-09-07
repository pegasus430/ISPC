$(document).ready(function() {
		$(".add_calendar").mask("99.99.9999");

		$('.add_calendar').datepicker({
			showOn: "both",
			buttonImage: $('#calImg').attr('src'),
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true,
			nextText: '',
			prevText: ''
		});
		
		 $(".textarea1").keyup(function(){
			 mark_edited($(this));
       });
		$('.cf_selectbox')
		.change(function(evt, params) {
			    	    
		    if(typeof params.deselected !== 'undefined' ){
		    	// remove the symptoms related to the cf deselected id
		    	populate_symptoms(evt.target,1);
		    	
		    } else{
		    	populate_symptoms(evt.target);
		    }
		  })
		.chosen({
			placeholder_text_single: translate('please select'),
			placeholder_text_multiple : translate('please select'),
			multiple:1,
			width:'250px',
			style: "padding-top:10px",
			"search_contains": true,
			no_results_text: translate('noresultfound') 
		});
		
});

function populate_symptoms(_this,delete_syms){
	
	$.ajax({	
			type: 'POST',
			url: appbase+'ajax/contactformsymptoms?id='+idpd,
			data: {
				ids: $(_this).val()	
			},

			success:function(data){
				if(delete_syms == 1 && data == 0){
				   $(".textarea1").each(function(){
				        if($(this).data('edited') == "1"){
							 // append
							 // do nothing
						 } else{
							 $(this).val("");
						 }    
				    });
				} 
				else
				{
					var all = jQuery.parseJSON(data);
					var visitdata = all.text;
					
					$.each( visitdata, function( gr_id, values ) {
						 var input = $('.textarea1', $('.row'+gr_id));
						 
						 if(input.data('edited') == "1"){
							 // append
							 if(delete_syms != 1){
								 input.val(input.val() +' \n '+ values);
							 } 
						 } else{
							 
							 input.val(values);
						 }
					});
				}
			},
			error:function(){
				ajax_done = 1;
				// failed request; give feedback to user
			}
	});
}

function mark_edited(_this){
	$(_this).attr('data-edited', '1');
}