$(document).ready(function() {
	
	  $('#interval_start_date').datepicker();
	  $('#interval_end_date').datepicker();
 
		$.make_vital_chart = function( json_data )
		{
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('vital_signs_chart', {
				chart: {
					marginLeft: 250,
				},
				title: json_data.title,
			    
				exporting: {
		             enabled: false
		        },
		         
			    yAxis: json_data.yaxis,

	            xAxis: {
	                crosshair: false,
	                type: 'datetime',
	                minTickInterval: 1800000,
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
	                dateTimeLabelFormats: {
	                	minute:"<b>%d.%m</b> %H:%M",
	                    day: '<b>%d.%m</b>'
	                },
	                labels: {
	                    style: {
	                        fontSize: '12px'
	                    }
	                },
	                plotLines: json_data.xplotlines
	            },			    
			    
			    tooltip: {
			        enabled: true,
			        hideDelay: 10,
			        snap: 0,
			        dateTimeLabelFormats: {
			            millisecond:"%d.%m.%Y %H:%M:%S.%L",
			            second:"%d.%m.%Y %H:%M:%S",
			            minute:"%d.%m.%Y %H:%M",
			            hour:"%d.%m.%Y %H:%M",
			            day:"%d.%m.%Y",
			            week:"%d.%m.%Y",
			            month:"%m.%Y"		            
			        },
			        style: {
	                    fontSize: '18px'
	                },
	                pointFormat: '{series.name}: <b>{point.y:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}',
	                headerFormat: '',
	                formatter: function () {

	                                   	     	           	
		                	return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
		                        point: this,
		                        series: this.series
		                    });
	                    
	                    return false;                                        
	                }
			    },
			    plotOptions: {
			    	series: {
			    		stickyTracking: false
			    	},
			    	column: {
			    		cursor: 'pointer',
			    		
			            point: {
				            events: {
				                click: function (e) {
				                }
				            }
			            }
				    }
			    },
			    
			    
			    legend: {
				    enabled: false,
				    layout: 'horizontal',
			        align: 'center',
			        verticalAlign: 'bottom',
			        itemMarginTop: 20,
			        itemMarginBottom: 20
			    },
			    
			    credits: {
			        enabled: false
			    },
			    
			    series: json_data.series
			    
			},  function (chart) {
		        
		    });

			return chart;
		};
		
		
		$.make_ventilation_info_chart = function( json_data )
		{
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('ventilation_info_chart', {
				chart: {
					marginLeft: 250,
					height: json_data.chart_height
				},
				title: json_data.title,
				exporting: {
		             enabled: false
		         },
				yAxis: {
					title: null,
					startOnTick: true,
					endOnTick: true,
			        labels: {
			            enabled: false
			        }
			    },
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
	                	minute:"<b>%d.%m</b> %H:%M",
	                    day: '<b>%d.%m</b>'
	                },
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
							
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{series.name}: <b>{point.y:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false
					},
					column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
								}
							}
						}
					}
				},
				legend: {
					enabled: true,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'middle',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
 
		$.make_positioning_chart = function( json_data )
		{
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('positioning_chart', {
				chart: {
					type: 'scatter',
					height: json_data.chart_height,
					marginLeft: 250,
					title: null
				},

				title: json_data.title,
				
				exporting: {
		             enabled: false
		         },
				
				yAxis: {
					title: false,
			        labels: {
			            enabled: false
			        }
			    },
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,
						dataLabels: {
							enabled:true,
							y:10,
							align: 'left',
							formatter: function() {
									return this.point.label;
								}
					        } 
					},
					scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#positioning_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
					/*column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
								}
							}
						}
					}*/
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		
		$.make_suckoff_events_chart = function( json_data )
		{
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('suckoff_events_chart', {
				chart: {
					type: 'scatter',
					height: json_data.chart_height,//height : 150,
					marginLeft: 250,
					title: null
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		         },
				yAxis: {
					title: false,
					labels: {
						enabled: false
					}
				},
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,
						dataLabels: {
							enabled:true,
							formatter: function() {
								if(this.y > 0)
									return this.point.name;
							}
						}
					},
					scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#suckoff_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
					/*column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
								}
							}
						}
					}*/
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		$.make_custom_events_chart = function( json_data )
		{
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('custom_events_chart', {
				chart: {
					type: 'scatter',
					height: json_data.chart_height,//height : 150,
					marginLeft: 250,
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		         },
				yAxis: {
						title: false,
				        labels: {
				            enabled: false
				        }
				    },
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,			
						dataLabels: {
							enabled:true,
							y:10,
							align: 'left',
							formatter: function() {
									return this.point.name;
								}
					        } 
					},
					scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#custom_event_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
					/*column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
									console.log(e);
								}
							}
						}
					}*/
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		$.make_organic_entries_exits_chart = function( json_data )
		{
			var today = new Date();
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('organic_entries_exits_chart', {
				chart: {
					type: 'scatter',
					marginLeft: 250,
					height: json_data.chart_height
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		         },
				yAxis: {
					title: false,
					labels: {
						enabled: false
					},
					plotLines: [{
	
		                 value: today,
		                 color: 'red',
		                 width: 3
		             }]
				
				},
				xAxis: {
					type: 'datetime',
					//opposite:true
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,			
						dataLabels: {
							enabled:true,
							y:10,
							align: 'left',
							formatter: function() {
								return this.point.name;
							}
						} 
					},
					scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#organicentriesexits_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		
		
		$.make_artificial_entires_exits_chart = function( json_data )
		{
			
			var today = new Date();
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('artificial_entires_exits_chart', {
				chart: {
					type: 'scatter',
					marginLeft: 250,
				     height: json_data.chart_height
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		         },
				yAxis: {
					title: false,
					labels: {
						enabled: false
					},
					plotLines: [{
						
						value: today,
						color: 'red',
						width: 3
					}]
					
				},
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,			
						dataLabels: {
							enabled:true,
							y:10,
							align: 'left',
							formatter: function() {
								return this.point.name;
							}
						} 
					},
					
	                scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#artificial_entries_exits_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		
		
		
		
		$.make_symptomatology_chart = function( json_data )
		{
			var today = new Date();
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('symptomatology_chart', {
				
				chart: {
					type: 'scatter',
					marginLeft: 250,
					title: null,
					height: json_data.chart_height
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		        },
				
		        yAxis: json_data.yaxis,
				
				xAxis: {
					type: 'datetime',
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
					plotLines: json_data.xplotlines
				},
				
				tooltip: {
					enabled: true,
					hideDelay: 10,
					snap: 0,
					dateTimeLabelFormats: {
						millisecond:"%d.%m.%Y %H:%M:%S.%L",
						second:"%d.%m.%Y %H:%M:%S",
						minute:"%d.%m.%Y %H:%M",
						hour:"%d.%m.%Y %H:%M",
						day:"%d.%m.%Y",
						week:"%d.%m.%Y",
						month:"%m.%Y"		            
					},
					style: {
						fontSize: '18px'
					},
					pointFormat: '{point.label}: {point.value}<br/>{point.x:%d.%m.%Y %H:%M}',
					headerFormat: '',
					formatter: function () {
						
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					}
				},
				plotOptions: {
					series: {
						stickyTracking: false,
						dataLabels: {
							enabled:true,
							y:10,
							formatter: function() {
								if(this.y > 0)
									return this.point.value;
								}
					        }
					        
					},
					column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
								}
							}
						}
					}
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					itemMarginBottom: 10
				},
				credits: {
					enabled: false
				},
				
				categories: json_data.categories,
				
				series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
		
		
		
		
		$.make_medication_actual_chart = function( json_data )
		{
			var today = new Date();
	        Highcharts.setOptions({
	             // time: {      timezone: 'Europe/London'  },
	             lang: {   
	                thousandsSep: ".",
	                decimalPoint: ",",
	                months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
	                shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
	                weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
	                shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
	            }
	        });
	        //ISPC-2871,Elena,30.03.2021
	        var max = 0;
	        if((json_data.plotlines !== undefined) && (json_data.plotlines.length>0)){
	        	max = json_data.plotlines.length-1;
			}

	        var medchart = Highcharts.chart('medication_actual_chart', {

	        	title: json_data.title,
	        	
	            exporting: {
		             enabled: false
		         },
	            chart: {
	    			marginLeft: 250,
					height: json_data.chart_height
	            },
	            yAxis: {
	                categories: json_data.categories,
	                type: 'category',
	                startOnTick: false,
	                tickInterval: 1,
	                min: 0,
	                max: max,//ISPC-2871,Elena,30.03.2021
	                minorTickInterval: null,
	                gridLineWidth: 1,
	                showFirstLabel: true,
	                showLastLabel: true,
	                tickmarkPlacement: 'between',
	                reversed: true,
	                title: {
	                    text: null
	                },
	                plotLines2: json_data.plotlines

	            },
	            xAxis: {
	                crosshair: false,
	                type: 'datetime',
	                minTickInterval: 1800000,
	                min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
	                dateTimeLabelFormats: {
	                    day: '<b>%d.%m</b>'
	                },
	                labels: {
	                    style: {
	                        fontSize: '12px'
	                    }
	                },
	                plotLines: json_data.xplotlines
	            },
	            tooltip: {
	                enabled: true,
	                hideDelay: 10,
	                snap: 0,

	                useHTML: true,
	                backgroundColor: '#FCFFC5',
	                borderWidth: 1,
	                shadow: false,

	                pointFormat: '{point.x:%d.%m.%Y %H:%M}',
	                dateTimeLabelFormats: {
	                    millisecond:"%d.%m.%Y %H:%M:%S.%L",
	                    second:"%d.%m.%Y %H:%M:%S",
	                    minute:"%d.%m.%Y %H:%M",
	                    hour:"%d.%m.%Y %H:%M",
	                    day:"%d.%m.%Y",
	                    week:"%d.%m.%Y",
	                    month:"%m.%Y"
	                },
	                style: {
	                    fontSize: '12px',
	                    padding: 0
	                },
					pointFormat: '{point.info}',
	                formatter: function () {
	                    if(!this.point.noTooltip)
	                    {
	                        return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
	                            point: this,
	                            series: this.series
	                        });
	                    }

	                    return false;
	                }
	            },
	            plotOptions: {

	                line: {
	                    cursor: 'pointer',
	                    allowPointSelect: false,
	                    stickyTracking: false,

	                    events: {
	                    }

	                },
	                
	        		series: {
						stickyTracking: false,
						dataLabels: {
							enabled:true,
							align: 'top',
							formatter: function() {
									return this.point.label;
								}
					        } 
					        
					},
	    			
	                scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#medication_dosage_interaction_modal" )
                                     .data('drugplan_id', this.options.drugplan_id )
                                     
                                     .data('medication_name', this.options.medication_name )
                                     .data('dosage_unit', this.options.dosage_unit )
                                     .data('time_schedule', this.options.time_schedule )
                                     
                                     .data('dosage_status', 		this.options.documented_dosage_interaction.status )
                                     .data('dosage', 				this.options.documented_dosage_interaction.dosage )
                                     .data('dosage_date', 			this.options.documented_dosage_interaction.dosage_date )
                                     .data('dosage_time_interval',  this.options.documented_dosage_interaction.dosage_time_interval )
                                     .data('documented_info', 		this.options.documented_dosage_interaction.documented_info )
                                     .data('not_given_reason', 		this.options.documented_dosage_interaction.not_given_reason)
                                     
                                     .data('md_source', 'charts').dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }

	            },
	            legend: {
	                enabled: false
	            },
	            credits: {
	                enabled: false
	            },
	            series: json_data.series,

	            navigator: {
	                enabled: false
	            },
	            scrollbar: {
	                enabled: false
	            },
	            rangeSelector: {
	                allButtonsEnabled: false,
	                buttons: [{
	                    type: 'all',
	                    text: 'Month'
	                }],
	                buttonTheme: {
	                    width: 60
	                },
	                selected: 0
	            }
	        },  function (chart) {

	        });

	        return medchart;
	    };
	/**
	 * ISPC-2871,Elena,30.03.2021
	 *
	 * @param json_data
	 * @returns {*}
	 */
	$.make_medication_isivmed_chart = function( json_data )
	{
		var today = new Date();
		Highcharts.setOptions({
			// time: {      timezone: 'Europe/London'  },
			global: {
				useUTC: true,
			},
			lang: {
				thousandsSep: ".",
				decimalPoint: ",",
				months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
				shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
				weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
				shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
			}
		});
		var max = 0;
		if((json_data.plotlines !== undefined) && (json_data.plotlines.length>0)){
			max = json_data.plotlines.length-1;
		}


		var medchart = Highcharts.chart('medication_isivmed_chart', {

			title: json_data.title,

			exporting: {
				enabled: false
			},
			chart: {
				marginLeft: 250,
				height: json_data.chart_height
			},
			yAxis: {
				categories: json_data.categories,
				type: 'category',
				startOnTick: false,
				tickInterval: 1,
				min: 0,
				max: max,
				minorTickInterval: null,
				gridLineWidth: 1,
				showFirstLabel: true,
				showLastLabel: true,
				tickmarkPlacement: 'between',
				reversed: true,
				title: {
					text: null
				},
				plotLines2: json_data.plotlines

			},
			xAxis: {
				//ISPC-2661 pct.8 Carmen 19.09.2020
				//visible: false,//TODO-3448 Ancuta 13.10.2020
				//--
				crosshair: false,
				opposite:true, // show time on top
				type: 'datetime',
				minTickInterval: 1800000,
				min: json_data.min,
				max: json_data.max,
				startOnTick: false,
				endOnTick: false,
				tickInterval:json_data.XtickInterval,
				dateTimeLabelFormats: {
					day: '<b>%d.%m</b>'
				},
				labels: {
					enabled: false,//TODO-3448 Ancuta 13.10.2020
					style: {
						fontSize: '12px'
					}
				},
				plotLines: json_data.xplotlines
			},
			tooltip: {
				enabled: true,
				hideDelay: 10,
				snap: 0,

				useHTML: true,
				backgroundColor: '#FCFFC5',
				borderWidth: 1,
				shadow: false,

				pointFormat: '{point.x:%d.%m.%Y %H:%M}',
				dateTimeLabelFormats: {
					millisecond:"%d.%m.%Y %H:%M:%S.%L",
					second:"%d.%m.%Y %H:%M:%S",
					minute:"%d.%m.%Y %H:%M",
					hour:"%d.%m.%Y %H:%M",
					day:"%d.%m.%Y",
					week:"%d.%m.%Y",
					month:"%m.%Y"
				},
				style: {
					fontSize: '12px',
					padding: 0
				},
				pointFormat: '{point.info}',
				formatter: function () {
					if(!this.point.noTooltip)
					{
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
					}

					return false;
				}
			},
			plotOptions: {

				line: {
					cursor: 'pointer',
					allowPointSelect: false,
					stickyTracking: false
				},

				series: {
					stickyTracking: false,
					dataLabels: {
						useHTML: true,
						enabled:true,
						align: 'left',
						verticalAlign: 'middle',
						formatter: function() {
							return this.point.label;
						}
					}
				},

				scatter: {
					allowPointSelect: false,
					stickyTracking: false,
					cursor: 'pointer',
					animation: false,

					point: {
						events: {
							click: function (e) {

								$( "#medication_dosage_interaction_modal" )
									.data('drugplan_id', this.options.drugplan_id )
									.data('cocktail_id', '0')
									.data('medication_name', this.options.medication_name )
									.data('dosage_unit', this.options.dosage_unit )
									.data('time_schedule', this.options.time_schedule )

									.data('entry_id', 			this.options.documented_dosage_interaction.entry_id)
									.data('dosage_status', 		this.options.documented_dosage_interaction.status )
									.data('dosage', 				this.options.documented_dosage_interaction.dosage )
									.data('dosage_date', 			this.options.documented_dosage_interaction.dosage_date )
									.data('dosage_time_interval',  this.options.documented_dosage_interaction.dosage_time_interval )
									.data('documented_info', 		this.options.documented_dosage_interaction.documented_info )
									.data('not_given_reason', 		this.options.documented_dosage_interaction.not_given_reason)

									.data('md_source', 'charts').dialog( "open" );

							}
						}
					}
				}

			},
			legend: {
				enabled: false
			},
			credits: {
				enabled: false
			},
			series: json_data.series,

			navigator: {
				enabled: false
			},
			scrollbar: {
				enabled: false
			}
		},  function (chart) {
			//ISPC-2871,Elena,30.03.2021
			//$.addMediExplanationToChart(chart);
			chart.showLoading(translate('chart_loadingpleasewait'));
			setTimeout(function () {
				chart.hideLoading();
			}, 3000);

		});

		return medchart;
	};

	/**
	 * ISPC-2871,Elena,30.03.2021
	 * Explanation for Medi chart
	 *
	 * @param chart
	 */
	$.addMediExplanationToChart = function(chart){
		chart.renderer.image( appbase + 'images/chart_icons/ok.svg',chart.plotLeft, chart.plotTop - 50,15,15).add();
		chart.renderer.text('gegeben',  chart.plotLeft + 20, chart.plotTop - 40)
			.css({
				//color: '#4572A7',
				fontSize: '12px',

			})
			.add();
		chart.renderer.image( appbase + 'images/chart_icons/no.svg',chart.plotLeft + 80, chart.plotTop - 50,15,15).add();
		chart.renderer.text('nicht gegeben',  chart.plotLeft + 100, chart.plotTop - 40)
			.css({
				//color: '#4572A7',
				fontSize: '12px',

			})
			.add();
		chart.renderer.image( appbase + 'images/chart_icons/other_dosage.svg',chart.plotLeft + 190, chart.plotTop - 50,15,15).add();
		chart.renderer.text('in anderer Dosierung gegeben',  chart.plotLeft + 210, chart.plotTop - 40)
			.css({
				//color: '#4572A7',
				fontSize: '12px',

			})
			.add();

		chart.renderer.image( appbase + 'images/chart_icons/reject.svg',chart.plotLeft + 400, chart.plotTop - 50,15,15).add();
		chart.renderer.text('Patient verweigert Einnahme',  chart.plotLeft + 420, chart.plotTop - 40)
			.css({
				//color: '#4572A7',
				fontSize: '12px',

			})
			.add();

	}




	$.make_medication_isbedarfs_chart = function( json_data )
	    {
	    	var today = new Date();
	    	Highcharts.setOptions({
	    		 // time: {      timezone: 'Europe/London'  },
	    		 lang: {   
	    			thousandsSep: ".",
	    			decimalPoint: ",",
	    			months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
	    			shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
	    			weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
	    			shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
	    		}
	    	});
	    	
	    	var medchart = Highcharts.chart('medication_isbedarfs_chart', {
	    		
				title: json_data.title,
				
	    		exporting: {
		             enabled: false
		         },
	    		chart: {
	    			marginLeft: 250,
	    			height: json_data.chart_height
	    		},
	    		yAxis: {
	    			categories: json_data.categories,
	    			type: 'category',
	    			startOnTick: false,
	    			tickInterval: 1,
	    			min: 0,
	    			max: (json_data.plotlines.length-1),
	    			minorTickInterval: null,
	    			gridLineWidth: 1,
	    			showFirstLabel: true,
	    			showLastLabel: true,
	    			tickmarkPlacement: 'between',
	    			reversed: true,
	    			title: {
	    				text: null
	    			},
	    			plotLines2: json_data.plotlines
	    			
	    		},
	    		xAxis: {
	    			crosshair: false,
	    			type: 'datetime',
	    			minTickInterval: 1800000,
	    			min: json_data.min,
	    			max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
	    			dateTimeLabelFormats: {
	    				day: '<b>%d.%m</b>'
	    			},
	    			labels: {
	    				style: {
	    					fontSize: '12px'
	    				}
	    			},
	    			plotLines: json_data.xplotlines
	    		},
	    		tooltip: {
	    			enabled: true,
	    			hideDelay: 10,
	    			snap: 0,
	    			
	    			useHTML: true,
	    			backgroundColor: '#FCFFC5',
	    			borderWidth: 1,
	    			shadow: false,
	    			
	    			pointFormat: '{point.x:%d.%m.%Y %H:%M}',
	    			dateTimeLabelFormats: {
	    				millisecond:"%d.%m.%Y %H:%M:%S.%L",
	    				second:"%d.%m.%Y %H:%M:%S",
	    				minute:"%d.%m.%Y %H:%M",
	    				hour:"%d.%m.%Y %H:%M",
	    				day:"%d.%m.%Y",
	    				week:"%d.%m.%Y",
	    				month:"%m.%Y"
	    			},
	    			style: {
	    				fontSize: '12px',
	    				padding: 0
	    			},
	    			pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}',
	    			formatter: function () {
	    				if(!this.point.noTooltip)
	    				{
	    					return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
	    						point: this,
	    						series: this.series
	    					});
	    				}
	    				
	    				return false;
	    			}
	    		},
	    		plotOptions: {
	    			
	    			line: {
	    				cursor: 'pointer',
	    				allowPointSelect: false,
	    				stickyTracking: false,
	    				
	    				events: {
	    				}
	    		
	    			},
	    			
	    			series: {
	    				stickyTracking: false,
	    				dataLabels: {
	    					enabled:true,
	    					align: 'top',
	    					formatter: function() {
	    						return this.point.label;
	    					}
	    				} 
	    			
	    			},
	    			
	                scatter: {
	                    allowPointSelect: false,
	                    stickyTracking: false,
	                    cursor: 'pointer',
	                    animation: false,

	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#medication_dosage_interaction_modal" )
                                     .data('drugplan_id', this.options.drugplan_id )
                                     
                                     .data('medication_name', this.options.medication_name )
                                     .data('dosage_unit', this.options.dosage_unit )
                                     .data('time_schedule', this.options.time_schedule )
                                     
                                     .data('dosage_status', 		this.options.documented_dosage_interaction.status )
                                     .data('dosage', 				this.options.documented_dosage_interaction.dosage )
                                     .data('dosage_date', 			this.options.documented_dosage_interaction.dosage_date )
                                     .data('dosage_time_interval',  this.options.documented_dosage_interaction.dosage_time_interval )
                                     .data('documented_info', 		this.options.documented_dosage_interaction.documented_info )
                                     .data('not_given_reason', 		this.options.documented_dosage_interaction.not_given_reason)
                                     
                                     .data('md_source', 'charts').dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
	    			
	    		},
	    		legend: {
	    			enabled: false
	    		},
	    		credits: {
	    			enabled: false
	    		},
	    		series: json_data.series,
	    		
	    		navigator: {
	    			enabled: false
	    		},
	    		scrollbar: {
	    			enabled: false
	    		},
	    		rangeSelector: {
	    			allButtonsEnabled: false,
	    			buttons: [{
	    				type: 'all',
	    				text: 'Month'
	    			}],
	    			buttonTheme: {
	    				width: 60
	    			},
	    			selected: 0
	    		}
	    	},  function (chart) {
	    		
	    	});
	    	
	    	return medchart;
	    };
	    
	    
		$.make_awake_sleep_status_chart = function( json_data )
		{
			var today = new Date();
			
			Highcharts.setOptions({
				 // time: {      timezone: 'Europe/London'  },
				 lang: {   
					thousandsSep: ".",
					decimalPoint: ",",
					months: [ "Januar" , "Februar" , "März" , "April" , "Mai" , "Juni" , "Juli" , "August" , "September" , "Oktober" , "November" , "Dezember" ],
					shortMonths: [ "Jan" , "Feb" , "März" , "Apr" , "Mai" , "Juni" , "Juli" , "Aug" , "Sept" , "Okt" , "Nov" , "Dez" ],
					weekdays: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
					shortWeekdays: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ]
				}
			});
			
			var chart = Highcharts.chart('awake_sleep_status_chart', {

		         chart: {
		             type: 'columnrange',
		             inverted: true,
		             marginLeft: 250,
		             height: json_data.chart_height
		         },
		         title: json_data.title,
 
		         exporting: {
		        	 enabled: false
		         },
		         scrollbar: {
		             enabled: false
		         },
		         
		         xAxis: {
			        labels: {
				        enabled: false
			        }
		         },
		         
		         yAxis: {
		            type: 'datetime',
		            min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					labels: {
						enabled: true,
						style: {
							fontSize: '12px'
						}
					},
		             title: {
		                 text: null
		             },
					dateTimeLabelFormats: {
						day: '<b>%d.%m</b>'
					},
					plotLines: json_data.xplotlines
 
		         },
		         plotOptions: {
		             columnrange: {
		                 grouping: true
		             }
		         },
		         legend: {
		             enabled: true,
				    layout: 'vertical',
			        align: 'left',
			        verticalAlign: 'middle',
			        itemMarginTop: 5,
		         },
		         tooltip: {
		             formatter: function () {
		                 return '<b>' + this.series.name + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>';
		             }
		         },
		         credits: {
				        enabled: false
				    },
		         series: json_data.series
				
			},  function (chart) {
				
			});
			
			return chart;
		};
});


function load_navigation(pid,chart_view_type='week',start,end){
	
	$.getnavigation = function() {
		
		var url = appbase+'charts/navigation?id='+pid+'&chart_view_type='+chart_view_type+'&start='+start+'&end='+end ;

		//show a loading gif
		$('#chart_navigation').html('<br/><div class="loadingdiv" align="center"><img src="'+res_path+'/images/ajax-loader.gif"><br />'+translate('loadingpleasewait')+'</div><br/>');


		xhr = $.ajax({
			url : url,
			cache: false ,
			success : function(response) {
				$('#chart_navigation').html(response);
			}
		});
	}
	
	$.getnavigation();
	
}

function load_charts(pid,start,end , chart_blocks){					//ISPC-2841 Lore 29.03.2021 - chart_blocks

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_actual")){
        var block = "medication_actual";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_medication_actual_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }
    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_isbedarfs")){
    	var block = "medication_isbedarfs";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
    		var chart = $.make_medication_isbedarfs_chart( data );
    		chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    	});	
    }


	//ISPC-2871,Elena,30.03.2021
	 if(chart_blocks.includes("medication_isivmed")){
	var block = "medication_isivmed";
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) {

		$('#medication_isivmed_chart').show();
		var chart = $.make_medication_isivmed_chart( data );
		chart.xAxis[0].setExtremes( data.min, data.max,true, false);

	});
	}

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("vital_signs")){
    	// vital signs chart
     	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=vital_signs', function (data) {
                var chart = $.make_vital_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });	
    }

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("awake_sleep_status")){
        var block = "awake_sleep_status";
     	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                 var chart = $.make_awake_sleep_status_chart( data );
                 chart.xAxis[0].setExtremes( data.min, data.max,false, false); // !!!!  
         });
    }


	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("symptomatology")){
        var block = "symptomatology";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_symptomatology_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("organic_entries_exits")){
        var block = "organic_entries_exits";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_organic_entries_exits_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });	
    }

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("artificial_entires_exits")){
        var block = "artificial_entires_exits";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_artificial_entires_exits_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }
    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("positioning")){
    	// positioning chart
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=positioning', function (data) {
                var chart = $.make_positioning_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("custom_events")){
    	// custom_events chart
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=custom_events', function (data) {
                var chart = $.make_custom_events_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }
	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("suckoff_events")){
        var block = "suckoff_events";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_suckoff_events_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }


	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("ventilation_info")){
        var block = "ventilation_info";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
                var chart = $.make_ventilation_info_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
        });
    }
	

	
}


function loadPage(){
	  var chart_view_type = $('#selected_chart_type').val();
 
//      var start = $('#interval_start_date').datepicker('getDate');
//      var end = $('#interval_end_date').datepicker('getDate');
//
//      start = Date.parse(start);
//      end = Date.parse(end);
//
      
	  
	  // Changes- before 02.05.2020
	  
		/*var start_input = $('#interval_start_date').datepicker('getDate');
		var date =  new Date(start_input); 
		var start =  new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()));
		start = Date.parse(start);
		 
		var end_input = $('#interval_end_date').datepicker('getDate');
		var end_date =  new Date(end_input); 
		var end = new Date( Date.UTC(end_date.getUTCFullYear(), end_date.getUTCMonth(), end_date.getUTCDate(),	 end_date.getUTCHours(), end_date.getUTCMinutes(), end_date.getUTCSeconds()));
		
		end = Date.parse(end);*/ 
	  //	
		
		// send exactly what is clicked
	  /*
		var start_input = $('#interval_start_date').datepicker('getDate');
		var start_date =  new Date(start_input);
		start = Date.parse(start_date);
		
		
		var end_input = $('#interval_end_date').datepicker('getDate');
		var end_date =  new Date(end_input);
		end = Date.parse(end_date);
		
		
		*/
		start = $('#interval_start_date').val();
		end = $('#interval_end_date').val();
		
	  // load navigation
	  load_navigation(pid,chart_view_type,start,end)
		  
	  // load charts
	  load_charts(pid,start,end)
	
}

function goToInterval(view_type,date){
	
//	window.location.href = appbase + 'charts/overview?id='+pid+'&date='+date+'&view_type='+view_type;
	  // load navigation
	  load_navigation(pid,chart_view_type,start,end)
		  
	  // load charts
	  load_charts(pid,start,end)
	
	
}

function goToInterval_old(view_type,date){
	
	window.location.href = appbase + 'charts/overview?id='+pid+'&date='+date+'&view_type='+view_type;
	
}
			

	   		/*function show_extrafields(orgid, recid)
	   		{
	   			$.get(appbase + 'charts/createformblockorganicextrafields?orgid='+orgid+'&recid='+recid, function(result) {
	   					$('.extrafieldsrow').remove();
	   					var newFieldset =  $(result).insertAfter($('#organic_id').closest('tr'));
	   					
					});
	   			
	   		}*/

//ISPC-2508 Carmen 21.04.2020
	/*function clientSettings(opt_id, client_options)
	{
		var cl_set;
		$.each(client_options, function(key, option){
			cl_set = {};
			if(option.id == opt_id)
			{
				cl_set['localization_available'] = option.localization_available;
				cl_set['name'] = option.name;
				cl_set['days_availability'] = option.days_availability;
				return false;
			}
		});
		return cl_set;
	}*/
//ISPC-2508 Carmen 21.04.2020
	   		