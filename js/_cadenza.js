var cadenza = {};
var _ = cadenza;

_.cadenzaUrl = null;
_.clientId = null;
_.browserApiKey = null;
_.fileuploadMaxChunkSize = null;
_.strings = null;
_.btnIcons = null;

_.init = function(cadenzaUrl, clientId, browserApiKey, fileuploadMaxChunkSize, jsStrings, btnIcons, videojsSwf, componentNames, adminComponentNames) {
	_.cadenzaUrl = cadenzaUrl;
	_.clientId = clientId;
	_.browserApiKey = browserApiKey;
	_.fileuploadMaxChunkSize = fileuploadMaxChunkSize;
	_.strings = jsStrings;
	_.btnIcons = btnIcons;
	_.page.init(videojsSwf, componentNames, adminComponentNames);
};

_.alert = function(text) {
	try {
		alert(text);
	}
	catch (e) {
		// do nothing
	}
};

_.getClientId = function() {
	return _.clientId;
};
_.getBrowserApiKey = function() {
	return _.browserApiKey;
};

_.getFileuploadMaxChunkSize = function() {
	return _.fileuploadMaxChunkSize;
};

_.translate = function(key) {
	if (_.strings[key]) {
		// return the translated text corresponding to the given key
		return _.strings[key];
	}
	// if we are here, the given key does not exist
	return '[ ' + key + ' ]';
};

_.btnIconHtml = function(key) {
	if (_.btnIcons[key]) {
		// return the icon html corresponding to the given key
		return _.btnIcons[key];
	}
	// if we are here, the given key does not exist
	return '[ ' + key + ' icon ]';
};
