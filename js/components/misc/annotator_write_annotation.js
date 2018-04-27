if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.annotator_write_annotation = {};

_.components.misc.annotator_write_annotation.componentId = "id_annotator_write_annotation";
_.components.misc.annotator_write_annotation.fileId = null;
_.components.misc.annotator_write_annotation.currentCommentId = null;
_.components.misc.annotator_write_annotation.currentCommentStartTime = null;
_.components.misc.annotator_write_annotation.currentReplyId = null;
_.components.misc.annotator_write_annotation.causedPlaybackPause = null;
_.components.misc.annotator_write_annotation.canAnnotate = true;

_.components.misc.annotator_write_annotation.init = function() {
	this.reset();
};

_.components.misc.annotator_write_annotation.reset = function() {
	this.fileId = null;
	this.currentCommentId = null;
	this.currentCommentStartTime = null;
	this.currentReplyId = null;
	this.causedPlaybackPause = null;
	this.canAnnotate = true;

	this.resetCommentSection();
	this.resetReplySection();

	$("#"+this.componentId+" .new_comment_button_container").removeClass("hidden");
	$("#"+this.componentId+" .write_comment_container").addClass("hidden");
	$("#"+this.componentId+" .write_reply_container").addClass("hidden");
    $("#"+this.componentId).css({visibility: null});
};

_.components.misc.annotator_write_annotation.resetCommentSection = function() {
	$("#"+this.componentId+" .write_comment .icon-save").removeClass("disabled");
	$("#"+this.componentId+" .write_comment .text").val("").prop("disabled", false);
	$("#"+this.componentId+" .write_comment .duration").val(5).prop("disabled", false);
	
	var currentTime = Math.floor(_.components.misc.annotator_media_playback.setCurrentTime());
	
	if (isNaN(currentTime)) {
		this.updateCurrentStartTime(0);
	}
	else {
		this.updateCurrentStartTime(currentTime);
	}
};

_.components.misc.annotator_write_annotation.resetReplySection = function() {
	$("#"+this.componentId+" .write_reply .icon-save").removeClass("disabled");
	$("#"+this.componentId+" .write_reply .text").val("").prop("disabled", false);
};

_.components.misc.annotator_write_annotation.cancelEditOnCommentDelete = function(commentId) {
	if (this.currentCommentId == commentId) {
		this.showNewCommentButton();
		this.beginPlaybackIfCausedPause();
	}
};

_.components.misc.annotator_write_annotation.cancelEditOnReplyDelete = function(commentId, replyId) {
	if (this.currentCommentId == commentId && this.currentReplyId == replyId) {
		this.showNewCommentButton();
		this.beginPlaybackIfCausedPause();
	}
};

_.components.misc.annotator_write_annotation.setup = function(fileId, canAnnotate) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;

	thisComponent.fileId = fileId;
	thisComponent.canAnnotate = canAnnotate;
    
    if (!thisComponent.canAnnotate) {
        $("#"+componentId).css({visibility: "hidden"});
    }
    else {
        $("#"+componentId).css({visibility: null});
    }
	
	// new comment button
	$("#"+componentId+" .new_comment_button .btn").off("click");
	$("#"+componentId+" .new_comment_button .btn").click(function(e) {
		e.preventDefault();
		thisComponent.showNewComment();
	});
	// new comment text
	$("#"+componentId+" .new_comment_button .action-label").off("click");
	$("#"+componentId+" .new_comment_button .action-label").click(function(e) {
		e.preventDefault();
		thisComponent.showNewComment();
	});
    
    
	// create/edit comment duration sanatization
	$("#"+componentId+" .write_comment .input-num").off("change");
	$("#"+componentId+" .write_comment .input-num").change(function(e) {
		var num = parseInt($(this).val());
		// Ensure num is valid
		if (isNaN(num)) {
            $(this).val(5);
		}
		else {
            if (num < 1) {
                num = 1;
            }
            else if (num > 60) {
                num = 60;
            }
            if (""+num != $(this).val()) {
                $(this).val(num);
            }
        }
	});

	// create/edit comment
	$("#"+componentId+" .write_comment .icon-save").off("click");
	$("#"+componentId+" .write_comment .icon-save").click(function(e) {
		e.preventDefault();
		var saveButtonObject = this;
		$(saveButtonObject).addClass("disabled");
		
		var content = $("#"+componentId+" .write_comment .text").val().trim();
		
		var currentPlaybackTime = _.components.misc.annotator_media_playback.getCurrentTime();
		if (currentPlaybackTime == null && thisComponent.currentCommentId) {
			// this happens when editing a comment while the media player hasn't been interacted with yet (before user clicks it)
			currentPlaybackTime = this.currentCommentStartTime;
		}
		var startTime = currentPlaybackTime ? Math.floor(currentPlaybackTime) : 0;
		
		var durationString = $("#"+componentId+" .write_comment .duration").val();
		var duration = _.components.modals.annotator.parseCommentDuration(durationString);
		
		var quotedFileContent = {
			'value': "Annotation"
		};
		
		var annotationCommentContent = _.components.modals.annotator.getContentString(content, startTime, duration);
		
		var completeCallback = function() {
			$(saveButtonObject).removeClass("disabled");
			thisComponent.showNewCommentButton();
            _.components.misc.annotator_annotations.clearAnnotationHighlights();
			_.components.misc.annotator_annotations.refreshComments();

			if (!thisComponent.currentCommentId) {
				// create new annotation notification in Cadenza
				_.components.modals.annotator.sendNewAnnotationNotification();
			}
		};
		
		var errorCallback = function(error) {
			_.drive.errorAlert(error);
			_.components.misc.annotator_annotations.hideLoadingOverlay();
		};
		
		_.components.misc.annotator_annotations.showLoadingOverlay();
		$("#"+componentId+" .write_comment .text").prop("disabled", true);
		$("#"+componentId+" .write_comment .duration").prop("disabled", true);
		
		if (thisComponent.currentCommentId) {
			_.drive.updateComment(thisComponent.fileId, thisComponent.currentCommentId, annotationCommentContent, completeCallback, errorCallback);
		}
		else {
			_.drive.createComment(thisComponent.fileId, annotationCommentContent, quotedFileContent, completeCallback, errorCallback);
		}
		thisComponent.beginPlaybackIfCausedPause();
	});

	$("#"+componentId+" .write_comment .icon-close-circle").off("click");
	$("#"+componentId+" .write_comment .icon-close-circle").click(function(e) {
		e.preventDefault();
		thisComponent.showNewCommentButton();
		thisComponent.beginPlaybackIfCausedPause();
        _.components.misc.annotator_annotations.clearAnnotationHighlights();
	});

	// create/edit reply
	$("#"+componentId+" .write_reply .icon-save").off("click");
	$("#"+componentId+" .write_reply .icon-save").click(function(e) {
		e.preventDefault();
		var saveButtonObject = this;
		$(saveButtonObject).addClass("disabled");

		var content = $("#"+componentId+" .write_reply .text").val().trim();

		var completeCallback = function() {
			$(saveButtonObject).removeClass("disabled");
			thisComponent.showNewCommentButton();
            _.components.misc.annotator_annotations.clearAnnotationHighlights();
			_.components.misc.annotator_annotations.refreshComments();

			if (!thisComponent.currentReplyId) {
				// create new annotation notification in Cadenza
				_.components.modals.annotator.sendNewAnnotationNotification();
			}
		};
		
		var errorCallback = function(error) {
			_.drive.errorAlert(error);
			_.components.misc.annotator_annotations.hideLoadingOverlay();
		};

		_.components.misc.annotator_annotations.showLoadingOverlay();
		$("#"+componentId+" .write_reply .text").prop("disabled", true);
		
		if (thisComponent.currentReplyId) {
			_.drive.updateReply(thisComponent.fileId, thisComponent.currentCommentId, thisComponent.currentReplyId, content, completeCallback, errorCallback);
		}
		else {
			_.drive.createReply(thisComponent.fileId, thisComponent.currentCommentId, content, completeCallback, errorCallback);
		}
		thisComponent.beginPlaybackIfCausedPause();
	});

	$("#"+componentId+" .write_reply .icon-close-circle").off("click");
	$("#"+componentId+" .write_reply .icon-close-circle").click(function(e) {
		e.preventDefault();
		thisComponent.showNewCommentButton();
		thisComponent.beginPlaybackIfCausedPause();
        _.components.misc.annotator_annotations.clearAnnotationHighlights();
	});
};

_.components.misc.annotator_write_annotation.updateCurrentStartTime = function(currentTime) {
	var startTimeString = _.components.modals.annotator.secondsToTimeString(currentTime);
	
	$("#"+this.componentId+" .write_comment .start-time").val(startTimeString);
};

_.components.misc.annotator_write_annotation.beginPlaybackIfCausedPause = function() {
	if (this.causedPlaybackPause) {
		_.components.misc.annotator_media_playback.play();
		this.causedPlaybackPause = null;
	}
};

_.components.misc.annotator_write_annotation.focusOnTextarea = function(type) {
    var componentId = this.componentId;
    // must update focus after click event is finished
    setTimeout(function() {
        $("#"+componentId+" ."+type+" .text").focus();
    }, 0);
};

_.components.misc.annotator_write_annotation.showNewCommentButton = function() {
	this.currentCommentId = null;
	this.currentCommentStartTime = null;
	this.currentReplyId = null;
	this.causedPlaybackPause = null;
	
	$("#"+this.componentId+" .new_comment_button_container").removeClass("hidden");
	$("#"+this.componentId+" .write_comment_container").addClass("hidden");
	$("#"+this.componentId+" .write_reply_container").addClass("hidden");
};

_.components.misc.annotator_write_annotation.showNewComment = function() {
	// Note: comments are time sensitive so pause on new
	var causedPause = _.components.misc.annotator_media_playback.isPlaying();
	_.components.misc.annotator_media_playback.pause();
	
	this.resetCommentSection();
	this.currentCommentId = null;
	this.currentCommentStartTime = null;
	this.currentReplyId = null;
	this.causedPlaybackPause = causedPause;

	$("#"+this.componentId+" .write_comment .action-label .pre-line").text($("#"+this.componentId+" .write_comment .action-label").data("new-message"));
    this.focusOnTextarea("write_comment");
	
	$("#"+this.componentId+" .new_comment_button_container").addClass("hidden");
	$("#"+this.componentId+" .write_comment_container").removeClass("hidden");
	$("#"+this.componentId+" .write_reply_container").addClass("hidden");
};

_.components.misc.annotator_write_annotation.showEditComment = function(commentId, content, startTime, duration) {
	// Note: comments are time sensitive so pause and seek to time on edit
	var causedPause = _.components.misc.annotator_media_playback.isPlaying();
	_.components.misc.annotator_media_playback.pause();
	_.components.misc.annotator_media_playback.setCurrentTime(startTime);
	
	this.resetCommentSection();
	this.currentCommentId = commentId;
	this.currentCommentStartTime = startTime;
	this.currentReplyId = null;
	this.causedPlaybackPause = causedPause;

	$("#"+this.componentId+" .write_comment .action-label .pre-line").text($("#"+this.componentId+" .write_comment .action-label").data("edit-message"));
	$("#"+this.componentId+" .write_comment .text").val(content);
	$("#"+this.componentId+" .write_comment .duration").val(duration);
    this.focusOnTextarea("write_comment");
	
	$("#"+this.componentId+" .new_comment_button_container").addClass("hidden");
	$("#"+this.componentId+" .write_comment_container").removeClass("hidden");
	$("#"+this.componentId+" .write_reply_container").addClass("hidden");
};

_.components.misc.annotator_write_annotation.showNewReply = function(commentId) {
	// Note: replies are not time sensitive, will pause to be consistent with comments
	var causedPause = _.components.misc.annotator_media_playback.isPlaying();
	_.components.misc.annotator_media_playback.pause();
	
	this.resetReplySection();
	this.currentCommentId = commentId;
	this.currentCommentStartTime = null;
	this.currentReplyId = null;
	this.causedPlaybackPause = causedPause;

	$("#"+this.componentId+" .write_reply .action-label .pre-line").text($("#"+this.componentId+" .write_reply .action-label").data("new-message"));
    this.focusOnTextarea("write_reply");
	
	$("#"+this.componentId+" .new_comment_button_container").addClass("hidden");
	$("#"+this.componentId+" .write_comment_container").addClass("hidden");
	$("#"+this.componentId+" .write_reply_container").removeClass("hidden");
};

_.components.misc.annotator_write_annotation.showEditReply = function(commentId, replyId, content) {
	// Note: replies are not time sensitive, will pause to be consistent with comments
	var causedPause = _.components.misc.annotator_media_playback.isPlaying();
	_.components.misc.annotator_media_playback.pause();
	
	this.resetReplySection();
	this.currentCommentId = commentId;
	this.currentCommentStartTime = null;
	this.currentReplyId = replyId;
	this.causedPlaybackPause = causedPause;

	$("#"+this.componentId+" .write_reply .action-label .pre-line").text($("#"+this.componentId+" .write_reply .action-label").data("edit-message"));
	$("#"+this.componentId+" .write_reply .text").val(content);
    this.focusOnTextarea("write_reply");
	
	$("#"+this.componentId+" .new_comment_button_container").addClass("hidden");
	$("#"+this.componentId+" .write_comment_container").addClass("hidden");
	$("#"+this.componentId+" .write_reply_container").removeClass("hidden");
};