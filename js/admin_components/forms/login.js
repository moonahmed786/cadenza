if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.navbars) _.admin_components.forms = {};
_.admin_components.forms.login = {};

_.admin_components.forms.login.formId = 'id_form_login';

_.admin_components.forms.login.init = function() {
	var thisAdminComponent = this;
	var adminComponentId = thisAdminComponent.formId;
	var usernameInputId = adminComponentId + '_username_input';
	var passwordInputId = adminComponentId + '_password_input';
	var loginBtnId = adminComponentId + '_login_btn';
	
	// Username input
	$('#'+usernameInputId).keyup(function(e) {
		if (e.which == 13) { // enter key
			$('#'+passwordInputId).focus();
		}
	});
	// Password input
	$('#'+passwordInputId).keyup(function(e) {
		if (e.which == 13) { // enter key
			thisAdminComponent.cmdLogin();
		}
	});
	// Login button
	$('#'+loginBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisAdminComponent.cmdLogin();
	});
};

_.admin_components.forms.login.cmdLogin = function() {
	var thisAdminComponent = this;
	var username = thisAdminComponent.getUsernameInputText();
	var password = thisAdminComponent.getPasswordInputText();
	var type = $('#inp-type').val();
	var data = { username:username, password:password };
	$.post(_.cadenzaUrl+'/pages/login.php?'+type, data, function(response) {
		_.admin_components.forms.login.clrInputErrorsAndAlertStatus();
		_.page.handleResponse(response, function(response) {console.log(response);
			if (!response.loginsuccess) {
				_.admin_components.forms.login.showLoginInputError(_.translate('login_error')+'.');
			}
			if(response.error){
				// var alertStatusId = this.formId + '_alert_status';
				// $('#'+alertStatusId).html(statusText);
				// $('#'+alertStatusId).attr("class", "alert " + statusClass);
				// alert(error);
				thisAdminComponent.setAlertStatus("alert-danger", response.error);
			}
		});
	}, "json");
};

_.admin_components.forms.login.singnup = function()
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
	$('#'+alertStatusId).html(statusText);
	$('#'+alertStatusId).attr("class", "alert " + statusClass);
};
