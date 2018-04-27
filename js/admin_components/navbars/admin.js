if (!_.admin_components) _.admin_components = {};
if (!_.admin_components.navbars) _.admin_components.navbars = {};
_.admin_components.navbars.admin = {};

_.admin_components.navbars.admin.navbarId = 'id_navbar_admin';
_.admin_components.navbars.admin.isUserSearchInited = false;

_.admin_components.navbars.admin.init = function() {
	var thisAdminComponent = this;
	var adminComponentId = thisAdminComponent.navbarId;
	
	// User Search Dropdown
	$('#'+adminComponentId+'_user_search_dropdown').on('show.bs.dropdown', function() {
		// reset value of search box
		if (!thisAdminComponent.isUserSearchInited) {
			thisAdminComponent.initUserSearch();
		}
		setTimeout(function() {
			if ($('#id_navbar_admin .typeahead.tt-input').length > 0) {
				$('#id_navbar_admin .typeahead.tt-input')[0].focus();
			}
		}, 0);
	});
};

_.admin_components.navbars.admin.initUserSearch = function() {
	var thisAdminComponent = this;
	var adminComponentId = thisAdminComponent.navbarId;
	
	// set inited to true
	thisAdminComponent.isUserSearchInited = true;
	
	// constructs the suggestion engine
	var users = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('uid', 'name', 'email'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		
		prefetch: {
			url: _.page.url + '?action=getUserSearchData',
			cache: false, // always reload on new pages
			transform: function(data) {
				return data.userSearchData;
			}
		}
	});
	
	var redirectToUser = function(user) {
		if (user) {
			// redirect user to user's page
			window.location = "view_user.php?uid=" + user.uid;
		}
	};
	
	var emptyMessage = $('#'+adminComponentId+' .typeahead').data("empty-message");

	$('#'+adminComponentId+' .typeahead').typeahead({
			hint: true,
			highlight: true,
			minLength: 1
		}, {
			limit: 10,
			source: users,
			display: 'name',
			templates: {
				empty: '<div class="empty">'
					+ emptyMessage
					+ '</div>',
				suggestion: function(data) {
					return '<div class="row nogutters">'
                    + '<div class="col-xs-3">' 
					+ '<img src="'+data.picture+'" alt="'+data.name+'" width="60" height="60" />'
					+ '</div>'
                    + '<div class="col-xs-9">' 
					+ _.translate("user_id") + ": " + data.uid + '<br />' 
					+ _.translate("name") + ": " + data.name + '<br />' 
					+ _.translate("email") + ": " + data.email
					+ '</div>'
					+ '</div>';
			}
		}
	}).bind('typeahead:select', function(ev, suggestion) {
		redirectToUser(suggestion);
	});
	
	// auto select first option when user presses enter
	$('#'+adminComponentId+' .typeahead').keyup(function(e) {
	    if (e.which == 13) { // enter key
    		// redirect to first match for the current search string
	    	var searchString = $('#'+componentId+' .typeahead.tt-input').val();
    		
	    	users.search(searchString, function(results) {
	    		if (results.length > 0) {
		    		redirectToUser(results[0]);
	    		}
	    	});
	    }
	});
};

_.admin_components.navbars.admin.refreshAdminComponent = function(html) {
	$('#'+this.navbarId).replaceWith(html);
	this.init();
};
