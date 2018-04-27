if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.report_issue = {};

_.components.modals.report_issue.modalId = 'id_modal_report_issue';
_.components.modals.report_issue.reportWhoUid = null;

_.components.modals.report_issue.init = function() {
	var componentId = this.modalId;
	var thisComponent = this;
	var cancelBtnId = componentId + '_cancel_btn';
	var sendBtnId = componentId + '_send_btn';
	var data;
	// Cancel button
	$('#'+cancelBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.close();
	});
	// Send button
	$('#'+sendBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.cmdSend();
	});
};

_.components.modals.report_issue.cmdSend = function() {
	var thisComponent = this;
	var reportWhoUid = thisComponent.reportWhoUid;
	var text = thisComponent.getReportText();
	// Input Validation
	if (text.trim() == "") {
		thisComponent.clrAlertStatus();
		thisComponent.showReportTextInputError(_.translate('please_enter_report_details')+'.');
		return;
	}
	// Input OK
	if (!_.page.isAjaxInProgress) {
		thisComponent.clrInputErrorsAndAlertStatus();
		data = { report_who_uid:reportWhoUid, report_text:text };
		_.page.actionPost('reportUser', data, function(response) {
			thisComponent.close();
		});
	}
};

_.components.modals.report_issue.clr = function() {
	this.clrText();
	this.clrInputErrorsAndAlertStatus();
};

_.components.modals.report_issue.clrText = function() {
	$('#'+this.modalId+'_text').val("");
};

_.components.modals.report_issue.clrAlertStatus = function() {
	var alertStatusId = this.modalId + '_alert_status';
	$('#'+alertStatusId).text("");
	$('#'+alertStatusId).attr("class", "alert hidden");
};

_.components.modals.report_issue.clrInputErrorsAndAlertStatus = function() {
	var textareaId = this.modalId + '_text';
	$('#'+textareaId).parent().removeClass("has-error");
	this.clrAlertStatus();
};

_.components.modals.report_issue.getReportText = function() {
	var textareaId = this.modalId + '_text';
	return $('#'+textareaId).val();
};

_.components.modals.report_issue.setReportWho = function(reportWhoUid, reportWhoName, reportWhoUserType) {
	if (reportWhoUserType == 'student') {
		this.reportWhoUid = reportWhoUid;
		$('#'+this.modalId+'_blurb').html(_.translate('report_student_x').replace('{x}', '<strong>'+reportWhoName+'</strong>'));
	}
	else if (reportWhoUserType == 'teacher') {
		this.reportWhoUid = reportWhoUid;
		$('#'+this.modalId+'_blurb').html(_.translate('report_teacher_x').replace('{x}', '<strong>'+reportWhoName+'</strong>'));
	}
};

_.components.modals.report_issue.showReportTextInputError = function(text) {
	var textareaId = this.modalId + '_text';
	$('#'+textareaId).parent().addClass("has-error");
	this.setAlertStatus("alert-danger", text);
};

_.components.modals.report_issue.setAlertStatus = function(statusClass, statusText) {
	var alertStatusId = this.modalId + '_alert_status';
	$('#'+alertStatusId).text(statusText);
	$('#'+alertStatusId).attr("class", "alert " + statusClass);
};

_.components.modals.report_issue.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.report_issue.close = function() {
	$('#'+this.modalId).modal('hide');
};
