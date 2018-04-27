_.autocomplete = {};

_.autocomplete.initInputAutocomplete = function(inputId, action) {
	var texts = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		
		prefetch: {
			url: _.page.url + '?action='+action,
			cache: false, // always reload on new pages
			transform: function(data) {
				return data.autocompleteData;
			}
		}
	});

	$('#'+inputId).typeahead({
			hint: true,
			highlight: true,
			minLength: 1
		}, {
			limit: 3,
			source: texts,
			display: 'text',
			templates: {
				suggestion: function(data) {
					return '<div>'
					+ data.text
					+ '</div>';
			}
		}
	}).bind('typeahead:select', function(ev, suggestion) {
		$('#'+inputId).val(suggestion);
	});
};
