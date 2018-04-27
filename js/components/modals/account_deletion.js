if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.account_deletion = {};

_.components.modals.account_deletion.modalId = 'id_modal_account_deletion';

_.components.modals.account_deletion.init = function() {
	var componentId = this.modalId;
	var thisComponent = this;
	var cancelBtnId = componentId + '_cancel_btn';
	var sendBtnId = componentId + '_send_btn';
	var textareaId = componentId + '_text';
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
		var text = $('#'+textareaId).val();
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { report_text:text };
			_.page.actionPost('requestDeleteAccount', data, function(response) {
				thisComponent.close();
			});
		}
	});
};

_.components.modals.account_deletion.clrText = function() {
	$('#'+this.modalId+'_text').val("");
};

_.components.modals.account_deletion.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.account_deletion.close = function() {
	$('#'+this.modalId).modal('hide');
};
