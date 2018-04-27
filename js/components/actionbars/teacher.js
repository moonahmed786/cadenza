if (!_.components) _.components = {};
if (!_.components.actionbars) _.components.actionbars = {};
_.components.actionbars.teacher = {};

// NOTE: This js component depends on the following other js components
// - actionoverflows/actionbar_overflow_teacher
// - modals/notes_on_student
// - modals/view_goals
// - modals/report_issue

_.components.actionbars.teacher.actionbarId = 'id_actionbar_teacher';

_.components.actionbars.teacher.init = function() {
	var notesBtnId = this.actionbarId + '_notes_btn';
	var notesIconId = this.actionbarId + '_notes_icon';
	var newLessonBtnId = this.actionbarId + '_new_btn';
	var newLessonIconId = this.actionbarId + '_new_icon';
	var goalsBtnId = this.actionbarId + '_goals_btn';
	var goalsIconId = this.actionbarId + '_goals_icon';
	
	// Notes button (depends on modals/notes_on_student)
	_.actionbuttons.initNotesBtn(notesBtnId, notesIconId);
	
	// New Lesson button
	_.actionbuttons.initNewLessonBtn(newLessonBtnId, newLessonIconId);
	
	// Goals button (depends on modals/view_goals)
	_.actionbuttons.initGoalsBtn(goalsBtnId, goalsIconId);
};

_.components.actionbars.teacher.refreshComponent = function(html) {
	$('#'+this.actionbarId).replaceWith(html);
	this.init();
	_.components.modals.view_goals.init();
	_.components.modals.notes_on_student.init();
	_.components.modals.report_issue.init();
    _.components.actionoverflows.actionbar_overflow_teacher.init();
};
