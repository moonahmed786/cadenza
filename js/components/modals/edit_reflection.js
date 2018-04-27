if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.edit_reflection = {};

// NOTE: This js component depends on the following other js components
// - ratings/edit_lesson_reflection

_.components.modals.edit_reflection.modalId = 'id_modal_edit_reflection';
_.components.modals.edit_reflection.lessonId;
_.components.modals.edit_reflection.isClosing;

_.components.modals.edit_reflection.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var refreshBtnId = componentId + '_refresh_btn';
	var editReflectionIndexBtnId = componentId + '_edit_reflection_index_btn';
	var saveReflectionIndexBtnId = componentId + '_save_reflection_index_btn';
	var editReflectionTextBtnId = componentId + '_edit_reflection_text_btn';
	var saveReflectionTextBtnId = componentId + '_save_reflection_text_btn';
	var data;
	
	thisComponent.lessonId = null; // lessonId will be set in setModalData
	thisComponent.isClosing = false;
	
	// Edit Reflection Index button
	$('#'+editReflectionIndexBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.showReflectionIndexEdit();
	});
	
	// Save Reflection Index button
	$('#'+saveReflectionIndexBtnId).click(function(e) {
		var jqObj = $(this);
		var reflectionIndex = _.components.ratings.edit_lesson_reflection.getSelectedReflectionIndex();
		e.preventDefault();
		jqObj.blur();
		
		jqObj.html(_.btnIconHtml('loading'));
		data = {
			lesson_id:thisComponent.lessonId,
			reflection_index:reflectionIndex
		};
		_.page.actionPost('saveLessonReflectionIndex', data, function(response) {
			if (response.refresh) {
				_.page.refreshComponents(response.refresh);
			}
			jqObj.html(_.btnIconHtml('save'));
			if (reflectionIndex != null) {
				thisComponent.showReflectionIndexSaved();
			}
		});
	});
	
	// Edit Reflection Text button
	$('#'+editReflectionTextBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.showReflectionTextEdit();
	});
	
	// Save Reflection Text button
	$('#'+saveReflectionTextBtnId).click(function(e) {
		var jqObj = $(this);
		var reflectionText = $('#'+componentId+'_text_edit').val();
		var reflectionPrompt = $('#'+componentId+'_prompt').text();
		e.preventDefault();
		jqObj.blur();
		
		jqObj.html(_.btnIconHtml('loading'));
		data = {
			lesson_id:thisComponent.lessonId,
			reflection_text:reflectionText,
			reflection_prompt:reflectionPrompt
		};
		_.page.actionPost('saveLessonReflectionText', data, function(response) {
			if (response.refresh) {
				_.page.refreshComponents(response.refresh);
			}
			$('#'+componentId+'_text_saved').text(reflectionText);
			jqObj.html(_.btnIconHtml('save'));
			if (reflectionText != "") {
				thisComponent.showReflectionTextSaved();
			}
		});
	});
	
	// Refresh Prompt button
	$('#'+refreshBtnId).click(function(e) {
		var jqObj = $(this);
		var reflectionPrompt = $('#'+componentId+'_prompt').text();
		e.preventDefault();
		jqObj.blur();
		
		if (!_.page.isAjaxInProgress) {
            jqObj.html(_.btnIconHtml('loading'));
            data = {
                lesson_id:thisComponent.lessonId,
                current_reflection_prompt:reflectionPrompt
            };
            
            _.page.actionPost('getNewRandomReflectionPrompt', data, function(response) {
                jqObj.html(_.btnIconHtml('refresh'));
                
                if (response.reflectionPromptRandom) {
                    $('#'+componentId+'_prompt').text(response.reflectionPromptRandom);
                }
            });
        }
	});
	
	// "X" Close button
	$('#'+componentId).on('hide.bs.modal', function (e) {
		var isReflectionIndexEdit = thisComponent.isReflectionIndexEdit();
		var isReflectionTextEdit = thisComponent.isReflectionTextEdit();
		var isCommentsNewHasText = thisComponent.isCommentsNewHasText();
		var isCommentsEdit = thisComponent.isCommentsEdit();
		if (!thisComponent.isClosing && (isReflectionIndexEdit || isReflectionTextEdit || isCommentsNewHasText || isCommentsEdit)) {
			if (!confirm(_.translate('confirm_close_modal'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		}
	});
};

_.components.modals.edit_reflection.setModalData = function(modalData) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	thisComponent.lessonId = modalData.lessonId;
	$('#'+componentId+'_lesson_title').text(modalData.lessonTitle);
	$('#'+componentId+'_lesson_targets').text(modalData.lessonTargets);
	$('#'+componentId+'_lesson_time_spent').text(modalData.lessonTimeSpent);
	_.components.ratings.edit_lesson_reflection.setSelectedReflectionIndex(modalData.reflectionIndex);
	if (modalData.reflectionIndex != null) {
		thisComponent.showReflectionIndexSaved();
	}
	else {
		thisComponent.showReflectionIndexEdit();
	}
	$('#'+componentId+'_prompt').text(modalData.reflectionPrompt != null ? modalData.reflectionPrompt : modalData.reflectionPromptRandom);
	$('#'+componentId+'_text_edit').val(modalData.reflectionText);
	$('#'+componentId+'_text_saved').text(modalData.reflectionText);
	if (modalData.reflectionText != "") {
		thisComponent.showReflectionTextSaved();
	}
	else {
		thisComponent.showReflectionTextEdit();
	}
	
	$('#'+componentId+'_lesson_comments').empty().html(modalData.commentsHTML);
	_.components.misc.comments.initModalComments(componentId);
};

_.components.modals.edit_reflection.isReflectionIndexEdit = function() {
	return _.components.ratings.edit_lesson_reflection.isShowEdit();
};
_.components.modals.edit_reflection.isReflectionTextEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	return !($('#'+componentId+'_text_edit').hasClass("hidden"));
};
_.components.modals.edit_reflection.isCommentsNewHasText = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	return ($('#'+componentId).find('.comment-item[data-ref="lesson"][data-comment-id="new"] textarea').val().trim() != "");
};
_.components.modals.edit_reflection.isCommentsEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	return ($('#'+componentId).find('.comment-edit').not('.hidden').length > 1);
};

_.components.modals.edit_reflection.showReflectionIndexSaved = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var editReflectionIndexBtnId = componentId + '_edit_reflection_index_btn';
	var saveReflectionIndexBtnId = componentId + '_save_reflection_index_btn';
	_.components.ratings.edit_lesson_reflection.showSaved();
	$('#'+saveReflectionIndexBtnId).addClass("hidden");
	$('#'+editReflectionIndexBtnId).removeClass("hidden");
};

_.components.modals.edit_reflection.showReflectionIndexEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var editReflectionIndexBtnId = componentId + '_edit_reflection_index_btn';
	var saveReflectionIndexBtnId = componentId + '_save_reflection_index_btn';
	_.components.ratings.edit_lesson_reflection.showEdit();
	$('#'+editReflectionIndexBtnId).addClass("hidden");
	$('#'+saveReflectionIndexBtnId).removeClass("hidden");
};

_.components.modals.edit_reflection.showReflectionTextSaved = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var refreshBtnId = componentId + '_refresh_btn';
	var editReflectionTextBtnId = componentId + '_edit_reflection_text_btn';
	var saveReflectionTextBtnId = componentId + '_save_reflection_text_btn';
	$('#'+componentId+'_text_edit').addClass("hidden");
	$('#'+componentId+'_text_saved').removeClass("hidden");
	$('#'+refreshBtnId).addClass("hidden");
	$('#'+saveReflectionTextBtnId).addClass("hidden");
	$('#'+editReflectionTextBtnId).removeClass("hidden");
};

_.components.modals.edit_reflection.showReflectionTextEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var refreshBtnId = componentId + '_refresh_btn';
	var editReflectionTextBtnId = componentId + '_edit_reflection_text_btn';
	var saveReflectionTextBtnId = componentId + '_save_reflection_text_btn';
	$('#'+componentId+'_text_saved').addClass("hidden");
	$('#'+componentId+'_text_edit').removeClass("hidden");
	$('#'+editReflectionTextBtnId).addClass("hidden");
	$('#'+saveReflectionTextBtnId).removeClass("hidden");
	$('#'+refreshBtnId).removeClass("hidden");
    // IE HACK: Cannot use autosize js with IE 11 do to scrollbars issues
    if (!_.page.isIE()) {
	    autosize($('#'+componentId+'_text_edit'));
    }
};

_.components.modals.edit_reflection.open = function(shownCallback) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	
	if (shownCallback && typeof shownCallback == "function") {
		$('#'+componentId).on('shown.bs.modal', function(e) {
			shownCallback();
			$('#'+componentId).off('shown.bs.modal');
		});
	}
	
	$('#'+componentId).modal({
		backdrop:'static'
	});
};

_.components.modals.edit_reflection.close = function() {
	this.isClosing = true;
	$('#'+this.modalId).modal('hide');
	this.isClosing = false;
};
