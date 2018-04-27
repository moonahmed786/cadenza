if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.edit_practicelog_timer_student = {};

// NOTE: This js component depends on the following other js components
// - misc/practicelog
// - timers/editonly_timer_student

_.components.modals.edit_practicelog_timer_student.modalId = 'id_modal_edit_practicelog_timer_student';
_.components.modals.edit_practicelog_timer_student.practiceId = null;
_.components.modals.edit_practicelog_timer_student.isClosing;

_.components.modals.edit_practicelog_timer_student.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var saveBtnId = componentId + '_save_btn';
	var data;
	
	thisComponent.practiceId = null; // practiceId must be set using setModalData
	thisComponent.isClosing = false;
	
	// Save button
	$('#'+saveBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = thisComponent.practiceId;
		var timerMins = _.components.timers.editonly_timer_student.getTotalMin();
		e.preventDefault();
		jqObj.blur();
		
		jqObj.html(_.btnIconHtml('loading'));
		data = { practice_id:practiceId, timer_mins:timerMins };
		_.page.actionPost('savePracticelogTimer', data, function(response) {
			_.components.misc.practicelog.updateTimerVal(practiceId, response.time_spent);
			jqObj.html(_.btnIconHtml('save'));
			thisComponent.close();
		});
	});
	// "X" Close button
	$('#'+componentId).on('hide.bs.modal', function (e) {
		if (!thisComponent.isClosing) {
			if (!confirm(_.translate('confirm_close_modal'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		}
	});
};

_.components.modals.edit_practicelog_timer_student.setModalData = function(modalData) {
	var thisComponent = this;
	thisComponent.practiceId = modalData.practiceId;
	_.components.timers.editonly_timer_student.setTotalMin(modalData.timerMins);
};

_.components.modals.edit_practicelog_timer_student.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.edit_practicelog_timer_student.close = function() {
	this.isClosing = true;
	$('#'+this.modalId).modal('hide');
	this.isClosing = false;
};
