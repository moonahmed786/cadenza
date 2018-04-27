if (!_.components) _.components = {};
if (!_.components.timers) _.components.timers = {};
_.components.timers.editonly_timer_student = {};

_.components.timers.editonly_timer_student.timerId = 'id_timer_editonly_timer_student';

_.components.timers.editonly_timer_student.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	$('#'+componentId+' .input-num').focus(function() {
		var jqObj = $(this);
		jqObj.select();
	});
	$('#'+componentId+' .input-num').change(function() {
		var jqObj = $(this);
		var num = num = parseInt(jqObj.val());
		if (jqObj.parent().hasClass("edit-hrs")) {
			if (isNaN(num) || num < 0) {
				num = 0;
			}
			jqObj.val(num);
		}
		else if (jqObj.parent().hasClass("edit-min") || jqObj.parent().hasClass("edit-sec")) {
			if (isNaN(num) || num < 0) {
				num = 0;
			}
			if (num > 59) {
				num = 59;
			}
			if (num < 10) {
				jqObj.val('0'+num);
			}
			else {
				jqObj.val(num);
			}
		}
	});
};

_.components.timers.editonly_timer_student.getTotalSec = function() {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	var isEditHrs = $('#'+componentId).attr('data-edit-hrs') ? true : false;
	var isEditMin = $('#'+componentId).attr('data-edit-min') ? true : false;
	var isEditSec = $('#'+componentId).attr('data-edit-sec') ? true : false;
	var totalSec = 0;
	if (isEditSec) {
		totalSec += parseInt($('#'+componentId+' .edit-sec .input-num').val());
	}
	if (isEditMin) {
		totalSec += 60 * parseInt($('#'+componentId+' .edit-min .input-num').val());
	}
	if (isEditHrs) {
		totalSec += 3600 * parseInt($('#'+componentId+' .edit-hrs .input-num').val());
	}
	return totalSec;
};
_.components.timers.editonly_timer_student.getTotalMin = function() {
	var thisComponent = this;
	var totalSec = thisComponent.getTotalSec();
	return Math.floor(totalSec / 60);
};

_.components.timers.editonly_timer_student.setTotalMin = function(totalMin) {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	var hrs = Math.floor(totalMin / 60);
	var min = totalMin - (hrs * 60);
	if (min < 10) {
		min = '0'+min;
	}
	var sec = '00';
	$('#'+componentId+' .edit-hrs .input-num').val(hrs);
	$('#'+componentId+' .edit-min .input-num').val(min);
	$('#'+componentId+' .edit-sec .input-num').val(sec);
};
