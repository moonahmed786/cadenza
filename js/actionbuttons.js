/* This file contains helper functions for actionbars and actionoverflows
 * components, in order to avoid unnecessary code duplication between them.
 */

_.actionbuttons = {};

_.actionbuttons.debug = false;

_.actionbuttons.initGoalsBtn = function(goalsBtnId, goalsIconId) {
	var data;
	$('#'+goalsBtnId).click(function(e) {
		var jqObj = $(this);
		var linkedUserId = jqObj.attr('data-linked-user-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			$('#'+goalsIconId).html(_.btnIconHtml('loading'));
			data = { linked_user_id:linkedUserId };
			_.page.actionPost('getGoalsModalData', data, function(response) {
				$('#'+goalsIconId).html(_.btnIconHtml('goals'));
				if (response.modalData.isEditGoals) {
					_.components.modals.edit_goals.setModalData(response.modalData);
					_.components.modals.edit_goals.open();
				}
				else {
					_.components.modals.view_goals.setModalData(response.modalData);
					_.components.modals.view_goals.open();
				}
			});
		}
	});
};

_.actionbuttons.initNewLessonBtn = function(newLessonBtnId, newLessonIconId) {
	var data;
	$('#'+newLessonBtnId).click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			$('#'+newLessonIconId).html(_.btnIconHtml('loading'));
			data = { student_id:studentId };
			_.page.actionPost('createNewLesson', data, function(response) {
				$('#'+newLessonIconId).html(_.btnIconHtml('new'));
			});
		}
	});
};

_.actionbuttons.initNotesBtn = function(notesBtnId, notesIconId) {
	var data;
	$('#'+notesBtnId).click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		var studentName = jqObj.attr('data-student-name');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			$('#'+notesIconId).html(_.btnIconHtml('loading'));
			data = { student_id:studentId };
			_.page.actionPost('getNotesOnStudent', data, function(response) {
				$('#'+notesIconId).html(_.btnIconHtml('notes'));
				_.components.modals.notes_on_student.setStudent(studentId, studentName, response.notesOnStudent);
				_.components.modals.notes_on_student.open();
			});
		}
	});
};

_.actionbuttons.initDisconnectBtn = function(disconnectBtnId, disconnectIconId) {
	var data;
	$('#'+disconnectBtnId).click(function(e) {
		var jqObj = $(this);
		var connectedUserId = jqObj.attr('data-connected-user-id');
		var connectedUserUserType = jqObj.attr('data-connected-user-user-type');
		var confirmMsg = "";
		if (connectedUserUserType == 'student') {
			confirmMsg = _.translate('confirm_disconnect_student');
		}
		else if (connectedUserUserType == 'teacher') {
			confirmMsg = _.translate('confirm_disconnect_teacher');
		}
		else {
			return; // unknown user type
		}
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress && confirm(confirmMsg)) {
			$('#'+disconnectIconId).html(_.btnIconHtml('loading'));
			data = { connected_user_id:connectedUserId };
			_.page.actionPost('disconnectUser', data, function(response) {
				$('#'+disconnectIconId).html(_.btnIconHtml('disconnect'));
			});
		}
	});
};

_.actionbuttons.initReportIssueBtn = function(reportIssueBtnId, reportIssueIconId) {
	$('#'+reportIssueBtnId).click(function(e) {
		var jqObj = $(this);
		var connectedUserId = jqObj.attr('data-connected-user-id');
		var connectedUserName = jqObj.attr('data-connected-user-name');
		var connectedUserUserType = jqObj.attr('data-connected-user-user-type');
		e.preventDefault();
		jqObj.blur();
		_.components.modals.report_issue.clr();
		_.components.modals.report_issue.setReportWho(connectedUserId, connectedUserName, connectedUserUserType);
		_.components.modals.report_issue.open();
	});
};

_.actionbuttons.initNotifyTeacherBtn = function(notifyBtnId, notifyIconId) {
	var data;
	$('#'+notifyBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = jqObj.attr('data-practice-id');
		e.preventDefault();
		jqObj.blur();

		$('#'+notifyIconId).html(_.btnIconHtml('loading'));
		data = { practice_id:practiceId };
		_.page.actionPost('notifyTeacher', data, function(response) {
			$('#'+notifyIconId).html(_.btnIconHtml('notify'));
			$('#'+notifyBtnId).addClass("disabled"); // small DOM update to avoid needing an extra component refresh
			if (response.refresh) {
            	_.page.refreshComponents(response.refresh);
            }
		});
	});
};

_.actionbuttons.initEditPracticelogReflectionStudentBtn = function(editReflectionBtnId, editReflectionIconId) {
	$('#'+editReflectionBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = jqObj.attr('data-practice-id');
		var reflectionIndex = jqObj.closest('.practice-header').find('.reflection').attr('data-selected-reflection-index');
		e.preventDefault();
		jqObj.blur();
		_.components.modals.edit_practicelog_reflection_student.setPracticeId(practiceId);
		_.components.modals.edit_practicelog_reflection_student.setReflectionIndex(reflectionIndex);
		_.components.modals.edit_practicelog_reflection_student.open();
	});
};

_.actionbuttons.initEditPracticelogTimerStudentBtn = function(editTimerBtnId, editTimerIconId) {
	var data;
	$('#'+editTimerBtnId).click(function(e) {
		var jqObj = $(this);
		var practiceId = jqObj.attr('data-practice-id');
		e.preventDefault();
		jqObj.blur();
		
		$('#'+editTimerIconId).html(_.btnIconHtml('loading'));
		data = { practice_id:practiceId };
		_.page.actionPost('getEditPracticelogTimerModalData', data, function(response) {
			$('#'+editTimerIconId).html(_.btnIconHtml('timer'));
			_.components.modals.edit_practicelog_timer_student.setModalData(response.modalData);
			_.components.modals.edit_practicelog_timer_student.open();
		});
	});
};
