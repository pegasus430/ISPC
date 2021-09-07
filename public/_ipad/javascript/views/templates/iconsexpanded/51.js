;
/**
 * 51.js = todo_data
 * @date 01.04.2019
 * @author @cla
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


function mark_event_done(event_id, event_row, event_tabname, event_done_date)
{
	
	var url = appbase + 'overview/dashboardlist';
	var xhr = $.ajax({
		url: url + '?mode=done&eventid=' + event_id + '&tabname=' + event_tabname + '&donedate=' + event_done_date,
		success: function(response) {
			
			if ($("#done_event_"+event_row).length != 0 ) {
				
				$("#done_event_"+event_row)
				.closest('ul.tnotcomplete').removeClass('tnotcomplete').addClass('tcomplete')
				.end()
				.closest('li.marktodoasCompleted').hide()
				;
				
			}
		}
	});
}


jQuery(document).ready(function ($) {

	
	$('.done_event', $("#placeholder\\.patient\\.icons\\.new")).live('click', function() {
		
		
		if ($(this).is(':checked')) {
			var todo_nr = $(this).data('todo_nr');
			var todo_idss = $("#todoids_"+todo_nr).val();
			var todo_ids = todo_idss.split(',');
			
			var event_tabname = 'todo';
			var event_done_date = $.datepicker.formatDate('yy-mm-dd', new Date());
			$('#loading_div_'+ todo_nr).show();
			//$('tr#d_row_' + row_id).effect("highlight", {}, 5000);
			$.each(todo_ids, function(index, value) {
				//alert(value);
			mark_event_done(value, todo_nr, event_tabname, event_done_date);
			});
		}
	});
	
	
});