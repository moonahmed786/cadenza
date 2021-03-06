if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.navbars) _.admin_components.forms = {};
_.admin_components.forms.forget_password = {};

_.admin_components.forms.forget_password.formId = 'id_forget_password';

_.admin_components.forms.forget_password.init = function() {
	alert('yes');
	var thisAdminComponent = this;
	var adminComponentId = thisAdminComponent.formId;
	var usernameInputId = adminComponentId + '_username_input';
	var loginBtnId = adminComponentId + '_forget_password_btn';
	
	// Username input
	$('#'+usernameInputId).keyup(function(e) {
		if (e.which == 13) { // enter key
			$('#'+passwordInputId).focus();
		}
	});
	// Password input
	// $('#'+passwordInputId).keyup(function(e) {
	// 	if (e.which == 13) { // enter key
	// 		thisAdminComponent.cmdLogin();
	// 	}
	// });
	// Login button
	$('#'+loginBtnId).click(function(e) {
		alert('yes');
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisAdminComponent.cmdLogin();
	});
};

_.admin_components.forms.login.cmdLogin = function() {
	var thisAdminComponent = this;
	var username = thisAdminComponent.getUsernameInputText();
	var type = $('#inp-type').val();
	console.log(type);
	var data = { username:username};
	$.post(_.cadenzaUrl+'/pages/login.php?'+type, data, function(response) {
		_.admin_components.forms.login.clrInputErrorsAndAlertStatus();
		_.page.handleResponse(response, function(response) {
			if (!response.loginsuccess) {
				_.admin_components.forms.login.showLoginInputError(_.translate('login_error')+'.');
			}
		});
	}, "json");
};

_.admin_components.forms.login.forget_password = function()
{
	alert('yes');
}
_.admin_components.forms.login.getUsernameInputText = function() {
	var usernameInputId = this.formId + '_username_input';
	return $('#'+usernameInputId).val();
};

_.admin_components.forms.login.getPasswordInputText = function() {
	var passwordInputId = this.formId + '_password_input';
	return $('#'+passwordInputId).val();
};

_.admin_components.forms.login.clrAlertStatus = function() {
	var alertStatusId = this.formId + '_alert_status';
	$('#'+alertStatusId).text("");
	$('#'+alertStatusId).attr("class", "alert hidden");
};

_.admin_components.forms.login.clrInputErrorsAndAlertStatus = function() {
	var usernameInputId = this.formId + '_username_input';
	var passwordInputId = this.formId + '_password_input';
	$('#'+usernameInputId).parent().removeClass("has-error");
	$('#'+passwordInputId).parent().removeClass("has-error");
	this.clrAlertStatus();
};

_.admin_components.forms.login.showLoginInputError = function(text) {
	var usernameInputId = this.formId + '_username_input';
	var passwordInputId = this.formId + '_password_input';
	$('#'+usernameInputId).parent().addClass("has-error");
	$('#'+passwordInputId).parent().addClass("has-error");
	this.setAlertStatus("alert-danger", text);
};

_.admin_components.forms.login.setAlertStatus = function(statusClass, statusText) {
	var alertStatusId = this.formId + '_alert_status';
	$('#'+alertStatusId).text(statusText);
	$('#'+alertStatusId).attr("class", "alert " + statusClass);
};
