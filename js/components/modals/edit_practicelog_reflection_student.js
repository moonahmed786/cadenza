if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.edit_practicelog_reflection_student = {};

// NOTE: This js component depends on the following other js components
// - misc/practicelog
// - ratings/practice_reflection

_.components.modals.edit_practicelog_reflection_student.modalId = 'id_modal_edit_practicelog_reflection_student';
_.components.modals.edit_practicelog_reflection_student.practiceId = null;
_.components.modals.edit_practicelog_reflection_student.isClosing;

_.components.modals.edit_practicelog_reflection_student.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var saveBtnId = componentId + '_save_btn';
	var data;
	
	thisComponent.practiceId = null; // practiceId must be set using setPracticeId
	thisComponent.isClosing = false;
	
	// Save button
	$('#'+saveBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = thisComponent.practiceId;
		var reflectionIndex = _.components.ratings.practice_reflection.getSelectedReflectionIndex();
		e.preventDefault();
		jqObj.blur();
		
		jqObj.html(_.btnIconHtml('loading'));
		data = { practice_id:practiceId, reflection_index:reflectionIndex };
		_.page.actionPost('savePracticelogReflection', data, function(response) {
			_.components.misc.practicelog.updateReflection(practiceId, response.reflection_index);
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

_.components.modals.edit_practicelog_reflection_student.setPracticeId = function(practiceId) {
	this.practiceId = practiceId;
};

_.components.modals.edit_practicelog_reflection_student.setReflectionIndex = function(reflectionIndex) {
	var index = (reflectionIndex != "") ? reflectionIndex : 0; 
	_.components.ratings.practice_reflection.setSelectedReflectionIndex(index);
};

_.components.modals.edit_practicelog_reflection_student.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.edit_practicelog_reflection_student.close = function() {
	this.isClosing = true;
	$('#'+this.modalId).modal('hide');
	this.isClosing = false;
};
