if (!_.components) _.components = {};
if (!_.components.navbars) _.components.navbars = {};
_.components.navbars.student = {};

_.components.navbars.student.navbarId = 'id_navbar_student';

_.components.navbars.student.init = function() {
	// do nothing (nothing to initialize)
};

_.components.navbars.student.refreshComponent = function(html) {
	$('#'+this.navbarId).replaceWith(html);
	this.init();
};
