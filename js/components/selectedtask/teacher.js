if (!_.components) _.components = {};
if (!_.components.selectedtask) _.components.selectedtask = {};
_.components.selectedtask.teacher = {};

// NOTE: This js component depends on the following other js components
// - checklists/task_checklist
// - misc/comments
// - misc/practicelog
// - misc/task_moreinfo
// - uploads/practicelog_student_attachments

_.components.selectedtask.teacher.selectedtaskId = 'id_selectedtask_teacher';

_.components.selectedtask.teacher.init = function() {
	var editLessonBtnId = this.selectedtaskId + '_edit_btn';
	var editLessonIconId = this.selectedtaskId + '_edit_icon';
	// Edit Task button
	$('#'+editLessonBtnId).click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		var lessonId = jqObj.attr('data-lesson-id');
		var taskId = jqObj.attr('data-task-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			$('#'+editLessonIconId).html(_.btnIconHtml('loading'));
			data = { student_id:studentId, lesson_id:lessonId, task_id:taskId };
			_.page.actionPost('editLesson', data, function(response) {
				$('#'+editLessonIconId).html(_.btnIconHtml('edit'));
			});
		}
	});
};

_.components.selectedtask.teacher.refreshComponent = function(html) {
	$('#'+this.selectedtaskId).replaceWith(html);
	this.init();
	_.components.checklists.task_checklist.init();
	_.components.misc.comments.init();
	_.components.misc.practicelog.init();
	_.components.misc.task_moreinfo.init();
	_.components.uploads.practicelog_student_attachments.init();
	_.components.uploads_drive.practicelog_student_annotator.init();
};

