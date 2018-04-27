if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.tables) _.admin_components.tables = {};
_.admin_components.tables.list_of_reports = {};

_.admin_components.tables.list_of_reports.tableId = 'id_table_list_of_reports';

_.admin_components.tables.list_of_reports.init = function() {
	var adminComponentId = this.tableId;
	var data;
	this.initWidgets();
	// Show/Hide Toggle buttons
	$('#'+adminComponentId+' .icon-toggle-showhide').click(function(e) {
		var jqObj = $(this);
		var show = jqObj.attr('data-show');
		var adminReportId = jqObj.closest('tr').attr('data-admin-report-id');
		e.preventDefault();
		jqObj.blur();
		if (show) {
			jqObj.html(_.btnIconHtml('toggle_hide'));
			jqObj.attr('data-show', "");
		}
		else {
			jqObj.html(_.btnIconHtml('toggle_show'));
			jqObj.attr('data-show', "1");
		}
		$('#'+adminComponentId+' .report-details[data-admin-report-id="'+adminReportId+'"]').toggleClass("hidden");
	});
	// View Reported User buttons
	$('#'+adminComponentId+' .view-reported').click(function(e) {
		var jqObj = $(this);
		var reportedUid = jqObj.attr('data-reported-uid');
		e.preventDefault();
		jqObj.blur();
		window.location = _.cadenzaUrl + '/pages/admin/view_user.php?uid='+reportedUid;
	});
	// View Reporter buttons
	$('#'+adminComponentId+' .view-reporter').click(function(e) {
		var jqObj = $(this);
		var reporterUid = jqObj.attr('data-reporter-uid');
		e.preventDefault();
		jqObj.blur();
		window.location = _.cadenzaUrl + '/pages/admin/view_user.php?uid='+reporterUid;
	});
	// Mark as Resolved buttons
	$('#'+adminComponentId+' .mark-resolved').click(function(e) {
		var jqObj = $(this);
		var adminReportId = jqObj.attr('data-admin-report-id');
		e.preventDefault();
		jqObj.blur();
		data = _.page.getParamsClone();
		data['admin_report_id'] = adminReportId;
		if (!_.page.isAjaxInProgress) {
			_.page.actionPost('markAsResolvedFromListOfReports', data, function(response) {
				if (response.refreshAdmin) {
					_.page.refreshAdminComponents(response.refreshAdmin);
				}
			});
		}
	});
};

_.admin_components.tables.list_of_reports.initWidgets = function() {
	_.widgets.pagination.init();
	_.widgets.sortable_label.init();
};

_.admin_components.tables.list_of_reports.refreshAdminComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
};
