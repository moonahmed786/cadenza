// _.drive = {};

// _.drive.debug = false;
// _.drive.success = [];
// _.drive.errors = [];
// _.drive.setupWorking = false;
// _.drive.setupComplete = false;
// _.drive.setupAuthError = null;

// _.drive.uploadStorage = {};

// /**
//  * Call to authenticate before making a client API request. Loads the client script with onload=handleClientLoad and then
//  * authenticates by performing the following sequence:
//  * 1. _.drive.handleClientLoad gets called (NOTE: we define window.handleClientLoad as _.drive.handleClientLoad so the client script can find the function).
//  * 2. _.drive.loadServices gets called.
//  * 3. _.drive.checkAuth gets called, which performs the getAccessToken action.
//  * 4. The response of the getAccessToken action is handled in an anonymous function that determines whether the authorization was
//  * successful or failed, and then makes the appropriate success or error function calls.
//  *
//  * @param {Function} success Function to call if the authorization is successful.
//  * @param {Function} error Function to call if the authorization failed.
//  */
// _.drive.authenticateApiRequest = function(success, error) {
// 	if (_.drive.setupComplete) {
// 		if (_.drive.setupAuthError) {
// 			error(_.drive.setupAuthError);
// 		}
// 		else {
// 			success();
// 		}
// 	}
// 	else if (_.drive.setupWorking) {
// 		_.drive.success.push(success); // called when authorization is successful
// 		_.drive.errors.push(error); // called when authorization failed
// 	}
// 	else {
// 		_.drive.setupWorking = true;
// 		_.drive.success.push(success); // called when authorization is successful
// 		_.drive.errors.push(error); // called when authorization failed

// 		// Load client library
// 		if (_.drive.debug) { console.debug('loading client library...'); }
// 		window.handleClientLoad = _.drive.handleClientLoad; // called when client library is loaded

// 		$.getScript("https://apis.google.com/js/client.js?onload=handleClientLoad", function() {
// 			if (_.drive.debug) { console.debug('client library script loaded.'); }
// 		});
// 	}
// };

// /**
//  * Called when the client library is loaded to start the auth flow.
//  */
// _.drive.handleClientLoad = function() {
// 	if (_.drive.debug) { console.debug('_.drive.handleClientLoad'); }

// 	_.drive.loadServices(function() {
// 		window.setTimeout(_.drive.checkAuth, 1);
// 	});
// };

// /**
//  * Load the Drive API client.
//  *
//  * @param {Function} callback Function to call when the client is loaded.
//  */
// _.drive.loadServices = function(callback) {
// 	// Load services libraries
// 	if (_.drive.debug) { console.debug('_.drive.loadServices BEGIN'); }
//     gapi.client.setApiKey(_.getBrowserApiKey());
// 	gapi.client.load('drive', 'v3', function() {
// 		callback();
// 	});
// 	if (_.drive.debug) { console.debug('_.drive.loadServices END'); }
// };

// /**
//  * Check if the current user has authorized the application.
//  */
// _.drive.checkAuth = function() {
// 	if (_.drive.debug) { console.debug('_.drive.checkAuth'); }
// 	_.page.actionPost('getAccessToken', {}, function(response) {

// 		if (response.access_token) {
// 			// authorization successful
// 			if (_.drive.debug) {
// 				console.debug('-> authorization successful:');
// 				console.debug(response);
// 				console.debug('-> access token:' + response.access_token);
// 			}

// 			gapi.auth.setToken(response.access_token);

// 			$.each(_.drive.success, function(index, callback) {
// 				callback();
// 			});
// 		}
// 		else {
// 			// authorization failed
// 			if (_.drive.debug) {
// 				console.debug('-> authorization failed');
// 				console.debug(response);
// 			}

// 			_.drive.setupAuthError = _.drive.getCustomError("authError");

// 			$.each(_.drive.errors, function(index, callback) {
// 				callback(_.drive.setupAuthError);
// 			});
// 		}
//         // auth complete
//         _.drive.setupComplete = true;
//         _.drive.setupWorking = false;

// 		_.drive.success = [];
// 		_.drive.errors = [];
// 	});
// };

// _.drive.getAccessToken = function() {
// 	return gapi.auth.getToken().access_token;
// };

// _.drive.driveDateStringToCadenzaDateString = function(driveCreatedDate) {
// 	var createdDate = new Date(driveCreatedDate);
// 	var year = createdDate.getFullYear();
// 	var month = createdDate.getMonth()+1;
// 	var day = createdDate.getDate();
// 	if (month < 10) { month = "0"+month; }
// 	if (day < 10) { day = "0"+day; }

// 	return year+"-"+month+"-"+day;
// };

// // TODO: implement a better error message system for the annotator (display it in html instead of a javascript alert)
// _.drive.errorAlert = function(error) {
// 	var msg = "A Google Drive error occurred: \n\n";

// 	// custom Cadenza error messages for Google Drive issues
// 	if (error.cadenzaCustomError) {
// 		// just display the message
// 		msg += error.message;
// 	}
// 	else {
// 		// generic google drive error message, just display the error code and message
// 		msg += error.code + " - " + error.message;
// 	}

// 	_.alert(msg);
// };

// _.drive.getCustomError = function(errorType) {
// 	var customError = {
// 		cadenzaCustomErrorType: errorType,
// 		cadenzaCustomError: true,
// 		message: ""
// 	};

// 	switch (errorType) {
// 	    case "authError":
// 	    	customError.message = "Authentication error occurred. You are not signed in with your Google Drive account.";
// 	        break;
// 	    default:
// 	    	// do nothing
// 	}

// 	return customError;
// };

// /**
//  * Insert new file.
//  */
// _.drive.insertFile = function(fileData, userEmail, uploadTrackingId, successCallback, errorCallback, progressCallback) {
// 	if (_.drive.debug) { console.debug('_.drive.insertFile'); }
// 	_.drive.authenticateApiRequest(function() {
// 		var contentType = fileData.type || 'application/octet-stream';
// 		var metadata = {
// 			'name': fileData.name,
// 			'mimeType': contentType,
// 			'writersCanShare': false,
// 			'appProperties':{
// 				"cadenzaProperty" : "true",
// 				"notemakerProperty":"false",
// 				"originalOwnerEmailAddressProperty":userEmail,
// 			}

// 		};

// 		var upload_options = {
// 			metadata: metadata,
// 			file: fileData,
// 			token: _.drive.getAccessToken(),
// 		    onComplete: function(response) {
// 				response = $.parseJSON(response);
// 		    	successCallback(response);

// 		    	if (_.drive.uploadStorage[uploadTrackingId]) {
// 		    		delete _.drive.uploadStorage[uploadTrackingId];
// 		    	}
// 		    },
// 		    onError: function(response) {
// 				response = $.parseJSON(response);
// 		    	errorCallback(response);

// 		    	if (_.drive.uploadStorage[uploadTrackingId]) {
// 		    		delete _.drive.uploadStorage[uploadTrackingId];
// 		    	}
// 		    },
// 		    onProgress: function(progressEvent) {
// 		    	progressCallback(progressEvent);
// 		    }
// 		};

// 		var uploader = new MediaUploader(upload_options);
// 		uploader.upload();

// 		_.drive.uploadStorage[uploadTrackingId] = uploader;
// 	}, errorCallback);
// };

// _.drive.insertFileAbort = function(uploadTrackingId, successCallback) {
// 	if (_.drive.debug) { console.debug('_.drive.insertFileAbort'); }

// 	if (_.drive.uploadStorage[uploadTrackingId]) {
// 		_.drive.uploadStorage[uploadTrackingId].abort();
// 		delete _.drive.uploadStorage[uploadTrackingId];
// 		successCallback();
// 	}
// };

// _.drive.fetchFile = function(fileId, fields, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/files/get
// 		var request = gapi.client.drive.files.get({
// 			'fileId': fileId,
// 			'fields': fields
// 		});
// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };


// _.drive.fetchNotemakerFiles = function(userEmail, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/files/list
// 		var retrievePageOfFiles = function(request, result) {
// 			request.execute(function(resp) {
// 				if (resp.error) {
// 					errorCallback(resp.error);
// 				}
// 				else {
// 					result = result.concat(resp.files);
// 					var nextPageToken = resp.nextPageToken;
// 					if (nextPageToken) {
// 						request = gapi.client.drive.files.list({
// 							'orderBy': "createdTime desc",
// 							'q': 	"appProperties has { key = 'notemakerProperty' AND value = 'true' } AND " +
// 									"appProperties has { key = 'originalOwnerEmailAddressProperty' AND value = '"+userEmail+"' } ",
// 							'pageToken': nextPageToken,
// 							'fields': "nextPageToken, files(id, name, createdTime, thumbnailLink)"
// 				        });
// 						retrievePageOfFiles(request, result);
// 				    }
// 				    else {
// 				    	successCallback(result);
// 				    }
// 				}
// 			});
// 		};
// 		var initialRequest = gapi.client.drive.files.list({
// 			'orderBy': "createdTime desc",
// 			'q': 	"appProperties has { key = 'notemakerProperty' AND value = 'true' } AND " +
// 					"appProperties has { key = 'originalOwnerEmailAddressProperty' AND value = '"+userEmail+"' } ",
// 			'fields': "nextPageToken, files(id, name, createdTime, thumbnailLink)"
// 		});

// 		retrievePageOfFiles(initialRequest, []);
// 	}, errorCallback);
// };

// _.drive.listFileComments = function(fileId, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/comments/list
// 		var retrievePageOfComments = function(request, result) {
// 			request.execute(function(resp) {
// 				if (resp.error) {
// 					errorCallback(resp.error);
// 				}
// 				else {
// 					result = result.concat(resp.comments);
// 					var nextPageToken = resp.nextPageToken;
// 					if (nextPageToken) {
// 						request = gapi.client.drive.comments.list({
// 							'fileId': fileId,
// 							'pageToken': nextPageToken,
// 							'fields': "nextPageToken, comments"
// 				        });
// 						retrievePageOfComments(request, result);
// 				    }
// 				    else {
// 				    	successCallback(result);
// 				    }
// 				}
// 			});
// 		};
// 		var initialRequest = gapi.client.drive.comments.list({
// 			'fileId': fileId,
// 			'fields': "nextPageToken, comments"
// 		});

// 		retrievePageOfComments(initialRequest, []);
// 	}, errorCallback);
// };

// _.drive.createComment = function(fileId, content, quotedFileContent, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/comments/create
// 		var request = gapi.client.drive.comments.create({
// 			'fileId': fileId,
// 			'fields': 'id',
// 			'resource': {
// 				'content': content,
// 				'quotedFileContent': quotedFileContent
// 			}
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };

// _.drive.updateComment = function(fileId, commentId, content, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/comments/update
// 		var request = gapi.client.drive.comments.update({
// 			'fileId': fileId,
// 			'commentId': commentId,
// 			'fields': 'id',
// 			'resource': {
// 				'content': content
// 			}
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };

// _.drive.deleteComment = function(fileId, commentId, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/comments/delete
// 		var request = gapi.client.drive.comments.delete({
// 			'fileId': fileId,
// 			'commentId': commentId
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };


// _.drive.createReply = function(fileId, commentId, content, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/replies/create
// 		var request = gapi.client.drive.replies.create({
// 			'fileId': fileId,
// 			'commentId': commentId,
// 			'fields': 'id',
// 			'resource': {
// 				'content': content
// 			}
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };

// _.drive.updateReply = function(fileId, commentId, replyId, content, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/replies/update
// 		var request = gapi.client.drive.replies.update({
// 			'fileId': fileId,
// 			'commentId': commentId,
// 		    'replyId': replyId,
// 		    'fields': 'id',
// 			'resource': {
// 				'content': content
// 			}
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };

// _.drive.deleteReply = function(fileId, commentId, replyId, successCallback, errorCallback) {
// 	_.drive.authenticateApiRequest(function() {
// 		// See https://developers.google.com/drive/v3/reference/replies/delete
// 		var request = gapi.client.drive.replies.delete({
// 			'fileId': fileId,
// 			'commentId': commentId,
// 		    'replyId': replyId
// 		});

// 		request.execute(function(resp) {
// 			if (resp.error) {
// 				errorCallback(resp.error);
// 			}
// 			else {
// 				successCallback(resp);
// 			}
// 		});
// 	}, errorCallback);
// };
