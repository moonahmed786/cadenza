if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.add_students = {};

_.components.modals.add_students.modalId = 'id_modal_add_students';

_.components.modals.add_students.init = function() {
	var thisComponent = this;
	var emailInputId = thisComponent.modalId + '_email_input';
	var addBtnId = thisComponent.modalId + '_add_btn';
	var data;
	// Email input
	$('#'+emailInputId).keyup(function(e) {
		if (e.which == 13) { // enter key
			thisComponent.cmdAdd();
		}
	});
	// Add button
	$('#'+addBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.cmdAdd();
	});
};

_.components.modals.add_students.cmdAdd = function() {
	var thisComponent = this;
	var email = thisComponent.getEmailInputText();
	var emailPattern = /^.*@.*\..*$/;
	// Input Validation
	if (!emailPattern.test(email)) {
		thisComponent.clrAlertStatus();
		thisComponent.showEmailInputError(_.translate('please_enter_valid_email')+'.');
		return;
	}
	// Input OK
	if (!_.page.isAjaxInProgress) {
		thisComponent.clrInputErrorsAndAlertStatus();
		data = { email:email };
		_.page.actionPost('sendInvite', data, function(response) {
			var warningText;
			if (!response.invited) {
				if (response.found) {
					warningText = response.connected ? _.translate('student_already_connected')+'.' : _.translate('student_already_pending')+'.';
				}
				else {
					warningText = _.translate('student_not_found')+'.';
				}
				thisComponent.setAlertStatus("alert-warning", warningText);
			}
			else {
				thisComponent.setAlertStatus("alert-success", _.translate('student_invited')+'.');
				thisComponent.clrEmailInputText();
			}
			if (response.refresh) {
				_.page.refreshComponents(response.refresh);
			}
		});
	}
};

_.components.modals.add_students.clr = function() {
	var emailInputId = this.modalId + '_email_input';
	this.clrEmailInputText();
	this.clrInputErrorsAndAlertStatus();
};

_.components.modals.add_students.clrAlertStatus = function() {
	var alertStatusId = this.modalId + '_alert_status';
	$('#'+alertStatusId).text("");
	$('#'+alertStatusId).attr("class", "alert hidden");
};

_.components.modals.add_students.clrInputErrorsAndAlertStatus = function() {
	var emailInputId = this.modalId + '_email_input';
	$('#'+emailInputId).parent().removeClass("has-error");
	this.clrAlertStatus();
};

_.components.modals.add_students.clrEmailInputText = function() {
	var emailInputId = this.modalId + '_email_input';
	$('#'+emailInputId).val("");
};

_.components.modals.add_students.getEmailInputText = function() {
	var emailInputId = this.modalId + '_email_input';
	return $('#'+emailInputId).val();
};

_.components.modals.add_students.showEmailInputError = function(text) {
	var emailInputId = this.modalId + '_email_input';
	$('#'+emailInputId).parent().addClass("has-error");
	this.setAlertStatus("alert-danger", text);
};

_.components.modals.add_students.setAlertStatus = function(statusClass, statusText) {
	var alertStatusId = this.modalId + '_alert_status';
	$('#'+alertStatusId).text(statusText);
	$('#'+alertStatusId).attr("class", "alert " + statusClass);
};

_.components.modals.add_students.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};
