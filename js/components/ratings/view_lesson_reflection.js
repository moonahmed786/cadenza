if (!_.components) _.components = {};
if (!_.components.ratings) _.components.ratings = {};
_.components.ratings.view_lesson_reflection = {};

_.components.ratings.view_lesson_reflection.ratingId = 'id_rating_view_lesson_reflection';

_.components.ratings.view_lesson_reflection.init = function() {
	// do nothing (nothing to initialize)
};

_.components.ratings.view_lesson_reflection.getSelectedReflectionIndex = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	if ($('#'+componentId+' .reflection-face-ro.selected').length > 0) {
		return $('#'+componentId+' .reflection-face-ro.selected').attr("data-reflection-index");
	}
	return null;
};

_.components.ratings.view_lesson_reflection.setSelectedReflectionIndex = function(index) {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	$('#'+componentId+' .reflection-face-ro').addClass("hidden").removeClass("selected");
	$('#'+componentId+' .reflection-face-ro[data-reflection-index="'+index+'"]').removeClass("hidden").addClass("selected");
};
