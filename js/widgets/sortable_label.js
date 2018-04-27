if (!_.widgets) _.widgets = {};
_.widgets.sortable_label = {};

_.widgets.sortable_label.init = function() {
	$('.widget-sortable-label').off("click");
	$('.widget-sortable-label').click(function(e) {
		var jqObj = $(this);
		var action = jqObj.data("action");
		var data = _.page.params;
		e.preventDefault();
		jqObj.blur();
		
		data['order_by'] = jqObj.data("order-by");
		data['order_direction'] = jqObj.data('order-direction');
		
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