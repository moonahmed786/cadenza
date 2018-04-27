if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.modals) _.admin_components.modals = {};
_.admin_components.modals.confirm_user_unblock = {};

_.admin_components.modals.confirm_user_unblock.modalId = 'id_modal_confirm_user_unblock';

_.admin_components.modals.confirm_user_unblock.init = function() {
	var componentId = this.modalId;
	var thisAdminComponent = this;
	var cancelBtnId = componentId + '_cancel_btn';
	var unblockBtnId = componentId + '_unblock_btn';
	// Cancel button
	$('#'+cancelBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisAdminComponent.close();
	});
	// Unblock button
	$('#'+unblockBtnId).click(function(e) {
		var jqObj = $(this);
		var data = _.page.getParamsClone();
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			_.page.actionPost('unblockUser', data, function(response) {
				thisAdminComponent.close();
				if (response.refreshAdmin) {
					_.page.refreshAdminComponents(response.refreshAdmin);
				}
			});
		}
	});
};

_.admin_components.modals.confirm_user_unblock.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.admin_components.modals.confirm_user_unblock.close = function() {
	$('#'+this.modalId).modal('hide');
};
