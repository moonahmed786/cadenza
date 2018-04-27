if (!_.components) _.components = {};
if (!_.components.navbars) _.components.navbars = {};
_.components.navbars.teacher = {};

// NOTE: This js component depends on the following other js components
// - modals/add_students
// - tables/list_of_invites

_.components.navbars.teacher.navbarId = 'id_navbar_teacher';
_.components.navbars.teacher.isStudentSearchInited = false;

_.components.navbars.teacher.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.navbarId;
	var addStudentsBtnId = componentId + '_add_students_btn';
	
	// Add Students button
	$('#'+addStudentsBtnId).click(function(e) {
		e.preventDefault();
		_.components.navbars.teacher.cmdAddStudents();
	});

	// Student Search Dropdown
	$('#'+componentId+'_student_search_dropdown').on('show.bs.dropdown', function() {
		// reset value of search box
		if (!thisComponent.isStudentSearchInited) {
			thisComponent.initStudentSearch();
		}
		setTimeout(function() {
			if ($('#id_navbar_teacher .typeahead.tt-input').length > 0) {
				$('#id_navbar_teacher .typeahead.tt-input')[0].focus();
			}
		}, 0);
	});
};

_.components.navbars.teacher.initStudentSearch = function() {
	var thisComponent = this;
	var componentId = thisComponent.navbarId;
	
	// set inited to true
	thisComponent.isStudentSearchInited = true;
	
	// constructs the suggestion engine
	var students = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		
		prefetch: {
			url: _.page.url + '?action=getStudentSearchData',
			cache: false, // always reload on new pages
			transform: function(data) {
				return data.studentSearchData;
			}
		}
	});
	
	var redirectToStudent = function(student) {
		if (student) {
			// redirect user to student's page
			window.location = "lessons.php?student_id=" + student.uid;
		}
	};
	
	var emptyMessage = $('#'+componentId+' .typeahead').data("empty-message");

	$('#'+componentId+' .typeahead').typeahead({
			hint: true,
			highlight: true,
			minLength: 1
		}, {
			limit: 3,
			source: students,
			display: 'name',
			templates: {
				empty: '<div class="empty">'
					+ emptyMessage
					+ '</div>',
				suggestion: function(data) {
					return '<div>'
					+ '<img src="'+data.picture+'" alt="'+data.name+'" width="60" height="60" />'
					+ data.name
					+ '</div>';
			}
		}
	}).bind('typeahead:select', function(ev, suggestion) {
		redirectToStudent(suggestion);
	});
	
	// auto select first option when user presses enter
	$('#'+componentId+' .typeahead').keyup(function(e) {
	    if (e.which == 13) { // enter key
    		// redirect to first match for the current search string
	    	var searchString = $('#'+componentId+' .typeahead.tt-input').val();
    		
	    	students.search(searchString, function(results) {
	    		if (results.length > 0) {
		    		redirectToStudent(results[0]);
	    		}
	    	});
	    }
	});
};

_.components.navbars.teacher.cmdAddStudents = function() {
	this.reload(function() { // also includes add students modal, which in turn includes list of invites
		_.components.modals.add_students.open();
	});
};

_.components.navbars.teacher.reload = function(done) {
	var thisComponent = this;
	_.page.actionPost('loadNavbar', {}, function(response) {
		thisComponent.refreshComponent(response.html);
		done();
	});
};

_.components.navbars.teacher.refreshComponent = function(html) {
	$('#'+this.navbarId).replaceWith(html);
	this.init();
	_.components.modals.add_students.init();
	_.components.tables.list_of_invites.init();
};
