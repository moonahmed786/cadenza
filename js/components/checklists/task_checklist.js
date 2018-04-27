if (!_.components) _.components = {};
if (!_.components.checklists) _.components.checklists = {};
_.components.checklists.task_checklist = {};

_.components.checklists.task_checklist.checklistId = 'id_checklist_task_checklist';

_.components.checklists.task_checklist.init = function() {
	$('#'+this.checklistId+' .icon-chk').click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		if (jqObj.attr('data-checked')) {
			jqObj.html(_.btnIconHtml('chk_off'));
			jqObj.attr('data-checked', "");
		}
		else {
			jqObj.html(_.btnIconHtml('chk_on'));
			jqObj.attr('data-checked', "1");
		}
	});
};

_.components.checklists.task_checklist.getPracticeValues = function() {
	var checklist = [];
	$('#'+this.checklistId+' .icon-chk').each(function() {
		checklist.push({
			checklist_item_id:$(this).attr('data-checklist-item-id'),
			field_value:($(this).attr('data-checked') ? 1 : 0)
		});
	});
	return checklist;
};
