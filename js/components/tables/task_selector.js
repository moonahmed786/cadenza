if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.task_selector = {};

// NOTE: This js component depends on the following other js components
// - forms/assign_task (NOTE: forms/assign_task is NOT used as a sub-component here; this component just needs a reference to it)
// - modals/edit_reflection (for student only)
// - modals/view_reflection
// - ratings/edit_lesson_reflection (for student only)
// - ratings/view_lesson_reflection

_.components.tables.task_selector.tableId = 'id_table_task_selector';
_.components.tables.task_selector.lessonId;
_.components.tables.task_selector.selectedTaskId;

_.components.tables.task_selector.init = function() {
	if ($('#'+this.tableId).is('.overview')) {
		this.initOverview();
	}
	else {
		this.initSideMenu();
	}
};

_.components.tables.task_selector.initOverview = function() {
	var reflectionBtnId = this.tableId + '_reflection_btn';
	var reflectionIconId = this.tableId + '_reflection_icon';
	// Properties
	this.lessonId = $('#'+this.tableId+' td.task-info').first().attr('data-lesson-id');
	this.selectedTaskId = null;
	// Reflection button
	$('#'+reflectionBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();

		$('#'+reflectionIconId).html(_.btnIconHtml('loading'));
		data = { lesson_id:_.components.tables.task_selector.lessonId };
		_.page.actionPost('getReflectionModalData', data, function(response) {
			$('#'+reflectionIconId).html(_.btnIconHtml('reflection'));
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

	// if should automatically open reflection modal, mimic reflection button click
	if ($('#'+this.tableId).data("show-reflection-modal")) {
		$('#'+reflectionBtnId).click();
	}
};

_.components.tables.task_selector.initSideMenu = function() {
	var newBtnId = this.tableId + '_new_btn';
	var newIconId = this.tableId + '_new_icon';
	var reflectionBtnId = this.tableId + '_reflection_btn';
	var reflectionIconId = this.tableId + '_reflection_icon';
	var data;
	// Properties
	this.lessonId = $('#'+this.tableId+' tr.selected').attr('data-lesson-id');
	this.selectedTaskId = $('#'+this.tableId+' tr.selected').attr('data-task-id');
	// Selectable Tasks
	$('#'+this.tableId + ' tr.selectable').click(function(e) {
		var jqObj = $(this);
		var isEdit = jqObj.attr("data-edit") ? true : false;
		e.preventDefault();
		jqObj.blur();
		if (isEdit) {
			// Input Validation
			if (_.components.forms.assign_task.isBlank()) {
				_.alert(_.translate('task_cannot_be_blank'));
				return;
			}
			// Input OK
			if (_.page.showUnsafeNavigationConfirmIfUploadsPending()) {
				data = {
					task_id:jqObj.attr('data-task-id'),
					edit:(isEdit ? '1' : '0'),
					task_form_data:_.components.forms.assign_task.getFormData()
				};
				_.page.actionPost('selectTask', data, function(response) {
					if (response.task_id) {
						_.components.tables.task_selector.selectedTaskId = response.task_id;
					}
					if (response.refresh) {
						_.page.refreshComponents(response.refresh);
					}
				});
			}
		}
		else {
			if (!_.page.isAjaxInProgress && _.page.showUnsafeNavigationConfirmIfUnsafe()) {
				data = { task_id:jqObj.attr('data-task-id'), edit:(isEdit ? '1' : '0') };
				_.page.actionPost('selectTask', data, function(response) {
					if (response.task_id) {
						_.components.tables.task_selector.selectedTaskId = response.task_id;
					}
					if (response.refresh) {
						_.page.refreshComponents(response.refresh);
					}
				});
			}
		}
	});
	// New Task button
	$('#'+newBtnId).click(function(e) {
		var jqObj = $(this);
		var isEdit = jqObj.attr("data-edit") ? true : false;
		e.preventDefault();
		jqObj.blur();
		if (isEdit) {
			// Input Validation
			if (_.components.forms.assign_task.isBlank()) {
				_.alert(_.translate('task_cannot_be_blank'));
				return;
			}
			// Input OK
			if (_.page.showUnsafeNavigationConfirmIfUploadsPending()) {
				$('#'+newIconId).html(_.btnIconHtml('loading'));
				data = {
					lesson_id:_.components.tables.task_selector.lessonId,
					edit:(isEdit ? '1' : '0'),
					task_form_data:_.components.forms.assign_task.getFormData()
				};
				_.page.actionPost('createNewTask', data, function(response) {
					if (response.task_id) {
						_.components.tables.task_selector.selectedTaskId = response.task_id;
					}
					if (response.refresh) {
						_.page.refreshComponents(response.refresh);
					}
					$('#'+newIconId).html(_.btnIconHtml('new'));
				});
			}
		}
		else {
			if (!_.page.isAjaxInProgress && _.page.showUnsafeNavigationConfirmIfUnsafe()) {
				$('#'+newIconId).html(_.btnIconHtml('loading'));
				data = { lesson_id:_.components.tables.task_selector.lessonId, edit:(isEdit ? '1' : '0') };
				_.page.actionPost('createNewTask', data, function(response) {
					if (response.task_id) {
						_.components.tables.task_selector.selectedTaskId = response.task_id;
					}
					if (response.refresh) {
						_.page.refreshComponents(response.refresh);
					}
					$('#'+newIconId).html(_.btnIconHtml('new'));
				});
			}
		}
	});
	// Reflection button
	$('#'+reflectionBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();

		$('#'+reflectionIconId).html(_.btnIconHtml('loading'));
		data = { lesson_id:_.components.tables.task_selector.lessonId };
		_.page.actionPost('getReflectionModalData', data, function(response) {
			$('#'+reflectionIconId).html(_.btnIconHtml('reflection'));
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

	// if should automatically open reflection modal, mimic reflection button click
	if ($('#'+this.tableId).data("show-reflection-modal")) {
		$('#'+reflectionBtnId).click();
	}
};

_.components.tables.task_selector.getSelectedTaskCountPractices = function() {
	if (this.selectedTaskId != null) {
		return $('#'+this.tableId+' tr.selected').attr('data-count-practices');
	}
	return null;
};

_.components.tables.task_selector.setSelectedTaskTitle = function(title) {
	$('#'+this.tableId+' tr.selected').find('.task-title').text(title);
};

_.components.tables.task_selector.setSelectedTaskTarget = function(target) {
	var selectedTaskCountPractices = this.getSelectedTaskCountPractices();
	var show_star = selectedTaskCountPractices >= target;
	var plus = show_star ? selectedTaskCountPractices - target : 0;
	var htmlTargetCircles = "";
	var i;
	for (i = 1; i <= target; i++) {
		htmlTargetCircles += '<div class="target-circle' + (i <= selectedTaskCountPractices ? ' completed' : '') + '"></div>';
	}
	$('#'+this.tableId+' tr.selected').find('.target-circles').html(htmlTargetCircles);
	
	if (show_star) {
		$('#'+this.tableId+' tr.selected').find('.target-star').removeClass("hidden");
	}
	else {
		$('#'+this.tableId+' tr.selected').find('.target-star').addClass("hidden");
	}
	
	if (plus > 0) {
		$('#'+this.tableId+' tr.selected').find('.target-plus').html('+'+plus);
	}
	else {
		$('#'+this.tableId+' tr.selected').find('.target-plus').html("");
	}
};

_.components.tables.task_selector.setSelectedTaskCategory = function(category) {
	var setClass = 'task-category bg-color-cat-'+category;
	$('#'+this.tableId+' tr.selected').find('.task-category').removeClass().addClass(setClass);
};

_.components.tables.task_selector.refreshComponent = function(html) {
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
