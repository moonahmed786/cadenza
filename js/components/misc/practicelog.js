if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.practicelog = {};

// NOTE: This js component depends on the following other js components
// - actionoverflows/practicelog_overflow_student
// - misc/comments
// - misc/practicelog_indicators
// - modals/edit_practicelog_reflection_student
// - modals/edit_practicelog_timer_student
// - ratings/practice_reflection
// - timers/editonly_timer_student
// - uploads/practicelog_student_attachments

_.components.misc.practicelog.practicelogId = 'id_practicelog';
_.components.misc.practicelog.hasMoreActions;

_.components.misc.practicelog.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	var selectPracticeId = $('#'+componentId).attr("data-select-practice-id");
	thisComponent.hasMoreActions = $('#'+componentId).attr("data-has-moreactions") ? true : false;
	// Select Practice Id
	if (selectPracticeId) {
		thisComponent.open(selectPracticeId);
		_.page.scrollToElement(componentId+'_practiceheader_'+selectPracticeId);
	}
	$('#'+componentId).removeAttr("data-select-practice-id");
	// Practice Headers
	$('#'+componentId+' .practice-header').click(function(e) {
		var jqObj = $(this);
		var practiceId = jqObj.attr('data-practice-id');
		if (jqObj.hasClass("open")) {
			thisComponent.close(practiceId);
		}
		else {
			thisComponent.closeAll();
			thisComponent.open(practiceId);
		}
	});
};

_.components.misc.practicelog.closeAll = function() {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	$('#'+componentId+' .practice-header.open').removeClass("open").next('.practice-content').hide();
	if (thisComponent.hasMoreActions) {
		_.components.actionoverflows.practicelog_overflow_student.hideAll();
	}
};

_.components.misc.practicelog.close = function(practiceId) {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	$('#'+componentId+' .practice-content[data-practice-id="'+practiceId+'"]').hide();
	$('#'+componentId+' .practice-header[data-practice-id="'+practiceId+'"]').removeClass("open");
	if (thisComponent.hasMoreActions) {
		_.components.actionoverflows.practicelog_overflow_student.hide(practiceId);
	}
};

_.components.misc.practicelog.open = function(practiceId) {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	$('#'+componentId+' .practice-content[data-practice-id="'+practiceId+'"]').show();
	$('#'+componentId+' .practice-header[data-practice-id="'+practiceId+'"]').addClass("open");
	if (thisComponent.hasMoreActions) {
		_.components.actionoverflows.practicelog_overflow_student.show(practiceId);
	}
};

_.components.misc.practicelog.updateReflection = function(practiceId, reflectionIndex) {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	var practiceheaderId = componentId + '_practiceheader_' + practiceId;
	if (reflectionIndex == null || reflectionIndex == 0) {
		reflectionIndex = "";
	}
	$('#'+practiceheaderId+' .reflection').attr('data-selected-reflection-index', reflectionIndex);
};

_.components.misc.practicelog.updateTimerVal = function(practiceId, timerVal) {
	var thisComponent = this;
	var componentId = thisComponent.practicelogId;
	var practiceheaderId = componentId + '_practiceheader_' + practiceId;
	$('#'+practiceheaderId+'_timer_val').text(timerVal);
};

_.components.misc.practicelog.refreshComponent = function(html) {
	$('#'+this.practicelogId).replaceWith(html);
	this.init();
	if (this.hasMoreActions) {
		_.components.actionoverflows.practicelog_overflow_student.init();
	}
	_.components.misc.comments.init();
	_.components.misc.practicelog_indicators.init();
	_.components.modals.edit_practicelog_reflection_student.init();
	_.components.modals.edit_practicelog_timer_student.init();
	_.components.ratings.practice_reflection.init();
	_.components.timers.editonly_timer_student.init();
	_.components.uploads.practicelog_student_attachments.init();
	_.components.uploads_drive.practicelog_student_annotator.init();
};
