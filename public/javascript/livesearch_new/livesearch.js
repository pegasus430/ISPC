jQuery.fn.liveSearch = function (conf) {
	var config = jQuery.extend({
		url:		'/search-results.php?q=',
		id:		'jquery-live-search',
		duration:	400,
		typeDelay:	200,
		loadingClass:	'loading',
		onSlideUp:	function () {}, //onSlideUp callback maybe is needed
		returnRowId:	function () {}, //callback to append rowid of a multi line input
		typeValue:	function () {}, //callback to append rowid of a multi line input
		allowSameValue: false,
		uptadePosition:	false, //(bool)
		aditionalWidth:	'130', //by default ls is inheriting input size, use this to sum with the input width (px)
		customStart: false,
		DOMelement:	false,
		noResultsDelay: '900', //hide time in miliseconds (ms)
		livesearch_class : null,
			
	}, conf);

	var liveSearch    = jQuery('#' + config.id);
	var request;
	// Create live-search if it doesn't exist
	if (!liveSearch.length) {
		liveSearch = jQuery('<div id="' + config.id + '"></div>')
		.appendTo(document.body)
		.hide()
		.slideUp(0);
		
		//claudiu added
		if (typeof config.livesearch_class === 'string') {
			liveSearch.addClass(config.livesearch_class);
		}
		
		//claudiu added
		liveSearch.click(function(event){
			var clicked = jQuery(event.target);
			
			
//			 Close live-search when clicked inside on a row
			if(clicked.is('#' + config.id + ' table tbody tr') || clicked.is('#' + config.id + ' table tbody tr td')){
				liveSearch.slideUp(config.duration, function () {
					config.onSlideUp(clicked);
				});
			} else if ( ! (clicked.is('#' + config.id) || clicked.parents('#' + config.id).length || clicked.is('input'))) {
				liveSearch.slideUp(config.duration, function () {
					config.onSlideUp();
				});
				//clear livesearch when user clicks outside... Performance...
				liveSearch.html('');
			} else {
				
				event.preventDefault();
				event.stopPropagation();
				return false;
				//console.log('!! problem !!', clicked);
			}
		
		});
		
		//claudiu removed
//		// Close live-search when clicking outside it or in it on a row
//		jQuery(document.body).click(function(event) {
//			var clicked = jQuery(event.target);
//
//			//			 Close live-search when clicked inside on a row
//			if(clicked.is('#' + config.id+' table tbody tr') || clicked.is('#' + config.id+' table tbody tr td')){
//				liveSearch.slideUp(config.duration, function () {
//					config.onSlideUp();
//				});
//			}
//
//
//
//			if (!(clicked.is('#' + config.id) || clicked.parents('#' + config.id).length || clicked.is('input'))) {
//				liveSearch.slideUp(config.duration, function () {
//					config.onSlideUp();
//				});
//				//clear livesearch when user clicks outside... Performance...
//				liveSearch.html('');
//			}
//		});
	}

	return this.each(function () {
		var input                            = jQuery(this).attr('autocomplete', 'off');
		var custom_start_element = jQuery(config.DOMelement);
		var liveSearchPaddingBorderHoriz    = parseInt(liveSearch.css('paddingLeft'), 10) + parseInt(liveSearch.css('paddingRight'), 10) + parseInt(liveSearch.css('borderLeftWidth'), 10) + parseInt(liveSearch.css('borderRightWidth'), 10);

		// Re calculates live search's position
		var repositionLiveSearch = function () {
			var tmpOffset    = input.offset();
			var customOffset = custom_start_element.offset();
			var inputDim = '';

			if(config.customStart){ //get top of element
				inputDim    = {
					left:		parseInt(customOffset.left),
					top:		parseInt(customOffset.top), //align with custom start element
					width:		input.outerWidth(),
					height:		input.outerHeight()
				};
			} else {
				inputDim    = {
					left:		tmpOffset.left,
					top:		tmpOffset.top,
					width:		input.outerWidth(),
					height:		input.outerHeight()
				};
			}



//			printObject(inputDim);
			if(config.customStart){
				inputDim.topPos        = inputDim.top;
			} else {
				inputDim.topPos        = inputDim.top + inputDim.height;
			}

			inputDim.totalWidth    = inputDim.width - liveSearchPaddingBorderHoriz;

			liveSearch.css({
				position:	'absolute',
				left:		inputDim.left + 'px',
				top:		inputDim.topPos + 'px',
				width:		parseInt(inputDim.totalWidth)+parseInt(config.aditionalWidth) + 'px'
			});
		};

		// Shows live-search for this input
		var showLiveSearch = function () {
			// Always reposition the live-search every time it is shown
			// in case user has resized browser-window or zoomed in or whatever
			repositionLiveSearch();

			// We need to bind a resize-event every time live search is shown
			// so it resizes based on the correct input element
			$(window).unbind('resize', repositionLiveSearch);
			$(window).bind('resize', repositionLiveSearch);
			
			//focus first tr but not the header-row
			$('#'+config.id+" table tbody tr:not(.header-row):first").addClass('focused');
			
			liveSearch.slideDown(config.duration);

			// Close livesearc if output has no results
			if($('#'+config.id+'_no_records').val() == '1'){
				setTimeout(function(){
					hideLiveSearch();
				}, config.noResultsDelay);
			}
		};

		// Hides live-search for this input
		//claudiu removed
//		var hideLiveSearch = function () {
		//claudiu added
		var hideLiveSearch = function (_this) {
			liveSearch.slideUp(config.duration, function () {
				//claudiu removed
//				config.onSlideUp();
				//claudiu added
				config.onSlideUp(_this);
			});
		};

		input
		// On focus, if the live-search is empty, perform an new search
		// If not, just slide it down. Only do this if there's something in the input
//		.focus(function () { //removed... ls will appear only on keyup
////			if (this.value !== '' && this.lastValue != this.value) {
////				// Perform a new search if there are no search results
////				if (liveSearch.html() == '') {
////					this.lastValue = '';
////					input.keyup();
////				}
////				// If there are search results show live search
////				else {
////					// HACK: In case search field changes width onfocus
////					setTimeout(showLiveSearch, 1);
////				}
////			}
//		})

		// Auto update live-search onkeyup

		.keyup(function (e) {
			if(e.keyCode == 13){  //press enter
				e.preventDefault();
			}
			// Don't update live-search if it's got the same value as last time
			//LE(27.10.2014): allow same value only if sameValue parameter is true
//			if (this.value != this.lastValue && this.lastValue != this.value) {
			if (this.value != this.lastValue || config.allowSameValue !== false) {
				input.addClass(config.loadingClass);

				var q = this.value;
				// Stop previous ajax-request
				if(request){
					request.abort();
				}
				if (this.timer) {
					clearTimeout(this.timer);
				}

				// Start a new ajax-request in X ms
				this.timer = setTimeout(function () {
					var r = '';
					var t = '';
						if(config.returnRowId(input)){
							q = encodeURIComponent(q);
							r = '&row='+config.returnRowId(input)
						} else {
							q = encodeURIComponent(q);
							r = '';
						}

						if(config.typeValue(input)){
							q = encodeURIComponent(q);
							t = '&type='+config.typeValue(input);
						} else {
							q = encodeURIComponent(q);
							t = '';
						}

						request = jQuery.get(config.url + q + r + t, function (data) {
						input.removeClass(config.loadingClass);

						// Show live-search if results and search-term aren't empty
						if (data.length && q.length) {
							liveSearch.html(data);
							showLiveSearch();
						}
						else {
							hideLiveSearch();
						}
					});
				}, config.typeDelay);

				this.lastValue = this.value;
			}

			if( e.keyCode == 40 ) { //press down
				if( $('#'+config.id+" table tbody").find('.focused' ).length < 1 ) {
					$('#'+config.id+" table tbody tr:first").addClass('focused');
				} else {
					if($('#'+config.id+" table tbody").find( '.focused' ).next().length < 1 ) {
						$('#'+config.id+" table tbody").find( '.focused' ).removeClass( 'focused' );
						$('#'+config.id+" table tbody tr:first").addClass('focused');
					} else {
						var itsNext = $('#'+config.id+" table tbody").find('.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
						if( itsNext.is( ':hidden' ) ) {
							$( this ).trigger( {
								type : 'keyup',
								keyCode : 40
							});
						} else {
							var activeItem = $('#'+config.id+" table tbody").find( '.focused' )[0];
							offsetTop = activeItem.offsetTop;
							var container = $('#'+config.id);

							upperBound = container.scrollTop();
							lowerBound = upperBound + 200 - 25;

							if (offsetTop < upperBound) {
								container.scrollTop(offsetTop);
							} else if (offsetTop > lowerBound) {
								container.scrollTop(offsetTop - 200 + 25);
							}
						}
					}
				}
				e.preventDefault();
				return false;
			}
			if( e.keyCode == 38 ) { //press up
				if( $('#'+config.id+" table tbody").find('.focused' ).length < 1 ) {
					$('#'+config.id+" table tbody tr:last").addClass('focused');
				} else {
					if( $('#'+config.id+" table tbody").find( '.focused' ).prev().length < 1 ) {
						$('#'+config.id+" table tbody").find( '.focused' ).removeClass( 'focused' );
						$('#'+config.id+" table tbody tr:last").addClass('focused');
					} else {
						var itsPrev = $('#'+config.id+" table tbody").find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
						if( itsPrev.is( ':hidden' ) ) {
							$( this ).trigger( {
								type : 'keyup',
								keyCode : 38
							});
						} else {
							var activeItem = $('#'+config.id+" table tbody").find( '.focused' )[0];
							offsetTop = activeItem.offsetTop;
							var container = $('#'+config.id);

							upperBound = container.scrollTop();
							lowerBound = upperBound + 200 - 25;
							if (offsetTop < upperBound) {
								container.scrollTop(offsetTop);
							} else if (offsetTop > lowerBound) {
								container.scrollTop(offsetTop - 200 + 25);
							}
						}
					}
				}

				e.preventDefault();
				return false;
			}

			if( e.keyCode == 13 ) { //press enter
				e.preventDefault();
				$('#'+config.id+" table tbody").find( '.focused' ).trigger('click');
				hideLiveSearch();
				return false;
			}

		})
//		.keypress(function(e){
//			if(e.keyCode == 13){  //press enter
//				e.preventDefault();
//			}
//		})
		//hide livesearch if input looses focus
		.blur(function(){
			// Cancel previous ajax request, even if request is done
			// clear timeout to prevent ls to be shown with empty results
			// remove class loading

			clearTimeout(this.timer);
//			abort request
			if(request){
				request.abort();
			}


			input.removeClass(config.loadingClass);

			//claudiu chaged.... body is still not enough for the onclick cause you have fixed width
			//fix for alowing user to click inside the ls and not hide(scrollbar)
			jQuery(document.body).click(function(event) {
				var clicked = jQuery(event.target);
				
				if ( ! clicked.parents('#'+config.id).length) {
					hideLiveSearch();
				}
//				if( ! (clicked.parents().is('#'+config.id))){
//					hideLiveSearch();
//				}
//
//				if(clicked.is('#'+config.id+'table tbody tr')) {
//					hideLiveSearch();
//				}
			});
		});
	});
};