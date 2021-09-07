

$(document).ready(function() {
	$( window ).load(function() {
		recipient_options();
	});
    	
    // open close on expand button	
    $('.close_scc').on('click',function(){
    	$('.users-select-dd').hide();
    	$('.btnExpand').removeClass('shown');
    	$('.btnExpand').addClass('hidden');
    });
    
    $('.recipients').on('click',function(){
    	recipient_options();
    });
    
    
    $(document).off('click','.btnRemove').on('click','.btnRemove',function(e){
    	var uid = $(this).data('uid'); 
    	$('#selected_u_'+uid).remove();
    	$('#client_user_'+uid).attr('checked', false);
    	
    	 e.stopPropagation();
    	 e.stopImmediatePropagation();
    	 e.preventDefault();
    });
    
    
    $(document).off('click','.users-select').on('click','.users-select',function(e){
    	$('.users-select-dd').toggle();
    	
    	 if($('.users-select-dd').is(":visible")){
    		$('.btnExpand').removeClass('hidden');
    		$('.btnExpand').addClass('shown');
    	 } else{
    		$('.btnExpand').removeClass('shown');
    		$('.btnExpand').addClass('hidden');
    	 }
    });
	
});

function recipient_options(){
	var recipientrs_lists = "";
	$('.recipients').each(function(){
		var uid = $(this).data('user_id');
		var uname = $(this).data('user_name');
		
		if($(this).is(':checked')){
			//alert($(this).data('user_name'));
			var li = '<li class="user" id="selected_u_'+uid+'">'+uname+'<input type="button" data-uid="'+uid+'"   class="btnRemove"></li>';
			recipientrs_lists  = recipientrs_lists + li;
		}
	});
	
	recipientrs_lists  = recipientrs_lists + '<li class="btnSelect"><input type="button" class="btnExpand shown"></li>';
	
	$('.users-select').html(recipientrs_lists);
	
	//TODO-2989 Ancuta 09.03.2020
	$(window).scrollTop(0);
	$(".users-select" ).focus();
	//-- 
}
 