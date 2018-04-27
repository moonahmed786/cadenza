if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.notes_on_student = {};

_.components.modals.notes_on_student.modalId = 'id_modal_notes_on_student';
_.components.modals.notes_on_student.studentId = null;
_.components.modals.notes_on_student.isClosing;

_.components.modals.notes_on_student.init = function() {
	var componentId = this.modalId;
	var thisComponent = this;
	var saveBtnId = componentId + '_save_btn';
	var textareaId = componentId + '_text';
	var data;
	thisComponent.isClosing = false;
	// Save button
	$('#'+saveBtnId).click(function(e) {
		var jqObj = $(this);
		var studentId = thisComponent.studentId;
		var text = $('#'+textareaId).val();
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_id:studentId, notes_on_student:text };
			_.page.actionPost('saveNotesOnStudent', data, function(response) {
				jqObj.html(_.btnIconHtml('save'));
				_.components.modals.notes_on_student.close();
			});
		}
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

_.components.modals.notes_on_student.setStudent = function(studentId, studentName, notesOnStudent) {
	this.studentId = studentId;
	$('#'+this.modalId+'_label').text(_.translate('notes_on_x').replace('{x}', studentName));
	$('#'+this.modalId+'_text').val(notesOnStudent);
};

_.components.modals.notes_on_student.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.notes_on_student.close = function() {
	this.isClosing = true;
	$('#'+this.modalId).modal('hide');
	this.isClosing = false;
};
