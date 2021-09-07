;
/**
 * overviewmessages.js ISPC-2827 
 * @date 02.04.2021 
 * @author @ancuta
 *
 */
if (typeof (DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : ' + document.currentScript.src);
}

$(document).ready(function() {


	$('.overview_openMsg').live('click', function() {
		
		var msg_id = $(this).data('msg_id');
		
		var dlist = '<div class="loadingdiv" align="center" style="width: 100%;float: left;"><img src="'+res_path +'/images/pageloading.gif"><br />	Loading... please wait</div>';
		$('.messages_content').html(dlist );
		
			var url = appbase + 'efa/openmail?modal=1&msg_id=' + msg_id;     //TODO-2643 Lore 12.11.2019
			xhr = $.ajax({
				url : url,
				success : function(response) {
					$('.messages_content').html(response);
				}
			});
		
		
//		$('#messages-modal')
//		.data('modal_title', "MESSAGESS open mailt")
//		.dialog('open');
		messages_modal('open');
	});

	
	$('.reply_button').live('click', function() {
		
		var msg_id = $(this).data('msg_id');
		
		var dlist = '<div class="loadingdiv" align="center" style="width: 100%;float: left;"><img src="'+res_path +'/images/pageloading.gif"><br />	Loading... please wait</div>';
		$('.messages_content').html(dlist );
		
			var url = appbase + 'efa/replymail?modal=1&msg_id=' + msg_id;     //TODO-2643 Lore 12.11.2019
			xhr = $.ajax({
				url : url,
				success : function(response) {
					$('.messages_content').html(response);
				}
			});
		
//		$('#messages-modal')
//		.data('modal_title', "MESSAGESS open REPLY")
//		.dialog('open');
		messages_modal('reply');
	});

	
	$('.overview_addMsg').live('click', function() {
		
		var dlist = '<div class="loadingdiv" align="center" style="width: 100%;float: left;"><img src="'+res_path +'/images/pageloading.gif"><br />	Loading... please wait</div>';
		$('.messages_content').html(dlist );
		
			var url = appbase + 'efa/sendmessages'; 
			xhr = $.ajax({
				url : url,
				success : function(response) {
					$('.messages_content').html(response);
				}
			});
		
//		$('#messages-modal')
//		.data('modal_title', "MESSAGESS open NEW")
//		.dialog('open');
//		
		messages_modal('add');
		
	});

});


function messages_modal(__dialogType){
		
	switch (__dialogType) 
	{
		case "add":
			__dialogTitle = 'Nachricht verfassen';
		break;
	
		case "open":
			__dialogTitle = 'Nachricht lesen';
		break;
	
		case "reply":
			__dialogTitle = 'Nachricht verfassen';
		break;
	}
		$("#messages-modal").dialog({
		autoOpen: true,
		resizable: false,
		height: 600,
		scroll: true,
		width: 800,
		modal: true,
 		title: __dialogTitle,
	 
		buttons: [{
	        id: "btnDelete",
	        text: "Abbrechen",
	        click: function () {
	         
	            $(this).dialog('close');
	        }
		}]
		
	});
	
}
