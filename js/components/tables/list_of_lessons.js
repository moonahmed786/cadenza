if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.list_of_lessons = {};

// NOTE: This js component depends on the following other js components
// - modals/edit_reflection (for student only)
// - modals/view_reflection
// - ratings/edit_lesson_reflection (for student only)
// - ratings/view_lesson_reflection

_.components.tables.list_of_lessons.tableId = 'id_table_list_of_lessons';

_.components.tables.list_of_lessons.init = function() {
	var componentId = this.tableId;
	var data;
	this.initWidgets();
	// Show/Hide Toggle buttons
	$('#'+componentId+' .icon-toggle-showhide').click(function(e) {
		var jqObj = $(this);
		var show = jqObj.attr('data-show');
		var lessonId = jqObj.closest('tr').attr('data-lesson-id');
		e.preventDefault();
		jqObj.blur();
		if (show) {
			jqObj.html(_.btnIconHtml('toggle_hide'));
			jqObj.attr('data-show', "");
		}
		else {
			jqObj.html(_.btnIconHtml('toggle_show'));
			jqObj.attr('data-show', "1");
		}
		$('.lesson-task[data-lesson-id="'+lessonId+'"]').toggleClass("hidden");
	});
	// Reflection buttons
	$('#'+componentId+' .icon-reflection').click(function(e) {
		var jqObj = $(this);
		var lessonId = jqObj.attr('data-lesson-id');
		e.preventDefault();
		jqObj.blur();

		jqObj.html(_.btnIconHtml('loading'));
		data = { lesson_id:lessonId };
		_.page.actionPost('getReflectionModalData', data, function(response) {
			jqObj.html(_.btnIconHtml('reflection'));
			if (response.modalData.isEditReflection) {
				_.components.modals.edit_reflection.setModalData(response.modalData);
				_.components.modals.edit_reflection.open();
			}
			else {
				_.components.modals.view_reflection.setModalData(response.modalData);
				_.components.modals.view_reflection.open();
			}
		});
	});
};

_.components.tables.list_of_lessons.initWidgets = function() {
	_.widgets.pagination.init();
	_.widgets.sortable_label.init();
};

_.components.tables.list_of_lessons.refreshComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
	if (_.components.modals.edit_reflection) { // only loaded if student
		_.components.modals.edit_reflection.init();
	}
	_.components.modals.view_reflection.init();
	if (_.components.ratings.edit_lesson_reflection) { // only loaded if student
		_.components.ratings.edit_lesson_reflection.init();
	}
	_.components.ratings.view_lesson_reflection.init();
};
