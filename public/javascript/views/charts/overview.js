/**
 * @auth Ancuta 
 * #ISPC-2512PatientCharts
 * @returns 
 * 
 */
$(document).ready(function() {

	//ISPC-2512  Ancuta N3 09.06.2020
	var wrapp_floater = null;
	wrapp_floater = $('#charts_display');
	

	/*$('#time_chart_container').prependTo(wrapp_floater);
	$('#time_chart_container').portamento({
		wrapper: wrapp_floater,
		gap:45
	});*/
	//--
	$(document).scroll(function() {
		  var y = $(this).scrollTop();
		  if (y > 50) {
		    $('.sticky').fadeIn();
		  } /*else {
		    $('.sticky').fadeOut();
		  }*/
		});
	
	  $('#interval_start_date').datepicker();
	  $('#interval_end_date').datepicker();

		$.make_vital_chart = function( json_data )
		{
			Highcharts.setOptions({
			    time: {
			        useUTC: false
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
			
			var colors = Highcharts.getOptions().colors;
			
			var chart = Highcharts.chart('vital_signs_chart', {
				chart: {
					marginLeft: 250,
					height: 400,
					 spacingLeft: 20,
					 spacingBottom: 40,
					  events: {
					      render: function() {
					        var chart = this;

					        chart.yAxis.forEach(function(axis, i) {
					          if (!axis.callout) {
					            var callout = chart.renderer.label(
					                axis.options.customTitle,
					                chart.plotLeft + axis.offset - 12,
					                chart.plotTop + axis.len + 6,
					                'callout',
					                chart.plotLeft + axis.offset,
					                chart.plotTop + axis.len
					              )
					              .css({
					                color: '#FFF'
					              })
					              .attr({
					                fill: axis.options.color,
					                padding: 5,
					                r: 5,
					                zIndex: 6
					              })
					              .add();
					          }
					        })

					      }
					    }
				},
				title: json_data.title,
			    
				exporting: {
		             enabled: false
		        },
		         
			    yAxis: json_data.yaxis,

	            xAxis: {
	            	//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false, //TODO-3448 Ancuta 13.10.2020 
	            	//--
	                crosshair: false,
	                type: 'datetime',
	                opposite:true, // show time on top
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
			    		stickyTracking: false,
						dataLabels: {
							enabled:true,
							formatter: function() {
									if(this.point.label){
										return this.point.label;
									}
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
				    /*enabled: false,
				    layout: 'horizontal',
			        align: 'center',
			        verticalAlign: 'bottom',
			        itemMarginTop: 20,
			        itemMarginBottom: 20*/
			    },
			    
			    credits: {
			        enabled: false
			    },
			    
			    series: json_data.series
			    
			},  function (chart) {
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
		    });

			return chart;
		};
		
		
		$.make_ventilation_info_chart = function( json_data )
		{
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
 
		$.make_positioning_individual_chart = function( json_data )
		{
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
			
			var chart = Highcharts.chart('positioning_individual_chart', {
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		
		
		$.make_positioning_chart = function( json_data )
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
			
			var chart = Highcharts.chart('positioning_chart', {

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
		        	//ISPC-2661 pct.13 
		        	categories: json_data.categories,
					 //ISPC-2903,Elena,26.04.2021
					 labels: {
						 enabled: true,
						 formatter: function () {
							 return '<span title="' + this.value  + '">' + this.value + '</span>';
						 },
						 useHTML: true
					 }
			        /*labels: {
				        enabled: false
			        }*/
		        	//--
		         },
		         
		         yAxis: {
		        	//ISPC-2661 pct.8 Carmen 19.09.2020
		            //visible: false,//TODO-3448 Ancuta 13.10.2020 
		            //--
		            type: 'datetime',
		            opposite:true, // show time on top
		            min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					labels: {
						//ISPC-2661 pct.8 Carmen 14.09.2020
						enabled: false,//TODO-3448 Ancuta 13.10.2020 
						//enabled: false,
						//--
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
		                 
		             },
					series: {
	                    cursor: 'pointer',
	                	dataLabels: {
							enabled:false,
					        align: 'top',
					        verticalAlign: 'top',
							formatter: function() {
								if(this.y > 0)
									return this.series.name;
							}
						},
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
		         },
		         legend: {
		            enabled: false,
				    layout: 'vertical',
			        align: 'left',
			        verticalAlign: 'middle',
			        itemMarginTop: 5,
		         },
		         tooltip: {
		             formatter: function () {
		            	//ISPC-2661 pct.13 
		                 /*return '<b>' + this.series.name + '</b><br/>'+ this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;*/
		            	
		            	 if(this.point.uncertainend == 0)
		            	 {
		            		 return '<b>' + translate(this.series.name) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
		            	 }
		            	 else
		            	{
		            		 return '<b>' + translate(this.series.name) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
		            	}
		            	//--
		             }
		             
		         },
		         credits: {
				        enabled: false
				    },
		         series: json_data.series
				
			},  function (chart) {
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		
		$.make_suckoff_events_chart = function( json_data )
		{
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}<br />{point.usershortname}',
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		//ISPC-2661 pct.13 Carmen 11.09.2020
		$.make_custom_events_individual_chart = function( json_data )
		{
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
			
			var chart = Highcharts.chart('custom_events_individual_chart', {
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
					opposite:true, // show time on top
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
					//useHTML: true, //ISPC-2661 pct.5 Carmen 11.09.2020
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
						fontSize: '18px',
							zIndex: 1000	
					},
//					pointFormat: '<span class="custom_tooltip">{point.info}<br/>{point.x:%d.%m.%Y %H:%M}</span>',
					pointFormat: '{point.info}<br />{point.usershortname}',
					
					formatter: function () {
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					},
					
//					formatter: function() {
//						return 	''+'<p style="color:#9ab;font-family:Lucida Grande,sans-serif;font-size:11px;text-align:center;margin:0;padding:2px 7px;">' + 
//								'<strong style="color:#fff;font-weight:normal;font-size:16px;">' + this.y +' film' + (this.y == 1 ? '' : 's') + '</strong></p>';
//					},
				},
				plotOptions: {
	        		series: {
						stickyTracking: false,
						dataLabels: {
							useHTML: true,
							enabled:true,
					        align: 'left',
					        verticalAlign: 'middle',
							formatter: function() {
									return this.point.Htmllabel;
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
	                            	if(this.options.is_cf == '1'){
	                            		
	                            		$( "#cfinfo_modal" )
	                            		.data('recid', this.options.entry_id)
	                            		.dialog( "open" );
	                            		
	                            	} else{
		                            	 $( "#custom_event_modal" )
		                            	 .data('recid', this.options.entry_id)
	                                     .dialog( "open" );
	                            	}
	                            	
	                            }
	                        }
	                    }
	                },
					column: {
						cursor: 'pointer',
						
						point: {
							events: {
								click: function (e) {
									console.log(e);
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		$.make_custom_events_chart  = function( json_data )
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
			
			var chart = Highcharts.chart('custom_events_chart', {

		         chart: {
		             //type: 'columnrange',
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
		         
		         xAxis:[{
		        	 //ISPC-2661 Carmen
		           categories: json_data.categories, 
			       labels: {
				        enabled: true
			        }
		         },
		         {
		        	 title: false,
				        labels: {
				            enabled: false
				        },
				        top: '0',
				        height: '30',
		         }]
		        	//--
		         ,
		         
		         yAxis: {
		        	//ISPC-2661 pct.8 Carmen 19.09.2020
		            //visible: false,//TODO-3448 Ancuta 13.10.2020 
		            //--
		            type: 'datetime',
		            opposite:true, // show time on top
		            min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					labels: {
						enabled: false,//TODO-3448 Ancuta 13.10.2020 
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
		                grouping: true,
		                cursor: 'pointer',
	                	dataLabels: {
							enabled:false,
					        align: 'top',
					        verticalAlign: 'top',
							formatter: function() {
								if(this.y > 0)
									return this.series.name;
							}
						},
	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#custom_event_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
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
		                            	if(this.options.is_cf == '1'){
		                            		
		                            		$( "#cfinfo_modal" )
		                            		.data('recid', this.options.entry_id)
		                            		.dialog( "open" );
		                            		
		                            	} else{
			                            	 $( "#custom_event_modal" )
			                            	 .data('recid', this.options.entry_id)
		                                     .dialog( "open" );
		                            	}
		                            	
		                            }
		                        }
		                    },
							dataLabels: {
								useHTML: true,
								enabled:true,
						        align: 'left',
						        verticalAlign: 'middle',
								formatter: function() {
										return this.point.Htmllabel;
									}
						        } 
		                },
		         },
		         legend: {
		            enabled: false,
				    layout: 'vertical',
			        align: 'left',
			        verticalAlign: 'middle',
			        itemMarginTop: 5,
		         },
		         tooltip: {
		             formatter: function () {
		            	 
		                if(this.series.type == 'columnrange') 
		                {
		                //ISPC-2661 pct.13
		            	 /*return '<b>' + this.series.name + '</b><br/>'+ this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
	                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;*/
		            	 if(this.point.uncertainend == 0)
		            	 {		            		 
		            		 return '<b>' + this.series.name + '</b><br/>'+ this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;		            		 
		            	 }
		            	 else
		            	{
		            		 return '<b>' + this.series.name + '</b><br/>'+ this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
		            	}
		                }
		                else
		                {
		                	return false;
		                }
		            	//--
		             }
		         },
		         credits: {
				        enabled: false
				    },
		         series: json_data.series
		        	 /*[{
		        	 xAxis: 0,
		        	 type: 'columnrange',
		        	 data: json_data.series
		         	},
		         	{
		         		xAxis: 1,
		         		type: 'scatter',
			        	data: json_data.seriesi
		         	}]*/
				
			},  function (chart) {
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		//--
		
		
		$.make_organic_entries_exits_bilancing_oe_chart  = function( json_data )
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
			
			var chart = Highcharts.chart('organic_entries_exits_bilancing_oe_chart', {
				
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
					//ISPC-2661 Carmen
					categories: json_data.categories, 
					/*labels: {
				        enabled: false
			        }*/
					//--
				},
				
				yAxis: {
					//ISPC-2661 pct.8 Carmen 19.09.2020
					//visible: false,//TODO-3448 Ancuta 13.10.2020 
					//--
					type: 'datetime',
					opposite:true, // show time on top
					min: json_data.min,
					max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					labels: {
						enabled: false,//TODO-3448 Ancuta 13.10.2020 
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
						grouping: true,
						
					},
					series: {
						//cursor: 'pointer', //ISPC-2661 Carmen 12.10.2020
						dataLabels: {
							enabled:false,
							align: 'top',
							verticalAlign: 'top',
							formatter: function() {
								if(this.y > 0)
									return this.series.name;
							}
						},
						point: {
							//ISPC-2661 Carmen 12.10.2020
							/*events: {
								click: function (e) {
									
//									$( "#custom_event_modal" )
//									.data('recid', this.options.entry_id)
//									.dialog( "open" );
									
								}
							}*/
							//--
						}
					}
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'middle',
					itemMarginTop: 5,
				},
				tooltip: {
					formatter: function () {
						
						//ISPC-2661 pct.13
						/*return '<b>' + this.series.name + '</b><br/>'+ this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
	                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;*/
						if(this.point.uncertainend == 0)
						{		            		 
							return /*'<b>' + this.series.name + '</b><br/>'+ */this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
							' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;		            		 
						}
						else
						{
							return /*'<b>' + this.series.name + '</b><br/>'+ */this.point.label +' <br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
							' - ' + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
						}
						//--
					}
				},
				credits: {
					enabled: false
				},
				series: json_data.series
				
			},  function (chart) {
				chart.showLoading(translate('chart_loadingpleasewait')); 
				setTimeout(function () {
					chart.hideLoading();
				}, 3000)
			});
			
			return chart;
		};
		//--
		
		//ISPC-2512  Ancuta N3 09.06.2020
		$.make_time_chart = function( json_data )
		{
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
			
			var chart = Highcharts.chart('time_chart', {
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
					opposite:true, // show time on top
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
					useHTML: true,
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
						fontSize: '18px',
						zIndex: 1000	
					},
//					pointFormat: '<span class="custom_tooltip">{point.info}<br/>{point.x:%d.%m.%Y %H:%M}</span>',
					pointFormat: '{point.info}',
					
					formatter: function () {
						
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
						
						return false;                                        
					},
					
//					formatter: function() {
//						return 	''+'<p style="color:#9ab;font-family:Lucida Grande,sans-serif;font-size:11px;text-align:center;margin:0;padding:2px 7px;">' + 
//								'<strong style="color:#fff;font-weight:normal;font-size:16px;">' + this.y +' film' + (this.y == 1 ? '' : 's') + '</strong></p>';
//					},
				},
				plotOptions: {
					series: { },
					scatter: { }
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
				//ISPC-2661 pct.8 Carmen 19.09.2020
				if ($(window).width() <= 560) {
					//alert($(window).width());
				   chart.setSize(chart.width, 68);
				}
				else if ($(window).width() > 560 && $(window).width() <= 860) {
					//alert($(window).width());
				   chart.setSize(chart.width, 66);
				}
				else if ($(window).width() > 860 && $(window).width() <= 1015) {
					//alert($(window).width());
				   chart.setSize(chart.width, 65);
				}
				else if ($(window).width() > 1015 && $(window).width() < 1020) {
					//alert($(window).width());
				   chart.setSize(chart.width, 42);
				}
				else
				{
					//alert($(window).width());
					chart.setSize(chart.width, 40);
				}
				//--
			});
			
			return chart;
		};
		// --
		
		
		$.make_organic_entries_exits_chart = function( json_data )
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
	
		/*
		$.make_organic_entries_exits_bilancing_chart = function( json_data )
		{
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
			
			var chart = Highcharts.chart('organic_entries_exits_bilancing_chart', {
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};*/
		
		
		$.make_artificial_entires_exits_chart = function( json_data )
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
			
			var chart = Highcharts.chart('artificial_entires_exits_chart', {
				chart: {
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
	            	//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
	                crosshair: false,
	                type: 'datetime',
	                opposite:true, // show time on top
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
	                	enabled:  false,//TODO-3448 Ancuta 13.10.2020 
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
	                pointFormat: '{point.info}',
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
						},
						  point: {
			            	  events: {
		                            click: function (e) {
		                            	//ISPC-2508 Carmen 20.05.2020 new design
		                            	/* $( "#artificial_entries_exits_modal" )
		                            	 .data('recid', this.options.entry_id)
	                                     .dialog( "open" );*/
		                            	$( "#patient_charts_actions_modal" )
		                            	 .data('recid', this.options.entry_id)
	                                     .data('openfrom', 'charts')
	                                     .dialog( "open" );
		                            	//--
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		
		
		
		
		$.make_artificial_entires_exits_chart_old = function( json_data )
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
			
			var chart = Highcharts.chart('artificial_entires_exits_old_chart', {
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		
		
		
		
		$.make_symptomatology_chart = function( json_data )
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
			
			var chart = Highcharts.chart('symptomatology_chart', {
				
				chart: {
					type: 'scatter',
					marginLeft: 250,
					title: null,
					ignoreHiddenSeries: false,
					height: json_data.chart_height
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		        },
				
		        yAxis: json_data.yaxis,
				
				xAxis: {
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
	                min: json_data.min,
	                max: json_data.max,
	                //gridLineWidth: 1,
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
					pointFormat: '{point.info} <br/>{point.x:%d.%m.%Y %H:%M}<br />{point.usershortname}', //ISPC-2661 pct.2 Carmen 10.09.2020
					headerFormat: '',
					formatter: function () {
						
						if(!this.point.noTooltip) {
							return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
								point: this,
								series: this.series
							});
						}
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
								if(this.y > 0 && !this.point.noTooltip)
									return this.point.value;
								},
							style: {
			                    fontWeight: 'bold',
//			                    fillcolor: '#FFFFFF',
			                    color: '#ffffff',
			                    textShadow: false ,
			                    textOutline: false 
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		
		
		
		$.make_medication_actual_chart = function( json_data )
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
					//ISPC-2903,Elena,26.04.2021
					labels: {
						enabled: true,
						formatter: function () {
							return '<span class="mediact" title="' + this.value  + '">' + this.value + '</span>';
						},
						useHTML: true
					},
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
                                    //ISPC-2871,Elena,07.05.2021
                                    //prevents interaction for not approved if feature is available
	                            	if(this.options.approved !== undefined && this.options.approved == 0){
	                            	    return;
                                    }
									//ISPC-2871,Elena,30.03.2021
									//prevents interaction for future (tomorrow an so on)
									//console.log(this.options.documented_dosage_interaction.dosage_date);
									if(this.options.documented_dosage_interaction.dosage_date.length > 0){
										var nextMidnight = new Date();

										nextMidnight.setHours(24,0,0,0);//next midnight
										//ISPC-2903,Elena,26.04.2021
										var in12hours = new Date();

										var hours = in12hours.getHours();
										in12hours.setHours(hours + 12);
										//console.log('in 12 stunden', in12hours);

										var interactionDate = new Date(this.options.documented_dosage_interaction.dosage_date);
										//console.log('interaction', interactionDate);
										//ISPC-2903,Elena,26.04.2021
										if(interactionDate >= in12hours){

											return;
										}

									}


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
				$.addMediExplanationToChart(chart);
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000);
				//ISPC-2903,Elena,26.04.2021
				$('.mediact').qtip({
					style: {
						classes : 'tooltip_category',
						tip: true
					},
/*
					position: {
						my: 'center right',  // Position my top left...
						at: 'center left' // at the bottom right of...
					},*/
					show: {
						event: 'click mouseenter'
					},
					hide: {
						event: 'click mouseout'
					}
				});

		       
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
			var max = 0;//ISPC-2871,Elena,30.03.2021
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
					//ISPC-2903,Elena,26.04.2021
					labels: {
						enabled: true,
						formatter: function () {
							return '<span class="isivmedact" title="' + this.value  + '">' + this.value + '</span>';
						},
						useHTML: true
					},
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
	                            	//ISPC-2871,Elena,30.03.2021
									//prevents interaction for future (tomorrow an so on)
									//console.log(this.options.documented_dosage_interaction.dosage_date);
									if(this.options.documented_dosage_interaction.dosage_date.length > 0){
										var nextMidnight = new Date();

										nextMidnight.setHours(24,0,0,0);//next midnight
										//ISPC-2903,Elena,26.04.2021
										var in12hours = new Date();
										var hours = in12hours.getHours();
										in12hours.setHours(hours + 12);

										var interactionDate = new Date(this.options.documented_dosage_interaction.dosage_date);
										//ISPC-2903,Elena,26.04.2021
										if(interactionDate >= in12hours){

											return;
										}

									}

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
				$.addMediExplanationToChart(chart);
				chart.showLoading(translate('chart_loadingpleasewait'));
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000);
				//ISPC-2903,Elena,26.04.2021
				$('.isivmedact').qtip({
					style: {
						classes : 'tooltip_category',
						tip: true
					},
					/*
                                        position: {
                                            my: 'center right',  // Position my top left...
                                            at: 'center left' // at the bottom right of...
                                        },*/
					show: {
						event: 'click mouseenter'
					},
					hide: {
						event: 'click mouseout'
					}
				});


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
			    time: {
			        useUTC: false
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
			//ISPC-2871,Elena,30.03.2021
			var max = 0;
			if((json_data.plotlines !== undefined) && (json_data.plotlines.length>0)){
				max = json_data.plotlines.length-1;
			}
	    	
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
	    			max: max,//ISPC-2871,Elena,30.03.2021
	    			minorTickInterval: null,
	    			gridLineWidth: 1,
	    			showFirstLabel: true,
	    			showLastLabel: true,
	    			tickmarkPlacement: 'between',
	    			reversed: true,
	    			title: {
	    				text: null
	    			},//ISPC-2903,Elena,26.04.2021
					labels: {
						enabled: true,
						formatter: function () {
							return '<span class="isbedarfsmed" title="' + this.value  + '">' + this.value + '</span>';
						},
						useHTML: true
					},
	    			plotLines2: json_data.plotlines
	    			
	    		},
	    		xAxis: {
	    			//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
	    			crosshair: false,
	    			type: 'datetime',
	    			opposite:true, // show time on top
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
	    				stickyTracking: false,
	    				
	    				events: {
	    				}
	    		
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
									//ISPC-2871,Elena,30.03.2021
									//prevents interaction for future (tomorrow an so on)
									//console.log(this.options.documented_dosage_interaction.dosage_date);
									if(this.options.documented_dosage_interaction.dosage_date.length > 0){
										var nextMidnight = new Date();
										//ISPC-2903,Elena,26.04.2021
										var in12hours = new Date();
										in12hours.setHours(in12hours.getHours() + 12);


										nextMidnight.setHours(24,0,0,0);//next midnight
										var interactionDate = new Date(this.options.documented_dosage_interaction.dosage_date);
										//ISPC-2903,Elena,26.04.2021
										if(interactionDate >= in12hours){

											return;
										}

									}

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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
	    	});
			//ISPC-2903,Elena,26.04.2021
			$('.isbedarfsmed').qtip({
				style: {
					classes : 'tooltip_category',
					tip: true
				},
				/*
                                    position: {
                                        my: 'center right',  // Position my top left...
                                        at: 'center left' // at the bottom right of...
                                    },*/
				show: {
					event: 'click mouseenter'
				},
				hide: {
					event: 'click mouseout'
				}
			});
	    	
	    	return medchart;
	    };
	    
	    
	    
	    $.make_medication_iscrisis_chart = function( json_data )
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
	    	//ISPC-2871,Elena,30.03.2021
	    	var maxyaxis = 0;
	    	if(json_data.plotlines != undefined){
				maxyaxis = json_data.plotlines.length-1;
			}
	    	
	    	var medchart = Highcharts.chart('medication_iscrisis_chart', {
	    		
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
	    			max: (maxyaxis),//ISPC-2871,Elena,30.03.2021
	    			minorTickInterval: null,
	    			gridLineWidth: 1,
	    			showFirstLabel: true,
	    			showLastLabel: true,
	    			tickmarkPlacement: 'between',
	    			reversed: true,
	    			title: {
	    				text: null
	    			},
					labels: {
						enabled: true,
						formatter: function () {
							return '<span class="iscrisismed" title="' + this.value  + '">' + this.value + '</span>';
						},
						useHTML: true
					},
	    			plotLines2: json_data.plotlines
	    			
	    		},
	    		xAxis: {
	    			//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
	    			crosshair: false,
	    			type: 'datetime',
	    			opposite:true, // show time on top
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
	    				stickyTracking: false,
	    				
	    				events: {
	    				}
	    		
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
									//ISPC-2871,Elena,30.03.2021
									//prevents interaction for future (tomorrow and so on)
									//console.log(this.options.documented_dosage_interaction.dosage_date);
									if(this.options.documented_dosage_interaction.dosage_date.length > 0){
										var nextMidnight = new Date();
										//ISPC-2903,Elena,26.04.2021
										var in12hours = new Date();
										in12hours.setHours(in12hours.getHours() + 12);

										nextMidnight.setHours(24,0,0,0);//next midnight
										var interactionDate = new Date(this.options.documented_dosage_interaction.dosage_date);
										if(interactionDate >= in12hours){//ISPC-2903,Elena,26.04.2021

											return;
										}

									}


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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
	    	});
			//ISPC-2903,Elena,26.04.2021
			$('.iscrisismed').qtip({
				style: {
					classes : 'tooltip_category',
					tip: true
				},
				/*
                                    position: {
                                        my: 'center right',  // Position my top left...
                                        at: 'center left' // at the bottom right of...
                                    },*/
				show: {
					event: 'click mouseenter'
				},
				hide: {
					event: 'click mouseout'
				}
			});
	    	
	    	return medchart;
	    };
	    
	    
	    
	    $.make_medication_isschmerzpumpe_chart = function( json_data )
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
	    	
	    	var medchart = Highcharts.chart('medication_isschmerzpumpe_chart', {
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
					categories : json_data.categories,//ISPC-2871,Elena,30.03.2021

					//type: 'category',
					startOnTick: true,
					endOnTick: true,
			        labels: {
			            enabled: true //ISPC-2871,Elena,30.03.2021
			        }
			    },
				xAxis: {
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
					outside: true, //ISPC-2871,Elena,30.03.2021
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
	    				if(!this.point.noTooltip) {
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
	    					click: function (e) {
//	    						alert('linee');
	    					}
	    					
	    				}
	    		
	    			},
	    			
	    			series: {
	    				//stickyTracking: false,

	    				dataLabels: {
	    					useHTML: true,
	    					enabled:true,
	    					align: 'left',
	    					verticalAlign: 'middle',
	    					formatter: function() {
	    						return this.point.label;
	    					}
	    				} ,
	    				
	    				point: {
	    					events: {
	    						click: function (e) {
	    						    //ISPC-2871,Elena,30.03.2021
                                    //prevents uncaught JS error if documented_dosage_interaction or documented_dosage_interaction.entry_id is undefined
//console.log('opts1',this.options);
	    						    if(this.options.documented_dosage_interaction === undefined){
	    						    	return;
                                    }
	    							$( "#medication_dosage_interaction_modal" )
	    							.data('drugplan_id', 0 )
	    							.data('cocktail_id', this.options.documented_dosage_interaction.cocktail_id )
	    							
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
	    			},
	    			
	    			
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
	    	});
	    	
	    	return medchart;
	    };

	/**
	 *
	 * @param json_data
	 * @returns {*}
	 * ISPC-2871,Elena,30.03.2021
	 */
	$.make_medication_ispumpe_chart = function( json_data )
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

	    	var medchart = Highcharts.chart('medication_ispumpe_chart', {
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
					categories : json_data.categories,//ISPC-2871,Elena,30.03.2021

					//type: 'category',
					startOnTick: true,
					endOnTick: true,
			        labels: {
			            enabled: true //ISPC-2871,Elena,30.03.2021
			        }
			    },
				xAxis: {
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
					outside: true, //ISPC-2871,Elena,30.03.2021
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
	    				if(!this.point.noTooltip) {
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
	    					click: function (e) {
//	    						alert('linee');
	    					}

	    				}

	    			},

	    			series: {
	    				//stickyTracking: false,

	    				dataLabels: {
	    					useHTML: true,
	    					enabled:true,
	    					align: 'left',
	    					verticalAlign: 'middle',
	    					formatter: function() {
	    						return this.point.label;
	    					}
	    				} ,
	    				
	    				point: {
	    					events: {
	    						click: function (e) {
	    						    //ISPC-2871,Elena,30.03.2021
                                    //prevents uncaught JS error if documented_dosage_interaction or documented_dosage_interaction.entry_id is undefined
								  	//console.log('pumpe2', this.options);
	    						    if(this.options.documented_dosage_interaction === undefined){
	    						    	return;
                                    }
	    							$( "#medication_dosage_interaction_modal" )
	    							.data('drugplan_id', 0 )
	    							.data('cocktail_id', this.options.cocktail_id )
									.data('pumpe_id', this.options.pumpe_id ) //ISPC-2871,Elena,30.03.2021
	    							
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
	    			},
	    			
	    			
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
	    	});
	    	
	    	return medchart;
	    };
	    
	    
		$.make_awake_sleep_status_chart = function( json_data )
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
		        	//ISPC-2661 Carmen
		        	categories: json_data.categories, 
			        labels: {
				        enabled: true,
				        formatter: function () {
				            return translate(this.value);
				        }
			        }
		        	//--
		         },
		         
		         yAxis: {
		        	//ISPC-2661 pct.8 Carmen 19.09.2020
		            //visible: false,//TODO-3448 Ancuta 13.10.2020 
		            //--
		            type: 'datetime',
		            opposite:true, // show time on top
		            min: json_data.min,
	                max: json_data.max,
					startOnTick: false,
					endOnTick: false,
					tickInterval:json_data.XtickInterval,
					labels: {
						enabled: false,//TODO-3448 Ancuta 13.10.2020 
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
		                 grouping: true,
		             },
					series: {
	                    cursor: 'pointer',
	                	dataLabels: {
							enabled:false,
					        align: 'top',
					        verticalAlign: 'top',
							formatter: function() {
								if(this.y > 0)
									return this.series.name;
							}
						},
	                    point: {
	                        events: {
	                            click: function (e) {
	                            	
	                            	 $( "#awake_sleeping_modal" )
	                            	 .data('recid', this.options.entry_id)
                                     .dialog( "open" );
	                            	
	                            }
	                        }
	                    }
	                }
		         },
		         legend: {
		             enabled: false,//ISPC-2661 Carmen
				    layout: 'vertical',
			        align: 'left',
			        verticalAlign: 'middle',
			        itemMarginTop: 5,
			        //ISPC-2661 pct.6 Carmen 10.09.2020
			        labelFormatter: function () {
			            return translate(this.name);
			        }
		         //--
		         },
		         tooltip: {
		             formatter: function () {

		            	//ISPC-2661 pct.13 
		                 /*return '<b>' + translate(this.series.name) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname;*/
		            	
		            	 if(this.point.uncertainend == 0)
		            	 {
		            		 return '<b>' + translate(this.series.name) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.high) + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
		            	 }
		            	 else
		            	{
		            		 return '<b>' + translate(this.series.name) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M', this.point.low) +
		                     ' - ' + '<br/>'+this.point.usershortname; //ISPC-2661 pct.6 Carmen 10.09.2020
		            	}
		            	//--
		             }
		         },
		         credits: {
				        enabled: false
				    },
		         series: json_data.series
				
			},  function (chart) {
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		
		//ISPC-2516 Carmen 15.07.2020
		$.make_symptomatologyII_chart = function( json_data )
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
			
			var chart = Highcharts.chart('symptomatologyII_chart', {
				
				chart: {
					type: 'scatter',
					marginLeft: 250,
					title: null,
					ignoreHiddenSeries: false,
					height: json_data.chart_height
				},
				
				title: json_data.title,
				
				exporting: {
		             enabled: false
		        },
				
		        yAxis: json_data.yaxis,
				
				xAxis: {
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
	                min: json_data.min,
	                max: json_data.max,
	                //gridLineWidth: 1,
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
					pointFormat: '{point.info} <br/>{point.x:%d.%m.%Y %H:%M}<br />{point.usershortname}', //ISPC-2661 pct.2 Carmen 10.09.2020
					headerFormat: '',
					formatter: function () {
						
						if(!this.point.noTooltip) {
							return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
								point: this,
								series: this.series
							});
						}
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
								if(this.y > 0 && !this.point.noTooltip)
									return this.point.value;
								},
							style: {
			                    fontWeight: 'bold',
//			                    fillcolor: '#FFFFFF',
			                    color: '#ffffff',
			                    textShadow: false ,
			                    textOutline: false 
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
		//--
		
		$('#cfinfo_modal').dialog({
		    autoOpen: false,
		    modal: true,
		    width: 620,
		   	height: 400,
		   	title:  translate('cfinfo_modal'),
		   	dialogClass: "cfinfo_modal",
		   	
		   	open: function(){
		   		
			   	 jQuery('.ui-widget-overlay').on('click', function () {
					 $(this).dialog('close');
		            });
			   	 
		   		$('#loading_sm', this).show();
		   		if($(this).data('recid'))
		   		{
		   			var url = 'patientevents/events?action=show_form&form=contact_form&recid='+$(this).data('recid');
//		   			if($(this).parent().find('.delbutton').is(":hidden"))
//					{
//		   				$(this).parent().find('.delbutton').show();
//					}
		   		}
		   		
		   		$.ajax({
					type: 'POST',
					url: url,

					success:function(data){
					
						$('#cfinfo_modal').html(data);
				   		
					},
					error:function(){
						
					}
				});
		   	},
		    buttons:[
 

			{
				click: function(){
			
					$('#cfinfo_modal').dialog('close');
				},
				text: translate('cancel'),
			}
		    ]
		});
		
		/*//ISPC-2508 Carmen 19.05.2020 new design
		$('#patient_charts_actions_modal').dialog({
			autoOpen: false,
			modal: true,
			maxWidth: 400,
			maxHeight: 400,
			width: 300,
			height: 350,
			
			close: function() {
				$('#patient_charts_actions_modal').html('');
			},
			open: function() {

				var entrydata_ID = $(this).data('recid');
				var url = appbase + 'ajax/modalactions?recid='+entrydata_ID;

				$.get(url, function(result) {
					var newFieldset =  $('#patient_charts_actions_modal').append(result);
					
			});
				
				 jQuery('.ui-widget-overlay').on('click', function () {
					 $('#patient_charts_actions_modal').dialog('close');
		            });
				
				 $(document).off('click', '.artificial_action').on('click', '.artificial_action', function(){

						if($(this).val() != 'edit' && $(this).val() != 'delete')
						{
							$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).dialog('open');
						}
			        	//else if($(this).val() == 'delete')
					 	if($(this).val() == 'delete')
			        	{
		        			$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).dialog('open');
		    	        	var buttons = $('#artificial_entries_exits_modal').dialog('option', 'buttons');
		    	        	$('#artificial_entries_exits_modal').dialog('close');
		    	        	buttons[0].click.apply($('#artificial_entries_exits_modal'));

			        	}
			        	else
			        	{
			        		$('#artificial_entries_exits_modal').data('recid', entrydata_ID).data('action', $(this).val()).dialog('open');
			        		
			        	}
			        	
			        	$('#patient_charts_actions_modal').dialog('close');
			    	});
			},

			buttons: [{
					text: translate('cancel'),
					click: function() {
						$(this).dialog("close");
					}
				},
			]
		});
		//--
*/
		
	//ISPC-2697, elena, 23.11.2020
	$.make_beatmung_chart = function( json_data )
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

		var chart = Highcharts.chart('beatmung_chart', {

			chart: {
				//type: 'line',
				type: 'columnrange',
				inverted: true,
				marginLeft: 250,
				title: null,
				ignoreHiddenSeries: false,
				height: json_data.chart_height
			},

			title: json_data.title,

			exporting: {
				enabled: false
			},

			xAxis: {
				title: false,
				labels: {
					enabled: true
				},
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
				plotLines2: json_data.plotlines

			},
			yAxis: {
				type: 'datetime',
				title: {
					text: null
				},

				opposite:true, // show time on top
				min: json_data.min,
				max: json_data.max,
				//gridLineWidth: 1,
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
				useHTML: true,//ISPC-2904,Elena,30.04.2021
				hideDelay: 10,
				outside: true, //TODO-3936,Elena,10.03.2021
				className: 'beatmung_tooltip',
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
				backgroundColor: '#FCFFC5',
				style: {
					fontSize: '12px'//,
					//backgroundColor: '#FFFFAE'
				},
				pointFormat: '{point.info}',
				headerFormat: '',
				formatter: function () {

					if(!this.point.noTooltip) {
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
					}
					return false;
				}
			},
			plotOptions: {
				columnrange: {
					grouping: false,
					inverted :true
				},
				series: {
					stickyTracking: false,
					dataLabels: {
						enabled:true,
						y:0,
						formatter: function() {
							if(this.y > 0 && !this.point.noTooltip)
								return this.point.value;
						},
						style: {
							fontWeight: 'bold',
//			                    fillcolor: '#FFFFFF',
							color: '#ffffff',
							textShadow: false ,
							textOutline: false
						}
					},
					point: { //ISPC-2904,Elena,30.04.2021
						events: {
							click: function (e) {
								console.log('click beatmung');
								console.log('options', this.options);
								console.log(JSON.parse(this.options.saved_data));
								$( "#patient_beatmung_modal" )
									.data('recid', this.options.entry_id)
									.data('saved_data', this.options.saved_data)
									.dialog( "open" )

							}
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
			chart.showLoading(translate('chart_loadingpleasewait'));
			setTimeout(function () {
				chart.hideLoading();
			}, 3000)
		});

		return chart;
	};

	//TODO-4163
	//IM-153 Nico
	$.make_pcoc_phase_chart = function( json_data )
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

		var chart = Highcharts.chart('pcoc_phase_chart', {

			chart: {
				type: 'scatter',
				marginLeft: 250,
				title: null,
				ignoreHiddenSeries: false,
				height: json_data.chart_height
			},

			title: json_data.title,

			exporting: {
				enabled: false
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
				plotLines: json_data.plotlines

			},
			xAxis: {
				type: 'datetime',
				opposite:true, // show time on top
				min: json_data.min,
				max: json_data.max,
				//gridLineWidth: 1,
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
				pointFormat: '{point.info}',
				headerFormat: '',
				formatter: function () {

					if(!this.point.noTooltip) {
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
					}
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
							if(this.y > 0 && !this.point.noTooltip)
								return this.point.value;
						},
						style: {
							fontWeight: 'bold',
//			                    fillcolor: '#FFFFFF',
							color: '#ffffff',
							textShadow: false ,
							textOutline: false
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
			chart.showLoading(translate('chart_loadingpleasewait'));
			setTimeout(function () {
				chart.hideLoading();
			}, 3000)
		});

		return chart;
	};

	//ISPC-2836,Elena,23.02.2021
	//ISPC-2697, elena, 23.11.2020
	/*
	$.make_beatmung_chart = function( json_data )
	{
		var today = new Date()
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

		var chart = Highcharts.chart('beatmung_chart', {

			chart: {
				//type: 'line',
				marginLeft: 250,
				title: null,
				ignoreHiddenSeries: false,
				height: json_data.chart_height
			},

			title: json_data.title,

			exporting: {
				enabled: false
			},

			yAxis: {
				title: false,
				labels: {
					enabled: true
				},
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
				plotLines2: json_data.plotlines

			},
			xAxis: {
				type: 'datetime',
				opposite:true, // show time on top
				min: json_data.min,
				max: json_data.max,
				//gridLineWidth: 1,
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
				backgroundColor: '#FCFFC5',
				style: {
					fontSize: '12px'//,
					//backgroundColor: '#FFFFAE'
				},
				pointFormat: '{point.info}',
				headerFormat: '',
				formatter: function () {

					if(!this.point.noTooltip) {
						return this.point.tooltipFormatter(this.series.tooltipOptions.pointFormat, {
							point: this,
							series: this.series
						});
					}
					return false;
				}
			},
			plotOptions: {
				series: {
					stickyTracking: false,
					dataLabels: {
						enabled:true,
						y:0,
						formatter: function() {
							if(this.y > 0 && !this.point.noTooltip)
								return this.point.value;
						},
						style: {
							fontWeight: 'bold',
//			                    fillcolor: '#FFFFFF',
							color: '#ffffff',
							textShadow: false ,
							textOutline: false
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
			chart.showLoading(translate('chart_loadingpleasewait'));
			setTimeout(function () {
				chart.hideLoading();
			}, 3000)
		});

		return chart;
	};

	 */



		//ISPC-2683 Carmen 16.10.2020
		$.make_vigilance_awareness_events_chart = function( json_data )
		{
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
			
			var chart = Highcharts.chart('vigilance_awareness_events_chart', {
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
					//ISPC-2661 pct.8 Carmen 19.09.2020
	            	//visible: false,//TODO-3448 Ancuta 13.10.2020 
	            	//--
					type: 'datetime',
					opposite:true, // show time on top
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
					pointFormat: '{point.info}<br/>{point.x:%d.%m.%Y %H:%M}<br />{point.usershortname}',
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
	                            	
	                            	 $( "#vigilance_awareness_modal" )
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
	        	chart.showLoading(translate('chart_loadingpleasewait')); 
		        setTimeout(function () {
		        	chart.hideLoading();
		        }, 3000)
			});
			
			return chart;
		};
	//--
});


//ISPC-2517 Carmen 05.06.2020
$( window ).on("load", function() {
    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');

    if(hash != ''){
    	
       // Clear the hash in the URL
       // location.hash = '';   // delete front "//" if you want to change the address bar
    	 setTimeout(function(){$('html, body').animate({ scrollTop: $('#'+hash).offset().top}, 1000); }, 15000);
       // $('html, body').animate({ scrollTop: $('#'+hash).offset().top}, 1000);

       }
   });
//--


function load_navigation(pid,chart_view_type,start,end){
	
	if(chart_view_type === undefined) {
	      chart_view_type = 'week';
	   }
	
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

function load_charts(pid,start,end, chart_view_type, chart_blocks ){ //ISPC-2661 pct.10 Carmen 17.09.2020  //ISPC-2841 Lore 29.03.2021 - chart_blocks

	//ISPC-2841 Ancuta 
	if(chart_blocks === undefined){
		var chart_blocks = window.blocks;
	}
	//--

	//ISPC-2512  Ancuta N3 09.06.2020	
	// time chart
	
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=time&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
		
			$('#time_chart').show();
            var chart = $.make_time_chart( data );
            chart.xAxis[0].setExtremes( data.min, data.max,true, false);
	 
    });

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_actual")){
        var block = "medication_actual";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#medication_actual_chart').show();
                var chart = $.make_medication_actual_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else {
    			$('#medication_actual_chart').hide();
    		}*/
            //--
        });
    }


	//ISPC-2871,Elena,30.03.2021
	if(chart_blocks.includes("medication_actual")){
	var block = "medication_isivmed";
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) {

		$('#medication_isivmed_chart').show();
            var chart = $.make_medication_isivmed_chart( data );
            chart.xAxis[0].setExtremes( data.min, data.max,true, false);

    });
    }

	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_isbedarfs")){
    	var block = "medication_isbedarfs";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#medication_isbedarfs_chart').show();
    			var chart = $.make_medication_isbedarfs_chart( data );
    			chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else {
    			$('#medication_isbedarfs_chart').hide();
    		}*/
    		//--
    	});	
    }
	

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_iscrisis")){
    	var block = "medication_iscrisis";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#medication_iscrisis_chart').show();
    			var chart = $.make_medication_iscrisis_chart( data );
    			chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else {
    			$('#medication_iscrisis_chart').hide();
    		}*/
    		//--
    	});
    }
	

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("medication_isschmerzpumpe")){
    	var block = "medication_isschmerzpumpe";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#medication_isschmerzpumpe_chart').show();
    			var chart = $.make_medication_isschmerzpumpe_chart( data );
    			chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else {
    			$('#medication_isschmerzpumpe_chart').hide();
    		}*/
    		//--
    	});	
    }
    
    
     if(chart_blocks.includes("medication_ispumpe")){
	var block = "medication_ispumpe";
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
		//ISPC-2661 pct.11 Carmen 14.09.2020
		//if(data.hasData == 1){
			$('#medication_ispumpe_chart').show();
			var chart = $.make_medication_ispumpe_chart( data );
			chart.xAxis[0].setExtremes( data.min, data.max,true, false);
		/*} else {
			$('#medication_isschmerzpumpe_chart').hide();
		}*/
		//--
	});
	}
	
	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("vital_signs")){
    	// vital signs chart
     	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=vital_signs&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
     		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#vital_signs_chart').show();
                var chart = $.make_vital_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
     		/*} else {
     			$('#vital_signs_chart').hide();
     		}*/
        });
    }


	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("awake_sleep_status")){
        var block = "awake_sleep_status";
     	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
     		//ISPC-2661 pct.11 Carmen 14.09.2020
     		//if(data.hasData == 1){
     			$('#awake_sleep_status_chart').show();
                 var chart = $.make_awake_sleep_status_chart( data );
                 chart.xAxis[0].setExtremes( data.min, data.max,false, false); // !!!!
     		/*} else{
     			$('#awake_sleep_status_chart').hide();
     		}*/
            //--
         });
    }

	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("symptomatology")){
        var block = "symptomatology";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#symptomatology_chart').show();
                var chart = $.make_symptomatology_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
     			$('#symptomatology_chart').hide();
     		}*/    
        });
    }

    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("organic_entries_exits")){
        var block = "organic_entries_exits";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#organic_entries_exits_chart').show();
                var chart = $.make_organic_entries_exits_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
    			$('#organic_entries_exits_chart').hide();
    		}*/
            //--
        });
    }

    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("artificial_entires_exits")){
        var block = "artificial_entires_exits";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#artificial_entires_exits_chart').show();
                var chart = $.make_artificial_entires_exits_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
                /*} else{
    			$('#artificial_entires_exits_chart').hide();
    		}*/
            //--
        });
    }

   /* var block = "organic_entries_exits_bilancing";
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
		//ISPC-2661 pct.11 Carmen 14.09.2020
		//if(data.hasData == 1){
			$('#organic_entries_exits_bilancing_chart').show();
            var chart = $.make_organic_entries_exits_bilancing_chart( data );
            chart.xAxis[0].setExtremes( data.min, data.max,true, false);
		//} else{
		//	$('#organic_entries_exits_bilancing_chart').hide();
		//}
        //--
    });*/
	
//	var block = "artificial_entires_exits_old";
//	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
//		var chart = $.make_artificial_entires_exits_chart_old( data );
//		chart.xAxis[0].setExtremes( data.min, data.max,true, false);
//	});
	
	
	// positioning chart //DEPRECATED
/*	
	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=positioning_individual', function (data) {
            var chart = $.make_positioning_individual_chart ( data );
            chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    });
*/
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("positioning")){
    	// positioning chart
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=positioning&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#positioning_chart').show();
    			var chart = $.make_positioning_chart( data );
    			chart.xAxis[0].setExtremes( data.min, data.max,false, false); // !!!!
    		/*} else{
    			$('#positioning_chart').hide();
    		}*/
    	});
    }


	
	// custom_events chart
	//ISPC-2661 pct.13 Carmen 11.09.2020
	/*$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=custom_events_individual', function (data) {
		if(data.hasData == 1){
			$('#custom_events_chart').show();
            var chart = $.make_custom_events_individual_chart( data );
            chart.xAxis[0].setExtremes( data.min, data.max,true, false);
		} else{
			$('#custom_events_chart').hide();
		}
    });*/
	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("custom_events")){
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=custom_events&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#custom_events_chart').show();
                var chart = $.make_custom_events_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,false, false);
    		/*} else{
    			$('#custom_events_chart').hide();
    		}*/
            //--
        });	
    }

	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("organic_entries_exits_bilancing_oe")){
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type=organic_entries_exits_bilancing_oe&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    		$('#organic_entries_exits_bilancing_oe_chart').show();
    			var chart = $.make_organic_entries_exits_bilancing_oe_chart( data );
    			chart.xAxis[0].setExtremes( data.min, data.max,false, false);
    		/*} else{
    			$('#organic_entries_exits_bilancing_oe_chart').hide();
    		}*/
    		//--
    	});
    	//--
    }


	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("suckoff_events")){
        var block = "suckoff_events";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#suckoff_events_chart').show();
                var chart = $.make_suckoff_events_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
    			$('#suckoff_events_chart').hide();
    		}*/
            //--
        });
    }

    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("ventilation_info")){
        var block = "ventilation_info";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#ventilation_info_chart').show();
                var chart = $.make_ventilation_info_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
    			$('#ventilation_info_chart').hide();
    		}*/
            //--
        });
    }

	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("symptomatologyII")){
    	//ISPC-2516 Carmen 15.07.2020
    	var block = "symptomatologyII";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#symptomatologyII_chart').show();
                var chart = $.make_symptomatologyII_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
     			$('#symptomatologyII_chart').hide();
     		} */
            //--
        });
    	//--
    }

	
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("vigilance_awareness_events")){
    	//ISPC-2683 Carmen 16.10.2020
    	var block = "vigilance_awareness_events";
    	$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block+'&chart_interval='+chart_view_type, function (data) { //ISPC-2661 pct.10 Carmen 17.09.2020
    		//ISPC-2661 pct.11 Carmen 14.09.2020
    		//if(data.hasData == 1){
    			$('#vigilance_awareness_events_chart').show();
                var chart = $.make_vigilance_awareness_events_chart( data );
                chart.xAxis[0].setExtremes( data.min, data.max,true, false);
    		/*} else{
    			$('#suckoff_events_chart').hide();
    		}*/
            //--
        });
    }
	//TODO-4163
	if(chart_blocks.includes("pcoc_phase")) {
		var block = "pcoc_phase";
		$.getJSON(appbase + 'charts/chartdata?id=' + pid + '&start=' + start + '&end=' + end + '&chart_type=' + block, function (data) {
			if (data.hasData == 1) {
				$('#pcoc_phase_chart').show();
				var chart = $.make_pcoc_phase_chart(data);
				chart.xAxis[0].setExtremes(data.min, data.max, true, false);
			} else {
				$('#pcoc_phase_chart').hide();
			}
		});
	}
    
	//ISPC-2841 Lore 29.03.2021
    if(chart_blocks.includes("beatmung")){
    	//ISPC-2697, elena, 20.11.2020
		var block = "beatmung";
		$.getJSON(appbase+'charts/chartdata?id='+pid+'&start='+start+'&end='+end+'&chart_type='+block, function (data) {
			if(data.hasData == 1){
				$('#beatmung_chart').show();
				var chart = $.make_beatmung_chart( data );
			//chart.xAxis[0].setExtremes( data.min, data.max,true, false);
			chart.xAxis[0].setExtremes( data.min, data.max,false, false);
			console.log(' chart beatmung created');
				$('.beatmung_tooltip').css({position: 'fixed'});
			} else{
				$('#beatmung_chart').hide();
			}
		});
    }

    
	
}


function loadPage(){
	var chart_view_type = $('#selected_chart_type').val();
	start = $('#interval_start_date').val();
	end = $('#interval_end_date').val();

	// load navigation
	load_navigation(pid,chart_view_type,start,end)
		  
	// load charts
	load_charts(pid,start,end, chart_view_type) //ISPC-2661 pct.10 Carmen 17.09.2020
	
	
}

function goToInterval(view_type,date){
	// load navigation
	load_navigation(pid,chart_view_type,start,end)
		  
	// load charts
	load_charts(pid,start,end)
}


function goToInterval_old(view_type,date){
}

//ISPC-2661 pct.10 Carmen 15.09.2020
function zoomdesc(_this, chart_type){
var backcol = window.backcol;
 
var prev_chart_type = $(_this).parent().parent().find('div.default').find("#"+chart_type).prev().attr('id');

	$("#"+chart_type).css('background-color', backcol);
	$("#"+prev_chart_type).css('background-color', '#0000A0');
	$('#selected_chart_type').val(prev_chart_type);
	if(prev_chart_type == 'week')
	{
		$(".zoomasc").hide();
		$(".zoomdesc").show();
	}
	else if(prev_chart_type != 'week' && prev_chart_type != '4hours')
	{
		$(".zoomasc").show();
		$(".zoomdesc").show();
	}
	else if(prev_chart_type == '4hours')
	{
		$(".zoomasc").show();
		$(".zoomdesc").hide();
	}
	

	showPicker(prev_chart_type, 'zoom');
	/*$(".zoomdesc").toggle();
	if($(".default").is(":visible"))
	{
		$(".default").hide();
	}
	else if($(".zoomdesc").is(":hidden"))
	{
		$(".default").show();
	}
	if($(".zoomasc").is(":visible"))
	{
		$(".zoomasc").hide();
	}*/
}

function zoomasc(_this, chart_type){

	var backcol = window.backcol;
	
	var next_chart_type = $(_this).parent().parent().find('div.default').find("#"+chart_type).next().attr('id');
	
	$("#"+chart_type).css('background-color', backcol);
	$("#"+next_chart_type).css('background-color', '#0000A0');
	$('#selected_chart_type').val(next_chart_type);
	
	if(next_chart_type == 'week')
	{
		$(".zoomasc").hide();
		$(".zoomdesc").show();
	}
	else if(next_chart_type != 'week' && next_chart_type != '4hours')
	{
		$(".zoomasc").show();
		$(".zoomdesc").show();
	}
	else if(next_chart_type == '4hours')
	{
		$(".zoomasc").show();
		$(".zoomdesc").hide();
	}
	

	showPicker(next_chart_type, 'zoom');
	/*$(".zoomasc").toggle();
	if($(".default").is(":visible"))
	{
		$(".default").hide();
	}
	else if($(".zoomasc").is(":hidden"))
	{
		$(".default").show();
	}
	if($(".zoomdesc").is(":visible"))
	{
		$(".zoomdesc").hide();
	}	*/
}
//--


	   		