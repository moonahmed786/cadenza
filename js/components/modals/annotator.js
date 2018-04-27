if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.annotator = {};

_.components.modals.annotator.componentId = "id_modal_annotator";
_.components.modals.annotator.practiceId = null;
_.components.modals.annotator.fileId = null;
_.components.modals.annotator.fileName = null;
_.components.modals.annotator.file = null;
_.components.modals.annotator.isPracticing = false;
_.components.modals.annotator.canAnnotate = true;

_.components.modals.annotator.init = function() {
	this.reset();
};

_.components.modals.annotator.reset = function() {
	this.practiceId = null;
	this.fileId = null;
	this.fileName = null;
	this.file = null;
	this.isPracticing = false;
	this.canAnnotate = true;
	
	_.components.misc.annotator_annotations.reset();
	_.components.misc.annotator_media_playback.reset();
	_.components.misc.annotator_write_annotation.reset();
};

_.components.modals.annotator.setupNewAnnotator = function(practiceId, fileId, fileName, isPracticing, canAnnotate, readyCallback) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;
	thisComponent.practiceId = practiceId;
	thisComponent.fileId = fileId;
	thisComponent.fileName = fileName;
	thisComponent.isPracticing = isPracticing;
	thisComponent.canAnnotate = canAnnotate;

	$("#"+this.componentId+" .right-side-content").off("scroll");
	$("#"+this.componentId+" .right-side-content").scroll(function() {
		_.components.misc.annotator_annotations.updateOverlayPosition($(this).scrollTop());
	});

	thisComponent.loadFileData(function() {
		// setup own data
		$("#"+componentId+"_title").text(thisComponent.file.name);
		$("#"+componentId+"_created_date").text(_.drive.driveDateStringToCadenzaDateString(thisComponent.file.createdTime));
		
		// setup playback
		_.components.misc.annotator_media_playback.setupNewMediaPlayer(thisComponent.file);
		
		// annotator is ready to display
		readyCallback();
	}, function() {
		// setup own data
		$("#"+componentId+"_title").text(thisComponent.fileName);
		$("#"+componentId+"_created_date").text($("#"+componentId+"_created_date_no_content").text());
		
		// setup playback
		_.components.misc.annotator_media_playback.setupNewMediaPlayerWithError();
		
		// annotator is ready to display
		readyCallback();
	});
	
	_.components.misc.annotator_write_annotation.setup(thisComponent.fileId, thisComponent.canAnnotate);
	
	// fetching annotations can be done as a standalone operation.
	_.components.misc.annotator_annotations.setupAnnotations(thisComponent.fileId, thisComponent.canAnnotate);
};

_.components.modals.annotator.loadFileData = function(successCallback, errorCallback) {
	var thisComponent = this;
	var fields = "id, name, createdTime, mimeType, webContentLink, webViewLink";
	_.drive.fetchFile(thisComponent.fileId, fields, function(file) {
		thisComponent.file = file;
		thisComponent.fileName = file.name;
		successCallback();
	}, function(error) {
		errorCallback();
	});
};



_.components.modals.annotator.secondsToTimeString = function(seconds) {
	// NOTE: using the time format used in Notemaker aka, apple's approach. Display "minutes:seconds"
	
	var secondsRemainder = seconds % 60;
	var secondsString = secondsRemainder < 10 ? "0"+secondsRemainder  : ""+secondsRemainder;
	
	var minutes = Math.floor(seconds / 60);
	var minutesString = ""+minutes;
	
	return minutesString+":"+secondsString;
};

_.components.modals.annotator.getContentString = function(content, startTime, duration) {
    return "Display Annotation at: "+startTime+" seconds(s)\nDisplay for: "+duration+" seconds(s)\n"+content;
};

_.components.modals.annotator.parseAnnotatorCommentContent = function(content) {
	var results = {
		'content': "",
		'startTime': 0,
		'duration': 5
	};
	var contentArray = content.split("\n");
	
	if (contentArray.length >= 3) {
		var startTime = contentArray.shift().replace(/\D/g,'');
		results.startTime = this.parseCommentStartTime(startTime);
		
		var duration = contentArray.shift().replace(/\D/g,'');
		results.duration = this.parseCommentDuration(duration);
		
		results.content = contentArray.join("\n");
	}
	
	return results;
};

_.components.modals.annotator.parseCommentStartTime = function(startTimeString) {
	var startTime = Math.floor(parseInt(startTimeString));
	
	if (isNaN(startTime)) {
		startTime = 0;
	}
	else if (startTime < 0) {
		startTime = 0;
	}
	
	return startTime;
};

_.components.modals.annotator.parseCommentDuration = function(durationString) {
	var duration = Math.floor(parseInt(durationString));
	
	if (isNaN(duration)) {
		duration = 5;
	}
	else if (duration < 1) {
		duration = 1;
	}
	else if (duration > 60) {
		duration = 60;
	}
	
	return duration;
};

_.components.modals.annotator.sendNewAnnotationNotification = function() {
	var data = { practice_id: this.practiceId };
	var action = this.isPracticing ? 'addAnnotationNotificationPracticing' : 'addAnnotationNotification';
	_.page.actionPost(action, data, function(response) {
		// do nothing
	});
};

_.components.modals.annotator.open = function(practiceId, fileId, fileName, isPracticing, canAnnotate, readyCallback) {
	var thisComponent = this;
	thisComponent.setupNewAnnotator(practiceId, fileId, fileName, isPracticing, canAnnotate, function() {
		readyCallback();
		
		$('#'+thisComponent.componentId).modal({
			backdrop:'static'
		}).on('hidden.bs.modal', function(e) {
			thisComponent.reset();
		});
	});
};

_.components.modals.annotator.close = function() {
	$('#'+this.componentId).modal('hide');
};
