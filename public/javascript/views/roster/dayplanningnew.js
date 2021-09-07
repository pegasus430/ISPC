;
/**
 * dayplanningnew.js
 * roster
 * @date 17.01.2019
 * @author @cla
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}



$(document).ready(function() {

	$("#selector_manual_forms_editmode").checkFormularEditmode({'ajax':{'data' : {'pathname' : 'roster/dayplanningnew'}}});
	//$("#MainContent_resultMessages").checkFormularEditmode({'ajax':{'data' : {'pathname' : 'roster/dayplanningnew'}}});
	
	

	var _sort_users_from, _sort_users_to;
	
	$('table.selector_sortables_board_users_table , table.selector_sortables_board_pseudogroups_table').sortable({
		
		items: "div.selector_sortables_item_day_plan",		
		handle: '>div.day_plan_user_title',
		cursor: 'move',
		placeholder: 'boxsort_placeholder',
		forcePlaceholderSize: true,
		
		opacity: 0.4,
		
		start: function (event, ui) {
			
			$(ui.item).find(".day_plan_body").hide();
			
			$(ui.item).height(30);
			
			_sort_users_from = $(ui.item).parent();
			
			
			//disable dropable on each row and title
			$('#day_planning_table_board div.day_plan_user').each(function(){
				$(this).find('.day_plan_visit_hourly .droptrue, .day_plan_user_title').each(function(){
					if ($(this).data('droppable')) {
						var _droppableState = $(this).droppable( "option", "disabled" );
						$(this).data('_droppableState', _droppableState);
						$(this).droppable( "option", "disabled" , true);
					}
				});
			});
        },
		
		stop: function(event, ui){
			//$(ui.item).find('div.versorger-catheader').click();
			var itemorder=$(this).sortable('toArray');
			
			if ( ! $(".selector_sortables_item_day_plan", _sort_users_from).length) {
				$("<div class='selector_sortables_item_day_plan selector_sortables_empty boxsort_placeholder'>" + translate('This row is empty, you can drag/drop here a box') + "</div>").appendTo(_sort_users_from);
			} else {
				$(".selector_sortables_empty", $(ui.item).parent()).remove();
			}
			
			//restore dropable on each row
			$('#day_planning_table_board div.day_plan_user').each(function(){
				$(this).find('.day_plan_visit_hourly .droptrue, .day_plan_user_title').each(function(){
					if ($(this).data('droppable')) {						
						var _droppableState = $(this).data('_droppableState') || false;
						$(this).droppable( "option", "disabled" , _droppableState);
					}
				});
				
			});
			
		},
		
		update : function (event, ui) {
			
			var _order = {},
			_type = $(this).hasClass('selector_sortables_board_users_table') ? 'users' : ($(this).hasClass('selector_sortables_board_pseudogroups_table') ? 'pseudogroups' : null);

			
			$('.td_users', this).each(function(i){
				var _col = [""];
				$(".selector_sortables_item_day_plan", this).each(function(){
					_col.push($(this).data("userid")); 
				});
				
				_order[i] = _col;
			});
			
				
			var _url = appbase + "roster/dayplanningnew";
			
			var _data = {};
			_data.__action = 'updateBoxOrder';
			_data.type = _type;
			_data.order = _order;
			
			
			
			$.ajax({
		        dataType	: "json",
		        type		: "POST",
		        url			: _url,
		        data		: _data,
		    });
			
		}
	})
	//.disableSelection()
	;

});
