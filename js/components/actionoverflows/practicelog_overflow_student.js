if (!_.components) _.components = {};
if (!_.components.actionoverflows) _.components.actionoverflows = {};
_.components.actionoverflows.practicelog_overflow_student = {};

_.components.actionoverflows.practicelog_overflow_student.actionoverflowIdPrefix = 'id_actionoverflow_practicelog_overflow_student_';
_.components.actionoverflows.practicelog_overflow_student.actionoverflowDropdownClass = 'dropdown dropdown-actionoverflow-practicelog-overflow-student';

_.components.actionoverflows.practicelog_overflow_student.init = function() {
	var dropdownSelector = '.'+this.actionoverflowDropdownClass.replace(' ', '.');
	$(dropdownSelector).each(function() {
		var jqDropdownObj = $(this);
		var actionoverflowId = jqDropdownObj.closest('.actionoverflow').attr("id");
		var editReflectionBtnId = actionoverflowId + '_edit_reflection_btn';
		var editReflectionIconId = actionoverflowId + '_edit_reflection_icon';
		var editTimerBtnId = actionoverflowId + '_edit_timer_btn';
		var editTimerIconId = actionoverflowId + '_edit_timer_icon';
		var notifyBtnId = actionoverflowId + '_notify_btn';
		var notifyIconId = actionoverflowId + '_notify_icon';
		jqDropdownObj.find('.dropdown-toggle').dropdown();
		jqDropdownObj.click(function(e) {
			e.stopPropagation(); // to prevent accordion from closing
		});
		
		// Edit Reflection button
		_.actionbuttons.initEditPracticelogReflectionStudentBtn(editReflectionBtnId, editReflectionIconId);
		
		// Edit Timer button
		_.actionbuttons.initEditPracticelogTimerStudentBtn(editTimerBtnId, editTimerIconId);
		
		// Notify Teacher button
		_.actionbuttons.initNotifyTeacherBtn(notifyBtnId, notifyIconId);
	});
};

_.components.actionoverflows.practicelog_overflow_student.show = function(practiceId) {
	var actionoverflowId = _.components.actionoverflows.practicelog_overflow_student.actionoverflowIdPrefix + practiceId;
	$('#'+actionoverflowId).removeClass("hidden");
};

_.components.actionoverflows.practicelog_overflow_student.hide = function(practiceId) {
	var actionoverflowId = _.components.actionoverflows.practicelog_overflow_student.actionoverflowIdPrefix + practiceId;
	$('#'+actionoverflowId).addClass("hidden");
};

_.components.actionoverflows.practicelog_overflow_student.hideAll = function() {
	var dropdownSelector = '.'+this.actionoverflowDropdownClass.replace(' ', '.');
	$(dropdownSelector).each(function() {
		var jqDropdownObj = $(this);
		var actionoverflowId = jqDropdownObj.closest('.actionoverflow').attr("id");
		$('#'+actionoverflowId).addClass("hidden");
	});
};
