if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.view_goals = {};

_.components.modals.view_goals.modalId = 'id_modal_view_goals';
_.components.modals.view_goals.noGoalsStrings;

_.components.modals.view_goals.init = function() {
	this.noGoalsStrings = null;
	this.initWidgets();
};

_.components.modals.view_goals.initWidgets = function() {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	_.widgets.filter.init(componentId+'_complete_filter', function(selectedIndex) {
		// Apply filter
		$("#"+componentId).attr("data-completed-filter", selectedIndex);
		// Check if no goals
		$('#'+componentId+'_no_goals').addClass("hidden").text("");
		if (selectedIndex == 0 && $('#'+componentId+' .goals-item > .goal-saved').length == 0) {
			$('#'+componentId+'_no_goals').removeClass("hidden").text(thisComponent.noGoalsStrings['all']);
		}
		else if (selectedIndex == 1 && $('#'+componentId+' .goals-item[data-completed=0] > .goal-saved').length == 0) {
			$('#'+componentId+'_no_goals').removeClass("hidden").text(thisComponent.noGoalsStrings['incomplete']);
		}
		else if (selectedIndex == 2 && $('#'+componentId+' .goals-item[data-completed=1] > .goal-saved').length == 0) {
			$('#'+componentId+'_no_goals').removeClass("hidden").text(thisComponent.noGoalsStrings['completed']); 
		}
	});
};

_.components.modals.view_goals.setModalData = function(modalData) {
	var thisComponent = this;
	var componentId = thisComponent.modalId;
	var studentGoals = modalData.studentGoals;
	var i;
	var studentGoalId;
	var itemId;
	var itemCompleted;
	
	thisComponent.noGoalsStrings = modalData.noGoalsStrings;
	
	// Clear goal items
	$('#'+componentId+'_items').text("");
	// Saved goal items
	for (i = 0; i < studentGoals.length; i++) {
		studentGoalId = studentGoals[i].student_goal_id;
		itemId = componentId+'_item_'+studentGoalId;
		itemCompleted = studentGoals[i].is_completed;
		
		$('#'+componentId+'_item_dummy').clone().attr("id", itemId).attr("data-student-goal-id", studentGoalId).removeClass("hidden").addClass("goals-item").appendTo($('#'+componentId+'_items'));
		$('#'+itemId+' .item-title').text(studentGoals[i].title);
		$('#'+itemId+' .item-text').text(studentGoals[i].text);

		if (itemCompleted == 0) {
			$('#'+itemId+' .goal-saved .icon-chk-ro').html("");
		}
		else {
			$('#'+itemId+' .goal-saved .icon-chk-ro').html(_.btnIconHtml('chk_on_ro'));
		}
		$('#'+itemId).attr("data-completed", itemCompleted);
	}
	// Check if no goals
	if (studentGoals.length == 0) {
		$('#'+componentId+'_no_goals').removeClass("hidden").text(thisComponent.noGoalsStrings['all']);
	}
	else {
		$('#'+componentId+'_no_goals').addClass("hidden").text("");
	}
};

_.components.modals.view_goals.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.view_goals.close = function() {
	$('#'+this.modalId).modal('hide');
};
