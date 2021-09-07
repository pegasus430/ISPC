;
/**
 * patientcourse.mobile.js
 * @date 01.11.2018
 * @author @cla
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}



jQuery(document).ready(function ($) {
	
	
	/**
	 * the bar with print, filter, jump to top|bottom
	 */
	$("#patient\\.course\\.verlauf\\.filter\\.top, #patient\\.course\\.verlauf\\.filter\\.bottom")
	.on('click', '.btnFilter', function(event) {
		event.preventDefault();
		$(this)
		.toggleClass('active')
		.parents('.divPrintFiltersJump').find(".verlaufFilter").slideToggle();
		;
	    
	})
	.on('click', '.btnArrowdown', function(event) {

		event.preventDefault();
		if ($(this).parents('.divFiltersBottom').length){
			window.location.hash = "#patient.course.verlauf.filter.top";
		} else {
			window.location.hash = "#patient.course.verlauf.filter.bottom";
		}
	});
	
	
	filter_course_verlauf_course_type($('#patient\\.course\\.verlauf\\.filter\\.top') , true);
	
	
});
	
/**
 * this fn overwrites the original from patientcourse.js
 */
function checkboxurl(_this, isfilter)
{
	//top and bottom filter move as one
	$("div[id^='patient\\.course\\.verlauf\\.filter'] input[type='checkbox'][name='" +_this.name + "']").prop('checked', _this.checked);
	
	var _thisParent = $(_this).parents("div[id^='patient\\.course\\.verlauf\\.filter']");
	
	filter_course_verlauf_course_type(_thisParent);
	
	return;
	
}


function filter_course_verlauf_course_type(_thisObj , _onInit)
{
	//todo: _owned _shared
	if (typeof(_thisObj) != 'object') {
		return; //fail-safe
	}
	
	var _show_course_type = $(_thisObj).find("input[type='checkbox']:checked").map(function() {
	    return this.name;
    }).get() || 0;
	  
	
	if (_onInit === true && _show_course_type.length == 0) {
		return; // on init we don't hide anything if no filter is selected
	}
	
	
	var _hide_course_type = $(_thisObj).find("input[type='checkbox']:not(:checked)").map(function() {
		return this.name;
	}).get();
	
	var _show_wrong = $(_thisObj).find("input[type='checkbox'][name='wrong']:checked").length || 0;
	
	//hide "li"
	$(".selector_patient_course_list").find("li.vItem").each(function() {

		var _course_type = $(this).data('course_type') || false;
		
		if (_course_type !== false) {
			
			if ($.inArray(_course_type, _hide_course_type) !== -1) {
				
				if (_show_wrong && $(this).data('wrong') == '1') {//this must remain shown
					$(this).show();
				} else {
					$(this).hide();
				}
				
			} else if ($.inArray(_course_type, _show_course_type) !== -1) {
				
				$(this).show();

			}

		}
	});
	
	//hide entire "ul" if no item is shown
	$(".selector_patient_course_list").find("ul.vListRecord").each(function() {
		
		var _shown_lis = $('li.vItem', this).filter(function(){
			 return $(this).css('display') != "none";
		}).length;
		
		if (_shown_lis > 0) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
	
	
	
	
}


