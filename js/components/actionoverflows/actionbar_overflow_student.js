if (!_.components) _.components = {};
if (!_.components.actionoverflows) _.components.actionoverflows = {};
_.components.actionoverflows.actionbar_overflow_student = {};

// NOTE: We can rely on the parent actionbar to take care of any dependencies,
// unless in the future this component has any dependencies additional to those.

_.components.actionoverflows.actionbar_overflow_student.actionoverflowId = 'id_actionoverflow_actionbar_overflow_student';

_.components.actionoverflows.actionbar_overflow_student.init = function() {
	var goalsBtnId = this.actionoverflowId + '_goals_btn';
	var goalsIconId = this.actionoverflowId + '_goals_icon';
	var disconnectBtnId = this.actionoverflowId + '_disconnect_btn';
	var disconnectIconId = this.actionoverflowId + '_disconnect_icon';
	var reportIssueBtnId = this.actionoverflowId + '_report_btn';
	var reportIssueIconId = this.actionoverflowId + '_report_icon';
	
	// Goals button
	_.actionbuttons.initGoalsBtn(goalsBtnId, goalsIconId);
	
	// Disconnect button
	_.actionbuttons.initDisconnectBtn(disconnectBtnId, disconnectIconId);
	
	// Report Issue button
	_.actionbuttons.initReportIssueBtn(reportIssueBtnId, reportIssueIconId);
};
