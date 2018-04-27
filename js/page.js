_.page = {};

_.page.url = null;
_.page.params = null;
_.page.isAjaxInProgress = false;
_.page.isUnloading = false;
_.page.unsafeNavigationUploadCount = 0;
_.page.unsafeNavigationPageFlag = false;
_.page.isDebugResponse = false;

_.page.init = function(videojsSwf, componentNames, adminComponentNames) {
	this.url = window.location.href.split('?').shift().split('#').shift();
	this.params = {};
	var paramPairs = this.getParamPairsArray();
	$.each(paramPairs, function(index, value) {
		var pair = value.split('=');
		if (pair[1] !== undefined) {
			_.page.params[pair[0]] = pair[1];
		}
	});
	this.initVideojs(videojsSwf);
	this.initAjax();
	if (componentNames != null) {
		this.initComponents(componentNames);
	}
	if (adminComponentNames != null) {
		this.initAdminComponents(adminComponentNames);
	}
	window.onbeforeunload = function() {
		_.page.isUnloading = true;
		
		if (_.page.isUnsafeToNavigateAway()) {
			return _.translate('unsafe_navigation_warning');
		}
	};
	window.onpopstate = function(e) {
	    if (e.state) {
	    	if (e.state.params) {
	    		this.params = e.state.params;
	    	}
	    	if (e.state.response) {
	    		if (e.state.response.refresh) {
					_.page.refreshComponents(e.state.response.refresh);
	    		}
	    		if (e.state.response.refreshAdmin) {
	    			_.page.refreshAdminComponents(e.state.response.refreshAdmin);
	    		}
	    	}
	    }
	};
};
_.page.initVideojs = function(videojsSwf) {
	videojs.options.flash.swf = videojsSwf;
};
_.page.initAjax = function() {
	$.ajaxSetup({
		timeout: 120000, // 120 seconds
		error: function(response, textStatus, errorThrown) {
			if (_.page.isUnloading) {
				return; // page is unloading, ignore ajax error
			}
			else if (errorThrown === 'abort') {
				return; // ajax request was aborted by user
			}
			var msg = "An AJAX error occurred: \n" + textStatus;
			if (textStatus == "error") {
				msg += ' ' + response.status;
			}
			_.alert(msg);
		}
	});
	$(document).ajaxStart(function() {
		_.page.isAjaxInProgress = true;
	});
	$(document).ajaxStop(function() {
		_.page.isAjaxInProgress = false;
	});
};
_.page.initComponents = function(componentNames) {
	var i;
	for (i = 0; i < componentNames.length; i++) {
		this.getComponent(componentNames[i]).init();
	}
};
_.page.initAdminComponents = function(adminComponentNames) {
	var i;
	for (i = 0; i < adminComponentNames.length; i++) {
		this.getAdminComponent(adminComponentNames[i]).init();
	}
};
_.page.refreshComponents = function(refresh) {
	var key;
	var componentName;
	var html;
	for (key in refresh) {
		if (refresh.hasOwnProperty(key)) {
			componentName = key;
			html = refresh[key];
			this.getComponent(componentName).refreshComponent(html);
		}
	}
};
_.page.refreshAdminComponents = function(refreshAdmin) {
	var key;
	var componentName;
	var html;
	for (key in refreshAdmin) {
		if (refreshAdmin.hasOwnProperty(key)) {
			adminComponentName = key;
			html = refreshAdmin[key];
			this.getAdminComponent(adminComponentName).refreshAdminComponent(html);
		}
	}
};
_.page.getParamPairsArray = function() {
	return window.location.search.substring(1).split('#').shift().split('&');
};
_.page.getParamsClone = function() {
	var paramsClone = {};
	for (key in this.params) {
		if (this.params.hasOwnProperty(key)) {
			paramsClone[key] = this.params[key];
		}
	}
	return paramsClone;
};
_.page.getComponent = function(componentName) {
	return eval('_.components.' + componentName.replace('/', '.'));
};
_.page.getAdminComponent = function(adminComponentName) {
	return eval('_.admin_components.' + adminComponentName.replace('/', '.'));
};

_.page.showUnsafeNavigationConfirmIfUnsafe = function() {
	if (_.page.isUnsafeToNavigateAway()) {
		var continueAnyways = confirm(_.translate('unsafe_navigation_warning'));
		if (continueAnyways) {
			_.page.resetUnsafeNavigation();
		}
		return continueAnyways;
	}
	return true;
};

_.page.showUnsafeNavigationConfirmIfUploadsPending = function() {
	if (_.page.unsafeNavigationUploadCount > 0) {
		var continueAnyways = confirm(_.translate('unsafe_navigation_warning'));
		if (continueAnyways) {
			_.page.resetUnsafeNavigation();
		}
		return continueAnyways;
	}
	return true;
};

_.page.resetUnsafeNavigation = function() {
	_.page.unsafeNavigationUploadCount = 0;
	_.page.unsafeNavigationPageFlag = false;
};

_.page.isUnsafeToNavigateAway = function() {
	return (_.page.unsafeNavigationUploadCount > 0 || _.page.unsafeNavigationPageFlag);
};

_.page.addUnsafeNavigationUpload = function() {
	_.page.unsafeNavigationUploadCount++;
};

_.page.removeUnsafeNavigationUpload = function() {
	_.page.unsafeNavigationUploadCount--;
	if (_.page.unsafeNavigationUploadCount < 0) {
		_.page.unsafeNavigationUploadCount = 0;
	}
};

_.page.setUnsafeNavigationForPageFlag = function(isUnsafe) {
	_.page.unsafeNavigationPageFlag = isUnsafe;
};

_.page.actionPost = function(actionname, data, success) {
	var url = this.url + '?action=' + actionname;
	var dataType = "json";
	$.post(url, data, function(response) {
		_.page.handleResponse(response, success);
	}, dataType);
};

_.page.actionLoad = function(loadSelector, actionname, data, complete) {
	_.page.actionPost(actionname, data, function(response) {
		if (response.html) {
			$(loadSelector).html(response.html);
		}
		complete(response);
	});
};

_.page.actionReplace = function(replaceSelector, actionname, data, complete) {
	_.page.actionPost(actionname, data, function(response) {
		if (response.html) {
			$(replaceSelector).replaceWith(response.html);
		}
		complete(response);
	});
};

_.page.scrollToElement = function(elementId) {
	$('html, body').scrollTop($('#'+elementId).offset().top);
};

_.page.handleResponse = function(response, callback) {
	var debug = _.page.isDebugResponse;
	if (debug) { console.debug('_.page.handleResponse BEGIN'); }
	if (typeof response == 'string') {
		if (debug) { console.debug('_.page.handleResponse : response is of type string, attempting parseJSON...'); }
		try {
			response = $.parseJSON(response);
			if (debug) { console.debug('_.page.handleResponse : parseJSON successful? YES'); }
		}
		catch (e) {
			if (debug) { console.debug('_.page.handleResponse : parseJSON successful? NO'); }
			// response is not valid json, something went wrong
			_.alert('A server error occurred.');
			return;
		}
	}
	if (debug) { console.debug('_.page.handleResponse : tracing response:'); }
	if (debug) { console.debug(response); }
	if (typeof response == 'object') {
		if (debug) { console.debug('_.page.handleResponse : response is of type object, assuming it\'s a JSON object.'); }
		if (response.result) {
			if (debug) { console.debug('_.page.handleResponse : response has result "'+response.result+'"'); }
			if (response.result == 'success') {
				if (debug) { console.debug('_.page.handleResponse : performing callback...'); }
				callback(response);
			}
			else if (response.result == 'error') {
				if (debug) { console.debug('_.page.handleResponse : handling error...'); }
				if (response.html) {
					$('body').prepend(response.html);
				}
				else {
					_.alert('A server error occurred' + (response.error ? (": \n" + response.error) : '.'));
				}
			}
			else if (response.result == 'redirect') {
				if (response.destination) {
					if (debug) { console.debug('_.page.handleResponse : redirect destination "'+response.destination+'"...'); }
                    
                    if (response.message) {
                        if (debug) { console.debug('_.page.handleResponse : redirect message "'+response.message+'"...'); }
                        _.alert(response.message);
                    }
                    if (response.disable_unsafe_navigation) {
                        if (debug) { console.debug('_.page.handleResponse : redirect disable_unsafe_navigation "'+response.disable_unsafe_navigation+'"...'); }
                        _.page.resetUnsafeNavigation();
                    }
                    
					window.location.href = response.destination;
				}
				else {
					if (debug) { console.debug('_.page.handleResponse : redirect has no destination (ignore)'); }
				}
			}
			else {
				if (debug) { console.debug('_.page.handleResponse : unknown result (ignore)'); }
			}
		}
		else {
			if (debug) { console.debug('_.page.handleResponse : response has no result (ignore)'); }
		}
	}
	else {
		if (debug) { console.debug('_.page.handleResponse : unknown type of response "'+(typeof response) + '" (ignore)'); }
	}
	if (debug) { console.debug('_.page.handleResponse END'); }
};

// TODO: only use for pagination and column sorting (for now)
_.page.historyPushState = function(params, response) {
	this.params = params;
	
	var paramArray = [];
	$.each(this.params, function( index, value ) {
		paramArray.push(index+"="+value);
	});
	
	var queryString = (paramArray.length > 0) ? "?"+paramArray.join("&") : "";
	
	var state = {
		params: this.params,
		response: response
	};
	
    window.history.pushState(state, "", this.url + queryString);
};

_.page.isFirefox = function() {
    return (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);
};
_.page.isIE = function() {
    var ua = window.navigator.userAgent;
    
    // IE <= 10
    if (ua.indexOf('MSIE ') > 0) {
        return true;
    } 
    // IE == 11
    else if (ua.indexOf('Trident/') > 0) {
        return true;
    }
    // IE >= 12
    else if (ua.indexOf('Edge/') > 0) {
        return true;
    }
    
    // other browser
    return false;
};
