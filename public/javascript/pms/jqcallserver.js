    function ajaxCallserver(urls)
    {
	//alert(urls.url);
	//starting setting some animation when the ajax starts and completes
	//alert(appbase+urls.url);
	if(urls.callLoading) {
	    if(urls.loadingOptions) {
		urls.callLoading(urls.loadingOptions);
	    } else {
		urls.callLoading();
	    }
	}

	if(urls.method != undefined && urls.method) {
	    var method = urls.method;
	} else {
	    var method = 'GET';
	}
	if(urls.data != undefined && urls.data) {
	    var data = urls.data;
	} else {
	    var data = {};
	}

	$.ajaxSetup({
	    beforeSend: function (jqXHR) {
		if($('#btnsubmit'))
		{
		    $('#btnsubmit').addClass('loading_verlauf');
		}
	    },
	    complete: function (jqXHR) {
		if($('#btnsubmit'))
		{
		    $('#btnsubmit').removeClass('loading_verlauf');
		}
	    }
	});

	$.ajax({
	    url: urls.url,
	    type: method,
	    data: data,
	    mode: "abort",
	    secureuri: false,
	    fileElementId: 'icon',
	    dataType: 'json',
	    success: function (data, status) {
		var test = jQuery.parseJSON(data);
		if(data) {
		    if(data.error != '') {
		    } else {

			if(data.callBack) {
			    callback = eval(data.callBack);
			    callback(data.callBackParameters);
			}
		    }
		}
	    },
	    error: function (data, status, e) {
	    }
	});
	return false;
    }
    
//assoc-serialize entire form    
(function($) {
  return $.fn.serializeObject = function() {
    var json, patterns, push_counters,
      _this = this;
    json = {};
    push_counters = {};
    patterns = {
//      validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
      validate: /^[a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
      key: /[a-zA-Z0-9_]+|(?=\[\])/g,
      push: /^$/,
      fixed: /^\d+$/,
      named: /^[a-zA-Z0-9_]+$/
    };
    this.build = function(base, key, value) {
      base[key] = value;
      return base;
    };
    this.push_counter = function(key) {
      if (push_counters[key] === void 0) {
        push_counters[key] = 0;
      }
      return push_counters[key]++;
    };
    $.each($(this).serializeArray(), function(i, elem) {
      var k, keys, merge, re, reverse_key;
      if (!patterns.validate.test(elem.name)) {
        return;
      }
      keys = elem.name.match(patterns.key);
      merge = elem.value;
      reverse_key = elem.name;
      while ((k = keys.pop()) !== void 0) {
        if (patterns.push.test(k)) {
          re = new RegExp("\\[" + k + "\\]$");
          reverse_key = reverse_key.replace(re, '');
//          merge = _this.build([], _this.push_counter(reverse_key), merge);
          merge = _this.build({}, _this.push_counter(reverse_key), merge);
        } else if (patterns.fixed.test(k)) {
//          merge = _this.build([], k, merge);
          merge = _this.build({}, k, merge);
        } else if (patterns.named.test(k)) {
          merge = _this.build({}, k, merge);
        }
      }
      return json = $.extend(true, json, merge);
    });
    return json;
  };
})(jQuery);