;
/**
 * @cla on 16.07.2018
 * bind event order r.c.1
 * 
 * start ideea from:
 * https://stackoverflow.com/questions/2360655/jquery-event-handlers-always-execute-in-order-they-were-bound-any-way-around-t
 * 
 * !! inline event attr is removed and $.binded with eval() , see bindInlineEvents
 * 
 * $(el).isBoundNamespaced('click.myNS')
 * $(el).bindFirst('click', myFn), will be the first click of clicks
 * $(el).bindLast('focusout', myFn), myFn will be the last focusout executed when you focusout
 * $(el).bindNth()
 * 
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

$.fn.isBoundNamespaced = function(name) {
	var result = false;
	this.each(function () {
		var data = $._data(this, 'events');
		if (data === undefined || data.length === 0) {
	        return;//continue;
	    }
		var handlers = data[name.split('.')[0]];
		if (handlers === undefined || handlers.length === 0) {
			result = false;
		} else {
			var namespace = name.split('.')[1];	
			if (namespace === undefined) {
				result = true;//no namespace, so true
			} else {
				$.each(handlers, function() {
					if (this.namespace == namespace) {
						result = true;
						return false;//break
					}
				});
			}
		}
		if (result == true) {
			return false;//break
		}
	});
	return result;
};
$.fn.bindFirst = function (name, fn) {	
    this.bindNth(name, fn, 0);
};

$.fn.bindLast = function (name, fn) {
    this.bindNth(name, fn, -1);
};

$.fn.bindNth = function (name, fn, index) {
	this.bindInlineEvents(name);
    // Bind event normally.
    this.bind(name, fn);
    // Move to nth position.
    this.changeEventOrder(name, index);
};

$.fn.bindInlineEvents = function (name) {
	function getInlineEvent(element, evname) {
		var _r = false;
		$.each(element.get(0).attributes, function(){
			if (this.name == evname && this.value.length) {
				_r = this.value;
				return false; 
			}
		});
		return _r;
	}
	
	var _evname = 'on' + name.toLowerCase().split('.')[0];
	var _inlineEventVal =  getInlineEvent(this, _evname);
	
	if (_inlineEventVal !== false) {
		this.bind(name, function () {
			try {return eval(_inlineEventVal); } catch(e) {console.log(e);}
		});
		$(this).removeAttr(_evname);
	}
};


$.fn.changeEventOrder = function (names, newIndex) {
    var that = this;
    // Allow for multiple events.
    $.each(names.split(' '), function (idx, name) {
        that.each(function () {
            var handlers = $._data(this, 'events')[name.split('.')[0]];
            
            if (newIndex == -1) {   
            	newIndex = handlers.length -1;
            } else {
            	// Validate requested position.
            	newIndex = Math.min(newIndex, handlers.length - 1);
            }
            
        	handlers.splice(newIndex, 0, handlers.pop()); 
        	
        });
    });
};
