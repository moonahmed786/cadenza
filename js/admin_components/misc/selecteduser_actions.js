if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.misc) _.admin_components.misc = {};
_.admin_components.misc.selecteduser_actions = {};

// NOTE: This js component depends on the following other js components
// - modals/confirm_user_block
// - modals/confirm_user_delete
// - modals/confirm_user_delete_data
// - modals/confirm_user_unblock

_.admin_components.misc.selecteduser_actions.selecteduserActionsId = 'id_selecteduser_actions';
_.admin_components.misc.selecteduser_actions.userStatus;

_.admin_components.misc.selecteduser_actions.init = function() {
	var adminComponentId = this.selecteduserActionsId;
	var thisAdminComponent = this;
	var deleteUserBtnId = adminComponentId+'_delete_btn';
	var blockUserBtnId = adminComponentId+'_block_btn';
	var deleteUserDataBtnId = adminComponentId+'_delete_data_btn';
	var unblockUserBtnId = adminComponentId+'_unblock_btn';
	thisAdminComponent.userStatus = $('#'+adminComponentId).attr('data-user-status');
	// Delete User button
	$('#'+deleteUserBtnId).click(function(e) {
		e.preventDefault();
		$(this).blur();
		if (thisAdminComponent.userStatus == 'active') {
			_.admin_components.modals.confirm_user_delete.open();
		}
	});
	// Block User button
	$('#'+blockUserBtnId).click(function(e) {
		e.preventDefault();
		$(this).blur();
		if (thisAdminComponent.userStatus == 'active') {
			_.admin_components.modals.confirm_user_block.open();
		}
	});
	// Delete User Data button
	$('#'+deleteUserDataBtnId).click(function(e) {
		e.preventDefault();
		$(this).blur();
		if (thisAdminComponent.userStatus == 'blocked') {
			_.admin_components.modals.confirm_user_delete_data.open();
		}
	});
	// Unblock User button
	$('#'+unblockUserBtnId).click(function(e) {
		e.preventDefault();
		$(this).blur();
		if (thisAdminComponent.userStatus == 'blocked') {
			_.admin_components.modals.confirm_user_unblock.open();
		}
	});
};

_.admin_components.misc.selecteduser_actions.refreshAdminComponent = function(html) {
	$('#'+this.selecteduserActionsId).replaceWith(html);
	this.init();
	_.admin_components.modals.confirm_user_block.init();
	_.admin_components.modals.confirm_user_delete.init();
	_.admin_components.modals.confirm_user_delete_data.init();
	_.admin_components.modals.confirm_user_unblock.init();
};
