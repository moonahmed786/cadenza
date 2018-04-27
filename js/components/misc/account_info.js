if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.account_info = {};

// NOTE: This js component depends on the following other js components
// - modals/account_deletion

_.components.misc.account_info.accountInfoId = 'id_account_info';

_.components.misc.account_info.init = function() {
	// Request Delete button
	$('#'+this.accountInfoId+' .account-delete a').click(function(e) {
		e.preventDefault();
		$(this).blur();
		_.components.modals.account_deletion.clrText();
		_.components.modals.account_deletion.open();
	});
};
