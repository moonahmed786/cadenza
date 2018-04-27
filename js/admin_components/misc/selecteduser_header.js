if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.misc) _.admin_components.misc = {};
_.admin_components.misc.selecteduser_header = {};

_.admin_components.misc.selecteduser_header.selecteduserHeaderId = 'id_selecteduser_header';

_.admin_components.misc.selecteduser_header.init = function() {
	// do nothing (nothing to initialize)
};

_.admin_components.misc.selecteduser_header.refreshAdminComponent = function(html) {
	$('#'+this.selecteduserHeaderId).replaceWith(html);
	this.init();
};
