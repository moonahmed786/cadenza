if (!_.widgets) _.widgets = {};
_.widgets.filter = {};

_.widgets.filter.init = function(widgetId, changeCallback) {
	var filterDropdownId = widgetId+'_dropdown';
	var selectedIndex = $('#'+filterDropdownId).attr("data-selected-index");
	var selectedItem = $('#'+filterDropdownId+" .dropdown-menu li a[data-select-index="+selectedIndex+"]");
	
	// init dropdown
	$('#'+filterDropdownId+" .dropdown-toggle").dropdown();
	
	// init dropdown text
	$('#'+filterDropdownId+" .filter-text").text(selectedItem.text());
	
	$('#'+filterDropdownId+" .dropdown-menu li a").each(function() {
		var filterOption = $(this);
		
		filterOption.off("click");
		filterOption.click(function(e) {
			e.preventDefault();
			selectedIndex = $(filterOption).attr("data-select-index");
			selectedItem = filterOption;
			
			$('#'+filterDropdownId).attr("data-selected-index", selectedIndex);
			$('#'+filterDropdownId+" .filter-text").text(selectedItem.text());
			
			changeCallback(selectedIndex);
		});
	});
};