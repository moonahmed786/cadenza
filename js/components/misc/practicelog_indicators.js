if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.practicelog_indicators = {};

_.components.misc.practicelog_indicators.practicelogIndicatorsIdPrefix = 'id_practicelog_indicators_';
_.components.misc.practicelog_indicators.practicelogIndicatorsClass = 'practicelog-indicators';

_.components.misc.practicelog_indicators.init = function() {
	// do nothing (nothing to initialize)
};

_.components.misc.practicelog_indicators.initPractice = function(practice_id) {
	// do nothing (nothing to initialize)
};

_.components.misc.practicelog_indicators.refreshComponent = function(html) {
	var practice_id = $(html).attr('data-practice-id');
	$('#'+this.practicelogIndicatorsIdPrefix+practice_id).replaceWith(html);
	this.initPractice(practice_id);
};
