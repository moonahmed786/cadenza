if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.welcome_student = {};

_.components.misc.welcome_student.welcomeId = 'id_welcome_student';
_.components.misc.welcome_student.indexMin;
_.components.misc.welcome_student.indexMax;

_.components.misc.welcome_student.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.welcomeId;
	var refreshBtnId = this.welcomeId + '_refresh_btn';
	var carouselId = componentId + '_carousel';
	var prevBtnId = componentId + '_prev_btn';
	var nextBtnId = componentId + '_next_btn';
	thisComponent.indexMin = 0;
	thisComponent.indexMax = $('#'+carouselId+' .item').length - 1;
	
	// Carousel
	$('#'+carouselId).on('slid.bs.carousel', function() {
		var slidToIndex = $('#'+carouselId+' .item.active').index();
		thisComponent.setPrevButtonEnabled(slidToIndex > thisComponent.indexMin);
		thisComponent.setNextButtonEnabled(slidToIndex < thisComponent.indexMax);
	});
	// Prev button
	$('#'+prevBtnId).click(function(e) {
		// just blur for consistency with the rest of the tool
		// (the actual sliding/animation is already handled by the bootstrap carousel)
		$(this).blur();
	});
	// Next button
	$('#'+nextBtnId).click(function(e) {
		// just blur for consistency with the rest of the tool
		// (the actual sliding/animation is already handled by the bootstrap carousel)
		$(this).blur();
	});
	// Refresh button
	$('#'+refreshBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		window.location.reload();
	});
};

_.components.misc.welcome_student.setPrevButtonEnabled = function(setEnabled) {
	var prevBtnId = this.welcomeId + '_prev_btn';
	if (setEnabled) {
		$('#'+prevBtnId).removeClass("disabled");
	}
	else {
		$('#'+prevBtnId).addClass("disabled");
	}
};
_.components.misc.welcome_student.setNextButtonEnabled = function(setEnabled) {
	var nextBtnId = this.welcomeId + '_next_btn';
	if (setEnabled) {
		$('#'+nextBtnId).removeClass("disabled");
	}
	else {
		$('#'+nextBtnId).addClass("disabled");
	}
};
