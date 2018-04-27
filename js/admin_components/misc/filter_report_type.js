if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.misc) _.admin_components.misc = {};
_.admin_components.misc.filter_report_type = {};

_.admin_components.misc.filter_report_type.filterId = 'id_filter_report_type';

_.admin_components.misc.filter_report_type.init = function() {
	this.initWidgets();
};

_.admin_components.misc.filter_report_type.initWidgets = function() {
	var adminComponentId = this.filterId;
	var thisAdminComponent = this;
	var filterWidgetId = this.filterId + '_filter_widget';
	_.widgets.filter.init(filterWidgetId, function(selectedIndex) {
		var filterAction = $('#'+filterWidgetId).attr('data-filter-report-type-action');
		var filterVal = thisAdminComponent.mapIndexToValue(selectedIndex);
		var paramPairs = _.page.getParamPairsArray();
		var data = {};
		$.each(paramPairs, function(index, value) {
			var pair = value.split('=');
			if (pair[1] !== undefined && pair[0] != 'page' && (pair[0] != 'filter_report_type' || filterVal != null)) {
				data[pair[0]] = pair[1];
			}
		});
		if (filterVal != null) {
			data['filter_report_type'] = filterVal;
		}
		_.page.actionPost(filterAction, data, function(response) {
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

_.admin_components.misc.filter_report_type.mapIndexToValue = function(index) {
	index = parseInt(index);
	switch (index) {
		case 1: return 'resolved';
		case 2: return 'unresolved';
		default: return null;
	}
};
