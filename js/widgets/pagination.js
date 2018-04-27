if (!_.widgets) _.widgets = {};
_.widgets.pagination = {};

_.widgets.pagination.init = function() {
	$('.widget-pagination a').click(function(e) {
		var action = $(this).closest('.widget-pagination').data("action");
		var data = _.page.params;
		e.preventDefault();
		$(this).blur();
		
		data['page'] = $(this).data("page-number");
		_.page.actionPost(action, data, function(response) {
			if (response.refresh) {
				_.page.refreshComponents(response.refresh);
				_.page.historyPushState(data, response);
			}
			if (response.refreshAdmin) {
				_.page.refreshAdminComponents(response.refreshAdmin);
				_.page.historyPushState(data, response);
			}
		});
	});
};