if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.view_reflection = {};

// NOTE: This js component depends on the following other js components
// - ratings/view_lesson_reflection

_.components.modals.view_reflection.modalId = 'id_modal_view_reflection';
_.components.modals.view_reflection.modalData = null;

_.components.modals.view_reflection.init = function() {
	// do nothing (nothing to initialize)
};

_.components.modals.view_reflection.setModalData = function(modalData) {
	this.modalData = modalData;
	$('#'+this.modalId+'_lesson_title').text(modalData.lessonTitle);
	$('#'+this.modalId+'_lesson_targets').text(modalData.lessonTargets);
	$('#'+this.modalId+'_lesson_time_spent').text(modalData.lessonTimeSpent);
	_.components.ratings.view_lesson_reflection.setSelectedReflectionIndex(modalData.reflectionIndex);
	$('#'+this.modalId+'_prompt').text(modalData.reflectionPrompt != null ? modalData.reflectionPrompt : "");
	$('#'+this.modalId+'_text').text(modalData.reflectionText);
	if (modalData.reflectionNoText) {
		$('#'+this.modalId+'_no_text').removeClass("hidden").text(modalData.reflectionNoText);
	}
	else {
		$('#'+this.modalId+'_no_text').addClass("hidden").text("");
	}
	
	$('#'+this.modalId+'_lesson_comments').empty().html(modalData.commentsHTML);
	
	_.components.misc.comments.initModalComments(this.modalId);
};

_.components.modals.view_reflection.open = function() {
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.view_reflection.close = function() {
	$('#'+this.modalId).modal('hide');
};
