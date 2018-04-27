if (!_.widgets) _.widgets = {};
_.widgets.sortable_dropdown_select = {};

_.widgets.sortable_dropdown_select.init = function() {
	$(".widget-sortable-dropdown-select").each(function() {
		var jqObj = $(this);
		var action = jqObj.data("action");
		
		var changeCallback = function(selectedItem) {
			var data = _.page.params;
			jqObj.blur();
			
			data['order_by'] = $(selectedItem).data('order-by');
			data['order_direction'] = $(selectedItem).data('order-direction');
			
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
		};
		
		var selectedIndex = jqObj.find(".dropdown").data("selected-index");
		var selectedItem = jqObj.find(".dropdown-menu li a[data-select-index="+selectedIndex+"]");

		// init dropdown
		jqObj.find(".dropdown-toggle").dropdown();
		
		// init dropdown text
		jqObj.find(".order-by-text").text(selectedItem.text());

		jqObj.find(".dropdown-menu li a").each(function() {
			var orderByOption = $(this);
			
			orderByOption.off("click");
			orderByOption.click(function() {
				selectedIndex = $(orderByOption).data("select-index");
				selectedItem = orderByOption;
				
				jqObj.find(".dropdown").data("selected-index", selectedIndex);
				jqObj.find(".order-by-text").text(selectedItem.text());
				
				changeCallback(selectedItem);
			});
		});
	});
};
