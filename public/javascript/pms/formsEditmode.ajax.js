;
/**
 * formsEditmode.ajax.js
 * 
 * @date 17.01.2019
 * @author
 * @cla
 * 
 * requires bootstrap-notify.js
 * 
 * usage example:
 * $('#MainContent').checkFormularEditmode();
 * $.checkFormularEditmode(); // it uses body as selector
 *
 * TODO: overhaul all, so the ajax is performed for all attached elements.. if 10 forms in 1 page we do 1 single ajax, and we get data.notifications[__element_id]
 * as it stands now... if you attach to a page like verlauf or stammdaten, each box/form get's his own xhr query, this is very bad to be used as it is now for a page like that
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
    //console.info('custom view js included : ' + document.currentScript.src);
}


(function($, window, document, undefined) {

    var pluginName = 'checkFormularEditmode',
        dataKey = "plugin_" + pluginName,
        defaults = {
            milliseconds: 15000, // for setTimeout
            ajax: {
                url: window.appbase + "ajax/forms_editmode_ajax",
                type: "POST",
                dataType: 'json',
                data: {
                    'pathname': window.location.pathname,
                    'search': window.location.search,
                    'pid': window.idpd,
                    'cid': window.idcidpd,
                },
            },
        };

    var CheckFormularEditmode = function(element, options) {

        this.element = element; // jquery el
        this.ajax = null; // will hold request so we can abort
        this.setTimeout_ajax = null; // will hold fn timeout so we can clear
        this.__change_date = null; // we display notifications only if notification date changed
        
        //this.uniqueId = $(this.element).uniqueId(); // jquery 1.9 , ispc is using 1.8       
        if ( ! element.attr('id')) {
        	element.attr('id', '_randID_' + Math.random().toString(36).substr(2, 9));
        }
        
        this.options = $.extend(true, {}, defaults, options);
        
        this.init();
    };
    
    $.extend(CheckFormularEditmode.prototype, {
    	 
    	init: function() {
    		
    		this.displayDefaultNotification(); //display an initial notifications

    		this.checkAjax(); 
    		
         },
         
         
         displayDefaultNotification: function () {
        	 
        	 $(this.element).FormularNotifications({
        		 'type'		: 'warning',
        		 'title'	: translate("Formular Editmode"),
        		 'message'	: translate("This Formular Has multiple editors pugin, checking if anyone else is editing it now") 
        		 			+ " ... <img src='" + appbase + "images/loading-transp.gif' >",
        		 'allow_dismiss' : false,
    		 });
         },
         
         checkAjax: function (options) {
        	 
        	 var self = this,
        	 ajax_data = $.extend(true, {}, self.options.ajax.data, {'__element_id': this.element.attr('id')}, options);
        	         	 
        	 self.ajax = $.ajax({

                 url		: self.options.ajax.url,
                 type		: self.options.ajax.type,
                 dataType	: self.options.ajax.dataType,
                 data		: ajax_data,

                 error: function(jqXHR, textStatus, errorThrown) {
                	 //re-call self
                	 self.setTimeout_ajax = setTimeout(function() {
                    	 self.checkAjax();
                     }, self.options.milliseconds);
                 },
                 
                 success: function(data, textStatus, jqXHR) {
                	 
                	 if (data.hasOwnProperty('result')
                         && data.result == true
                         && data.hasOwnProperty('notification')
                         && data.notification.hasOwnProperty('type')
                         && data.notification.__change_date != self.__change_date
                	 ) 
                	 {                	
                		 self.__change_date = data.notification.__change_date;
            			 $(self.element).FormularNotifications('update', data.notification);
                	 }
                	 
                	 //re-call self
            		 self.setTimeout_ajax = setTimeout(function() {
                    	 self.checkAjax();
                     }, self.options.milliseconds);
                 }
        	 });
 		},
 		
 		gracefulEditor : function() {
 			clearTimeout(this.setTimeout_ajax); 			
 			this.ajax.abort();
 			this.checkAjax({"__action" : "gracefulEditor"});
 		},
 		closeEditor : function() {
 			clearTimeout(this.setTimeout_ajax); 			
 			this.ajax.abort();
 			this.checkAjax({"__action" : "closeEditor"});
 		},
 		overwriteEditor : function() {
 			clearTimeout(this.setTimeout_ajax); 			
 			this.ajax.abort();
 			this.checkAjax({"__action" : "overwriteEditor"});
 		},
 		
    });
    

    /*
     * Plugin wrapper, preventing against multiple instantiations and return
     * plugin instance.
     */
    $[pluginName] = $.fn[pluginName] = function(options) {

        var args = arguments, 
        self = this;

        if (options === undefined || typeof options === 'object') {
        	
            if ( ! (this instanceof $)) {
                $.extend(true, defaults, options);
                self = $('body');
            }
            return self.each(function() {
                if ( ! $.data(this, dataKey)) {
                    $.data(this, dataKey, new CheckFormularEditmode($(this), options));
                }
            });

        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {

            var returns;
            if ( ! (this instanceof $)) {
                self = $('body');
            }
            
            self.each(function() {

                var instance = $.data(this, dataKey);

                if (!instance) {
                	
                    instance = $.data(this, dataKey, new CheckFormularEditmode($(this), options));
                }

                if (instance instanceof CheckFormularEditmode && typeof instance[options] === 'function') {
                    returns = instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                }

                if (options === 'destroy') {
                    $.data(this, dataKey, null);
                }
            });

            return returns !== undefined ? returns : this;
        }
    };

}(jQuery, window, document));









(function($, window, document, undefined) {

    var pluginName = 'FormularNotifications',
        dataKey = "plugin_" + pluginName,
        defaults = {
    		bootstrap_notify: true,
    		desktop_notification: true,
        };

    var FormularNotifications = function(element, options) {

        this.element = element;
        
        this.options = $.extend(true, {}, defaults, options, {'dataKey': dataKey});
        
        this.init(options);
    };

    FormularNotifications.prototype = {
    	/*
		bootstrapNotificationJqueryfn : function() {
			//because we are not using css/js Bootstrap
    		if ( ! $.isFunction($.fn.notify)) {		
    			$.fn.notify = $.notify;
    		}
    		if ( ! $.isFunction($.fn.notifyDefaults)) {		
    			$.fn.notifyDefaults = $.notifyDefaults;
    		}
    		if ( ! $.isFunction($.fn.notifyClose)) {		
    			$.fn.notifyClose =	$.fn.notifyClose;
    		}
    		if ( ! $.isFunction($.fn.notifyCloseExcept)) {
    			$.fn.notifyCloseExcept = $.fn.notifyCloseExcept;
    		}
        },*/
         
        init: function(options) {
        	var _that = this;
        	// bootstrap-notify.js
        	if (this.options.bootstrap_notify) {
        		
        		/*this.bootstrapNotificationJqueryfn();*/
        	
    			this.notify = $.notify({
        				// options
        				icon	: typeof options === 'object' && options.hasOwnProperty('icon') ? options.icon : 'glyphicon glyphicon-warning-sign',
        				title	: typeof options === 'object' && options.hasOwnProperty('title') ? options.title : null,
        				message	: typeof options === 'object' && options.hasOwnProperty('message') ? options.message : null,
    				}, 
    				{
    					// settings
    					element			: $(this.element),
    					type			: typeof options === 'object' && options.hasOwnProperty('type') ? options.type : 'success',
    					allow_dismiss	: typeof options === 'object' && options.hasOwnProperty('allow_dismiss') ? options.allow_dismiss : true,
    					delay			: typeof options === 'object' && options.hasOwnProperty('delay') ? options.delay : 0, // so it does not auto-closes
    					position		: 'relative',
    					newest_on_top	: true,
    					z_index			: null,
    					placement		: {from: "top", align: "center"},
						offset			: {x: 0, y: 0},
						icon_type		: 'class',						
						template		: '<div data-notify="container" class="formularNotifications alert alert-{0}" role="alert">' +
						'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
						'<span data-notify="icon"></span> ' +
						'<h4 class="title_container" data-notify="title">{1}</h4> ' +
						'<div class="message_container" data-notify="message">{2}</div>' +
						'<div class="progress" data-notify="progressbar">' +
						'<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
						'</div>' +
						//'<a href="{3}" target="{4}" data-notify="url"></a>' +
						'</div>',
						
						onShow : function (){
							$(this).css({'display' : 'block'});
						},
						onClosed : function (){
							$.data(_that.element, _that.options.dataKey, null);    							
							$(_that.element).data(_that.options.dataKey, null);
						},
				});
        		
        		
        		
        		if (typeof options === 'object' && options.hasOwnProperty('type') && options.hasOwnProperty('message')) 
        		{
        			this.update(options);
        		}
        	};
        	
        	
        	
        	
        	if (this.options.desktop_notification) {
	        	// Desktop Notification
	        	if ( ! ("Notification" in window)) {
	                // "This browser does not support desktop notification"
	            } else if (Notification.permission === "granted") {
	                // "This browser allready granted notification permission"
	            	if (options.hasOwnProperty('type') && options.hasOwnProperty('message') && options.type == 'danger'){
	            		var notificationObj = new Notification(translate('Formular is being edited'), {                	
	                        type: 'basic',
	                        //icon: options.icon,
	                        body: options.title,

	                    });
	            	}
	            	
	            } else if (Notification.permission !== 'denied' ||
	                Notification.permission === "default") {
	                Notification.requestPermission(function(permission) {
	                	// If the user accepts, let's create a notification
	                	if (permission === "granted") {
	                		var notification = new Notification(
	                				window.translate("ISPC Browser Notification Permissions"), {
	                					body: window.translate("Here you will receive informations when one of your coleags is doying work on the same patient/formular as you, so you both don't to the same job twice and break things"),
	                                });
	                    }
	                });
	            }
        	}
        },
        
        
        update: function(options) {

        	// bootstrap-notify.js
        	if (this.options.bootstrap_notify && this.notify) {
        		this.notify.update(options);
        	}
        	
        	
        	// Desktop Notification
            if ( this.options.desktop_notification && "Notification" in window && Notification.permission === "granted" && options.type == 'danger') {
            	var notificationObj = new Notification(translate('Formular is being edited'), {                	
                    type: 'basic',
                    body: options.title,
                });
            }
        },

    };

    /*
     * Plugin wrapper, preventing against multiple instantiations and return
     * plugin instance.
     */
    $[pluginName] = $.fn[pluginName] = function(options) {

        var args = arguments;

        if (options === undefined || typeof options === 'object') {

            if ( ! (this instanceof $)) {
            	$.extend(true, defaults, options);
                //$.extend(defaults, options);
            }

            return this.each(function() {
                if ( ! $.data(this, dataKey)) {
                    $.data(this, dataKey, new FormularNotifications($(this), options));
                }
            });

        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {

            var returns;

            this.each(function() {

                var instance = $.data(this, dataKey);

                if (!instance) {
                	
                    instance = $.data(this, dataKey, new FormularNotifications($(this), options));
                }

                if (instance instanceof FormularNotifications && typeof instance[options] === 'function') {
                    returns = instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                }

                if (options === 'destroy') {
                    $.data(this, dataKey, null);
                }
                
            });

            return returns !== undefined ? returns : this;
        }
    };

}(jQuery, window, document));