if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.tabbedareas) _.admin_components.tabbedareas = {};
_.admin_components.tabbedareas.selecteduser = {};

_.admin_components.tabbedareas.selecteduser.tabbedareaId = 'id_tabbedarea_selecteduser';

_.admin_components.tabbedareas.selecteduser.init = function() {
	var adminComponentId = this.tabbedareaId;
	var thisAdminComponent = this;
	
	$('#'+adminComponentId+' .nav-tabs > li > a[data-toggle="tab"]').click(function(e) {
		$(this).blur();
	});
	
	$('#'+adminComponentId+' .nav-tabs > li > a[data-toggle="tab"]').on('show.bs.tab', function(e) {
		var tabKey = $(this).attr('data-tab-key');
		var action = $(this).attr('data-action');
		thisAdminComponent.loadTab(tabKey, action);
	});
	
	$('#'+adminComponentId+' .nav-tabs > li > a[data-toggle="tab"]').on('hidden.bs.tab', function(e) {
		var tabKey = $(this).attr('data-tab-key');
		thisAdminComponent.unloadTab(tabKey);
	});
};

_.admin_components.tabbedareas.selecteduser.loadTab = function(tabKey, action) {
	var adminComponentId = this.tabbedareaId;
	var thisAdminComponent = this;
	var paramPairs = _.page.getParamPairsArray();
	var data = {};
	$.each(paramPairs, function(index, value) {
		var pair = value.split('=');
		if (pair[0] != 'page' && pair[0] != 'order_by' && pair[0] != 'order_direction') {
			data[pair[0]] = pair[1];
		}
	});
	data['tab'] = tabKey;
	if (action) {
		$('#'+adminComponentId+' .tab-pane[data-tab-key="'+tabKey+'"]').children().first().html(thisAdminComponent.getLoadingHtml());
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
	}
};

_.admin_components.tabbedareas.selecteduser.unloadTab = function(tabKey) {
	var adminComponentId = this.tabbedareaId;
	var tabComponentId = $('#'+adminComponentId+' .tab-pane[data-tab-key="'+tabKey+'"]').children().first().attr("id");
	$('#'+tabComponentId).replaceWith('<div id="'+tabComponentId+'"></div>');
};

_.admin_components.tabbedareas.selecteduser.getLoadingHtml = function() {
	return _.btnIconHtml('loading')+' '+_.translate('loading')+'&hellip;';
};
