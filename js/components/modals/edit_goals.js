if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.edit_goals = {};

_.components.modals.edit_goals.modalId = 'id_modal_edit_goals';
_.components.modals.edit_goals.goalsFormTeacherId;
_.components.modals.edit_goals.isClosing;

_.components.modals.edit_goals.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	thisComponent.goalsFormTeacherId = null;
	thisComponent.isClosing = false;
	// "X" Close button
	$('#'+componentId).on('hide.bs.modal', function (e) {
		var hasNewGoalText = (thisComponent.getGoalsFormItemEditText(componentId+'_item_new') != "");
		var isGoalEdit = ($('#'+componentId).find('.goal-edit').not('.hidden').length > 1);
		if (!thisComponent.isClosing && (hasNewGoalText || isGoalEdit)) {
			if (!confirm(_.translate('confirm_close_modal'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		}
	});
	// Widgets
	thisComponent.initWidgets();
};

_.components.modals.edit_goals.initWidgets = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	_.widgets.filter.init(componentId+'_complete_filter', function(selectedIndex) {
		$("#"+componentId).attr("data-completed-filter", selectedIndex);
	});
};

_.components.modals.edit_goals.setModalData = function(modalData) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var studentGoals = modalData.studentGoals;
	var i;
	var studentGoalId;
	var itemId;
	thisComponent.goalsFormTeacherId = modalData.teacherId;
	// Clear goal items
	$('#'+componentId+'_items').text("");
	// New goal item
	itemId = componentId+'_item_new';
	$('#'+componentId+'_item_dummy').clone().attr("id", itemId).removeClass("hidden").addClass("goals-item").appendTo($('#'+componentId+'_items'));
	thisComponent.initGoalsFormDummyCloneNew(itemId, modalData.newGoalTitle);
	// Saved goal items
	for (i = 0; i < studentGoals.length; i++) {
		studentGoalId = studentGoals[i].student_goal_id;
		itemId = componentId+'_item_'+studentGoalId;
		$('#'+componentId+'_item_dummy').clone().attr("id", itemId).attr("data-student-goal-id", studentGoalId).removeClass("hidden").addClass("goals-item").appendTo($('#'+componentId+'_items'));
		thisComponent.initGoalsFormDummyCloneSaved(itemId, studentGoals[i]);
	}
};

_.components.modals.edit_goals.initGoalsFormDummyCloneNew = function(itemId, newGoalTitle) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var data;
	$('#'+itemId+' .goal-saved').remove();
	$('#'+itemId+' .goal-saved .icon-chk').remove();
	$('#'+itemId+' .goal-edit').removeClass("hidden");
	$('#'+itemId+' .icon-delete').addClass("disabled");
	$('#'+itemId+' .item-title').text(newGoalTitle);
	$('#'+itemId+' .item-text').val(""); // clear text
	// Save button
	$('#'+itemId+' .item-buttons .icon-save').click(function(e) {
		var jqObj = $(this);
		var insertedStudentGoalId;
		var insertedItemId;
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = {
				teacher_id:thisComponent.goalsFormTeacherId,
				text:thisComponent.getGoalsFormItemEditText(itemId)
			};
			_.page.actionPost('addGoal', data, function(response) {
				jqObj.html(_.btnIconHtml('save'));
				if (response.added) {
					insertedStudentGoalId = response.studentGoal.student_goal_id;
					insertedItemId = componentId+'_item_'+insertedStudentGoalId;
					$('#'+componentId+'_item_dummy').clone().attr("id", insertedItemId).attr("data-student-goal-id", insertedStudentGoalId).removeClass("hidden").addClass("goals-item").insertAfter($('#'+componentId+'_item_new'));
					thisComponent.initGoalsFormDummyCloneSaved(insertedItemId, response.studentGoal);
					// clear item new for next goal
					$('#'+componentId+'_item_new .item-title').text(response.studentGoal.title); // update title (date) just in case current date has changed
					$('#'+componentId+'_item_new .item-text').val(""); // clear text
				}
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
};

_.components.modals.edit_goals.initGoalsFormDummyCloneSaved = function(itemId, studentGoal) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var data;
	var itemCompleted = studentGoal.is_completed;
	// Title
	$('#'+itemId+' .item-title').text(studentGoal.title);
	// Complete
	thisComponent.updateCompletedStyle(itemId, itemCompleted);
	// Text
	thisComponent.setGoalsFormItemSavedText(itemId, studentGoal.text);
	// Edit button
	$('#'+itemId+' .item-buttons .icon-edit').click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.setGoalsFormItemEditTextToSavedText(itemId);
		$('#'+itemId+' .goal-saved').addClass("hidden");
		$('#'+itemId+' .goal-edit').removeClass("hidden");
        // IE HACK: Cannot use autosize js with IE 11 do to scrollbars issues
        if (!_.page.isIE()) {
            autosize($('#'+itemId+' .goal-edit .item-text'));
        }
	});
	// Delete button
	$('#'+itemId+' .item-buttons .icon-delete').click(function(e) {
		var jqObj = $(this);
		var studentGoalId = jqObj.closest(".goals-item").attr("data-student-goal-id");
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_goal_id:studentGoalId };
			_.page.actionPost('deleteGoal', data, function(response) {
				jqObj.html(_.btnIconHtml('delete'));
				jqObj.closest(".goals-item").remove();
			});
		}
	});
	// Save button
	$('#'+itemId+' .item-buttons .icon-save').click(function(e) {
		var jqObj = $(this);
		var studentGoalId = jqObj.closest(".goals-item").attr("data-student-goal-id");
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_goal_id:studentGoalId, text:thisComponent.getGoalsFormItemEditText(itemId) };
			_.page.actionPost('updateGoal', data, function(response) {
				jqObj.html(_.btnIconHtml('save'));
				if (response.updated) {
					thisComponent.setGoalsFormItemEditText(itemId, "");
					thisComponent.setGoalsFormItemSavedText(itemId, response.updatedText);
					$('#'+itemId+' .goal-edit').addClass("hidden");
					$('#'+itemId+' .goal-saved').removeClass("hidden");
				}
			});
		}
	});
	// Completed button
	$('#'+itemId+' .goal-saved .icon-chk').click(function(e) {
		var jqObj = $(this);
		var studentGoalId = jqObj.closest(".goals-item").attr("data-student-goal-id");
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_goal_id:studentGoalId, completed: itemCompleted == 0 ? 1 : 0 };
			_.page.actionPost('updateGoalCompleted', data, function(response) {
				itemCompleted = response.updatedCompleted;
				thisComponent.updateCompletedStyle(itemId, itemCompleted);
			});
		}
	});
};

_.components.modals.edit_goals.getGoalsFormItemEditText = function(itemId) {
	return $('#'+itemId+' .goal-edit .item-text').val().trim();
};
_.components.modals.edit_goals.setGoalsFormItemEditText = function(itemId, text) {
	$('#'+itemId+' .goal-edit .item-text').val(text);
};
_.components.modals.edit_goals.setGoalsFormItemEditTextToSavedText = function(itemId) {
	$('#'+itemId+' .goal-edit .item-text').val($('#'+itemId+' .goal-saved .item-text').text().trim());
};

_.components.modals.edit_goals.getGoalsFormItemSavedText = function(itemId) {
	return $('#'+itemId+' .goal-saved .item-text').text().trim();
};
_.components.modals.edit_goals.setGoalsFormItemSavedText = function(itemId, text) {
	$('#'+itemId+' .goal-saved .item-text').text(text);
};

_.components.modals.edit_goals.updateCompletedStyle = function(itemId, completed) {
	if (completed == 0) {
		$('#'+itemId+' .goal-saved .icon-chk').html(_.btnIconHtml('chk_off'));
	}
	else {
		$('#'+itemId+' .goal-saved .icon-chk').html(_.btnIconHtml('chk_on'));
	}
	$('#'+itemId).attr("data-completed", completed);
};

_.components.modals.edit_goals.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.edit_goals.close = function() {
	this.isClosing = true;
	$('#'+this.modalId).modal('hide');
	this.isClosing = false;
};
