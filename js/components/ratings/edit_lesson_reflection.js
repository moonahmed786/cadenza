if (!_.components) _.components = {};
if (!_.components.ratings) _.components.ratings = {};
_.components.ratings.edit_lesson_reflection = {};

_.components.ratings.edit_lesson_reflection.ratingId = 'id_rating_edit_lesson_reflection';

_.components.ratings.edit_lesson_reflection.init = function() {
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

_.components.ratings.edit_lesson_reflection.getSelectedReflectionIndex = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	if ($('#'+componentId+' .reflection-face.selected').length > 0) {
		return $('#'+componentId+' .reflection-face.selected').attr("data-reflection-index");
	}
	return null;
};

_.components.ratings.edit_lesson_reflection.setSelectedReflectionIndex = function(index) {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	if (index > 0) {
		$('#'+componentId+' .reflection-face').removeClass("selected");
		$('#'+componentId+' .reflection-face[data-reflection-index="'+index+'"]').addClass("selected");
		$('#'+componentId+' .reflection-face-ro').attr('data-reflection-index', index).html($('#'+componentId+' .reflection-face.selected').html());
	}
	else {
		$('#'+componentId+' .reflection-face').removeClass("selected");
		$('#'+componentId+' .reflection-face-ro').attr('data-reflection-index', "").html("");
	}
};

_.components.ratings.edit_lesson_reflection.isShowEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	return !($('#'+componentId+' .reflection-faces.edit').hasClass("hidden"));
};

_.components.ratings.edit_lesson_reflection.showEdit = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	$('#'+componentId+' .reflection-faces.saved').addClass("hidden");
	$('#'+componentId+' .reflection-faces.edit').removeClass("hidden");
};

_.components.ratings.edit_lesson_reflection.showSaved = function() {
	var thisComponent = this;
	var componentId = thisComponent.ratingId;
	$('#'+componentId+' .reflection-faces.edit').addClass("hidden");
	$('#'+componentId+' .reflection-faces.saved').removeClass("hidden");
};
