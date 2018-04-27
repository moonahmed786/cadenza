if (!_.components) _.components = {};
if (!_.components.misc) _.components.actionbars = {};
_.components.actionbars.student = {};

// NOTE: This js component depends on the following other js components
// - actionoverflows/actionbar_overflow_student
// - modals/edit_goals
// - modals/report_issue
// - modals/view_goals

_.components.actionbars.student.actionbarId = 'id_actionbar_student';

_.components.actionbars.student.init = function() {
	var goalsBtnId = this.actionbarId + '_goals_btn';
	var goalsIconId = this.actionbarId + '_goals_icon';
	
	// Goals button (depends on modals/edit_goals and modals/view_goals)
	_.actionbuttons.initGoalsBtn(goalsBtnId, goalsIconId);
};

_.components.actionbars.student.refreshComponent = function(html) {
	$('#'+this.actionbarId).replaceWith(html);
	this.init();
	_.components.modals.edit_goals.init();
	_.components.modals.view_goals.init();
	_.components.modals.report_issue.init();
    _.components.actionoverflows.actionbar_overflow_student.init();
};
