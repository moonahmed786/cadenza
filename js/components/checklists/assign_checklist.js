if (!_.components) _.components = {};
if (!_.components.checklists) _.components.checklists = {};
_.components.checklists.assign_checklist = {};

_.components.checklists.assign_checklist.checklistId = 'id_checklist_assign_checklist';
_.components.checklists.assign_checklist.checklistNewItemCounter = 0;

_.components.checklists.assign_checklist.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.checklistId;
	var checklistJson = $('#'+componentId+'_json').val();
	var checklistItemRows = JSON.parse(checklistJson);
	var i;
	for (i = 0; i < checklistItemRows.length; i++) {
		this.initChecklistItem(checklistItemRows[i]);
	}
	this.createNewChecklistItem();
	this.updateChecklistStatus();
};

_.components.checklists.assign_checklist.initChecklistItem = function(checklistItem) {
	var thisComponent = this;
	var componentId = thisComponent.checklistId;
	var itemId = componentId+'_item_'+checklistItem.checklist_item_id;
	var inputId = itemId+'_input';
	$('#'+componentId+'_item_dummy').clone().attr("id", itemId).removeClass("hidden").addClass("checklist-item").appendTo($('#'+componentId+'_item_dummy').parent());
	$('#'+itemId).find('.item-text input').attr("id", inputId);
	// Remove Button
	// -- init
	$('#'+itemId+' .item-remove a').removeClass("disabled");
	// -- bind events
	$('#'+itemId+' .item-remove a').click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		jqObj.closest("tr").remove();
	});
	// Text
	// -- init
	$('#'+inputId).val(checklistItem.text);
	_.autocomplete.initInputAutocomplete(inputId, 'getTaskChecklistItemAutocompleteData');
	// -- bind events
	$('#'+inputId).change(function() {
		_.components.checklists.assign_checklist.updateChecklistStatus();
	});
	$('#'+inputId).blur(function() {
		_.components.checklists.assign_checklist.updateChecklistStatus();
	});
	$('#'+inputId).keyup(function(e) {
		var nextItemId;
		var nextInputId;
		if (e.which == 13) { // enter key
            $(this).blur();
            nextItemId = $(this).closest("tr").next().attr("id");
            nextInputId = nextItemId+'_input';
			$('#'+nextInputId).focus();
		}
	});
	// Target
	// -- init
	this.setChecklistItemTargetType(itemId, checklistItem.target_type);
	this.setChecklistItemTargetVal(itemId, checklistItem.target_val);
	// -- bind events
	$('#'+itemId+' .item-target .dropdown-menu a').click(function(e) {
		var jqObj = $(this);
		var setTargetType = jqObj.attr('data-target-type');
		e.preventDefault();
		jqObj.blur();
		_.components.checklists.assign_checklist.setChecklistItemTargetType(itemId, setTargetType);
		if (setTargetType != 1) {
			$('#'+itemId+' .item-target input').focus();
			$('#'+itemId+' .item-target input').select();
		}
	});
	$('#'+itemId+' .item-target .input-num').change(function(e) {
		var num = parseInt(thisComponent.getChecklistItemTargetVal(itemId));
		// Ensure num is valid
		if (isNaN(num)) {
			num = 1;
		}
		thisComponent.setChecklistItemTargetVal(itemId, num);
	});
};
_.components.checklists.assign_checklist.createNewChecklistItem = function() {
	var newChecklistItem = { checklist_item_id:'new_'+(++this.checklistNewItemCounter), target_type:1 };
	this.initChecklistItem(newChecklistItem);
};
_.components.checklists.assign_checklist.getChecklistItemText = function(itemId) {
	var inputId = itemId+'_input';
	return $('#'+inputId).val().trim();
};
_.components.checklists.assign_checklist.isChecklistItemHasText = function(itemId) {
	var inputId = itemId+'_input';
	return ($('#'+inputId).val().trim().length > 0);
};
_.components.checklists.assign_checklist.setChecklistItemRemoveButtonEnabled = function(itemId, setEnabled) {
	if (setEnabled) {
		$('#'+itemId+' .item-remove a').removeClass("disabled");
	}
	else {
		$('#'+itemId+' .item-remove a').addClass("disabled");
	}
};
_.components.checklists.assign_checklist.getChecklistItemTargetType = function(itemId) {
	return parseInt($('#'+itemId+' .item-target button .icon-targettype').attr("data-target-type"));
};
_.components.checklists.assign_checklist.setChecklistItemTargetType = function(itemId, targetType) {
	var thisComponent = this;
	var componentId = thisComponent.checklistId;
	var targetTypePrev = this.getChecklistItemTargetType(itemId);
	if (targetType != targetTypePrev) {
		$('#'+itemId+' .item-target button .icon-targettype').attr("data-target-type", targetType).html(_.btnIconHtml('targettype'+targetType));
		$('#'+itemId+' .item-target input').prop("disabled", (targetType == 1)).val("");
		$('#'+itemId+' .item-target .input-group-addon').text($('#'+componentId+'_target_unit_'+targetType).val());
	}
	if (targetType == 1) {
		$('#'+itemId+' .item-target input').hide();
	}
	else {
		$('#'+itemId+' .item-target input').show();
	}
};
_.components.checklists.assign_checklist.getChecklistItemTargetVal = function(itemId) {
	var targetVal = $('#'+itemId+' .item-target input').val();
	return targetVal;
};
_.components.checklists.assign_checklist.setChecklistItemTargetVal = function(itemId, targetVal) {
	$('#'+itemId+' .item-target input').val(targetVal);
};
_.components.checklists.assign_checklist.updateChecklistStatus = function() {
	var thisComponent = this;
	var componentId = thisComponent.checklistId;
	var lastItemId = componentId+'_item_new_'+this.checklistNewItemCounter;
	if (this.isChecklistItemHasText(lastItemId)) {
		this.setChecklistItemRemoveButtonEnabled(lastItemId, true);
		this.createNewChecklistItem();
		lastItemId = componentId+'_item_new_'+this.checklistNewItemCounter;
	}
	if (!this.isChecklistItemHasText(lastItemId)) {
		this.setChecklistItemRemoveButtonEnabled(lastItemId, false);
	}
};
_.components.checklists.assign_checklist.isAnyChecklistItemHasText = function() {
	var checklistData = this.getChecklistData();
	var i;
	for (i = 0; i < checklistData.length; i++) {
		if (checklistData[i]['text'].length > 0) {
			return true;
		}
	}
	return false;
};
_.components.checklists.assign_checklist.getChecklistData = function() {
	var thisComponent = this;
	var componentId = thisComponent.checklistId;
	var data = new Array();
	$('#'+componentId+' .checklist-item').each(function() {
		var itemData;
		var itemId = $(this).attr("id");
		var checklist_item_id = parseInt(itemId.substr((componentId+'_item_').length));
		if (isNaN(checklist_item_id)) {
			checklist_item_id = "new";
		}
		itemData = {
			checklist_item_id:checklist_item_id,
			text:_.components.checklists.assign_checklist.getChecklistItemText(itemId),
			target_type:_.components.checklists.assign_checklist.getChecklistItemTargetType(itemId),
			target_val:_.components.checklists.assign_checklist.getChecklistItemTargetVal(itemId)
		};
		data.push(itemData);
	});
	return data;
};