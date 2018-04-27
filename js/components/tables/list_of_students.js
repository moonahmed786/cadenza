if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.list_of_students = {};

// NOTE: This js component depends on the following other js components
// - modals/notes_on_student

_.components.tables.list_of_students.tableId = 'id_table_list_of_students';

_.components.tables.list_of_students.init = function() {
	var data;
	this.initWidgets();
	// Notes buttons
	$('.icon-notes').click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		var studentName = jqObj.attr('data-student-name');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_id:studentId };
			_.page.actionPost('getNotesOnStudent', data, function(response) {
				jqObj.html(_.btnIconHtml('notes'));
				_.components.modals.notes_on_student.setStudent(studentId, studentName, response.notesOnStudent);
				_.components.modals.notes_on_student.open();
			});
		}
	});
	// New Lesson buttons
	$('.icon-new').click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqObj.html(_.btnIconHtml('loading'));
			data = { student_id:studentId };
			_.page.actionPost('createNewLesson', data, function(response) {
				jqObj.html(_.btnIconHtml('new'));
			});
		}
	});
};

_.components.tables.list_of_students.initWidgets = function() {
	_.widgets.pagination.init();
	_.widgets.sortable_label.init();
};

_.components.tables.list_of_students.refreshComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
	_.components.modals.notes_on_student.init();
};
