//IE8 live() bug in jq 1.3.2, functions required
    function togglePatientRelatedEventType(element) {

	if(element.val() == 10) {
	    //forced to hide by changing css because of show() which changes display:block=> freak in tables :
	    $('.patientSelRow').css("display", "table-row");
	} else {
	    //revese
	    $('.patientSelRow').css("display", "none");
	}
    }
    function toggleTimePeriods(element) {
	if(element.is(':checked')) {
	    $('#customShift-' + element.attr('id')).hide();
	} else {
	    $('#customShift-' + element.attr('id')).show();
	}
    }
    function toggleEventType(element) {
	if($('#' + element.attr('id') + ' option:selected').val() == 4) {
	    $('#endDateRow').hide();
//	    $('#startDateRow').hide();
	    $('#eventNameRow').hide();
	    $('#allDayRow').hide();
	    $('#usersSelect').show();

	} else if($('#' + element.attr('id') + ' option:selected').val() >= 12) {

	    $('#startDateRow').show();
	    $('#endDateRow').show();

	    $('#eventNameRow').show();
	    $('#allDayRow').show();
	    $('#usersSelect').hide();
	}
    }
    function toggleEventAllDayT(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeT').val(" ");
	    $('#startDateTimeT').hide();

	    $('#endDateTimeT').val(" ");
	    $('#endDateTimeT').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeT').val(" ");
	    $('#startDateTimeT').show();

	    $('#endDateTimeT').val(" ");
	    $('#endDateTimeT').show();
	}
    }
    function toggleEventAllDay(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTime').val(" ");
	    $('#startDateTime').hide();

	    $('#endDateTime').val(" ");
	    $('#endDateTime').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTime').val(" ");
	    $('#startDateTime').show();

	    $('#endDateTime').val(" ");
	    $('#endDateTime').show();
	}
    }
    function toggleEventAllDayE(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects

	    $('#startDateTimeE').hide();
	    $('#endDateTimeE').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeE').show();
	    $('#endDateTimeE').show();
	}
    }

    function toggleEventAllDayET(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeET').hide();
	    $('#endDateTimeET').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeET').show();
	    $('#endDateTimeET').show();
	}
    }
    function toggleEventAllDayEP(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeEP').val(" ");
	    $('#startDateTimeEP').hide();

	    $('#endDateTimeEP').val(" ");
	    $('#endDateTimeEP').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeEP').val(" ");
	    $('#startDateTimeEP').show();

	    $('#endDateTimeEP').val(" ");
	    $('#endDateTimeEP').show();
	}
    }
    function toggleEventAllDayP(element) {
	if($("input[name='" + element.attr('name') + "']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeP').val(" ");
	    $('#startDateTimeP').hide();

	    $('#endDateTimeP').val(" ");
	    $('#endDateTimeP').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeP').val(" ");
	    $('#startDateTimeP').show();

	    $('#endDateTimeP').val(" ");
	    $('#endDateTimeP').show();
	}
    }
    
//start document.ready
$(document).ready(function() {
	var roster_blocked_element;
	if(roster_page === '1')
	{
	    roster_blocked_element = '#MainContent';
	} 
	//ISPC-2827 Ancuta 30.03.2021
	else if(efa_page === '1')
	{
	    roster_blocked_element = '#efa_calendar';
	} 
	//--
	else {
	    roster_blocked_element = '#tabs-3';
	}
/*************************************************************************** PATIENT ****************************************************************************/
	$("input[name='allDayP']").live('change', function() {
	    if($("input[name='allDayP']:checked").val() == '1') {
		//disable and hide time selects
		$('#startDateTimeP').val(" ");
		$('#startDateTimeP').hide();

		$('#endDateTimeP').val(" ");
		$('#endDateTimeP').hide();
	    } else {
		//enable and show time selects
		$('#startDateTimeP').val(" ");
		$('#startDateTimeP').show();

		$('#endDateTimeP').val(" ");
		$('#endDateTimeP').show();
	    }
	});
	$("input[name='allDayEP']").live('change', function() {
	    if($("input[name='allDayEP']:checked").val() == '1') {
		//disable and hide time selects
		$('#startDateTimeEP').val(" ");
		$('#startDateTimeEP').hide();

		$('#endDateTimeEP').val(" ");
		$('#endDateTimeEP').hide();
	    } else {
		//enable and show time selects
		$('#startDateTimeEP').val(" ");
		$('#startDateTimeEP').show();

		$('#endDateTimeEP').val(" ");
		$('#endDateTimeEP').show();
	    }
	});

	/***************************************************************************  DOCTOR  ****************************************************************************/
	//allday doctors w/ edit
	$("input[name='allDay']").live('change', function() {
	    if($("input[name='allDay']:checked").val() == '1') {
		//disable and hide time selects
		$('#startDateTime').val(" ");
		$('#startDateTime').hide();

		$('#endDateTime').val(" ");
		$('#endDateTime').hide();
	    } else {
		//enable and show time selects
		$('#startDateTime').val(" ");
		$('#startDateTime').show();

		$('#endDateTime').val(" ");
		$('#endDateTime').show();
	    }
	});

	$("input[name='allDayE']").live('change', function() {
	    if($("input[name='allDayE']:checked").val() == '1') {
		//disable and hide time selects

		$('#startDateTimeE').hide();
		$('#endDateTimeE').hide();
	    } else {
		//enable and show time selects
		$('#startDateTimeE').show();
		$('#endDateTimeE').show();
	    }
	});

	if($("input[name='allDay']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTime').val(" ");
	    $('#startDateTime').hide();

	    $('#endDateTime').val(" ");
	    $('#endDateTime').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTime').val(" ");
	    $('#startDateTime').show();

	    $('#endDateTime').val(" ");
	    $('#endDateTime').show();
	}

	if($("input[name='allDayE']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeE').hide();
	    $('#endDateTimeE').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeE').show();
	    $('#endDateTimeE').show();
	}
	//allday Team w/ edit
	$("input[name='allDayT']").live('change', function() {
	    if($("input[name='allDayT']:checked").val() == '1') {
		//disable and hide time selects
		$('#startDateTimeT').val(" ");
		$('#startDateTimeT').hide();

		$('#endDateTimeT').val(" ");
		$('#endDateTimeT').hide();
	    } else {
		//enable and show time selects
		$('#startDateTimeT').val(" ");
		$('#startDateTimeT').show();

		$('#endDateTimeT').val(" ");
		$('#endDateTimeT').show();
	    }
	});
	$("input[name='allDayET']").live('change', function() {
	    if($("input[name='allDayET']:checked").val() == '1') {
		//disable and hide time selects

		$('#startDateTimeET').hide();
		$('#endDateTimeET').hide();
	    } else {
		//enable and show time selects
		$('#startDateTimeET').show();
		$('#endDateTimeET').show();
	    }
	});

	if($("input[name='allDayT']:checked").val() == '1') {
	    //disable and hide time selects
	    $('#startDateTimeT').val(" ");
	    $('#startDateTimeT').hide();

	    $('#endDateTimeT').val(" ");
	    $('#endDateTimeT').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeT').val(" ");
	    $('#startDateTimeT').show();

	    $('#endDateTimeT').val(" ");
	    $('#endDateTimeT').show();
	}

	if($("input[name='allDayET']:checked").val() == '1') {
	    //disable and hide time selects

	    $('#startDateTimeET').hide();
	    $('#endDateTimeET').hide();
	} else {
	    //enable and show time selects
	    $('#startDateTimeET').show();
	    $('#endDateTimeET').show();
	}

	$('#eventTypeT').live('change', function() {
	    if($('#eventTypeT option:selected').val() == 4) {
		$('#endDateRow').hide();
//		$('#startDateRow').hide();
		$('#eventNameRow').hide();
		$('#allDayRow').hide();
		$('#usersSelect').show();
	    } else if($('#eventTypeT option:selected').val() >= 12) {
		$('#endDateRow').show();
//		$('#startDateRow').show();
		$('#eventNameRow').show();
		$('#allDayRow').show();
		$('#usersSelect').hide();
	    }
	});
	if($('#eventTypeT option:selected').val() == 4) {
	    $('#endDateRow').hide();
//	    $('#startDateRow').hide();
	    $('#eventNameRow').hide();
	    $('#allDayRow').hide();
	    $('#usersSelect').show();

	} else if($('#eventTypeT option:selected').val() >= 12) {

	    $('#startDateRow').show();
	    $('#endDateRow').show();

	    $('#eventNameRow').show();
	    $('#allDayRow').show();
	    $('#usersSelect').hide();
	}
//	add new event doctors
	$("#addnewevent").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 400,
	    width: 620,
	    modal: true,
	    buttons: {
		"Termin hinzufügen": function() {
		    if($("input[name='allDay']:checked").val() == '1') {
				var allDaySelected = true;
				var startDateTimeEntered = " 00:00:00";
				var endDateTimeEntered = " 00:00:00";
		    } else {
				var allDaySelected = false;
				var startDateTimeEntered = " " + $('#startDateTime').val() + ":00";
				var endDateTimeEntered = " " + $('#endDateTime').val() + ":00";
		    }
		    var eventTitle = $('#eventTitle').val();
		    //transform dd.mm.yyyy h:i in yyyy-mm-dd h:i:00
		    var startDateEntered = $('#startDate').val().split('.');
		    var endDateEntered = $('#endDate').val().split('.');
		    var viewForAll = "0";
		    var dayplan_inform = $('input[name=dayplan_inform]:checked', this).val() || 0;
		    var _comments = $('textarea[name=comments]', this).val() || '';

		    if($('#viewForAll').is(':checked')) {
			viewForAll = "1";
		    } else {
			viewForAll = "0";
		    }

		    var eventType = $('#eventType').val();
		    var allDay = $("input[name='allDay']:checked").val();
		    var patientSelected = $('#selectPatient').val();
		    var createDate = $('#createDate').val()

		    if (eventType == 10 && patientSelected == 0) {
		    	if ( ! $(".selector_error_selectPatient", this).length)  {
		    		$("#selectPatient", this).after("<font style='font-weight: bold;color:red;' class='selector_error_selectPatient'><br>* "+translate('Please select a patient first') + "</font>");
		    	}
		    	return;
		    }
		    
		    var _that = this;//dialog
		    $.ajax({
			type: 'POST',
			url: 'calendar/savedoctorevents',
			async: true,
			data: {
				'eventTitle'	: eventTitle, 
				'startDate'		: startDateEntered[2] + "-" + startDateEntered[1] + "-" + startDateEntered[0] + startDateTimeEntered, 
				'endDate'		: endDateEntered[2] + "-" + endDateEntered[1] + "-" + endDateEntered[0] + endDateTimeEntered ,
				'eventType'		: eventType ,
				'allDay'		: allDay ,
				'patientSelected': patientSelected, 
				'viewForAll'	: viewForAll ,
				'dayplan_inform': dayplan_inform,
				'comments'		: _comments,
			}
			,
			success: function(responseText) {

				if ($(_that).data('parentTab') !== undefined) {
				    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
				} else {
					$('#calendar').fullCalendar('refetchEvents');					
				}
				
			}
		    });
		    $("#addnewevent").dialog("close");
		}
	    },
	    close: function() {
			$("#startDate, #endDate").datepicker('hide');
			$("#startDate, #endDate").datepicker('destroy');
			$("#ui-timepicker-div").hide();
	    },
	    open: function() {
			$('#eventTitle').val("");
			$('#viewForAll').attr('checked', false);
			$('#eventType').live('change', function() {
	
			    if($(this).val() == 10) {
				//forced to hide by changing css because of show() which changes display:block=> freak in tables :
				$('.patientSelRow').css("display", "table-row");
			    } else {
				//revese
				$('.patientSelRow').css("display", "none");
			    }
			});
			//datepicker
			$("#startDate, #endDate").datepicker({
			    dateFormat: 'dd.mm.yy',
			    showOn: "both",
			    buttonImage: $('#calImg').attr('src'),
			    buttonImageOnly: true
			});
			//timepicker
			$('#startDateTime, #endDateTime').timepicker({
			    minutes: {
				interval: 5
			    },
			    showPeriodLabels: false,
			    rows: 4,
			    hourText: 'Stunde',
			    minuteText: 'Minute'
			});
			$('#dayplan_inform_off', this).attr('checked', true);
			
			$('textarea[name=comments]', this).val('');
			
			$('#allDayOff', this).attr('checked', true).trigger('click');
			
			$(".selector_error_selectPatient", this).remove();
			
	    }
	});

	function toJSON(obj) {
	    var json = '{';
	    $.each(obj, function(k, v) {
		var q = typeof v == 'string' ? ~v.indexOf("'") ? "'" : '"' : '';
		if(typeof v == 'object')
		    v = toJSON(v).slice(0, -1).substr(1);
		json += q + k + q + ':' + q + v + q + ',';
	    });
	    return json.slice(0, -1) + '}';
	}
	;

	$("#editEvent").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 400,
	    width: 620,
	    modal: true,
	    buttons: {
		"Termin bearbeiten": function() {

		    var eventId = $('#eventIdE').val();

		    if($("input[name='allDayE']:checked").val() == '1') {
			var startDateTimeEntered = " 00:00:00";
			var endDateTimeEntered = " 00:00:00";
		    } else {
			var startDateTimeEntered = " " + $('#startDateTimeE').val() + ":00";
			var endDateTimeEntered = " " + $('#endDateTimeE').val() + ":00";
		    }
		    var eventTitle = $('#eventTitleE').val();
		    //transform dd.mm.yyyy h:i in yyyy-mm-dd h:i:00
		    var startDateEntered = $('#startDateE').val().split('.');
		    var endDateEntered = $('#endDateE').val().split('.');
		    var eventType = $('#eventTypeE').val();
		    var allDay = $("input[name='allDayE']:checked").val();
		    var createDate = $('#createDateE').val();
		    var viewForAll = "0";
		    var dayplan_inform = $('input[name=dayplan_inform]:checked', this).val() || 0;
		    var _comments = $('textarea[name=comments]', this).val() || '';
		    
		    if($('#viewForAllE').is(':checked')) {
			viewForAll = "1";
		    } else {
			viewForAll = "0";
		    }

		    var _that = this;
		    
		    var patientSelected = $('#selectPatientE').val();
		    $.ajax({
			type: 'POST',
			url: 'calendar/savedoctorevents',
			async: true,
			data: {
				'eventId' : eventId ,
				'eventTitle' : eventTitle ,
				'startDate' : startDateEntered[2] + "-" + startDateEntered[1] + "-" + startDateEntered[0] + startDateTimeEntered ,
				'endDate' : endDateEntered[2] + "-" + endDateEntered[1] + "-" + endDateEntered[0] + endDateTimeEntered ,
				'eventType' : eventType ,
				'allDay' : allDay ,
				'patientSelected' : patientSelected ,
				'cDate' : createDate ,
				'viewForAll' : viewForAll ,
				'dayplan_inform' : dayplan_inform,
				'comments' : _comments,
			},
			
			success: function(responseText) {

				if ($(_that).data('parentTab') !== undefined) {
				    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
				} else {
					$('#calendar').fullCalendar('refetchEvents');					
				}
			    
			}
		    });
		    $("#editEvent").dialog("close");
		},
		"Entfernen": function() {

		    var eventId = $('#eventIdE').val();
		    var eventTitle = $('#eventTitleE').val();

		    $('#evTitle').html('<b>' + eventTitle + '</b>');
		    $('#parentModal').val($(this).attr('id'));
		    
		    $('#delEvent')
		    .data('parentTab', $(this).data('parentTab'))
		    .dialog('open');

		}
	    },
	    close: function() {
			$("#startDateE, #endDateE").datepicker('hide');
			$("#startDateE, #endDateE").datepicker('destroy');
			$("#ui-timepicker-div").hide();
			$(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).removeClass("ui-state-disabled");
			$(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).addClass("ui-state-enabled");

	    },
	    open: function() {
	    	
	    	
			$('#deletespan').remove();
	//		$(".ui-dialog-buttonpane button:contains('Entfernen')").button({icons: {primary: 'ui-icon-trash'}});
			$('input').blur();
		    
			//To Do here reset uiclassdisabled =>enabled
			var eventType = $('#eventTypeE').val();
			if(eventType == "10" || eventType == "11" || eventType == "14") {
			    $(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).removeClass("ui-state-disabled").addClass("ui-state-default");
			    
			} else {
			    $(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", true).addClass("ui-state-disabled");
			}
			
			//ISPC-2175
			var _eventType_DoctorCustomEvents = [10, 11, 14]; //this are on Calendar... in Patient you have 10, 14
			var _calEvent = $(this).data('calEvent') || {};
			if (_eventType_DoctorCustomEvents.indexOf(Number(_calEvent.eventType)) != -1) {
				$('textarea[name=comments]', this).val(_calEvent.comments).show();
			} else {
				$('textarea[name=comments]', this).val('').hide();
			}
	    }

	});


	

	$("#addneweventteam").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 400,
	    width: 620,
	    modal: true,
	    buttons: {
			"Termin hinzufügen": function() {
	
			    if($("input[name='allDayT']:checked").val() == '1') {
				var startDateTimeEntered = " 00:00:00";
				var endDateTimeEntered = " 00:00:00";
			    } else {
				var startDateTimeEntered = " " + $('#startDateTimeT').val() + ":00";
				var endDateTimeEntered = " " + $('#endDateTimeT').val() + ":00";
			    }
			    var eventTitle = $('#eventTitleT').val();
			    var dayplan_inform = $('input[name=dayplan_inform]:checked', this).val() || 0;
	
			    //transform dd.mm.yyyy h:i in yyyy-mm-dd h:i:00
			    var startDateEntered = $('#startDateT').val().split('.');
			    var endDateEntered = $('#endDateT').val().split('.');
	
	
			    var eventType = $('#eventTypeT').val();
			    var allDay = $("input[name='allDayT']:checked").val();
			    var selectedUsers = [];
			    var checkedChecks = [];
	
			    var startShiftDate = [];
			    var startShiftTime = [];
			    var endShiftDate = [];
			    var endShiftTime = [];
			    $("select[name='userGroup[]'] > option:selected").each(function() {
				selectedUsers.push($(this).val());
			    });
	
			    $("input[name='fullShift[]']").each(function() {
				if($(this).is(':checked')) {
				    checkedChecks.push($(this).val());
				} else {
				    checkedChecks.push("0");
				}
			    });
	
			    $("input[name='startShiftDate[]']").each(function() {
				if($(this).val() != "" || $(this).val() != " ") {
				    startShiftDate.push($(this).val());
				} else {
				    startShiftDate.push("0");
				}
			    });
			    $("input[name='startShiftTime[]']").each(function() {
				if($(this).val() != "" || $(this).val() != " ") {
				    startShiftTime.push($(this).val());
				} else {
				    startShiftTime.push("0");
				}
			    });
			    $("input[name='endShiftDate[]']").each(function() {
				if($(this).val() != "" || $(this).val() != " ") {
				    endShiftDate.push($(this).val());
				} else {
				    endShiftDate.push("0");
				}
			    });
	
			    $("input[name='endShiftTime[]']").each(function() {
				if($(this).val() != "" || $(this).val() != " ") {
				    endShiftTime.push($(this).val());
				} else {
				    endShiftTime.push("0");
				}
			    });
	
	
			    var finalSelectedUsers = toJSON(selectedUsers);
			    var finalCheckedChecks = toJSON(checkedChecks);
	
	
			    var finalStartShiftDate = toJSON(startShiftDate);
			    var finalStartShiftTime = toJSON(startShiftTime);
	
			    var finalEndShiftDate = toJSON(endShiftDate);
			    var finalEndShiftTime = toJSON(endShiftTime);
			    
			    var _that = this;
			    var _comments = $('textarea[name=comments]', this).val() || '';
			    
			    $.ajax({
					type: 'POST',
					url: 'calendar/saveteamevents',
					async: true,
					data: {
						'eventTitle' : eventTitle, 
						'startDate' : startDateEntered[2] + "-" + startDateEntered[1] + "-" + startDateEntered[0] + startDateTimeEntered ,
						'endDate' : endDateEntered[2] + "-" + endDateEntered[1] + "-" + endDateEntered[0] + endDateTimeEntered ,
						'allDay' : allDay ,
						'eventType' : eventType ,
						'selectedUsers' : finalSelectedUsers ,
						'checked' : finalCheckedChecks ,
						'startShiftDate' : finalStartShiftDate ,
						'startShiftTime' : finalStartShiftTime ,
						'endShiftDate' : finalEndShiftDate ,
						'endShiftTime' : finalEndShiftTime ,
						'dayplan_inform' : dayplan_inform ,
						'comments' : _comments ,
					},
					success: function(responseText) {
						
						if ($(_that).data('parentTab') !== undefined) {
						    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
						} else {
							$('#teamCalendar').fullCalendar('refetchEvents');					
						}
					}
			    });
			    $("#addneweventteam").dialog("close");
			}
	    },
	    close: function() {
			$("#startDateT, #endDateT").datepicker('hide');
			$("#startDateT, #endDateT").datepicker('destroy');
			$("#ui-timepicker-div").hide();
	    },
	    open: function() {
	    	
			$('#eventTitleT').val("");
			$('#eventTypeT').val("");
			toggleEventType($('#eventTypeT'));
			$('select[name="userGroup[]"]').each(function() {
			    $(this).val("");
			})
			//datepicker
			$("#startDateT, #endDateT").datepicker({
			    dateFormat: 'dd.mm.yy',
			    showOn: "both",
			    buttonImage: $('#calImg').attr('src'),
			    buttonImageOnly: true
			});
			$('div#addneweventteam input.datepick').datepicker({
			    dateFormat: 'dd.mm.yy',
			    showOn: "focus"
			});
			$('div#addneweventteam input.timepick').timepicker({
			    minutes: {
				interval: 5
			    },
			    showPeriodLabels: false,
			    rows: 4,
			    hourText: 'Stunde',
			    minuteText: 'Minute'
			});
			
			$('.fullshift').live('change', function() {
			    if($(this).is(':checked')) {
				$('#customShift-' + $(this).attr('id')).hide();
			    } else {
				$('#customShift-' + $(this).attr('id')).show();
			    }
			});
			$('.fullshift').each(function() {
			    if($(this).is(':checked')) {
				$('#customShift-' + $(this).attr('id')).hide();
			    } else {
				$('#customShift-' + $(this).attr('id')).show();
			    }
			});
			
			$("input[type=checkbox]").not('.day_checkbox, .allCalendarTypesCb').each(function() {
			    $(this).attr('checked', true);
			    $('#customShift-' + $(this).attr('id')).hide();
			});
	
			
			//timepicker
			$('#startDateTimeT, #endDateTimeT').timepicker({
			    minutes: {
				interval: 5
			    },
			    showPeriodLabels: false,
			    rows: 4,
			    hourText: 'Stunde',
			    minuteText: 'Minute'
			});
	
			//accordion
			$("#groups").accordion({
			    autoHeight: false
			});
			$('#dayplan_inform_off',this).attr('checked', true);
			
			$('#allDayOffT', this).attr('checked', true).trigger('click')
			
			$('textarea[name=comments]', this).val('');
			
	    }
	});

	$("#editEventTeam").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 400,
	    width: 620,
	    modal: true,
	    buttons: {
		"Termin bearbeiten": function() {

		    var eventId = $('#eventIdET').val();

		    if($("input[name='allDayET']:checked").val() == '1') {
			var startDateTimeEntered = " 00:00:00";
			var endDateTimeEntered = " 00:00:00";
		    } else {
			var startDateTimeEntered = " " + $('#startDateTimeET').val() + ":00";
			var endDateTimeEntered = " " + $('#endDateTimeET').val() + ":00";
		    }
		    var eventTitle = $('#eventTitleET').val();
		    //transform dd.mm.yyyy h:i in yyyy-mm-dd h:i:00
		    var startDateEntered = $('#startDateET').val().split('.');
		    var endDateEntered = $('#endDateET').val().split('.');
		    var eventType = $('#eventTypeET').val();
		    var allDay = $("input[name='allDayET']:checked").val();
		    
		    
		    var selectedUsers = [];
		    $("select[name='userGroup[]'] > option:selected").each(function() {
			selectedUsers.push($(this).val());
		    });
		    var finalSelectedUsers = toJSON(selectedUsers);
		    var dayplan_inform = $('input[name=dayplan_inform]:checked', this).val() || 0;
		    var _comments = $('textarea[name=comments]', this).val() || '';
		    
		    var _that = this;
		    
		    $.ajax({
			type: 'POST',
			url: 'calendar/saveteamevents',
			async: true,
			data: {				
				'eventId' : eventId ,
				'eventTitle' : eventTitle ,
				'startDate' : startDateEntered[2] + "-" + startDateEntered[1] + "-" + startDateEntered[0] + startDateTimeEntered ,
				'endDate' : endDateEntered[2] + "-" + endDateEntered[1] + "-" + endDateEntered[0] + endDateTimeEntered ,
				'allDay' : allDay ,
				'eventType' : eventType ,
				'selectedUsers' : finalSelectedUsers,
				'dayplan_inform' : dayplan_inform ,
				'comments' : _comments,
			},
			success: function(responseText) {
				
				if ($(_that).data('parentTab') !== undefined) {
				    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
				} else {
					$('#teamCalendar').fullCalendar('refetchEvents');					
				}
			}
		    });
		    $("#editEventTeam").dialog("close");
		},
		"Entfernen": function() {

		    var eventId = $('#eventIdET').val();
		    var eventTitle = $('#eventTitleET').val();

		    $('#evTitleT').html('<b>' + eventTitle + '</b>');
		    $('#parentModalT').val($(this).attr('id'));
		    
		    $('#delEventT')
		    .data('parentTab', $(this).data('parentTab'))
		    .dialog('open');

		}
	    },
	    close: function() {
			$("#startDateET, #endDateET").datepicker('hide');
			$("#startDateET, #endDateET").datepicker('destroy');
			$("#ui-timepicker-div").hide();
	
			$(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).removeClass("ui-state-disabled");
			$(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).addClass("ui-state-enabled");
	    },
	    open: function() {
			$('input').blur();
	
			var eventType = $('#eventTypeET').val();
			//this enables the delete event button for following event ids
			if(eventType == "10" || eventType == "11" || eventType == "12" || eventType == "13" || eventType == "14" || eventType == "15"
			|| eventType == "16" || eventType == "17" || eventType == "20" || eventType == "21" || eventType == "22") {
			    $(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", false).addClass("ui-state-default");
			} else {
			    $(".ui-dialog-buttonpane button:contains('Entfernen')").attr("disabled", true).addClass("ui-state-disabled");
			}

			//ISPC-2175
			var _eventType_TeamCustomEvents = [12, 13, 14, 15, 16, 17, 20, 21, 22];			
			var _calEvent = $(this).data('calEvent') || {};
			if (_eventType_TeamCustomEvents.indexOf(Number(_calEvent.eventType)) != -1) {
				$('textarea[name=comments]', this).val(_calEvent.comments).show();
			} else {
				$('textarea[name=comments]', this).val('').hide();
			}
			
	    }
	});
	
//print doctor calendar stuffs
	$('#calendarPrint').fullCalendar({
	    editable: false,
	    header: {
		left: '',
		center: 'title',
		right: ''
	    },
	    allDayText: 'ganztags',
	    loading: function(bool) {
		if(bool) {

		} else {

		    setTimeout('window.print()', 2500);
		}
	    },
	    events: "calendar/fetchdoctorsevents",
	    timeFormat: 'HH:mm{ - HH:mm}\n', //H pt 24h
	    axisFormat: 'HH:mm',
	    aspectRatio: 1,
	    disableResizing: true,
	    defaultView: 'month',
	    weekMode: 'liquid',
	    firstDay: 1,
	    slotMinutes: 30,
	    theme: true,
	    monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	    monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'],
	    dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
	    dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
	    buttonText: {
		today: 'heute',
		month: 'monat',
		week: 'woche',
		day: 'tag'
	    },
	    columnFormat: {
		month: 'ddd', // Mon
		week: 'ddd d.M', // Mon 9/7
		day: 'dddd d.M.yyyy'  // Monday 9/7
	    },
	    titleFormat: {
		month: 'MMMM yyyy', // September 2009
		week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep 7 - 13 2009
		//		    day: 'dddd, MMM d, yyyy'                  // Tuesday, Sep 8, 2009
		day: 'dddd d.M.yyyy'                  // Tuesday, Sep 8, 2009
	    }
	});
//print team calendar stuffs
	$('#teamCalendarPrint').fullCalendar({
	    editable: false,
	    header: {
		left: '',
		center: 'title',
		right: ''
	    },
	    allDayText: 'ganztags',
	    loading: function(bool) {
		if(bool) {

		} else {

		    setTimeout('window.print()', 2500);
		}
	    },
	    events: "calendar/fetchteamevents",
	    timeFormat: 'HH:mm{ - HH:mm}\n', //H pt 24h
	    axisFormat: 'HH:mm',
	    aspectRatio: 1,
	    disableResizing: true,
	    defaultView: 'month',
	    weekMode: 'liquid',
	    firstDay: 1,
	    slotMinutes: 30,
	    theme: true,
	    monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	    monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'],
	    dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
	    dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
	    buttonText: {
		today: 'heute',
		month: 'monat',
		week: 'woche',
		day: 'tag'
	    },
	    columnFormat: {
		month: 'ddd', // Mon
		week: 'ddd d.M', // Mon 9/7
		day: 'dddd d.M.yyyy'  // Monday 9/7
	    },
	    titleFormat: {
		month: 'MMMM yyyy', // September 2009
		week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep 7 - 13 2009
		//		    day: 'dddd, MMM d, yyyy'                  // Tuesday, Sep 8, 2009
		day: 'dddd d.M.yyyy'                  // Tuesday, Sep 8, 2009
	    }
	});


	$("#delEvent").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 150,
	    width: 300,
	    modal: true,
	    title: 'Entfernen',
	    buttons: {
		"Ja": function() {
		    var eventId = $('#eventIdE').val();
		    var eventType = $('#eventTypeE').val();
		    
		    var _that = this;
		    
		    $.ajax({
				type: 'POST',
				url: 'patient/delevents?calendar=doc',
				async: true,
				data: 'eventId=' + eventId + '&eventType=' + eventType,
				success: function(responseText) {
					
					if ($(_that).data('parentTab') !== undefined) {
						$('#' + $(_that).data('parentTab')).fullCalendar('unselect');
					    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
					} else {
						$('#calendar').fullCalendar('unselect');
						$('#calendar').fullCalendar('refetchEvents');					
					}
				}
		    });

		    $('#' + $("#parentModal").val() + '').dialog("close");
		    $("#delEvent").dialog("close");
		},
		"Nein": function() {
		    $("#delEvent").dialog('close');
		}
	    },
	    close: function() {
		$("#startDateE, #endDateE").datepicker('hide');
		$("#startDateE, #endDateE").datepicker('destroy');
		$("#ui-timepicker-div").hide();
	    }
	});
	$("#delEventT").dialog({
	    autoOpen: false,
	    resizable: false,
	    height: 150,
	    width: 300,
	    modal: true,
	    title: 'Entfernen',
	    buttons: {
		"Ja": function() {
		    var eventId = $('#eventIdET').val();
		    var eventType = $('#eventTypeET').val();

		    var _that = this;
		    
		    $.ajax({
				type: 'POST',
				url: 'patient/delevents?calendar=team',
				async: true,
				data: 'eventId=' + eventId + '&eventType=' + eventType,
				success: function(responseText) {
					if ($(_that).data('parentTab') !== undefined) {
						$('#' + $(_that).data('parentTab')).fullCalendar('unselect');
					    $('#' + $(_that).data('parentTab')).fullCalendar('refetchEvents');
					} else {
						$('#teamCalendar').fullCalendar('unselect');
						$('#teamCalendar').fullCalendar('refetchEvents');					
					}
				}
		    });

		    $('#' + $("#parentModalT").val() + '').dialog("close");
		    $("#delEventT").dialog("close");
		},
		"Nein": function() {
		    $("#delEventT").dialog('close');
		}
	    },
	    close: function() {
		$("#startDateE, #endDateE").datepicker('hide');
		$("#startDateE, #endDateE").datepicker('destroy');
		$("#ui-timepicker-div").hide();
	    }
	});
    
	
	
	
	
	
	
	
}); //eo document.ready


var created_calendar_calendar = false;

function create_calendar_calendar(  _startYear, _startMonth, _startDay ) {
	
	created_calendar_calendar = true;

	var roster_blocked_element;

	if (roster_page === '1') {
		roster_blocked_element = '#MainContent';
	} 
	//ISPC-2827 Ancuta 30.03.2021
	else if (efa_page === '1') {
		roster_blocked_element = '#efa_calendar';
	} 
	//--
	else {
		roster_blocked_element = '#tabs-3';
	}
	
	$('#calendar').fullCalendar({
	    header: {
		left: 'prev,next today',
		center: 'title',
		right: 'agendaDay,basicWeek,month'
	    },
	    
	    
	    events: "calendar/fetchdoctorsevents",
	    allDayText: 'ganztags',
	    
	    dayHeaderNumberClick : function(date, jsEvent, view){
			$('#calendar').fullCalendar('gotoDate', date);
			$('#calendar').fullCalendar('changeView', 'agendaDay');
		},
		
	    eventClick: function(calEvent, jsEvent, view) {
	    	eventClick_calendar(calEvent, jsEvent, view, 'calendar') ;
	    	
	    },
	    
	    eventResize: function(event, delta) { //save when resized(in agenda mode)
	    	eventResize_calendar(event, delta, 'calendar');
	    },
	    
	    eventDrop: function(event, delta) { //save when drag and drop
	    	eventDrop_calendar(event, delta, 'calendar');
	    },
	    
	    select: function(start, end, allDay) { //selected nonevent slot opens modal
	    	select_calendar(start, end, allDay , 'calendar');
	    },
	    
	    loading: function(bool) {
			if(bool)
			    $('#tabs-2').block({
				css: {
				    border: 'none',
				    padding: '15px',
				    backgroundColor: '#000',
				    '-webkit-border-radius': '10px',
				    '-moz-border-radius': '10px',
				    opacity: .5,
				    color: '#fff',
				    height: 'auto'
				},
				message: '<h2>Verarbeitung</h2>'
			    });
			else
			    $('#tabs-2').unblock();
	    },
	    
	    
	    
	    eventRender: qtip_renderer_comments,
	    
	    	    
	    editable: true,
	    timeFormat: 'HH:mm{ - HH:mm}\n', //H pt 24h
	    axisFormat: 'HH:mm',
	    aspectRatio: 2,
	    defaultView: 'month',
	    weekMode: 'liquid',
	    firstDay: 1,
	    slotMinutes: 30,
	    selectable: true,
	    selectHelper: true,
	    theme: true,
	    height: 850,
	    monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	    monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'],
	    dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
	    dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
	    buttonText: {
		today: 'heute',
		month: 'monat',
		week: 'woche',
		day: 'tag'
	    },
	    columnFormat: {
		month: 'ddd', // Mon
		week: 'ddd d.M', // Mon 9/7
		day: 'dddd d.M.yyyy'  // Monday 9/7
	    },
	    titleFormat: {
		month: 'MMMM yyyy', // September 2009
		week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep 7 - 13 2009
		//		    day: 'dddd, MMM d, yyyy'                  // Tuesday, Sep 8, 2009
		day: 'dddd d.M.yyyy'                  // Tuesday, Sep 8, 2009
	    }
	});
}

function eventClick_calendar(calEvent, jsEvent, view, parentTab)
{

	if(view.name != "agendaDay" && (calEvent.eventType == 1 || calEvent.eventType == 2 || (calEvent.eventType == 3 && calEvent.event_source != "newreceipt_1" && calEvent.event_source != "newreceipt_2") || calEvent.eventType == 4 || calEvent.eventType == 9 || calEvent.eventType == 10 || calEvent.eventType == 11 || calEvent.eventType >= 12 || calEvent.eventType == 15 || calEvent.eventType == 16 || calEvent.eventType == 17)) {
	    $('#ui-dialog-title-editEvent').html("Termin bearbeiten: " + calEvent.title + '');
	    //bind to dialog on opening event our clicked event values!
	    $("#editEvent")
	    .data('parentTab', parentTab)
	    .data('calEvent', calEvent)
	    .live("dialogopen", function(event, ui) {
		//event title
		$('#eventTitleE').val(calEvent.title);
		if(calEvent.eventType == 1 || calEvent.eventType == 2 || calEvent.eventType == 4 || calEvent.eventType == 9 || calEvent.eventType == 13 || calEvent.eventType == 15 || calEvent.eventType == 16 || calEvent.eventType == 17) {
//		    $('#eventTitleE').attr('readonly', true);
		    $('#eventTitleERow').hide();
		} else {
//		    $('#eventTitleE').attr('readonly', false);
		    $('#eventTitleERow').show();
		}


		//event id(hidden)
		$('#eventIdE').val(calEvent.id);
		$('#selectPatientE').val(calEvent.ipid);

		//event start-end date
		var start = new Date(calEvent.start);
		var startDay = "" + start.getDate();
		var startMonth = "" + (start.getMonth() + 1);
		var startYear = "" + start.getFullYear();
		var startHours = "" + start.getHours();
		var startMinutes = "" + start.getMinutes();
		var startSeconds = "" + start.getSeconds();

		if(startDay.length == "1") {
		    startDay = "0" + startDay;
		}
		
		if(startMonth.length == "1") {
		    startMonth = "0" + startMonth;
		}
		
		if(startHours.length == "1") {
		    startHours = "0" + startHours;
		}
		
		if(startMinutes.length == "1") {
		    startMinutes = "0" + startMinutes;
		}
		
		if(startSeconds.length == "1") {
		    startSeconds = "0" + startSeconds;
		}


		if(calEvent.end == null) {
		    calEvent.end = calEvent.start;
		}
		var end = new Date(calEvent.end);
		var endDay = "" + end.getDate();
		var endMonth = "" + (end.getMonth() + 1);
		var endYear = "" + end.getFullYear();
		var endHours = "" + end.getHours();
		var endMinutes = "" + end.getMinutes();
		var endSeconds = "" + end.getSeconds();

		if(endDay.length == "1") {
		    endDay = "0" + endDay;
		}
		if(endMonth.length == "1") {
		    endMonth = "0" + endMonth;
		}
		if(endHours.length == "1") {
		    endHours = "0" + endHours;
		}
		if(endMinutes.length == "1") {
		    endMinutes = "0" + endMinutes;
		}
		if(endSeconds.length == "1") {
		    endSeconds = "0" + endSeconds;
		}

		var finalStartDate = startDay + "." + startMonth + "." + startYear;
		var finalStartDateTime = startHours + ":" + startMinutes;

		var finalEndDate = endDay + "." + endMonth + "." + endYear;
		var finalEndDateTime = endHours + ":" + endMinutes;

		$('#startDateE').val(finalStartDate);
		$('#startDateTimeE').val(finalStartDateTime);

		$('#endDateE').val(finalEndDate);
		$('#endDateTimeE').val(finalEndDateTime);
		//event type
		$('#eventTypeE').val(calEvent.eventType);
		$('#createDateE').val(calEvent.createDate);
		//allDay
		if(calEvent.allDay == true) {
		    $('#allDayE').show();
		    $('#allDayOnE').attr('checked', true);
		    $('#allDayOffE').attr('checked', false);

		    $('#startDateTimeE').hide();
		    $('#endDateTimeE').hide();
		} else {
		    $('#allDayE').show();
		    $('#allDayOnE').attr('checked', false);
		    $('#allDayOffE').attr('checked', true);

		    $('#startDateTimeE').show();
		    $('#endDateTimeE').show();
		}
		//viewAll
		if(calEvent.viewForAll == "1") {
		    $('#viewForAllE').attr('checked', true);
		} else {
		    $('#viewForAllE').attr('checked', false);
		}

		if(calEvent.eventType == 1 || calEvent.eventType == 2 || calEvent.eventType == 9 || calEvent.eventType == 13 || calEvent.eventType == 15 || calEvent.eventType == 16 || calEvent.eventType == 17) {
		    $('#allDayEventERow').hide();
		    if(calEvent.eventType == 9 || calEvent.eventType == 15 || calEvent.eventType == 16 || calEvent.eventType == 17) {
			$('#endDateRowE').hide();
		    }
		    if(calEvent.eventType == 13) {
			$('#allViewRowE').hide();
		    }
		    $('#allDayOff').attr('checked', true);
		} else {
		    $('#allDayEventERow').show();
		    $('#endDateRowE').show();
		    $('#allViewRowE').show();

		}

		if(typeof(calEvent.dayplan_inform) === "undefined"){
			$("#dayplan_inform_Row",this).hide();
		}
		else if(calEvent.dayplan_inform == true) {
			$("#dayplan_inform_Row",this).show();
			$('#dayplan_inform_on',this).attr('checked', true);
			$('#dayplan_inform_off',this).attr('checked', false);
		}else{
			$("#dayplan_inform_Row",this).show();
			$('#dayplan_inform_on',this).attr('checked', false);
			$('#dayplan_inform_off',this).attr('checked', true);
		}
		

		/* Initialize datepicker and timepicker in modal */
		//datepicker
		$("#startDateE, #endDateE").datepicker({
		    dateFormat: 'dd.mm.yy',
		    showOn: "both",
		    buttonImage: $('#calImg').attr('src'),
		    buttonImageOnly: true
		});
		//timepicker
		$('#startDateTimeE, #endDateTimeE').timepicker({
		    minutes: {
			interval: 5
		    },
		    showPeriodLabels: false,
		    rows: 4,
		    hourText: 'Stunde',
		    minuteText: 'Minute'
		});

	    });
	    
	    $('#editEvent').data('parentTab', parentTab).data('calEvent', calEvent).dialog('open');
	}
}

function eventDrop_calendar(event, delta, parentTab)
{
	var start = new Date(event.start);
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = "" + start.getFullYear();
	var startHours = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();
	var startSeconds = "" + start.getSeconds();

	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}
	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHours.length == "1") {
	    startHours = "0" + startHours;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	if(startSeconds.length == "1") {
	    startSeconds = "0" + startSeconds;
	}

	var finalStartDate = startYear + "-" + startMonth + "-" + startDay + " " + startHours + ":" + startMinutes + ":" + startSeconds + "";

	var eventFinal = event;
	var deltaFinal = delta;
	var end = new Date(event.end);
	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = "" + end.getFullYear();
	var endHours = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();
	var endSeconds = "" + end.getSeconds();

	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHours.length == "1") {
	    endHours = "0" + endHours;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	if(endSeconds.length == "1") {
	    endSeconds = "0" + endSeconds;
	}

	if(event.end)
	{
	    var finalEndDate = endYear + "-" + endMonth + "-" + endDay + " " + endHours + ":" + endMinutes + ":" + endSeconds + "";
	}
	else
	{
	    var finalEndDate = finalStartDate;
	}

	if(eventFinal.allDay === true) {
	    var allDay = "1";
//			finalEndDate = finalStartDate;
	} else {
	    var allDay = "0";
	}
	jConfirm('Wollen Sie diesen Termin verschieben.', 'Bestätigung', function(r) {
	    var view = $('#'+parentTab).fullCalendar('getView');

	    if(r) {
			$.ajax({
			    type: 'POST',
			    url: 'calendar/savedoctorevents',
			    async: true,
			    //data: 'eventId=' + eventFinal.id + '&eventTitle=' + eventFinal.title + '&startDate=' + finalStartDate + '&endDate=' + finalEndDate + '&eventType=' + eventFinal.eventType + '&allDay=' + allDay + '&delta=' + deltaFinal + '&patientSelected=' + eventFinal.ipid + '&cDate=' + eventFinal.createDate + '&viewForAll=' + eventFinal.viewForAll,
			    data: {
			    	'eventId'		: eventFinal.id ,
			    	'eventTitle'	: eventFinal.title ,
			    	'startDate'		: finalStartDate ,
			    	'endDate'		: finalEndDate ,
			    	'eventType'		: eventFinal.eventType ,
			    	'allDay'		: allDay ,
			    	'delta'			: deltaFinal ,
			    	'patientSelected' : eventFinal.ipid ,
			    	'cDate'			: eventFinal.createDate ,
			    	'viewForAll'	: eventFinal.viewForAll,
			    	'comments'		: eventFinal.comments || '',
		    	},
			    success: function(responseText) {
					if(view.name != "agendaDay") {//fix for moving in agendaDay!
					    $('#'+parentTab).fullCalendar('refetchEvents'); //reload saved data
					}
			    }
			});
	    } else {
	    	$('#'+parentTab).fullCalendar('refetchEvents'); //refresh freezed event
	    }
	});
}

function eventResize_calendar(event, delta, parentTab)
{
	var start = new Date(event.start);
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = "" + start.getFullYear();
	var startHours = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();
	var startSeconds = "" + start.getSeconds();

	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}
	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHours.length == "1") {
	    startHours = "0" + startHours;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	if(startSeconds.length == "1") {
	    startSeconds = "0" + startSeconds;
	}

	var finalStartDate = startYear + "-" + startMonth + "-" + startDay + " " + startHours + ":" + startMinutes + ":" + startSeconds + "";

	var eventFinal = event;
	var deltaFinal = delta;
	var end = new Date(event.end);
	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = "" + end.getFullYear();
	var endHours = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();
	var endSeconds = "" + end.getSeconds();

	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHours.length == "1") {
	    endHours = "0" + endHours;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	if(endSeconds.length == "1") {
	    endSeconds = "0" + endSeconds;
	}

	var finalEndDate = endYear + "-" + endMonth + "-" + endDay + " " + endHours + ":" + endMinutes + ":" + endSeconds + "";

	if(eventFinal.allDay === true) {
	    var allDay = "1";
	} else {
	    var allDay = "0";
	}
	jConfirm('Wollen Sie diesen Termin verschieben.', 'Bestätigung', function(r) {
	    var view = $('#'+parentTab).fullCalendar('getView');
	    if(r) {
			$.ajax({
			    type: 'POST',
			    url: 'calendar/savedoctorevents',
			    async: true,
			    data: {
			    	'eventId' : eventFinal.id, 
			    	'eventTitle' : eventFinal.title, 
			    	'startDate' : finalStartDate ,
			    	'endDate' : finalEndDate ,
			    	'eventType' : eventFinal.eventType, 
			    	'allDay' : allDay ,
			    	'delta' : deltaFinal, 
			    	'patientSelected' : eventFinal.ipid, 
			    	'cDate' : eventFinal.createDate ,
			    	'viewForAll' : eventFinal.viewForAll,
			    	'comments' : eventFinal.comments || '',
			    	
			    },
			    success: function(responseText) {
					if(view.name != "agendaDay") {//fix for moving in agendaDay!
					    $('#'+parentTab).fullCalendar('refetchEvents'); //reload saved data
					}
			    }
			});
	    } else {
			if(view.name != "agendaDay") {//fix for moving in agendaDay!
			    $('#'+parentTab).fullCalendar('refetchEvents'); //reload saved data
			}
	    }
	});
}


function select_calendar(start, end, allDay , parentTab)
{

	$("#addnewevent").data('parentTab', parentTab).dialog('open');
	//modal title
	$('#ui-dialog-title-addnewevent').html("Termin hinzufügen");
	//get selected start/end date if is interval (start!= end) else is same day)

	//vars + put leading zeros to day and month
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = start.getFullYear();
	var startHour = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();

	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}
	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHour.length == "1") {
	    startHour = "0" + startHour;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	var startSelectedDate = startDay + '.' + startMonth + '.' + startYear;
	var startSelectedTime = startHour + ':' + startMinutes;

	//insert start date
	$('#startDate').val(startSelectedDate);


	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = end.getFullYear();
	var endHour = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();

	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHour.length == "1") {
	    endHour = "0" + endHour;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	var endSelectedDate = endDay + '.' + endMonth + '.' + endYear;
	var endSelectedTime = endHour + ':' + endMinutes;
	//insert end date
	$('#endDate').val(endSelectedDate);

	if(startSelectedDate == endSelectedDate && startSelectedTime == endSelectedTime) {

//	    $("#allDayOn").attr('checked', true); //ISPC-2175 removed
	    //disable and hide time selects
	    $('#startDateTime').val("");
//	    $('#startDateTime').hide();//ISPC-2175 removed

	    $('#endDateTime').val("");
//	    $('#endDateTime').hide();//ISPC-2175 removed
	} else {
//	    $("#allDayOff").attr('checked', true);//ISPC-2175 removed
	    //enable and show time selects
	    $('#startDateTime').val(startSelectedTime);
//	    $('#startDateTime').show();

	    $('#endDateTime').val(endSelectedTime);
//	    $('#endDateTime').show();

	}
	//remove selection!
	$('#'+parentTab).fullCalendar('unselect');
}







var created_calendar_teamCalendar = false;

function create_calendar_teamCalendar(  _startYear, _startMonth, _startDay ) {

	created_calendar_teamCalendar = true;

	var roster_blocked_element;

	var return_Tabs = ['teamCalendar', 'teamShiftsCalendar']; ////ISPC-2827 Ancuta 30.03.2021
	if (roster_page === '1' ) {
		roster_blocked_element = '#MainContent';
	} 
	//ISPC-2827 Ancuta 30.03.2021
	else if (efa_page === '1' ) {
		roster_blocked_element = '#efa_calendar';
		var return_Tabs = ['allPatientsCalendar'];
	} 
	// --
	else {
		roster_blocked_element = '#tabs-3';
	}
	
	$('#teamCalendar').fullCalendar({
		
	    editable: true,
	    
	    year	: _startYear,
	    month	: _startMonth,
	    date	: _startDay,
	    
	    /*events: "calendar/fetchteamevents",*/
	    events: {
			url: 'calendar/fetchallCalendarTypes',
			type: 'GET', // if you change into POST, multiple
			// changes needed
			data: function() { // add params
				return {
					//ISPC-2827 Ancuta 30.03.2021
					//'tabs': ['teamCalendar', 'teamShiftsCalendar'],
					'tabs': return_Tabs,
					//--
				};

			}
		},
		
	    allDayText: 'ganztags',
	    
	    header: {
			left: 'prev,next today',
			center: 'title',
			right: 'agendaDay,basicWeek,month'
	    },
	    
	    dayHeaderNumberClick : function(date, jsEvent, view){
			$('#teamCalendar').fullCalendar('gotoDate', date);
			$('#teamCalendar').fullCalendar('changeView', 'agendaDay');
		},
		
//	    eventClick : eventClick_teamCalendar ,
	    eventClick: function(calEvent, jsEvent, view) {
	    	eventClick_teamCalendar(calEvent, jsEvent, view , 'teamCalendar');
	    },
	    
//	    eventDrop : eventDrop_teamCalendar,
	    eventDrop: function(event, delta) { //save when drag and drop
	    	eventDrop_teamCalendar(event, delta, 'teamCalendar');
	    },
	    
//	    eventResize : eventResize_teamCalendar
	    eventResize: function(event, delta) { //save when resized(in agenda mode)
	    	eventResize_teamCalendar(event, delta, 'teamCalendar');	
	    },
	    
	    eventRender: qtip_renderer_comments,
	    
	    loading: function(bool, view) {
		
			if(bool) {
			    if(roster_blocked_element === '#MainContent') {
				//roster calendar block changes
				$(roster_blocked_element).block({
				    css: {
					border: 'none',
					padding: '15px',
					width:'150%',
					backgroundColor: '#000',
					'-webkit-border-radius': '10px',
					'-moz-border-radius': '10px',
					opacity: .5,
					color: '#fff',
					height: 'auto'
				    },
				    overlayCSS: {
					width:'154%'
				    },
				    message: '<h2>Verarbeitung</h2>'
				});
			    } else if(roster_blocked_element === '#tabs-3') {
				//old and rest of team calendars stay the same
				$(roster_blocked_element).block({
				    css: {
					border: 'none',
					padding: '15px',
					backgroundColor: '#000',
					'-webkit-border-radius': '10px',
					'-moz-border-radius': '10px',
					opacity: .5,
					color: '#fff',
					height: 'auto'
				    },
				    message: '<h2>Verarbeitung</h2>'
				});
			    }
			} else {
			    if(roster_page === '1') {
				var month = view.start.getFullYear()+'_'+("0" + (view.start.getMonth() + 1)).slice(-2);
				
				//load huge top roster data table via ajax (added '1' to force loading roster data)
				load_roster_data(roster_blocked_element, '1', month);			
			    }
				 else 
				{			
					$(roster_blocked_element).unblock();
			    }
			}
	    },
	    
	    
	    select: function(start, end, allDay) { //selected nonevent slot opens modal
	    	
	    	select_teamCalendar(start, end, allDay, 'teamCalendar');	
	    	
	    },
	    
	    timeFormat: 'HH:mm{ - HH:mm}\n', //H pt 24h
	    axisFormat: 'HH:mm',
	    aspectRatio: 2,
	    height: 850,
	    disableResizing: true,
	    defaultView: 'month',
	    weekMode: 'liquid',
	    firstDay: 1,
	    slotMinutes: 30,
	    selectable: true,
	    selectHelper: true,
	    theme: true,
	    monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	    monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'],
	    dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
	    dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
	    buttonText: {
		today: 'heute',
		month: 'Monat',//ISPC-2726,elena,21.01.2021
		week: 'Woche',//ISPC-2726,elena,21.01.2021
		day: 'Tag'//ISPC-2726,elena,21.01.2021
	    },
	    columnFormat: {
		month: 'ddd', // Mon
		week: 'ddd d.M', // Mon 9/7
		day: 'dddd d.M.yyyy'  // Monday 9/7
	    },
	    titleFormat: {
		month: 'MMMM yyyy', // September 2009
		week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep 7 - 13 2009
		//		    day: 'dddd, MMM d, yyyy'                  // Tuesday, Sep 8, 2009
		day: 'dddd d.M.yyyy'                  // Tuesday, Sep 8, 2009
	    },
	    dayRender: function(date, cell) {
		var day_of_week = date.getDay();
	
		if(day_of_week == '0' || day_of_week == '6')
		{
		    cell.addClass('ui-widget-content_special_day').removeClass('ui-widget-content');
		}
		
		if(typeof national_holidays != 'undefined' && national_holidays)
		{
		    //datepicker can only format dates not times!
		    var custom_date = $.datepicker.formatDate('yy-mm-dd', date);
		    var nat_holidays = jQuery.parseJSON(national_holidays);
		    if(nat_holidays)
		    {
			jQuery.map(nat_holidays, function(obj) {
			    if(obj === custom_date)
			    {
				cell.addClass('ui-widget-content_special_day').removeClass('ui-widget-content');
			    }
			});
		    }
	
	
		}
	    }
	});
}

function eventClick_teamCalendar(calEvent, jsEvent, view, parentTab) {


	if(view.name != "agendaDay" && (calEvent.eventType == 1 || calEvent.eventType == 2 || calEvent.eventType == 3 || calEvent.eventType == 4 || calEvent.eventType == 9 || calEvent.eventType == 10 || calEvent.eventType == 11 || calEvent.eventType >= 12) && calEvent.eventType != '18') 
	{
	
	    $('#ui-dialog-title-editEventTeam').html("Termin bearbeiten: " + calEvent.title + '');
	
	    //bind to dialog on opening event our clicked event values!
		    $("#editEventTeam").live("dialogopen", function(event, ui) {
			//event title
			$('#eventTitleET').val(calEvent.title);
			if(calEvent.eventType == 1 || calEvent.eventType == 2 || calEvent.eventType == 4 || calEvent.eventType == 9) {
			    $('#nameRow').hide();
			} else {
			    $('#nameRow').show();
			}
		
			//event id(hidden)
			$('#eventIdET').val(calEvent.id);
		
			//event start-end date
			var start = new Date(calEvent.start);
			var startDay = "" + start.getDate();
			var startMonth = "" + (start.getMonth() + 1);
			var startYear = "" + start.getFullYear();
			var startHours = "" + start.getHours();
			var startMinutes = "" + start.getMinutes();
			var startSeconds = "" + start.getSeconds();
		
			if(startDay.length == "1") {
			    startDay = "0" + startDay;
			}
			if(startMonth.length == "1") {
			    startMonth = "0" + startMonth;
			}
			if(startHours.length == "1") {
			    startHours = "0" + startHours;
			}
			if(startMinutes.length == "1") {
			    startMinutes = "0" + startMinutes;
			}
			if(startSeconds.length == "1") {
			    startSeconds = "0" + startSeconds;
			}
		
		
			if(calEvent.end == null) {
			    calEvent.end = calEvent.start;
			}
			var end = new Date(calEvent.end);
			var endDay = "" + end.getDate();
			var endMonth = "" + (end.getMonth() + 1);
			var endYear = "" + end.getFullYear();
			var endHours = "" + end.getHours();
			var endMinutes = "" + end.getMinutes();
			var endSeconds = "" + end.getSeconds();
		
			if(endDay.length == "1") {
			    endDay = "0" + endDay;
			}
			if(endMonth.length == "1") {
			    endMonth = "0" + endMonth;
			}
			if(endHours.length == "1") {
			    endHours = "0" + endHours;
			}
			if(endMinutes.length == "1") {
			    endMinutes = "0" + endMinutes;
			}
			if(endSeconds.length == "1") {
			    endSeconds = "0" + endSeconds;
			}
		
			var finalStartDate = startDay + "." + startMonth + "." + startYear;
			var finalStartDateTime = startHours + ":" + startMinutes;
		
			var finalEndDate = endDay + "." + endMonth + "." + endYear;
			var finalEndDateTime = endHours + ":" + endMinutes;
		
		
			if(calEvent.eventType == 4 || calEvent.eventType == 18) {
		//					$('#endDateRowET').hide();
			    $('#allDayET').hide();
			} else {
		//					$('#endDateRowET').show();
			    $('#allDayET').show();
			}
			$('#startDateET').val(finalStartDate);
			$('#startDateTimeET').val(finalStartDateTime);
		
			$('#endDateET').val(finalEndDate);
			$('#endDateTimeET').val(finalEndDateTime);
			//event type
			$('#eventTypeET').val(calEvent.eventType);
		
			//new procedure is taking user to the edit vacations page
			//reset event title readonly status used in event 18(vacation)
		//				if(calEvent.eventType == 18){
		//					$('#eventTitleET').attr('disabled', true);
		//				} else {
		//					$('#eventTitleET').removeAttr('disabled');
		//				}
		
			//allDay
			if(calEvent.allDay == true) {
		//					if(calEvent.eventType != 18){
			    $('#allDayET').show();
		//					} else {
		//						$('#allDayET').hide();
		//					}
			    $('#allDayOnET').attr('checked', true);
			    $('#allDayOffET').attr('checked', false);
		
			    $('#startDateTimeET').hide();
			    $('#endDateTimeET').hide();
			} else {
			    $('#allDayET').show();
			    $('#allDayOnET').attr('checked', false);
			    $('#allDayOffET').attr('checked', true);
		
			    $('#startDateTimeET').show();
			    $('#endDateTimeET').show();
			}
			
			if(typeof(calEvent.dayplan_inform) === "undefined"){
				$("#dayplan_inform_Row").hide();
			}
			else if(calEvent.dayplan_inform == true) {
				$("#dayplan_inform_Row").show();
				$('#dayplan_inform_on',this).attr('checked', true);
				$('#dayplan_inform_off',this).attr('checked', false);
			}else{
				$("#dayplan_inform_Row").show();
				$('#dayplan_inform_on',this).attr('checked', false);
				$('#dayplan_inform_off',this).attr('checked', true);
			}
		
			//					if(finalStartDate != finalEndDate){
			//						//show all datepickers
			//						$('#endDateRowET').show();
			//					} else {
			//						//show only start datepicker unless
			//						$('#endDateRowET').hide();
			//						if(finalStartDateTime != finalEndDateTime){
			//							//show both timepickers
			//							$('#startDateTimeET').show();
			//							$('#endDateTimeET').show();
			//							$('#endDateRowET').show();
			//						} else {
			//							//don`t show timepickers
			//							$('#startDateTimeET').hide();
			//							$('#endDateTimeET').hide();
			//						}
			//
			//					}
		
		
		
			/* Initialize datepicker and timepicker in modal */
			//datepicker
			$("#startDateET, #endDateET").datepicker({
			    dateFormat: 'dd.mm.yy',
			    showOn: "both",
			    buttonImage: $('#calImg').attr('src'),
			    buttonImageOnly: true
			});
			//timepicker
			$('#startDateTimeET, #endDateTimeET').timepicker({
			    minutes: {
				interval: 5
			    },
			    showPeriodLabels: false,
			    rows: 4,
			    hourText: 'Stunde',
			    minuteText: 'Minute'
			});
	    });
		
	    $('#editEventTeam').data('parentTab', parentTab).data('calEvent', calEvent).dialog('open');
	}

}

function eventDrop_teamCalendar(event, delta, parentTab) 
{
	var start = new Date(event.start);
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = "" + start.getFullYear();
	var startHours = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();
	var startSeconds = "" + start.getSeconds();

	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}
	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHours.length == "1") {
	    startHours = "0" + startHours;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	if(startSeconds.length == "1") {
	    startSeconds = "0" + startSeconds;
	}

	var finalStartDate = startYear + "-" + startMonth + "-" + startDay + " " + startHours + ":" + startMinutes + ":" + startSeconds + "";

	var eventFinal = event;
	var deltaFinal = delta;
	var end = new Date(event.end);
	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = "" + end.getFullYear();
	var endHours = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();
	var endSeconds = "" + end.getSeconds();

	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHours.length == "1") {
	    endHours = "0" + endHours;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	if(endSeconds.length == "1") {
	    endSeconds = "0" + endSeconds;
	}

	if(event.end)
	{
	    var finalEndDate = endYear + "-" + endMonth + "-" + endDay + " " + endHours + ":" + endMinutes + ":" + endSeconds + "";
	}
	else
	{
	    var finalEndDate = finalStartDate;
	}


	if(eventFinal.allDay === true) {
	    var allDay = "1";
	    //finalEndDate = finalStartDate;
	} else {
	    var allDay = "0";
	}

	jConfirm('Wollen Sie diesen Termin verschieben.', 'Bestätigung', function(r) {
	    var view = $('#'+ parentTab ).fullCalendar('getView');
	    if(r) {
			$.ajax({
			    type: 'POST',
			    url: 'calendar/saveteamevents',
			    async: true,
			    data: {
			    	'eventId' : eventFinal.id, 
			    	'eventTitle' : eventFinal.title, 
			    	'startDate' : finalStartDate ,
			    	'endDate' : finalEndDate ,
			    	'eventType' : eventFinal.eventType, 
			    	'allDay' : allDay ,
			    	'delta' : deltaFinal, 
			    	'operation': 2,
			    	'comments' : eventFinal.comments || '',
			    	
			    },
			    success: function(responseText) {
					if(view.name != "agendaDay") {//fix for moving in agendaDay!
					    $('#' + parentTab).fullCalendar('refetchEvents'); //reload saved data
					}
			    }
			});
	    } else {
			if(view.name != "agendaDay") {//fix for moving in agendaDay!
			    $('#'+ parentTab).fullCalendar('refetchEvents'); //reload saved data
			}
	    }
	});
}

function eventResize_teamCalendar(event, delta, parentTab)
{
	 //save when resized(in agenda mode)	
	var start = new Date(event.start);
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = "" + start.getFullYear();
	var startHours = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();
	var startSeconds = "" + start.getSeconds();
	
	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}
	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHours.length == "1") {
	    startHours = "0" + startHours;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	if(startSeconds.length == "1") {
	    startSeconds = "0" + startSeconds;
	}
	
	var finalStartDate = startYear + "-" + startMonth + "-" + startDay + " " + startHours + ":" + startMinutes + ":" + startSeconds + "";
	
	var eventFinal = event;
	var deltaFinal = delta;
	var end = new Date(event.end);
	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = "" + end.getFullYear();
	var endHours = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();
	var endSeconds = "" + end.getSeconds();
	
	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHours.length == "1") {
	    endHours = "0" + endHours;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	if(endSeconds.length == "1") {
	    endSeconds = "0" + endSeconds;
	}
	
	var finalEndDate = endYear + "-" + endMonth + "-" + endDay + " " + endHours + ":" + endMinutes + ":" + endSeconds + "";
	if(eventFinal.allDay === true) {
	    var allDay = "1";
	} else {
	    var allDay = "0";
	}
	jConfirm('Wollen Sie diesen Termin verschieben.', 'Bestätigung', function(r) {
	    var view = $('#'+parentTab).fullCalendar('getView');
	    if(r) {
			$.ajax({
			    type: 'POST',
			    url: 'calendar/saveteamevents',
			    async: true,
			    data: {
			    	'eventId' : eventFinal.id, 
			    	'eventTitle' : eventFinal.title ,
			    	'startDate' : finalStartDate ,
			    	'endDate' : finalEndDate ,
			    	'eventType' : eventFinal.eventType ,
			    	'allDay' : allDay ,
			    	'delta' : deltaFinal ,
			    	'operation' : 2,
			    	'comments' : eventFinal.comments,
			    },
			    success: function(responseText) {
					if(view.name != "agendaDay") {//fix for moving in agendaDay!
					    $('#'+parentTab).fullCalendar('refetchEvents'); //reload saved data
					}
			    }
			});
	    } else {
			if(view.name != "agendaDay") {//fix for moving in agendaDay!
			    $('#'+parentTab).fullCalendar('refetchEvents'); //reload saved data
			}
	    }
	});

}   

function select_teamCalendar(start, end, allDay, parentTab)
{

	$("#addneweventteam").data('parentTab', parentTab).dialog('open');
	//modal title
	$('#ui-dialog-title-addneweventteam').html("neuen Termin hinzufügen");

	//get current calendar view
	var view = $('#'+parentTab).fullCalendar('getView');
	//get selected start/end date if is interval (start!= end) else is same day)

	//vars + put leading zeros to day and month
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = start.getFullYear();
	var startHour = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();

	if(startDay.length == "1") {
	    startDay = "0" + startDay;
	}

	if(startMonth.length == "1") {
	    startMonth = "0" + startMonth;
	}
	if(startHour.length == "1") {
	    startHour = "0" + startHour;
	}
	if(startMinutes.length == "1") {
	    startMinutes = "0" + startMinutes;
	}
	var startSelectedDate = startDay + '.' + startMonth + '.' + startYear;
	var startSelectedTime = startHour + ':' + startMinutes;

	//insert start date
	$('#startDateT').val(startSelectedDate);
	$("input[name='startShiftDate[]']").each(function() {
	    $(this).val(startSelectedDate);
	});
	//				if(view.name == "agendaDay"){
	$("input[name='startShiftTime[]']").each(function() {
	    $(this).val(startSelectedTime);
	});
	//				}

	var endDay = "" + end.getDate();
	var endMonth = "" + (end.getMonth() + 1);
	var endYear = end.getFullYear();
	var endHour = "" + end.getHours();
	var endMinutes = "" + end.getMinutes();

	if(endDay.length == "1") {
	    endDay = "0" + endDay;
	}
	if(endMonth.length == "1") {
	    endMonth = "0" + endMonth;
	}
	if(endHour.length == "1") {
	    endHour = "0" + endHour;
	}
	if(endMinutes.length == "1") {
	    endMinutes = "0" + endMinutes;
	}
	var endSelectedDate = endDay + '.' + endMonth + '.' + endYear;
	var endSelectedTime = endHour + ':' + endMinutes;
	//insert end date
	$('#endDateT').val(endSelectedDate);
	$("input[name='endShiftDate[]']").each(function() {
	    $(this).val(endSelectedDate);
	});
	//				if(view.name == "agendaDay"){
	$("input[name='endShiftTime[]']").each(function() {
	    $(this).val(endSelectedTime);
	});
	//				}
	if(startSelectedDate == endSelectedDate) {

//	    $("#allDayOnT").attr('checked', true);//ISPC-2175 removed
	    //disable and hide time selects
	    $('#startDateTimeT').val(" ");
//	    $('#startDateTimeT').hide();//ISPC-2175 removed

	    $('#endDateTimeT').val(" ");
//	    $('#endDateTimeT').hide();//ISPC-2175 removed
	} else {
//	    $("#allDayOffT").attr('checked', true);//ISPC-2175 removed
	    //enable and show time selects
	    $('#startDateTimeT').val(" ");
//	    $('#startDateTimeT').show();//ISPC-2175 removed

	    $('#endDateTimeT').val(" ");
//	    $('#endDateTimeT').show();//ISPC-2175 removed
	}
	//remove selection!
	$('#'+parentTab).fullCalendar('unselect');	
}





var qtip_renderer_comments = function (event, element, view) {
	
	if (typeof(event.comments_qtip) !== 'undefined' && event.comments_qtip != '') {
		//add qtip only it it has the comments prop and not empty
		
		element.qtip({    
			content: {    
				title: function(evt, api) {
					
					var _title_prefix = '',
					_title = event.titlePrefix || event.title,
					_traffic_status = ['', 'images/traffic_g_light.png', 'images/traffic_y_light.png', 'images/traffic_r_light.png'];
					
					if (event.hasOwnProperty('_patient')) {
						if (event._patient.hasOwnProperty('isstandby') && event._patient.isstandby == 1) {
							_title_prefix = '<img style="max-height:16px" src="' + appbase + 'icons_system/is_standby_icon.png">&nbsp;';
						} else if (event._patient.hasOwnProperty('traffic_status')) {
							//TODO get is hospital location if you want to show this also
							//_title_prefix = '<img style="max-height:14px" src="' + appbase + _traffic_status[event._patient.traffic_status] + '">&nbsp;';
						} 
					} 
					
					return _title_prefix + _title;
		        },
		
				
				text: (event.title && event.titlePrefix ? event.title + '<br>' : '')
				+ '<span class="title">' + translate('comments') + ' </span><br/>' 
				+ event.comments_qtip
				
			},
			show: { solo: true },
			//hide: { when: 'inactive', delay: 3000 }, 
			style: { 
				width: 220,
				padding: 5,
				color: 'black',
				textAlign: 'left',
				border: {
					width: 1,
					radius: 3
				},
				tip: 'topLeft',
				
				classes: { 
					tooltip: 'ui-widget', 
					tip: 'ui-widget', 
					title: 'ui-widget-header', 
					content: 'ui-widget-content' 
				} 
			} 
		});
	}
};



 

function print_doc_calendar_action() {

	var date = $("#calendar").fullCalendar('getDate');
	var _view = $("#calendar").fullCalendar('getView');
	
	var d = '';
	if(date.getDay() == '0') {
	    d = '1';
	} else {
	    d = date.getDay();
	}
	document.calendarprint.action = "calendar/printdoctorcalendar"
		+ "?y=" + date.getFullYear() 
		+ "&m=" + date.getMonth() 
		+ '&d=' + d
		//+ '&d=' + date.getDate()  
		+ '&viewName=' + _view.name
		;
	
	document.calendarprint.target = "_blank";
	document.calendarprint.submit();
}

function printaction() {
	
	
	print_calendar_allCalendarTypes('teamCalendar');
	return;
	/*
	var date = $("#teamCalendar").fullCalendar('getDate');
	var _view = $("#teamCalendar").fullCalendar('getView');
	
	var d = '';
	if(date.getDay() == '0') {
	    d = '1';
	} else {
	    d = date.getDay();
	}
	document.calendarprint.action = "calendar/printteamcalendar"
		+ "?y=" + date.getFullYear() 
		+ "&m=" + date.getMonth() 
		//+ '&d=' + d
		+ '&d=' + date.getDate() 
		+ '&viewName=' + _view.name
		;
	document.calendarprint.target = "_blank";
	document.calendarprint.submit();
	*/
}




if (typeof print_calendar_allCalendarTypes !== "function") { 
	/* this is a copy from /javascript/view/calendar/calendars.js*/
	function print_calendar_allCalendarTypes(calendarId)
	{
		var date = $("#"+calendarId).fullCalendar('getDate');
		
		var _view = $("#"+calendarId).fullCalendar('getView');
		
		var _cbtabs = '';
		
		switch (calendarId) {
			case "todosFullCalendar":
				_cbtabs += 'tabs[]=todosFullCalendar&';
				break;
			case "allPatientsCalendar":
				_cbtabs += 'tabs[]=allPatientsCalendar&';
				break;
			case "allCalendarTypes":
				$("input.allCalendarTypesCb:checkbox:checked").each(function() { 
					_cbtabs += 'tabs[]=' + $(this).val() + '&';
				});
				break;
				
			case "teamCalendar" :
				_cbtabs += 'tabs[]=teamCalendar&';
				_cbtabs += 'tabs[]=teamShiftsCalendar&';
				break;
		}
		
		
		document.calendarprint.action = "calendar/printallcalendartypes"
			+ "?y=" + date.getFullYear() 
			+ "&m=" + date.getMonth() 
			+ '&d=' + date.getDate()  
			+ '&viewName=' + _view.name
			+ '&' + _cbtabs ;
		
		document.calendarprint.target = "_blank";
		document.calendarprint.submit();
	}

} 

