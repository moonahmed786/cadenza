if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.comments = {};

_.components.misc.comments.init = function() {
	var thisComponent = this;
	
	var all_comments = $(".comment-item");
	$(all_comments).each(function( index ) {
		var jqObj = $(this);
		var itemId = $(jqObj).attr('id');
		
		thisComponent.initComment(itemId);
	});
};

_.components.misc.comments.initModalComments = function(modalId, readOnly) {
	var thisComponent = this;
	
	var all_modal_comments = $("#"+modalId+" .comment-item");
	$(all_modal_comments).each(function( index ) {
		var jqObj = $(this);
		var itemId = $(jqObj).attr('id');
		
		thisComponent.initComment(itemId);
	});
};

_.components.misc.comments.initComment = function(itemId) {
	var thisComponent = this;
	var commentId = $("#"+itemId).data("comment-id");
	var ref = $("#"+itemId).data('ref');
	var refId = $("#"+itemId).data('ref-id');
	
	if (commentId == "new") {
		// change textarea styling for new comments
		$('#'+itemId+' .comment-edit .item-text').removeClass("autosize-js").attr("rows", 3);
		
		// Save button
		$('#'+itemId+' .item-buttons .icon-save').off("click");
		$('#'+itemId+' .item-buttons .icon-save').click(function(e) {
			var jqObj = $(this);
			e.preventDefault();
			jqObj.blur();
			if (!_.page.isAjaxInProgress) {
				jqObj.html(_.btnIconHtml('loading'));
				
				data = {
					ref: ref,
					ref_id: refId,
					comment_text: thisComponent.getCommentsFormItemEditText(itemId)
				};
				
				_.page.actionPost('addComment', data, function(response) {
					jqObj.html(_.btnIconHtml('save'));
					if (response.added) {
						var newCommentHTML = $(response.html);
						var newItemId = $(newCommentHTML).attr('id');
						$('#'+itemId).before(newCommentHTML);
						
						thisComponent.initComment(newItemId);
						thisComponent.setCommentsFormItemEditText(itemId, "");
                        // IE HACK: Cannot use autosize js with IE 11 do to scrollbars issues
                        if (!_.page.isIE()) {
						    autosize.update($('#'+itemId+' .comment-edit .item-text'));
                        }
						thisComponent.showHideEmptyCommentsMessage(ref, refId);
					}
					if (response.refresh) {
		            	_.page.refreshComponents(response.refresh);
		            }
				});
			}
		});
	}
	else {
		// only autosize existing comments
        // IE HACK: Cannot use autosize js with IE 11 do to scrollbars issues
        if (!_.page.isIE()) {
		    autosize($('#'+itemId+' .comment-edit .item-text'));
        }
        else {
            $('#'+itemId+' .comment-edit .item-text').removeClass("autosize-js").attr("rows", 3);
        }
		
		// Edit button
		$('#'+itemId+' .item-buttons .icon-edit').off("click");
		$('#'+itemId+' .item-buttons .icon-edit').click(function(e) {
			var jqObj = $(this);
			e.preventDefault();
			jqObj.blur();
			thisComponent.setCommentsFormItemEditTextToSavedText(itemId);
			$('#'+itemId+' .comment-saved').addClass("hidden");
			$('#'+itemId+' .comment-edit').removeClass("hidden");
			
			// must update autosize here since element must have fixed width or not "display: none"
            // IE HACK: Cannot use autosize js with IE 11 do to scrollbars issues
            if (!_.page.isIE()) {
			    autosize.update($('#'+itemId+' .comment-edit .item-text'));
            }
		});
		// Delete button
		$('#'+itemId+' .item-buttons .icon-delete').off("click");
		$('#'+itemId+' .item-buttons .icon-delete').click(function(e) {
			var jqObj = $(this);
			e.preventDefault();
			jqObj.blur();
			if (!_.page.isAjaxInProgress) {
				jqObj.html(_.btnIconHtml('loading'));
				
				data = {
					comment_id:commentId
				};
				
				_.page.actionPost('deleteComment', data, function(response) {
					jqObj.html(_.btnIconHtml('delete'));
					jqObj.closest(".comment-item").remove();
					thisComponent.showHideEmptyCommentsMessage(ref, refId);
					if (response.refresh) {
		            	_.page.refreshComponents(response.refresh);
		            }
				});
			}
		});
		// Save button
		$('#'+itemId+' .item-buttons .icon-save').off("click");
		$('#'+itemId+' .item-buttons .icon-save').click(function(e) {
			var jqObj = $(this);
			e.preventDefault();
			jqObj.blur();
			if (!_.page.isAjaxInProgress) {
				jqObj.html(_.btnIconHtml('loading'));
				
				data = {
					comment_id:commentId,
					comment_text:thisComponent.getCommentsFormItemEditText(itemId)
				};
				
				_.page.actionPost('updateComment', data, function(response) {
					jqObj.html(_.btnIconHtml('save'));
					if (response.updated) {
						thisComponent.setCommentsFormItemEditText(itemId, "");
						thisComponent.setCommentsFormItemSavedText(itemId, response.savedCommentText);
						
						$('#'+itemId+' .comment-edit').addClass("hidden");
						$('#'+itemId+' .comment-saved').removeClass("hidden");
						thisComponent.showHideEmptyCommentsMessage(ref, refId);
					}
				});
			}
		});
	}
};

_.components.misc.comments.showHideEmptyCommentsMessage = function(ref, refId) {
	var readonly = $(".comments-form[data-ref='"+ref+"'][data-ref-id='"+refId+"']").data("readonly") == "true";
	var comment_count = $(".comments-form[data-ref='"+ref+"'][data-ref-id='"+refId+"'] .comment-item").length;
	var emptyMessage = $(".comments-form[data-ref='"+ref+"'][data-ref-id='"+refId+"'] .empty-message");

	// show empty message onyl when readonly and ther eare no comments
	if (readonly && comment_count <= 0) {
		$(emptyMessage).removeClass("hidden");
	}
	else {
		$(emptyMessage).addClass("hidden");
	}
};

_.components.misc.comments.getCommentsFormItemEditText = function(itemId) {
	return $('#'+itemId+' .comment-edit .item-text').val().trim();
};
_.components.misc.comments.setCommentsFormItemEditText = function(itemId, text) {
	$('#'+itemId+' .comment-edit .item-text').val(text);
};
_.components.misc.comments.setCommentsFormItemEditTextToSavedText = function(itemId) {
	$('#'+itemId+' .comment-edit .item-text').val($('#'+itemId+' .comment-saved .item-text').text().trim());
};

_.components.misc.comments.getCommentsFormItemSavedText = function(itemId) {
	return $('#'+itemId+' .comment-saved .item-text').text().trim();
};
_.components.misc.comments.setCommentsFormItemSavedText = function(itemId, text) {
	$('#'+itemId+' .comment-saved .item-text').text(text);
};