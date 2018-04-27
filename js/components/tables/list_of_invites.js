if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.list_of_invites = {};

_.components.tables.list_of_invites.tableId = 'id_table_list_of_invites';

_.components.tables.list_of_invites.init = function() {
	var data;
	// Delete buttons
	$('#'+this.tableId+' .btn.invite-delete').click(function(e) {
		var jqObj = $(this);
		var studentId = jqObj.attr('data-student-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { student_id:studentId };
			_.page.actionPost('deleteInvite', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
		            _.components.modals.add_students.clrAlertStatus();
				}
			});
		}
	});
	// Resend buttons
	$('#'+this.tableId+' .btn.invite-resend').click(function(e) {
		var jqObj = $(this);
		var studentEmail = jqObj.attr('data-student-email');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { email:studentEmail };
			_.page.actionPost('sendInvite', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
		            _.components.modals.add_students.clrAlertStatus();
				}
			});
		}
	});
};

_.components.tables.list_of_invites.refreshComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
};
