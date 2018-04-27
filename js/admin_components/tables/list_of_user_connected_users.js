if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.tables) _.admin_components.tables = {};
_.admin_components.tables.list_of_user_connected_users = {};

_.admin_components.tables.list_of_user_connected_users.tableId = 'id_table_list_of_user_connected_users';

_.admin_components.tables.list_of_user_connected_users.init = function() {
	this.initWidgets();
};

_.admin_components.tables.list_of_user_connected_users.initWidgets = function() {
	_.widgets.pagination.init();
	_.widgets.sortable_label.init();
};

_.admin_components.tables.list_of_user_connected_users.refreshAdminComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
};
