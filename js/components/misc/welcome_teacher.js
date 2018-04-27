if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.welcome_teacher = {};

// NOTE: This js component depends on the following other js components
// - navbars/teacher

_.components.misc.welcome_teacher.welcomeId = 'id_welcome_teacher';

_.components.misc.welcome_teacher.init = function() {
	var addStudentsBtnId = this.welcomeId + '_add_students_btn';
	var refreshBtnId = this.welcomeId + '_refresh_btn';
	// Add Students button
	$('#'+addStudentsBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		_.components.navbars.teacher.cmdAddStudents();
	});
	// Refresh button
	$('#'+refreshBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		window.location.reload();
	});
};
