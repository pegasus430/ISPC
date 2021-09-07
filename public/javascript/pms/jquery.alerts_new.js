    (function ($) {
	$.alerts = {
	    // These properties can be read/written by accessing $.alerts.propertyName from your scripts at any time

//	    verticalOffset: -75, // vertical offset of the dialog from center screen, in pixels
//	    horizontalOffset: 0, // horizontal offset of the dialog from center screen, in pixels/
//	    repositionOnResize: true, // re-centers the dialog on window resize
//	    overlayOpacity: .01, // transparency level of overlay
//	    overlayColor: '#FFF', // base color of overlay
//	    draggable: true, // make the dialogs draggable (requires UI Draggables plugin)
	    okButton: 'Ja', // text for the OK button
	    cancelButton: 'Nein', // text for the Cancel button
//	    dialogClass: null, // if specified, this class will be applied to all dialogs

	    // Public methods
	    alert: function (message, title, callback) {
		if(!title) {
		    title = 'Benachrichtigung';
		}

		$.alerts._show(title, message, null, 'alert', function (result) {
		    if(callback)
			callback(result);
		});
	    },
	    confirm: function (message, title, callback) {
		if(!title) {
		    title = 'Bestätigung';
		}
		$.alerts._show(title, message, null, 'confirm', function (result) {
		    if(callback)
			callback(result);
		});
	    },
	    //not yet implemented
//	prompt: function (message, value, title, callback) {
//	    if(title == null)
//		title = 'Eingabeaufforderung';
//	    $.alerts._show(title, message, value, 'prompt', function (result) {
//		if(callback)
//		    callback(result);
//	    });
//	},
	    // Private methods

	    _show: function (title, msg, value, type, callback) {
		var modal_scheleton = $('<div id="popup_content"></div>')
		var message_scheleton = $('<div id="pop_message"></div>')
		$("BODY").append(modal_scheleton.append(message_scheleton));

		$('#popup_content').dialog({
		    autoOpen: false,
		    modal: true,
		    resizable: false,
		    width: 400
		});

		$('#popup_content').addClass(type);
		$("#pop_message").empty().html(msg);
		$("span#ui-dialog-title-popup_content").html(title);

		switch(type) {
		    case 'alert':

			$('#popup_content')
				.dialog('option', 'closeOnEscape', true)
				.dialog('option', 'open', function () {
				    $(document).keypress(function (e) {
					var keycode = (e.keyCode ? e.keyCode : e.which);
					if(keycode == 13) {
					    //click first button (on alert type is "ok" button)
					    $('#popup_content').parent().find("button:eq(0)").trigger("click");
					}
				    });
				})
				.dialog('option', 'close', function () {
				    $(document).unbind("keypress");
				    $("#popup_content").remove();
				})
				.dialog("option", "buttons", [
				    {
					text: $.alerts.okButton,
					click: function () {
					    $.alerts._hide();
					    if(callback) {
						callback(true);
					    }
					}
				    }
				]);
			break;
		    case 'confirm':
			$('#popup_content')
				.dialog('option', 'closeOnEscape', false)
				.dialog('option', 'open', function () {
				    $(document).keypress(function (e) {
					var keycode = (e.keyCode ? e.keyCode : e.which);
					if(keycode == 13) {
					    //click first button (on confirm type is "ok" button)
					    $('#popup_content').parent().find("button:eq(0)").trigger("click");
					}
					if(keycode == 27) {
					    //click second button (on confirm type is "cancel" button)
					    $('#popup_content').parent().find("button:eq(1)").trigger("click");
					}
				    });
				})
				.dialog('option', 'close', function () {
				    $(document).unbind("keypress");
				    $("#popup_content").remove();
				})
				.dialog("option", "buttons", [
				    {
					text: $.alerts.okButton,
					click: function () {
					    $.alerts._hide();
					    if(callback) {
						callback(true);
					    }
					}
				    },
				    {
					text: $.alerts.cancelButton,
					click: function () {
					    $.alerts._hide();
					    if(callback) {
						callback(false);
					    }
					}
				    }
				]);
			break;
//		case 'prompt':
//		    $("#pop_message").append('<br /><input type="text" size="30" id="popup_prompt" />').after('<div id="pop_panel"><input type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>');
//		    $("#popup_prompt").width($("#pop_message").width());
//		    $("#popup_ok").click(function () {
//			var val = $("#popup_prompt").val();
//			$.alerts._hide();
//			if(callback)
//			    callback(val);
//		    });
//		    $("#popup_cancel").click(function () {
//			$.alerts._hide();
//			if(callback)
//			    callback(null);
//		    });
//		    $("#popup_prompt, #popup_ok, #popup_cancel").keypress(function (e) {
//			if(e.keyCode == 13)
//			    $("#popup_ok").trigger('click');
//			if(e.keyCode == 27)
//			    $("#popup_cancel").trigger('click');
//		    });
//		    if(value)
//			$("#popup_prompt").val(value);
//		    $("#popup_prompt").focus().select();
//		    break;
		}
		$('#popup_content').dialog('open');
	    },
	    _hide: function () {
		$("#popup_content").dialog('close');
		$("#popup_content").remove();
	    }
	}

	// Shortuct functions
	jAlert = function (message, title, callback) {
	    $.alerts.alert(message, title, callback);
	}

	jConfirm = function (message, title, callback) {
	    $.alerts.confirm(message, title, callback);
	};
//not yet implemented
	jPrompt = function (message, value, title, callback) {
	    $.alerts.prompt(message, value, title, callback);
	};

    })(jQuery);