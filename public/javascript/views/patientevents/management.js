	$(document).ready(function() {
		var fix_helper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};

		$('table.datatable tbody').sortable({
			placeholder: "ui-state-highlight",
			helper: fix_helper,
			items: "tr:not(.disabled)",
			update: function(event,ui){
				var item = ui.item;
				var container = item.parent();
				console.log($(this).attr('id'));
				var reorder = [];
				container.children('tr').each(function(i){
					// save the item id order in array
					reorder[i] = $(this).attr('id');
					$('#group_order'+$(this).attr('id')).val(i+1);
				});


			}
		}).disableSelection();
	});