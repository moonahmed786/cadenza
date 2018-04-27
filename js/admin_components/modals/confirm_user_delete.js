if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.modals) _.admin_components.modals = {};
_.admin_components.modals.confirm_user_delete = {};

_.admin_components.modals.confirm_user_delete.modalId = 'id_modal_confirm_user_delete';

_.admin_components.modals.confirm_user_delete.init = function() {
	var componentId = this.modalId;
	var thisAdminComponent = this;
	var cancelBtnId = componentId + '_cancel_btn';
	var deleteBtnId = componentId + '_delete_btn';
	var data;
	// Cancel button
	$('#'+cancelBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisAdminComponent.close();
	});
	// Delete button
	$('#'+deleteBtnId).click(function(e) {
		var jqObj = $(this);
		var uid = jqObj.attr('data-uid');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { uid:uid };
			_.page.actionPost('deleteUser', data, function(response) {
			});
		}
	});
};

_.admin_components.modals.confirm_user_delete.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.admin_components.modals.confirm_user_delete.close = function() {
	$('#'+this.modalId).modal('hide');
};
