if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.annotator_annotations = {};

_.components.misc.annotator_annotations.componentId = "id_annotator_annotations";
_.components.misc.annotator_annotations.fileId = null;
_.components.misc.annotator_annotations.comments = null;
_.components.misc.annotator_annotations.currentTime = null;
_.components.misc.annotator_annotations.showAll = null;
_.components.misc.annotator_annotations.canAnnotate = true;

_.components.misc.annotator_annotations.init = function() {
	this.reset();
};

_.components.misc.annotator_annotations.reset = function() {
	this.fileId = null;
	this.annotations = null;
	this.currentTime = null;
	this.showAll = null;
	this.canAnnotate = true;
	
	$("#"+this.componentId+"_items").empty();
};

_.components.misc.annotator_annotations.setupAnnotations = function(fileId, canAnnotate) {
	this.fileId = fileId;
	this.canAnnotate = canAnnotate;
	_.components.misc.annotator_annotations.refreshComments();
};

_.components.misc.annotator_annotations.refreshComments = function() {
	var thisComponent = this;
	
	thisComponent.showLoadingOverlay();
	
	thisComponent.loadFileComments(function() {
		thisComponent.resetDom();

		thisComponent.hideLoadingOverlay();
	});
};

_.components.misc.annotator_annotations.showLoadingOverlay = function() {
	$("#"+this.componentId+"_loading_overlay:hidden").fadeIn();
};

_.components.misc.annotator_annotations.hideLoadingOverlay = function() {
	$("#"+this.componentId+"_loading_overlay").fadeOut();
};

_.components.misc.annotator_annotations.updateOverlayPosition = function(top) {
	$("#"+this.componentId+"_loading_overlay").css({
		"top": top + "px",
		"bottom": "-" + top + "px"
	});
};

_.components.misc.annotator_annotations.loadFileComments = function(callback) {
	var thisComponent = this;

	_.drive.listFileComments(thisComponent.fileId, function(comments) {
		// filter out non-annotation comments
		thisComponent.comments = $.grep(comments, function(comment, index) {
			if (comment && comment.quotedFileContent) {
				return comment.quotedFileContent.value == "Annotation";
			}
			else {
				return false;
			}
		});
		
		// parse start time and duration
		$.each(thisComponent.comments, function(key, comment) {
			var parsedContent = _.components.modals.annotator.parseAnnotatorCommentContent(comment.content);
			comment.content = parsedContent.content;
			comment.startTime = parsedContent.startTime;
			comment.duration = parsedContent.duration;
			
			// sort replies on created date
			comment.replies.sort(function(replyA, replyB) {
				return (replyA.createdTime < replyB.createdTime) ? -1 : ((replyA.createdTime > replyB.createdTime) ? 1 : 0);
			});
		});
		
		// sort by startTime then createdTime
		thisComponent.comments.sort(function(commentA, commentB) {
			if (commentA.startTime == commentB.startTime) {
				return (commentA.createdTime < commentB.createdTime) ? -1 : ((commentA.createdTime > commentB.createdTime) ? 1 : 0);
			}
			else {
				return commentA.startTime < commentB.startTime ? -1 : 1;
			}
		});
		
		callback();
	}, function(error) {
		_.drive.errorAlert(error);

		thisComponent.hideLoadingOverlay();
	});
};

_.components.misc.annotator_annotations.updateVisibleAnnotations = function(currentTime, showAll, animate, forceUpdate) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;
	
	// only update when second or playback state changes
	if (forceUpdate || thisComponent.currentTime != Math.floor(currentTime) || thisComponent.showAll != showAll) {
		thisComponent.currentTime = Math.floor(currentTime);
		thisComponent.showAll = showAll;
		
		_.components.misc.annotator_write_annotation.updateCurrentStartTime(thisComponent.currentTime);
		
		var lastVisible = null;
		
		$("#"+componentId+"_items .annotation_comment").each(function() {
			var annotationObj = $(this);
			var visible = false;
			
			if (thisComponent.showAll) {
				visible = true;
			}
			else {
				var startTime = parseInt(annotationObj.data("start-time"));
				if (isNaN(startTime) || startTime < 0) { startTime = 0; }
				
				var duration = parseInt(annotationObj.data("duration"));
				if (isNaN(duration) || duration < 1) { duration = 1; }
				
				if (startTime <= thisComponent.currentTime && (startTime + duration) > thisComponent.currentTime) {
					visible = true;
				}
			}

			if (visible) {
                annotationObj.removeData("disabled");
				animate ? annotationObj.stop().slideDown(250) : annotationObj.stop().show();
			}
			else if (!visible) {
                annotationObj.data("disabled", true);
				animate ? annotationObj.stop().slideUp(250) : annotationObj.stop().hide();
			}
			
			if (visible) {
				lastVisible = annotationObj;
				annotationObj.find(".seperator").show();
			}
		});

		// hide last seperator
		if (lastVisible) {
			lastVisible.find(".seperator").hide();
		}
	}
};

_.components.misc.annotator_annotations.resetDom = function() {
	var thisComponent = this;
	
	$("#"+this.componentId+"_items").empty();
	
	var startTimeArray = [];
	
	$.each(this.comments, function(key, comment) {
		thisComponent.createCommentDom(comment);
		
		if (startTimeArray.indexOf(comment.startTime) < 0) {
			startTimeArray.push(comment.startTime);
		}
	});
	
	_.components.misc.annotator_media_playback.setAnnotations(startTimeArray);
	
	// hide last seperator
	$("#"+this.componentId+"_items .annotation_comment").last().find(".seperator").hide();
	
	// if Dom reset in the middle of playback, only show relevent annotations
	if (this.currentTime != null && this.showAll != null) {
		this.updateVisibleAnnotations(this.currentTime, this.showAll, false, true);
	}
};

_.components.misc.annotator_annotations.createCommentDom = function(comment) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;

	var author = comment.author;
	var isReplyOnly = !author.me;
	var displayName = author.me ? $("#"+thisComponent.componentId).data("current-user-name") : author.displayName;
	
	var newComment = $('#'+componentId+'_dummy_comment').clone().removeClass("hidden").removeAttr("id");
	newComment.data("start-time", comment.startTime);
	newComment.data("duration", comment.duration);
	
	newComment.find(".annotation_time").text(_.components.modals.annotator.secondsToTimeString(comment.startTime));
	newComment.find(".annotation_author_name").text(displayName);
	newComment.find(".annotation_content").text(comment.content);
	if (author.photoLink) {
		newComment.find(".annotation_author_photo").html('<img src="'+author.photoLink+'"/>');
	}
    
    if (!thisComponent.canAnnotate) {
        newComment.find(".item-buttons").remove();
    } 
    else if (isReplyOnly) {
		newComment.find(".item-buttons .owner_only").remove();
	}
	else {
		newComment.find(".item-buttons .owner_only").removeClass("owner_only");
	}
	
	newComment.find(".item-buttons .icon-edit").click(function(e) {
		e.preventDefault();
        
        // disable button when being animated hidden
        if (newComment.data("disabled")) { return; }
        
        thisComponent.clearAnnotationHighlights();
        $(newComment).find(" > .annotation").addClass("highlight");

		_.components.misc.annotator_write_annotation.showEditComment(comment.id, comment.content, comment.startTime, comment.duration);
	});
	
	newComment.find(".item-buttons .icon-reply").click(function(e) {
		e.preventDefault();
        
        // disable button when being animated hidden
        if (newComment.data("disabled")) { return; }
        
        thisComponent.clearAnnotationHighlights();
        $(newComment).find(" > .annotation").addClass("highlight");
		
		_.components.misc.annotator_write_annotation.showNewReply(comment.id);
	});
	
	newComment.find(".item-buttons .icon-delete").click(function(e) {
		e.preventDefault();
        
        // disable button when being animated hidden
        if (newComment.data("disabled")) { return; }
		
		_.components.misc.annotator_annotations.showLoadingOverlay();
		
		_.components.misc.annotator_write_annotation.cancelEditOnCommentDelete(comment.id);

		_.drive.deleteComment(thisComponent.fileId, comment.id, function(result) {
			thisComponent.refreshComments();
		}, function(error) {
			// error is not found (already deleted), ignore
			if (error && error.code && error.code == 404) {
				thisComponent.refreshComments();
			}
			else {
				_.drive.errorAlert(error);
			}
		});
	});

	$("#"+componentId+"_items").append(newComment);
	
	$.each(comment.replies, function(key, reply) {
		thisComponent.createReplyDom(newComment, comment.id, reply);
	});
};


_.components.misc.annotator_annotations.createReplyDom = function(commentDom, commentId, reply) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;
	
	var author = reply.author;
	var isReplyOnly = !author.me;
	var displayName = author.me ? $("#"+thisComponent.componentId).data("current-user-name") : author.displayName;
	
	var newReply = $('#'+componentId+'_dummy_reply').clone().removeClass("hidden");
	newReply.find(".annotation_author_name").text(displayName);
	newReply.find(".annotation_content").text(reply.content);
	if (author.photoLink) {
		newReply.find(".annotation_author_photo").html('<img src="'+author.photoLink+'"/>');
	}
	
	if (!thisComponent.canAnnotate) {
        newReply.find(".item-buttons").remove();
	}
    else if (isReplyOnly) {
		newReply.find(".item-buttons .owner_only").remove();
    }
	else {
		newReply.find(".item-buttons .owner_only").removeClass("owner_only");
	}
	
	newReply.find(".item-buttons .icon-edit").click(function(e) {
		e.preventDefault();
        
        // disable button when being animated hidden
        if (newReply.parents(".annotation_comment").data("disabled")) { return; }
        
        thisComponent.clearAnnotationHighlights();
        $(newReply).find(" > .annotation").addClass("highlight");

		_.components.misc.annotator_write_annotation.showEditReply(commentId, reply.id, reply.content);
	});
	
	newReply.find(".item-buttons .icon-delete").click(function(e) {
		e.preventDefault();
        
        // disable button when being animated hidden
        if (newReply.parents(".annotation_comment").data("disabled")) { return; }
		
		_.components.misc.annotator_annotations.showLoadingOverlay();
		
		_.components.misc.annotator_write_annotation.cancelEditOnReplyDelete(commentId, reply.id);

		_.drive.deleteReply(thisComponent.fileId, commentId, reply.id, function(result) {
			thisComponent.refreshComments();
		}, function(error) {
			// error is not found (already deleted), ignore
			if (error && error.code && error.code == 404) {
				thisComponent.refreshComments();
			}
			else {
				_.drive.errorAlert(error);
			}
		});
	});

	$(commentDom).find(".reply_items").append(newReply);
};

_.components.misc.annotator_annotations.clearAnnotationHighlights = function() {
    $("#"+this.componentId+"_items .annotation.highlight").removeClass("highlight");
};