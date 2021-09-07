;
/**
 * patientproblems.js ISPC-2864   
 * @date 02.04.2021 
 * @author @ancuta
 *
 */
if (typeof (DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : ' + document.currentScript.src);
}

$(document).ready(function() {

	$('.add_problem').on('click', function() {

		$('#patient_problems_modal').data('recid', '').dialog('open');

	});
	$('.edit_problem').on('click', function() {

		var recid = $(this).data('problem_id');
		$('#patient_problems_modal').data('recid', recid).dialog('open');

	});

	$('.toggle_down_arrow').on('click', function() {
		var pr_id = $(this).data('block_problem');
		var sit_type = $(this).data('block_type');

		if ($(this).hasClass('up')) {
			$(this).removeClass('up');
		} else {
			$(this).addClass('up');
		}

		$("." + sit_type + "_" + pr_id).toggle();

	});

	$('.show_content').on('click', function() {
		var pr_id = $(this).data('problem_id');
		var sit_type = $(this).data('sit_type');
		var sit_status = $(this).data('sit_status');

		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
		} else {
			$(this).addClass('active');
		}

		$(".content_" + pr_id + "_" + sit_type + "_" + sit_status).toggle();

	});


	var fix_helper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	$('.inner_problems_div').sortable({
		placeholder: "ui-state-highlight",
		helper: fix_helper,
		items: ".problem_container",
		update: function(event, ui) {
			var item = ui.item;

			var container = item.parent();
			var reorder = [];
			container.children('.problem_container').each(function(i) {
				// save the item id order in array
				reorder[i] = $(this).attr('id');
			});

			$.ajax({
				method: 'post',
				url: appbase + 'efa/sortpatientproblems',
				data: {
					'id': window.idpd,
					'order': reorder
				}
			});


		}
	}).disableSelection();


	$('.save_sapv').on('click', function(e) {
		e.preventDefault();
		
		var setdata = $("#sapv_block form").serialize();
		
			$.ajax({
					type: 'POST',
					url: 'efa/events?action=save_form&form=sapvsave&patid=' + pid,
					data: setdata,
					success: function(data) {
						$('.modal_success_msg').show();
						setTimeout(function() {
							$('.modal_success_msg').hide();
						}, 1000);


						if (typeof loadPage == 'function') {
							loadPage();
						}
						else {
							window.location.reload(true);
						}
					}
				});
		
	});



	$('.bpss_text_container').qtip({
		style: {
			name: 'cream',
			tip: true
		},
		position: {
			my: 'center right',  // Position my top left...
			at: 'center left' // at the bottom right of...
		},
		show: {
			event: 'mouseover'
		},
		hide: {
			event: 'mouseout'
		}
	});


	$('.add_bpss').on('click', function() {
		var bpss_type = $(this).data('bpss_type');


		$('#bpss_modal')
			.data('bpss_type', bpss_type)
			.data('recid', '')
			.dialog('open');

	});

	$('.bpss_text_container').on('click', function() {
		var bpss_type = $(this).data('bpss_type');
		var recid = $(this).data('recid');

		$('#bpss_modal')
			.data('bpss_type', bpss_type)
			.data('recid', recid)
			.dialog('open');

	});

	//ISPC-2864 Ancuta 14.04.2021
	$('#bpss_modal').dialog({
		autoOpen: false,
		modal: true,
		width: 700,
		height: 300,
		title: translate('bpss_modal'),
		dialogClass: "charts_modal",

		open: function() {
			jQuery('.ui-widget-overlay').on('click', function() {
				//$(this).dialog('close');
			});


			$('.modal_loading_div', this).show();

			var bpss_type = $(this).data('bpss_type');

			if ($(this).data('recid')) {
				var url = 'efa/events?action=show_form&form=bpssadd&recid=' + $(this).data('recid') + '&patid=' + pid + '&bpss_type=' + bpss_type;

				if ($(this).parent().find('.delbutton').is(":hidden")) {
					$(this).parent().find('.delbutton').show();
				}
			}
			else {
				var url = 'efa/events?action=show_form&form=bpssadd&patid=' + pid + '&bpss_type=' + bpss_type;
				$(this).parent().find('.delbutton').hide();
			}

			$.ajax({
				type: 'POST',
				url: url,

				success: function(data) {

					$('#bpss_modal').html(data);

				},
				error: function() {

				}
			});
		},
		buttons: [

			//delete button
			{
				'class': "delbutton leftButton",
				text: 'Eintrag löschen',
				click: function() {
					var recid = $(this).data('recid');
					var bpss_type = $(this).data('bpss_type');
					jConfirm(translate('[Are you sure you want to delete this?]'), translate('confirmdeletetitle'), function(r) {
						if (r) {
							var setdata = {
								id: recid,
							};

							$.ajax({
								type: 'POST',
								url: 'efa/events?action=save_form&form=bpsssave&subaction=delete&patid=' + pid + '&bpss_type=' + bpss_type,
								data: setdata,
								success: function(data) {
									window.location.reload(true);
									if (typeof loadPage == 'function') {
										loadPage();
									}
								}
							});
						}
					});

				},

			},

			{
				click: function() {

					var error_name = 0;
					if ($('#problem_id').val() == '' || $('#problem_id').val() == '0') {
						alert(translate('PLease select problem'));				//ISPC-2864 Lore 16.04.2021
						error_name++;
					}

					if (error_name == 0) {
						var setdata = $("#bpss_modal form").serialize();

						var bpss_type = $(this).data('bpss_type');
						$.ajax({
							type: 'POST',
							url: 'efa/events?action=save_form&form=bpsssave&patid=' + pid + '&bpss_type=' + bpss_type,
							data: setdata,
							success: function(data) {
								$('.modal_success_msg').show();
								setTimeout(function() {
									$('.modal_success_msg').hide();
								}, 1000);


								if (typeof loadPage == 'function') {
									loadPage();
								}
								else {
									window.location.reload(true);
								}
							}
						});
					}
					else {
						$('#awareness_name_type_error').show();
					}

				},
				text: translate('save')
			},

			{
				click: function() {

					$(this).dialog('close');
				},
				text: translate('cancel'),
			}
		]
	});
	//--




});

