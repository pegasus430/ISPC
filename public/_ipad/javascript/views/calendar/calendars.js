var allCalendarTypesReloadTimoutId = null;

function allCalendarTypesReload(_this, evt) {
	
	$(_this).parent().toggleClass('ui-state-active');
	
	var _allVals = [];
	$('input.allCalendarTypesCb:checked').each(function() {
		_allVals.push( translate("calendar type " + $(this).val()) );
    });
	
	if (_allVals.length > 0) {	
		_allVals = _allVals.join(', ');
	} else {
		_allVals = translate("please select one calendar type");
	}
	
	$('.selector_legend').text(translate('You are now listing: %1%').format('', _allVals));

	
	clearTimeout(allCalendarTypesReloadTimoutId);
	allCalendarTypesReloadTimoutId = setTimeout(function() {
		$('#allCalendarTypes').fullCalendar('refetchEvents');
	}, 500);
}

var created_calendar_allCalendarTypes = false,
	created_calendar_todosFullCalendar = false,
	created_calendar_allPatientsCalendar = false;

function create_calendar_todosFullCalendar() {

	created_calendar_todosFullCalendar = true;

	/* todosFullCalendar */
	$('#todosFullCalendar')
		.fullCalendar({

			escapeEventTitle : false,
			
			disableDragging: true,
			disableResizing: true,
			editable: false,
			theme: true,
			selectable: false,
			selectHelper: true,
		
			events: "calendar/fetchalltodos",
			allDayText: 'ganztags',
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'agendaDay,basicWeek,month'
			},

			timeFormat: 'HH:mm{ - HH:mm}\n', // H pt 24h
			axisFormat: 'HH:mm',
			aspectRatio: 2,
			height: 850,
			
			defaultView: 'month',
			weekMode: 'liquid',
			firstDay: 1,
			slotMinutes: 30,
			monthNames: ["Januar", "Februar", "März", "April",
				"Mai", "Juni", "Juli", "August", "September",
				"Oktober", "November", "Dezember"
			],
			monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai',
				'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov',
				'Dez'
			],
			dayNames: ['Sonntag', 'Montag', 'Dienstag',
				'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
			],
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr',
				'Sa'
			],
			buttonText: {
				today: 'heute',
				month: 'monat',
				week: 'woche',
				day: 'tag'
			},
			columnFormat: {
				month: 'ddd', // Mon
				week: 'ddd d.M', // Mon 9/7
				day: 'dddd d.M.yyyy' // Monday 9/7
			},
			titleFormat: {
				month: 'MMMM yyyy', // September 2009
				week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep
				// 7 -
				// 13
				// 2009
				// day: 'dddd, MMM d, yyyy' // Tuesday, Sep 8, 2009
				day: 'dddd d.M.yyyy' // Tuesday, Sep 8, 2009
			},
			dayRender: function(date, cell) {
				var day_of_week = date.getDay();

				if (day_of_week == '0' || day_of_week == '6') {
					cell.addClass('ui-widget-content_special_day')
						.removeClass('ui-widget-content');
				}

				if (national_holidays) {
					// datepicker can only format dates not times!
					var custom_date = $.datepicker.formatDate(
						'yy-mm-dd', date);
					var nat_holidays = jQuery
						.parseJSON(national_holidays);
					if (nat_holidays) {
						jQuery
							.map(
								nat_holidays,
								function(obj) {
									if (obj === custom_date) {
										cell
											.addClass(
												'ui-widget-content_special_day')
											.removeClass(
												'ui-widget-content');
									}
								});
					}

				}
			}, // eo dayRender
			/*
			eventRender : function (event, element, view){
				
			    var title = element.find(".fc-title").val();			    
			    element.find(".fc-event-title").html( event.titlePrefix + "<br> <b>" + event.title + "</b>");
			},
			*/
			/*
			eventDataTransform : function (eventData) {
				
				eventData.title = '<b>' + eventData.titleX + '</b>';
				return eventData;
			},*/
			
			dayHeaderNumberClick : function(date, jsEvent, view){
				$('#todosFullCalendar').fullCalendar('gotoDate', date);
				$('#todosFullCalendar').fullCalendar('changeView', 'agendaDay');
			},
			
			eventClick: function(calEvent, jsEvent, view) {
				var _url = appbase +
					'patientcourse/patientcourse?id=' +
					calEvent.patient_id;
				var win = window.open(_url, '_blank');
				return true;
			},

			eventResize: function(event, delta) {
				return true;
			}, // eo eventResize

			eventDrop: function(event, delta) {
				return true;
			}, // eo eventDrop

			select: function(start, end, allDay) {
				return true;
			}, // eo select

			eventAfterAllRender : function(view) {
				//hide loader	
			},
			
			loading: function(bool, view) {
				
				var _parent_div_for_overlay = $(this).parent();
				
				if (bool) {
					$(_parent_div_for_overlay).block({
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
						message: '<h2>Verarbeitung</h2><div class="spinner_square"></div>'
					});
				} else {
					$(_parent_div_for_overlay).unblock();
				}
			}, // eo loading

		}); // eo todosFullCalendar

}



/**
function eventClick_allPatientsCalendar(calEvent, jsEvent, view, parentTab){

	if (view.name != "agendaDay" &&
		(calEvent.eventType == 1 ||
			calEvent.eventType == 2 ||
			(calEvent.eventType == 3 &&
				calEvent.event_source != "newreceipt_1" && calEvent.event_source != "newreceipt_2") ||
			calEvent.eventType == 4 ||
			calEvent.eventType == 9 ||
			calEvent.eventType == 10 ||
			calEvent.eventType == 11 ||
			calEvent.eventType >= 12 ||
			calEvent.eventType == 15 ||
			calEvent.eventType == 16 || calEvent.eventType == 17)) {
		$('#ui-dialog-title-editEvent').html(
			"Termin bearbeiten: " + calEvent.title +
			'');
		// bind to dialog on opening event our clicked
		// event values!
		$("#editEvent")
			.data('parentTab', parentTab)
			.live(
				"dialogopen",
				function(event, ui) {
					// event title
					$('#eventTitleE').val(
						calEvent.title);
					if (calEvent.eventType == 1 ||
						calEvent.eventType == 2 ||
						calEvent.eventType == 4 ||
						calEvent.eventType == 9 ||
						calEvent.eventType == 13 ||
						calEvent.eventType == 15 ||
						calEvent.eventType == 16 ||
						calEvent.eventType == 17) {
						// $('#eventTitleE').attr('readonly',
						// true);
						$('#eventTitleERow')
							.hide();
					} else {
						// $('#eventTitleE').attr('readonly',
						// false);
						$('#eventTitleERow')
							.show();
					}

					// event id(hidden)
					$('#eventIdE').val(
						calEvent.id);
					$('#selectPatientE').val(
						calEvent.ipid);

					// event start-end date
					var start = new Date(
						calEvent.start);
					var startDay = "" +
						start.getDate();
					var startMonth = "" +
						(start.getMonth() + 1);
					var startYear = "" +
						start
						.getFullYear();
					var startHours = "" +
						start.getHours();
					var startMinutes = "" +
						start
						.getMinutes();
					var startSeconds = "" +
						start
						.getSeconds();

					if (startDay.length == "1") {
						startDay = "0" +
							startDay;
					}

					if (startMonth.length == "1") {
						startMonth = "0" +
							startMonth;
					}

					if (startHours.length == "1") {
						startHours = "0" +
							startHours;
					}

					if (startMinutes.length == "1") {
						startMinutes = "0" +
							startMinutes;
					}

					if (startSeconds.length == "1") {
						startSeconds = "0" +
							startSeconds;
					}

					if (calEvent.end == null) {
						calEvent.end = calEvent.start;
					}
					var end = new Date(
						calEvent.end);
					var endDay = "" +
						end.getDate();
					var endMonth = "" +
						(end.getMonth() + 1);
					var endYear = "" +
						end.getFullYear();
					var endHours = "" +
						end.getHours();
					var endMinutes = "" +
						end.getMinutes();
					var endSeconds = "" +
						end.getSeconds();

					if (endDay.length == "1") {
						endDay = "0" + endDay;
					}
					if (endMonth.length == "1") {
						endMonth = "0" +
							endMonth;
					}
					if (endHours.length == "1") {
						endHours = "0" +
							endHours;
					}
					if (endMinutes.length == "1") {
						endMinutes = "0" +
							endMinutes;
					}
					if (endSeconds.length == "1") {
						endSeconds = "0" +
							endSeconds;
					}

					var finalStartDate = startDay +
						"." +
						startMonth +
						"." + startYear;
					var finalStartDateTime = startHours +
						":" +
						startMinutes;

					var finalEndDate = endDay +
						"." + endMonth +
						"." + endYear;
					var finalEndDateTime = endHours +
						":" + endMinutes;

					$('#startDateE').val(
						finalStartDate);
					$('#startDateTimeE').val(
						finalStartDateTime);

					$('#endDateE').val(
						finalEndDate);
					$('#endDateTimeE').val(
						finalEndDateTime);
					// event type
					$('#eventTypeE').val(
						calEvent.eventType);
					$('#createDateE')
						.val(
							calEvent.createDate);
					// allDay
					if (calEvent.allDay == true) {
						$('#allDayE').show();
						$('#allDayOnE')
							.attr(
								'checked',
								true);
						$('#allDayOffE').attr(
							'checked',
							false);

						$('#startDateTimeE')
							.hide();
						$('#endDateTimeE')
							.hide();
					} else {
						$('#allDayE').show();
						$('#allDayOnE').attr(
							'checked',
							false);
						$('#allDayOffE')
							.attr(
								'checked',
								true);

						$('#startDateTimeE')
							.show();
						$('#endDateTimeE')
							.show();
					}
					// viewAll
					if (calEvent.viewForAll == "1") {
						$('#viewForAllE')
							.attr(
								'checked',
								true);
					} else {
						$('#viewForAllE').attr(
							'checked',
							false);
					}

					if (calEvent.eventType == 1 ||
						calEvent.eventType == 2 ||
						calEvent.eventType == 9 ||
						calEvent.eventType == 13 ||
						calEvent.eventType == 15 ||
						calEvent.eventType == 16 ||
						calEvent.eventType == 17) {
						$('#allDayEventERow')
							.hide();
						if (calEvent.eventType == 9 ||
							calEvent.eventType == 15 ||
							calEvent.eventType == 16 ||
							calEvent.eventType == 17) {
							$('#endDateRowE')
								.hide();
						}
						if (calEvent.eventType == 13) {
							$('#allViewRowE')
								.hide();
						}
						$('#allDayOff')
							.attr(
								'checked',
								true);
					} else {
						$('#allDayEventERow')
							.show();
						$('#endDateRowE')
							.show();
						$('#allViewRowE')
							.show();

					}

					if (typeof(calEvent.dayplan_inform) === "undefined") {
						$(
							"#dayplan_inform_Row",
							this).hide();
					} else if (calEvent.dayplan_inform == true) {
						$(
							"#dayplan_inform_Row",
							this).show();
						$('#dayplan_inform_on',
								this)
							.attr(
								'checked',
								true);
						$(
							'#dayplan_inform_off',
							this).attr(
							'checked',
							false);
					} else {
						$(
							"#dayplan_inform_Row",
							this).show();
						$('#dayplan_inform_on',
							this).attr(
							'checked',
							false);
						$(
								'#dayplan_inform_off',
								this)
							.attr(
								'checked',
								true);
					}

					
					// Initialize datepicker and
					// timepicker in modal
					
					// datepicker
					$("#startDateE, #endDateE")
						.datepicker({
							dateFormat: 'dd.mm.yy',
							showOn: "both",
							buttonImage: $(
									'#calImg')
								.attr(
									'src'),
							buttonImageOnly: true
						});
					// timepicker
					$(
							'#startDateTimeE, #endDateTimeE')
						.timepicker({
							minutes: {
								interval: 5
							},
							showPeriodLabels: false,
							rows: 4,
							hourText: 'Stunde',
							minuteText: 'Minute'
						});

				});

		$('#editEvent').data('parentTab', parentTab).dialog('open');
	}

}

*/

function create_calendar_allPatientsCalendar() {

	created_calendar_allPatientsCalendar = true;


	/* allPatientsCalendar */
	$('#allPatientsCalendar')
		.fullCalendar({

			selectable: true,
			selectHelper: true,
			
			dayClick: function(date, jsEvent, view) {
				//console.log(arguments);
				//select_calendar(date, date, false , 'allPatientsCalendar');
			},
			
			dayHeaderNumberClick : function(date, jsEvent, view){
				$('#allPatientsCalendar').fullCalendar('gotoDate', date);
				$('#allPatientsCalendar').fullCalendar('changeView', 'agendaDay');
			},
			
			
			editable: true,
			disableDragging: false,
			disableResizing: false,
			
			
			theme: true,
			
			events: "calendar/fetchallpatientscustom",
			
			allDayText: 'ganztags',
			
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'agendaDay,basicWeek,month'
			},

			timeFormat: 'HH:mm{ - HH:mm}\n', // H pt 24h
			axisFormat: 'HH:mm',
			aspectRatio: 2,
			height: 850,
			defaultView: 'month',
			weekMode: 'liquid',
			firstDay: 1,
			slotMinutes: 30,
			
			monthNames: ["Januar", "Februar", "März", "April",
				"Mai", "Juni", "Juli", "August", "September",
				"Oktober", "November", "Dezember"
			],
			monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai',
				'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov',
				'Dez'
			],
			dayNames: ['Sonntag', 'Montag', 'Dienstag',
				'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
			],
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr',
				'Sa'
			],
			
			eventRender: qtip_renderer_comments,
			
			/*
			//first variation that added more infos, not just the comment
			eventRender : function (event, element, view){
				
//			    var title = element.find(".fc-title").val();			    
//			    element.find(".fc-event-title").html( event.titlePrefix + "<br> <b>" + event.title + "</b>");
//			    
			    element.qtip({    
		            content: {    
		                title: { text: event.titlePrefix || event.title },
		                text: '<b>' + event.title + '</b>'
		                	+ '<br/>'
		                	+'<br/><span class="title">ganztags: </span>'
		                	+ (event.allDay == true ? translate('option_yes') : translate('option_no'))
		                	
		                	+'<br/><span class="title">Begin Datum: </span>'
		                	+ (event.allDay == true ? ($.fullCalendar.formatDate(event.start, 'dd.MM.yyyy')) : ($.fullCalendar.formatDate(event.start, 'dd.MM.yyyy HH:mm')))
		                	
		                	+'<br/><span class="title">Ende Datum: </span>' 
		                	+ (event.allDay == true ? ($.fullCalendar.formatDate(event.end || event.start, 'dd.MM.yyyy')) : ($.fullCalendar.formatDate(event.end || event.start, 'dd.MM.yyyy HH:mm')))

		                	+'<br/><span class="title">Sichtbar für alle Team Mitglieder: </span>' 
		                	+(event.viewForAll == true ? translate('option_yes') : translate('option_no'))
		                	
		                	+'<br/><span class="title">Termin im Kalender des Benutzers: </span>' 
		                	+(event.dayplan_inform == true ? translate('option_yes') : translate('option_no'))
		                	       
		                	+'<br/><span class="title">' + translate('comments') + ' </span>' 
		                	+event.comments_qtip
		                	
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
			},
			 */
			buttonText: {
				today: 'heute',
				month: 'monat',
				week: 'woche',
				day: 'tag'
			},
			columnFormat: {
				month: 'ddd', // Mon
				week: 'ddd d.M', // Mon 9/7
				day: 'dddd d.M.yyyy' // Monday 9/7
			},
			titleFormat: {
				month: 'MMMM yyyy', // September 2009
				week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep
				// 7 -
				// 13
				// 2009
				// day: 'dddd, MMM d, yyyy' // Tuesday, Sep 8, 2009
				day: 'dddd d.M.yyyy' // Tuesday, Sep 8, 2009
			},
			dayRender: function(date, cell) {
				var day_of_week = date.getDay();

				if (day_of_week == '0' || day_of_week == '6') {
					cell.addClass('ui-widget-content_special_day')
						.removeClass('ui-widget-content');
				}

				if (national_holidays) {
					// datepicker can only format dates not times!
					var custom_date = $.datepicker.formatDate(
						'yy-mm-dd', date);
					var nat_holidays = jQuery
						.parseJSON(national_holidays);
					if (nat_holidays) {
						jQuery
							.map(
								nat_holidays,
								function(obj) {
									if (obj === custom_date) {
										cell
											.addClass(
												'ui-widget-content_special_day')
											.removeClass(
												'ui-widget-content');
									}
								});
					}

				}
			}, // eo dayRender

			eventClick: function(calEvent, jsEvent, view) {
				//eventClick_allPatientsCalendar(calEvent, jsEvent, view , 'allPatientsCalendar');
				eventClick_calendar(calEvent, jsEvent, view , 'allPatientsCalendar');
			}, // eo eventClick

			eventResize: function(event, delta, revertFunc, jsEvent, ui, view , calendar6) { //save when resized(in agenda mode)
				
				eventResize_calendar(event, delta, 'allPatientsCalendar');
				
			}, // eo eventResize

			eventDrop: function(event, delta, revertFunc, jsEvent, ui, view , param6, calendar7) { //save when drag and drop
				
				eventDrop_calendar(event, delta, 'allPatientsCalendar');
				
		    },// eo eventDrop

			select: function(start, end, allDay) { // selected
				select_calendar(start, end, allDay , 'allPatientsCalendar');
			}, // eo select

			loading: function(bool, view) {
				
				var _parent_div_for_overlay = $(this).parent();
				
				if (bool) {
					$(_parent_div_for_overlay).block({
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
						message: '<h2>Verarbeitung</h2><div class="spinner_square"></div>'
					});
				} else {
					$(_parent_div_for_overlay).unblock();
				}
			},// eo loading

		}); // eo allPatientsCalendar
}

function create_calendar_allCalendarTypes() {

	created_calendar_allCalendarTypes = true;
	
	$('.allCalendarTypesCbdiv label:first').addClass('ui-corner-left');
	$('.allCalendarTypesCbdiv label:last').addClass('ui-corner-right');
	
	
	/* allPatientsCalendar */
	$('#allCalendarTypes')
		.fullCalendar({

			disableDragging: false,
			disableResizing: false,

			selectable: true,
			editable: true,

			theme: true,
			selectHelper: true,
			
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'agendaDay,basicWeek,month'
			},

			timeFormat: 'HH:mm{ - HH:mm}\n', // H pt 24h
			axisFormat: 'HH:mm',
			aspectRatio: 2,
			height: 850,
			defaultView: 'month',
			weekMode: 'liquid',
			firstDay: 1,
			slotMinutes: 30,
			allDayText: 'ganztags',
			monthNames: ["Januar", "Februar", "März", "April",
				"Mai", "Juni", "Juli", "August", "September",
				"Oktober", "November", "Dezember"
			],
			monthNamesShort: ['Jän', 'Feb', 'März', 'Apr', 'Mai',
				'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov',
				'Dez'
			],
			dayNames: ['Sonntag', 'Montag', 'Dienstag',
				'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
			],
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr',
				'Sa'
			],
			buttonText: {
				today: 'heute',
				month: 'monat',
				week: 'woche',
				day: 'tag'
			},
			columnFormat: {
				month: 'ddd', // Mon
				week: 'ddd d.M', // Mon 9/7
				day: 'dddd d.M.yyyy' // Monday 9/7
			},
			titleFormat: {
				month: 'MMMM yyyy', // September 2009
				week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep
				// 7 -
				// 13
				// 2009
				// day: 'dddd, MMM d, yyyy' // Tuesday, Sep 8, 2009
				day: 'dddd d.M.yyyy' // Tuesday, Sep 8, 2009
			},

			events: {
				url: 'calendar/fetchallCalendarTypes',
				type: 'GET', // if you change into POST, multiple
				// changes needed
				data: function() { // add params
					return {
						'tabs': $("input.allCalendarTypesCb:checkbox:checked")
						.map(function() {
							return $(this).val();
						}).get()
					};

				}
			},
			
			dayHeaderNumberClick : function(date, jsEvent, view){
				$('#allCalendarTypes').fullCalendar('gotoDate', date);
				$('#allCalendarTypes').fullCalendar('changeView', 'agendaDay');
			},
			
			dayRender: function(date, cell) {

				var day_of_week = date.getDay();

				if (day_of_week == '0' || day_of_week == '6') {
					cell.addClass('ui-widget-content_special_day')
						.removeClass('ui-widget-content');
				}

				if (national_holidays) {
					// datepicker can only format dates not times!
					var custom_date = $.datepicker.formatDate(
						'yy-mm-dd', date);
					var nat_holidays = jQuery
						.parseJSON(national_holidays);
					if (nat_holidays) {
						jQuery
							.map(
								nat_holidays,
								function(obj) {
									if (obj === custom_date) {
										cell
											.addClass(
												'ui-widget-content_special_day')
											.removeClass(
												'ui-widget-content');
									}
								});
					}

				}
			}, // eo dayRender

			eventClick: function(calEvent, jsEvent, view) {
				
				switch (calEvent.calendarName) {
				
					case 'calendar':
						//eventClick_allPatientsCalendar(calEvent, jsEvent, view , 'allCalendarTypes');
						eventClick_calendar(calEvent, jsEvent, view , 'allCalendarTypes');
					break;
						
					case 'teamCalendar':
						eventClick_teamCalendar(calEvent, jsEvent, view , 'allCalendarTypes');
					break;
						
					case 'todosFullCalendar':
						//this opens new tab only
						var _url = appbase +
						'patientcourse/patientcourse?id=' +
						calEvent.patient_id;
						var win = window.open(_url, '_blank');
					break;
						
					case 'allPatientsCalendar':
						//eventClick_allPatientsCalendar(calEvent, jsEvent, view , 'allCalendarTypes');
						eventClick_calendar(calEvent, jsEvent, view , 'allCalendarTypes');
					break;
				
				}
				
				return;

			}, // eo eventClick

			eventResize: function(event, delta) {
				
				switch (event.calendarName) {
				
					case 'calendar':
						eventResize_calendar(event, delta, 'allCalendarTypes');
					break;
						
					case 'teamCalendar':
						eventResize_teamCalendar(event, delta , 'allCalendarTypes');
					break;
						
					case 'todosFullCalendar':
					break;
						
					case 'allPatientsCalendar':
						eventResize_calendar(event, delta, 'allCalendarTypes');
					break;
				}
				return true;
			}, // eo eventResize

			eventDrop: function(event, delta) {
				
				switch (event.calendarName) {
				
					case 'calendar':
						eventDrop_calendar(event, delta, 'allCalendarTypes');
					break;
						
					case 'teamCalendar':
						eventDrop_teamCalendar(event, delta , 'allCalendarTypes');
					break;
						
					case 'todosFullCalendar':
					break;
						
					case 'allPatientsCalendar':
						eventDrop_calendar(event, delta, 'allCalendarTypes');
					break;
			
				}
							
			}, // eo eventDrop

			select: function(start, end, allDay) {

				open_dialog_allCalendarTypes(arguments);

				$('#allCalendarTypes').fullCalendar('unselect');

				return;
			}, // eo select

			
			eventAfterAllRender : function(view) {
				//hide loader ?	
			},
			
			eventRender: qtip_renderer_comments,
			
			loading: function(bool, view) {
				
				var _parent_div_for_overlay = $(this).parent();
				
				if (bool) {
					$(_parent_div_for_overlay).block({
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
						message: '<h2>Verarbeitung</h2><div class="spinner_square"></div>'
					});
				} else {
					$(_parent_div_for_overlay).unblock();
				}
			}, // eo loading

		}); // eo todosFullCalendar

}

/*
function open_dialog_addneweventteam() 
{

	var _args = arguments[0];
	var start = _args[0],
		end = _args[1],
		allDay = _args[2];
	
	
	select_teamCalendar(start, end, allDay, parentTab);
	return;
	

	//vars + put leading zeros to day and month
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = start.getFullYear();
	var startHour = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();

	if (startDay.length == "1") {
		startDay = "0" + startDay;
	}

	if (startMonth.length == "1") {
		startMonth = "0" + startMonth;
	}
	if (startHour.length == "1") {
		startHour = "0" + startHour;
	}
	if (startMinutes.length == "1") {
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

	if (endDay.length == "1") {
		endDay = "0" + endDay;
	}
	if (endMonth.length == "1") {
		endMonth = "0" + endMonth;
	}
	if (endHour.length == "1") {
		endHour = "0" + endHour;
	}
	if (endMinutes.length == "1") {
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
	if (startSelectedDate == endSelectedDate) {

		$("#allDayOnT").attr('checked', true);
		//disable and hide time selects
		$('#startDateTimeT').val(" ");
		$('#startDateTimeT').hide();

		$('#endDateTimeT').val(" ");
		$('#endDateTimeT').hide();
	} else {
		$("#allDayOffT").attr('checked', true);
		//enable and show time selects
		$('#startDateTimeT').val(" ");
		$('#startDateTimeT').show();

		$('#endDateTimeT').val(" ");
		$('#endDateTimeT').show();
	};

	$("#addneweventteam")
		.data('parentTab', 'allCalendarTypes')
		.dialog('option', {
			title: translate("neuen Team Termin hinzufügen"),
		})
		.dialog('open');
}

function open_dialog_addnewevent() 
{

	var _args = arguments[0];
	var start = _args[0],
	end = _args[1],
	allDay = _args[2];
	
	
	select_calendar()

	return;
	
	//vars + put leading zeros to day and month
	var startDay = "" + start.getDate();
	var startMonth = "" + (start.getMonth() + 1);
	var startYear = start.getFullYear();
	var startHour = "" + start.getHours();
	var startMinutes = "" + start.getMinutes();

	if (startDay.length == "1") {
		startDay = "0" + startDay;
	}
	if (startMonth.length == "1") {
		startMonth = "0" + startMonth;
	}
	if (startHour.length == "1") {
		startHour = "0" + startHour;
	}
	if (startMinutes.length == "1") {
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

	if (endDay.length == "1") {
		endDay = "0" + endDay;
	}
	if (endMonth.length == "1") {
		endMonth = "0" + endMonth;
	}
	if (endHour.length == "1") {
		endHour = "0" + endHour;
	}
	if (endMinutes.length == "1") {
		endMinutes = "0" + endMinutes;
	}
	var endSelectedDate = endDay + '.' + endMonth + '.' + endYear;
	var endSelectedTime = endHour + ':' + endMinutes;
	//insert end date
	$('#endDate').val(endSelectedDate);

	if (startSelectedDate == endSelectedDate && startSelectedTime == endSelectedTime) {

		$("#allDayOn").attr('checked', true);
		//disable and hide time selects
		$('#startDateTime').val(" ");
		$('#startDateTime').hide();

		$('#endDateTime').val(" ");
		$('#endDateTime').hide();
	} else {
		$("#allDayOff").attr('checked', true);
		//enable and show time selects
		$('#startDateTime').val(startSelectedTime);
		$('#startDateTime').show();

		$('#endDateTime').val(endSelectedTime);
		$('#endDateTime').show();

	}


	$("#addnewevent")
		.data('parentTab', 'allCalendarTypes')
		.dialog('option', {
			title: translate("neuen Benutzer Termin hinzufügen"),
		})
		.dialog('open');
}
*/


function open_dialog_allCalendarTypes() 
{
	
	

	var _args = arguments[0];
	var start = _args[0],
		end = _args[1],
		allDay = _args[2];
	
	//if User&Team checked or !User&!Team checked then we need an extrat step to as what you want to add
	
	var _tabs = $("input.allCalendarTypesCb:checkbox:checked")
	.map(function() {
		return $(this).val();
	}).get()
	
	if ((_tabs.indexOf("calendar") != -1 || _tabs.indexOf("allPatientsCalendar") != -1) && _tabs.indexOf("teamCalendar") == -1) {
		// direclty to select Benutzer 
		
		select_calendar(start, end, allDay , 'allCalendarTypes');
		//open_dialog_addnewevent(_args);
		
		return; // return now, no extra dialog needed
		
	} else if (_tabs.indexOf("calendar") == -1 && _tabs.indexOf("allPatientsCalendar") == -1 && (_tabs.indexOf("teamCalendar") != -1)) {
		// direclty to select Team
		//open_dialog_addneweventteam(_args);
		select_teamCalendar(start, end, allDay, 'allCalendarTypes');
		
		return; // return now, no extra dialog needed
	}
	
	//this dialog is for ELSE
	
	$("<div/>")
		.dialog({
			autoOpen: true,
			modal: true,
			resizable: false,
			height: 1,
			width: 300,

			title: translate('Select new event type'),

			buttons: [{
					text: translate('user'),
					click: function() {
						
						$(this).dialog("close");
						
						$("input.allCalendarTypesCb[value=calendar]:checkbox").attr('checked', true);//chech the User cb
						
						select_calendar(start, end, allDay , 'allCalendarTypes');
						
						//open_dialog_addnewevent(_args);
					}
				},
				{
					text: translate('Team'),
					click: function() {
						$(this).dialog("close");
						
						$("input.allCalendarTypesCb[value=teamCalendar]:checkbox").attr('checked', true);//chech the Team cb
						
						//open_dialog_addneweventteam(_args);
						select_teamCalendar(start, end, allDay, 'allCalendarTypes');
					}
				}
			],

			close: function() {
				$(this).dialog('destroy').remove();
			},
			/*
			open : function() {
				var buttons = $(this).dialog('option', 'buttons');
				console.log(buttons);
			}
			*/
		});
}


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