if (!_.components) _.components = {};
if (!_.components.ratings) _.components.ratings = {};
_.components.ratings.practice_reflection = {};

_.components.ratings.practice_reflection.ratingId = 'id_rating_practice_reflection';

_.components.ratings.practice_reflection.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	$('#'+componentId+' .reflection-face').click(function(e) {
		var jqObj = $(this);
		var oldReflectionIndex = thisComponent.getSelectedReflectionIndex();
		var reflectionIndex = jqObj.attr('data-reflection-index');
		e.preventDefault();
		jqObj.blur();
		if (reflectionIndex != oldReflectionIndex) {
			thisComponent.setSelectedReflectionIndex(reflectionIndex);
		}
		else {
			thisComponent.setSelectedReflectionIndex(0); // unselect
		}
	});
};

_.components.ratings.practice_reflection.getSelectedReflectionIndex = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	if ($('#'+componentId+' .reflection-face.selected').length > 0) {
		return $('#'+componentId+' .reflection-face.selected').attr("data-reflection-index");
	}
	return null;
};

_.components.ratings.practice_reflection.setSelectedReflectionIndex = function(index) {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	if (index > 0) {
		$('#'+componentId+' .reflection-face').removeClass("selected");
		$('#'+componentId+' .reflection-face[data-reflection-index="'+index+'"]').addClass("selected");
	}
	else {
		$('#'+componentId+' .reflection-face').removeClass("selected");
	}
};
