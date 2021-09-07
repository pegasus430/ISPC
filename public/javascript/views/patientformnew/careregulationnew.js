var formular_button_action = window.formular_button_action;

function form_submit_validate() {
	
		return true;
}




$(document).ready(function() {
	
	$('.checkdummy').each(function(){
        var dummyel = $($(this).parent().find('.dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
        	 $('#'+dummyel).addClass('rcb_img');
        }
         else {
        	 $('#'+dummyel).addClass('no_img');
        }
    });

	$(document).off().on('click','.dummy',function(){
		var that = $($(this).parent().find('.checkdummy')).attr('id');
		
        if($('#'+that).is(':checked')) {
        	
            $('#'+that).removeAttr('checked');            
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
	});
	
	
	//disable enter key 
	$('input[type="text"]').keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
	

	$('.stamp_alert').hide();
	$('.stamp_alert_repr').hide();
	$('.stamp_alert_pfle').hide();
	
	$( ".date" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	});


	//$('.hasDatepicker').mask('99.99.9999'); //add date mask

	/*----------------------------------------------------------------------------------------------------------*/
	/*---------------------------------------- Stamp Info ------------------------------------------------------*/
	/*----------------------------------------------------------------------------------------------------------*/
	$('#stampusers_doct').live('change',function(){
		$('#user_stamp_block span').replaceWith('');
        $('#user_stamp_block textarea').replaceWith('');

		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $(this).val(), function(result) {

            if (result != 0){
            	var resultx = jQuery.parseJSON(result);

                var user_lanr = resultx.lanr;
                var user_bsnr = resultx.bsnr;

                $('#stamp_user_bsnr').val(user_bsnr);
                $('#stamp_user_lanr').val(user_lanr);

                $('#user_stamp_block span').replaceWith('');
                $('#user_stamp_block textarea').replaceWith('');

                var row1 = resultx.row1;
                var row2 = resultx.row2;
                var row3 = resultx.row3;
                var row4 = resultx.row4;
                var row5 = resultx.row5;
                var row6 = resultx.row6;
                var row7 = resultx.row7;

                var user_stamp = '<span>'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</span>';
                var user_stamp_hidden = '<textarea name="stamp_block" style="display: none">'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</textarea>';

                $('#user_stamp_block').append(user_stamp+user_stamp_hidden);


            } else{
            	$('.stamp_alert').show('fast').delay(1000).hide('slow');
            }

        });
		return false;
	});
	
	
	$('#stampusers_repr').live('change',function(){
		$('#user_stamp_block_repr span').replaceWith('');
        $('#user_stamp_block_repr textarea').replaceWith('');

		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $(this).val(), function(result) {

            if (result != 0){
            	var resultx = jQuery.parseJSON(result);

                var user_lanr = resultx.lanr;
                var user_bsnr = resultx.bsnr;

                $('#user_stamp_block_repr span').replaceWith('');
                $('#user_stamp_block_repr textarea').replaceWith('');

                var row1 = resultx.row1;
                var row2 = resultx.row2;
                var row3 = resultx.row3;
                var row4 = resultx.row4;
                var row5 = resultx.row5;
                var row6 = resultx.row6;
                var row7 = resultx.row7;

                var user_stamp = '<span>'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</span>';
                var user_stamp_hidden = '<textarea name="stamp_block_repr" style="display: none">'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</textarea>';

                $('#user_stamp_block_repr').append(user_stamp+user_stamp_hidden);


            } else{
            	$('.stamp_alert_repr').show('fast').delay(1000).hide('slow');
            }

        });
		return false;
	});
	
	$('#stampusers_pfle').live('change',function(){
		$('#user_stamp_block_pfle span').replaceWith('');
        $('#user_stamp_block_pfle textarea').replaceWith('');

		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $(this).val(), function(result) {

            if (result != 0){
            	var resultx = jQuery.parseJSON(result);

                var user_lanr = resultx.lanr;
                var user_bsnr = resultx.bsnr;

                $('#user_stamp_block_pfle span').replaceWith('');
                $('#user_stamp_block_pfle textarea').replaceWith('');

                var row1 = resultx.row1;
                var row2 = resultx.row2;
                var row3 = resultx.row3;
                var row4 = resultx.row4;
                var row5 = resultx.row5;
                var row6 = resultx.row6;
                var row7 = resultx.row7;

                var user_stamp = '<span>'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</span>';
                var user_stamp_hidden = '<textarea name="stamp_block_pfle" style="display: none">'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</textarea>';

                $('#user_stamp_block_pfle').append(user_stamp+user_stamp_hidden);


            } else{
            	$('.stamp_alert_pfle').show('fast').delay(1000).hide('slow');
            }

        });
		return false;
	});
	
	$('.livesearchicd').live('change', function() {
		var input_row = parseInt($(this).attr('id').substr(('icdnumber').length));

	}).liveSearch({
		url: 'ajax/diagnosis?mode=icdnumber&q=',
		id: 'livesearch_admission_diagnosis',
		aditionalWidth: '400',
		noResultsDelay: '900',
		typeDelay: '900',
		returnRowId: function (input) {return parseInt($(input).attr('id').substr(('icdnumber').length));}
	});
	
	
	
});
