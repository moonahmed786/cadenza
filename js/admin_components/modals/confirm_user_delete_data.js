if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.modals) _.admin_components.modals = {};
_.admin_components.modals.confirm_user_delete_data = {};

_.admin_components.modals.confirm_user_delete_data.modalId = 'id_modal_confirm_user_delete_data';

_.admin_components.modals.confirm_user_delete_data.init = function() {
	var componentId = this.modalId;
	var thisAdminComponent = this;
	var cancelBtnId = componentId + '_cancel_btn';
	var deleteDataBtnId = componentId + '_delete_data_btn';
	// Cancel button
	$('#'+cancelBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisAdminComponent.close();
	});
	// Delete button
	$('#'+deleteDataBtnId).click(function(e) {
		var jqObj = $(this);
		var data = _.page.getParamsClone();
		var uid = jqObj.attr('data-uid');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data['uid'] = uid;
			_.page.actionPost('deleteUserData', data, function(response) {
				thisAdminComponent.close();
				if (response.refreshAdmin) {
					_.page.refreshAdminComponents(response.refreshAdmin);
				}
			});
		}
	});
};

_.admin_components.modals.confirm_user_delete_data.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.admin_components.modals.confirm_user_delete_data.close = function() {
	$('#'+this.modalId).modal('hide');
};
