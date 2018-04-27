if (!_.components) _.components = {};
if (!_.components.tables) _.components.tables = {};
_.components.tables.list_of_teachers = {};

// NOTE: This js component depends on the following other js components
// - modals/edit_goals
// - modals/view_goals

_.components.tables.list_of_teachers.tableId = 'id_table_list_of_teachers';

_.components.tables.list_of_teachers.init = function() {
	this.initWidgets();
	// Goals button
	$('.icon-goals').parent('.btn-icon').click(function(e) {
		var jqObj = $(this);
		var jqIconObj = jqObj.find('.icon-goals');
		var linkedUserId = jqObj.attr('data-linked-user-id');
		e.preventDefault();
		jqObj.blur();
		if (!_.page.isAjaxInProgress) {
			jqIconObj.html(_.btnIconHtml('loading'));
			data = { linked_user_id:linkedUserId };
			_.page.actionPost('getGoalsModalData', data, function(response) {
				jqIconObj.html(_.btnIconHtml('goals'));
				if (response.modalData.isEditGoals) {
					_.components.modals.edit_goals.setModalData(response.modalData);
					_.components.modals.edit_goals.open();
				}
				else {
					_.components.modals.view_goals.setModalData(response.modalData);
					_.components.modals.view_goals.open();
				}
			});
		}
	});
};

_.components.tables.list_of_teachers.initWidgets = function() {
	_.widgets.sortable_dropdown_select.init();
};

_.components.tables.list_of_teachers.refreshComponent = function(html) {
	$('#'+this.tableId).replaceWith(html);
	this.init();
	_.components.modals.edit_goals.init();
	_.components.modals.view_goals.init();
};
