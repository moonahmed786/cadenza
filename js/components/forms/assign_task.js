if (!_.components) _.components = {};
if (!_.components.forms) _.components.forms = {};
_.components.forms.assign_task = {};

// NOTE: This js component depends on the following other js components
// - checklists/assign_checklist
// - tables/task_selector (NOTE: tables/task_selector is NOT used as a sub-component here; this component just needs a reference to it)
// - uploads/task_teacher_attachments

_.components.forms.assign_task.formId = 'id_form_assign_task';
_.components.forms.assign_task.targetMin = 1;
_.components.forms.assign_task.targetMax = 20;
_.components.forms.assign_task.category;

_.components.forms.assign_task.init = function() {
	// before saving, it is unsafe to leave the page
	_.page.setUnsafeNavigationForPageFlag(true);
	
	// Properties
	this.category = $('#'+this.formId + ' .task-category.selected').attr('data-category');
	// Title Input
	this.initTitleInput();
	// Target
	this.initTarget();
	// Category Chooser
	this.initCategoryChooser();
	// Bottom Bar
	this.initBottomBar();
};

// Title Input
_.components.forms.assign_task.initTitleInput = function() {
	var titleInputId = this.formId + '_title';
	_.autocomplete.initInputAutocomplete(titleInputId, 'getTaskTitleAutocompleteData');
	$('#'+titleInputId).change(function() {
		_.components.forms.assign_task.updateTaskTitle();
	});
	$('#'+titleInputId).blur(function() {
		_.components.forms.assign_task.updateTaskTitle();
	});
};
_.components.forms.assign_task.updateTaskTitle = function() {
	var titleInputId = this.formId + '_title';
	var title = $('#'+titleInputId).val().trim();
	if (title == "") {
		title = $('#'+titleInputId).attr('data-untitled-title');
	}
	_.components.tables.task_selector.setSelectedTaskTitle(title);
};

// Target
_.components.forms.assign_task.initTarget = function() {
	var inputNumId = this.formId + '_target_input_num';
	$('#'+inputNumId).change(function() {
		_.components.forms.assign_task.updateTargetStatus(true);
	});
	this.updateTargetStatus(false);
};
_.components.forms.assign_task.getTargetInputVal = function() {
	var inputId = this.formId + '_target_input_num';
	return $('#'+inputId).val();
};
_.components.forms.assign_task.setTargetInputVal = function(num) {
	var inputId = this.formId + '_target_input_num';
	$('#'+inputId).val(num);
};
_.components.forms.assign_task.updateTargetStatus = function(updateTaskSelector) {
	var plusBtnId = this.formId + '_target_plus_btn';
	var minusBtnId = this.formId + '_target_minus_btn';
	var num = parseInt(this.getTargetInputVal());
	// Ensure num is valid
	if (isNaN(num) || num < this.targetMin) {
		num = this.targetMin;
	}
	if (num > this.targetMax) {
		num = this.targetMax;
	}
	this.setTargetInputVal(num);
	// Check if min/max
	if (num == this.targetMin) {
		$('#'+minusBtnId).addClass("disabled");
	}
	else {
		$('#'+minusBtnId).removeClass("disabled");
	}
	if (num == this.targetMax) {
		$('#'+plusBtnId).addClass("disabled");
	}
	else {
		$('#'+plusBtnId).removeClass("disabled");
	}
	if (updateTaskSelector) {
		_.components.tables.task_selector.setSelectedTaskTarget(num);
	}
};

// Category Chooser
_.components.forms.assign_task.initCategoryChooser = function() {
	$('#'+this.formId + ' .task-category').click(function(e) {
		var jqObj = $(this);
		_.components.forms.assign_task.selectCategory(jqObj.attr('data-category'));
	});
	_.autocomplete.initInputAutocomplete(this.formId+'_category_other', 'getTaskCategoryOtherAutocompleteData');
};
_.components.forms.assign_task.selectCategory = function(category_new) {
	var category_old = this.category;
	if (category_new != category_old) {
		this.category = category_new;
		$("#"+this.formId + " .task-category[data-category='"+category_old+"']").removeClass('selected').removeClass('bg-color-cat-'+category_old).addClass('selectable');
		$("#"+this.formId + " .task-category[data-category='"+category_new+"']").removeClass('selectable').addClass('bg-color-cat-'+category_new).addClass('selected');
		if (category_old == '0') {
			$('#'+this.formId+'_category_other').val("");
		}
		else if (category_new == '0') {
			$('#'+this.formId+'_category_other').focus();
		}
		_.components.tables.task_selector.setSelectedTaskCategory(category_new);
	}
};

// Bottom Bar
_.components.forms.assign_task.initBottomBar = function() {
	var thisComponent = this;
	var componentId = thisComponent.formId;
	var deleteBtnId = componentId + '_delete_btn';
	var saveBtnId = componentId + '_save_btn';
	var data;
	// Delete button
	$('#'+deleteBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
        
		if (confirm(_.translate('confirm_delete_task'))) {
			jqObj.html(_.btnIconHtml('loading'));
			data = _.components.forms.assign_task.getFormData();
			
            _.fileuploads.abortAllUploads(false);
			_.page.setUnsafeNavigationForPageFlag(false);
			
			_.page.actionPost('deleteTask', data, function(response) {
				if (response.task_id) {
					_.components.tables.task_selector.selectedTaskId = response.task_id;
				}
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
				jqObj.html(_.btnIconHtml('delete'));
			});
		}
	});
	// Save button
	$('#'+saveBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		// Input Validation
		if (thisComponent.isBlank()) {
			_.alert(_.translate('task_cannot_be_blank'));
			return;
		}
		// Input OK
		if (_.page.showUnsafeNavigationConfirmIfUploadsPending()) {
			jqObj.html(_.btnIconHtml('loading'));
			data = {};
			data['task_form_data'] = _.components.forms.assign_task.getFormData();
			
			// NOTE: The flag must be removed before the ajax request because the server currently redirects immediately.
			// After saving, it is safe to leave the page.
			_.page.setUnsafeNavigationForPageFlag(false);
			
			_.page.actionPost('saveTask', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
				jqObj.html(_.btnIconHtml('save'));
			});
		}
	});
};

_.components.forms.assign_task.getFormData = function() {
	return {
		task_id:_.components.tables.task_selector.selectedTaskId,
		title:$('#'+this.formId+'_title').val().trim(),
		targetnum:_.components.forms.assign_task.getTargetInputVal(),
		category:this.category,
		category_other:$('#'+this.formId+'_category_other').val().trim(),
		description:$('#'+this.formId+'_description').val().trim(),
		checklist:_.components.checklists.assign_checklist.getChecklistData()
	};
};

_.components.forms.assign_task.isBlank = function() {
	var formData = this.getFormData();
	var isTitleBlank = (formData['title'] == "");
	var isTargetNumMin = (formData['targetnum'] == this.targetMin);
	var isCategoryZero = (formData['category'] == 0);
	var isCategoryOtherBlank = (formData['category_other'] == "");
	var isDescriptionBlank = (formData['description'] == "");
	var isChecklistTextBlank = !_.components.checklists.assign_checklist.isAnyChecklistItemHasText();
	var isNoAttachmentsUploaded = (_.components.uploads.task_teacher_attachments.countUploaded() == 0);
	return (isTitleBlank && isTargetNumMin && isCategoryZero && isCategoryOtherBlank && isDescriptionBlank && isChecklistTextBlank && isNoAttachmentsUploaded);
};

_.components.forms.assign_task.refreshComponent = function(html) {
	$('#'+this.formId).replaceWith(html);
	this.init();
	_.components.checklists.assign_checklist.init();
	_.components.uploads.task_teacher_attachments.init();
};

