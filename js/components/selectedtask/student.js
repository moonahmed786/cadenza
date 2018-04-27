if (!_.components) _.components = {};
if (!_.components.selectedtask) _.components.selectedtask = {};
_.components.selectedtask.student = {};

// NOTE: This js component depends on the following other js components
// - actionoverflows/practicelog_overflow_student
// - checklists/task_checklist
// - misc/comments
// - misc/practicelog
// - misc/task_moreinfo
// - ratings/practice_reflection
// - timers/practice_timer
// - uploads/practice_student_attachments
// - uploads/practicelog_student_attachments
// - uploads_drive/practice_student_annotator
// - uploads_drive/practicelog_student_annotator

_.components.selectedtask.student.selectedtaskId = 'id_selectedtask_student';

_.components.selectedtask.student.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.selectedtaskId;
	var startPracticeBtnId = componentId + '_start_practice_btn';
	var startPracticeIconId = componentId + '_start_practice_icon';
	var isPracticing = ($('#'+componentId).attr('data-practicing') == '1');

	if (isPracticing) {
		// practicing, it is unsafe to leave the page
		_.page.setUnsafeNavigationForPageFlag(true);
		
		thisComponent.initPractice();
	}
	else {
		// not practicing, it is safe to leave the page
		_.page.setUnsafeNavigationForPageFlag(false);
		
		if ($('#'+startPracticeBtnId).length > 0) {
			thisComponent.initStartPracticeBtn(startPracticeBtnId, startPracticeIconId);
		}
	}
};

_.components.selectedtask.student.initPractice = function() {
	var thisComponent = this;
	var componentId = thisComponent.selectedtaskId;
	var savePracticeBtnId = componentId + '_save_practice_btn';
	var savePracticeIconId = componentId + '_save_practice_icon';
	var data;
	
	// Save Practice button
	$('#'+savePracticeBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = jqObj.attr('data-practice-id');
		var checklist = _.components.checklists.task_checklist.getPracticeValues();
		var timerMins = _.components.timers.practice_timer.getTotalMin();
		var reflectionIndex = _.components.ratings.practice_reflection.getSelectedReflectionIndex();
		var commentText = $('#'+componentId+'_new_practice_first_comment').val();
		e.preventDefault();
		jqObj.blur();
		if (_.page.showUnsafeNavigationConfirmIfUploadsPending()) {
			$('#'+savePracticeIconId).html(_.btnIconHtml('loading'));
			_.components.timers.practice_timer.stop();
			data = {
				practice_id:practiceId,
				checklist:checklist,
				timer_mins:timerMins,
				reflection:reflectionIndex,
				comment_text:commentText
			};
			_.page.actionPost('savePractice', data, function(response) {
				$('#'+savePracticeIconId).html(_.btnIconHtml('save'));
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
};

_.components.selectedtask.student.initStartPracticeBtn = function(startPracticeBtnId, startPracticeIconId) {
	var data;
	
	// Start Practice button
	$('#'+startPracticeBtnId).click(function(e) {
		var jqObj = $(this);
		var taskId = jqObj.attr('data-task-id');
		e.preventDefault();
		jqObj.blur();
		if (_.page.showUnsafeNavigationConfirmIfUploadsPending()) {
			$('#'+startPracticeIconId).html(_.btnIconHtml('loading'));
			data = { task_id:taskId };
			_.page.actionPost('startPractice', data, function(response) {
				$('#'+startPracticeIconId).html(_.btnIconHtml('start'));
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
};

_.components.selectedtask.student.refreshComponent = function(html) {
	$('#'+this.selectedtaskId).replaceWith(html);
	this.init();
	_.components.checklists.task_checklist.init();
	_.components.misc.comments.init();
	_.components.misc.practicelog.init();
	_.components.misc.task_moreinfo.init();
	_.components.ratings.practice_reflection.init();
	_.components.timers.practice_timer.init();
	_.components.uploads.practice_student_attachments.init();
	_.components.uploads.practicelog_student_attachments.init();
	_.components.uploads_drive.practice_student_annotator.init();
	_.components.uploads_drive.practicelog_student_annotator.init();
	_.components.modals.notemaker_import.init();
	// Once misc/practicelog has been initialized, check for "moreactions"
	if (_.components.misc.practicelog.hasMoreActions) {
		_.components.actionoverflows.practicelog_overflow_student.init();
		_.components.modals.edit_practicelog_reflection_student.init();
		_.components.modals.edit_practicelog_timer_student.init();
		// NOTE: 'ratings/practice_reflection' already initialized above
		_.components.timers.editonly_timer_student.init();
	}
};

