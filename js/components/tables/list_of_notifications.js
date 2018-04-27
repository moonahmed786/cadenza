if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.list_of_notifications = {};

_.components.tables.list_of_notifications.tableId = 'id_table_list_of_notifications';

_.components.tables.list_of_notifications.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.tableId;
	var data;
	
	this.initWidgets();
	// Delete All button
	$('#'+componentId+'_delete_all_btn').click(function(e) {
		var jqObj = $(this);
		var lastNotificationId = jqObj.attr('data-last-notification-id');
		jqObj.blur();
		data = { 'last_notification_id':lastNotificationId };
		_.page.actionPost('deleteAllNotifications', data, function(response) {
			if (response.refresh) {
				_.page.refreshComponents(response.refresh);
			}
		});
	});
	// Mark All as Read button
	$('#'+componentId+'_mark_all_btn').click(function(e) {
		var jqObj = $(this);
		var lastNotificationId = jqObj.attr('data-last-notification-id');
		jqObj.blur();
		data = { 'last_notification_id':lastNotificationId };
		_.page.actionPost('markAllNotificationsAsRead', data, function(response) {
			$('.table-notifications tr.notification').attr('data-unread', "0");
			$('.table-notifications tr.notification div.toggle-unread').attr('data-unread', "0");
			thisComponent.setMarkAllButtonEnabled(false);
		});
	});
	// Reject buttons
	$('#'+componentId+' .invite-reject').click(function(e) {
		var jqObj = $(this);
		var teacherId = jqObj.attr('data-teacher-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { teacher_id:teacherId };
			_.page.actionPost('rejectInvite', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
	// Accept buttons
	$('#'+componentId+' .invite-accept').click(function(e) {
		var jqObj = $(this);
		var teacherId = jqObj.attr('data-teacher-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { teacher_id:teacherId };
			_.page.actionPost('acceptInvite', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
	// Delete buttons
	$('#'+componentId+' tr.notification .icon-delete').click(function(e) {
		var jqObj = $(this);
		var notificationId = jqObj.attr('data-notification-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { notification_id:notificationId };
			_.page.actionPost('deleteNotification', data, function(response) {
				if (response.refresh) {
					_.page.refreshComponents(response.refresh);
				}
			});
		}
	});
	// Toggle Unread buttons
	$('#'+componentId+' tr.notification .toggle-unread').click(function(e) {
		var jqObj = $(this);
		var notificationId = jqObj.attr('data-notification-id');
		var isUnread = (jqObj.attr('data-unread') == "1") ? true : false;
		var action = isUnread ? 'markNotificationAsRead' : 'markNotificationAsUnread';
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = { notification_id:notificationId };
			_.page.actionPost(action, data, function(response) {
				if (response.updatedNotification) {
					$('.table-notifications tr.notification[data-notification-id="'+notificationId+'"]').attr('data-unread', response.updatedNotification.is_unread);
					jqObj.attr('data-unread', response.updatedNotification.is_unread);
					thisComponent.setMarkAllButtonEnabled(response.countUnread > 0);
				}
			});
		}
	});
	// Go buttons
	$('#'+componentId+' tr.notification .icon-go').click(function(e) {
		var jqObj = $(this);
		var notificationId = jqObj.attr('data-notification-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			data = _.page.getParamsClone();
			data['notification_id'] = notificationId;
			_.page.actionPost('openNotification', data, function(response) {
				// do nothing (should redirect if successful)
			});
		}
	});
};

_.components.tables.list_of_notifications.initWidgets = function() {
	_.widgets.pagination.init();
};

_.components.tables.list_of_notifications.setMarkAllButtonEnabled = function(setEnabled) {
	if (setEnabled) {
		$('#'+this.tableId+'_mark_all_btn').removeClass("disabled");
	}
	else {
		$('#'+this.tableId+'_mark_all_btn').addClass("disabled");
	}
};

_.components.tables.list_of_notifications.refreshComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
};