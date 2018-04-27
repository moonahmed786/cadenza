if (!_.components) _.components = {};
if (!_.components.timers) _.components.timers = {};
_.components.timers.practice_timer = {};

_.components.timers.practice_timer.timerId = 'id_timer_practice_timer';
_.components.timers.practice_timer.timerStart = null;
_.components.timers.practice_timer.timerSec = 0;
_.components.timers.practice_timer.timerInterval = null;
_.components.timers.practice_timer.tickerIntervalMilliseconds = 100;
_.components.timers.practice_timer.tickerOnlySecFirstMin = true;
_.components.timers.practice_timer.tickerNoSecAfterFirstMin = true;
_.components.timers.practice_timer.tickerMaxTotalSec = 35940; // 9h59m

_.components.timers.practice_timer.init = function() {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	var pauseBtnId = componentId + '_pause_btn';
	var resumeBtnId = componentId + '_resume_btn';
	var editBtnId = componentId + '_edit_btn';
	var saveBtnId = componentId + '_save_btn';
	var isStartTimer = $('#'+componentId).attr('data-autostart') ? true : false;
	var delayMilliseconds = $('#'+componentId).attr('data-autostart-delay') ? parseInt($('#'+componentId).attr('data-autostart-delay')) * 1000 : 0;
	var isEditHrs = $('#'+componentId).attr('data-edit-hrs') ? true : false;
	var isEditMin = $('#'+componentId).attr('data-edit-min') ? true : false;
	var isEditSec = $('#'+componentId).attr('data-edit-sec') ? true : false;
	$('#'+pauseBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.pause();
		jqObj.addClass("hidden");
		$('#'+resumeBtnId).removeClass("hidden");
	});
	$('#'+resumeBtnId).click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		thisComponent.resume();
		jqObj.addClass("hidden");
		$('#'+pauseBtnId).removeClass("hidden");
	});
	$('#'+editBtnId).click(function(e) {
		var jqObj = $(this);
		var hrs = $('#'+componentId+' .hrs').text().substr(0, 1);
		var min = $('#'+componentId+' .min').text().substr(0, 2);
		var sec = $('#'+componentId+' .sec').text().substr(0, 2);
		if (!isEditSec) {
			// if ticker is currently showing seconds
			if (parseInt(min) == 0 || !thisComponent.tickerNoSecAfterFirstMin) {
				if (parseInt(sec) > 30) {
					min = parseInt(min) + 1;
					if (min < 10) {
						min = '0' + min;
					}
				}
			}
		}
		e.preventDefault();
		jqObj.blur();
		thisComponent.pause();
		if (isEditHrs) {
			$('#'+componentId+' .edit-hrs .input-num').val(hrs);
		}
		if (isEditMin) {
			$('#'+componentId+' .edit-min .input-num').val(min);
		}
		if (isEditSec) {
			$('#'+componentId+' .edit-sec .input-num').val(sec);
		}
		$('#'+componentId+' .timertime').addClass("hidden");
		$('#'+componentId+' .timeredit').removeClass("hidden");
		jqObj.addClass("hidden");
		$('#'+pauseBtnId).addClass("hidden");
		$('#'+resumeBtnId).addClass("hidden");
		$('#'+saveBtnId).removeClass("hidden");
	});
	$('#'+saveBtnId).click(function(e) {
		var jqObj = $(this);
		var totalSec = 0;
		e.preventDefault();
		jqObj.blur();
		if (isEditSec) {
			totalSec += parseInt($('#'+componentId+' .edit-sec .input-num').val());
			$('#'+componentId+' .sec').text($('#'+componentId+' .edit-sec .input-num').val()+'s');
		}
		else {
			$('#'+componentId+' .sec').text('00s');
		}
		if (isEditMin) {
			totalSec += 60 * parseInt($('#'+componentId+' .edit-min .input-num').val());
			$('#'+componentId+' .min').text($('#'+componentId+' .edit-min .input-num').val()+'m');
		}
		else {
			$('#'+componentId+' .min').text('00m');
		}
		if (isEditHrs) {
			totalSec += 3600 * parseInt($('#'+componentId+' .edit-hrs .input-num').val());
			$('#'+componentId+' .hrs').text($('#'+componentId+' .edit-hrs .input-num').val()+'h');
		}
		else {
			$('#'+componentId+' .hrs').text('0h');
		}
		if (totalSec >= 60) {
			if (thisComponent.tickerOnlySecFirstMin) {
				$('#'+componentId+' .hrs').removeClass("hidden");
				$('#'+componentId+' .min').removeClass("hidden");
			}
			if (thisComponent.tickerNoSecAfterFirstMin) {
				$('#'+componentId+' .sec').addClass("hidden");
			}
		}
		else if (totalSec < 60 && thisComponent.tickerOnlySecFirstMin) {
			$('#'+componentId+' .hrs').addClass("hidden");
			$('#'+componentId+' .min').addClass("hidden");
			$('#'+componentId+' .sec').removeClass("hidden");
		}
		thisComponent.timerSec = totalSec;
		if (thisComponent.timerSec == thisComponent.tickerMaxTotalSec) {
			$('#'+resumeBtnId).addClass("disabled");
		}
		else {
			$('#'+resumeBtnId).removeClass("disabled");
		}
		$('#'+componentId+' .timeredit').addClass("hidden");
		$('#'+componentId+' .timertime').removeClass("hidden");
		jqObj.addClass("hidden");
		$('#'+saveBtnId).addClass("hidden");
		$('#'+editBtnId).removeClass("hidden");
		$('#'+resumeBtnId).removeClass("hidden");
	});
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
	if (thisComponent.timerInterval != null) {
		thisComponent.stop();
	}
	if (thisComponent.tickerOnlySecFirstMin) {
		$('#'+componentId+' .hrs').addClass("hidden");
		$('#'+componentId+' .min').addClass("hidden");
		$('#'+componentId+' .sec').removeClass("hidden");
	}
	if (isStartTimer) {
		thisComponent.autostart(delayMilliseconds);
	}
};

_.components.timers.practice_timer.autostart = function(delayMilliseconds) {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	if (delayMilliseconds > 0) {
		$('#'+componentId+' .timerbuttons > .btn').addClass("disabled");
		setTimeout(function() {
			thisComponent.start();
		}, delayMilliseconds);
	}
	else {
		thisComponent.start();
	}
};

_.components.timers.practice_timer.start = function() {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	thisComponent.timerStart = new Date();
	thisComponent.timerSec = 0;
	if (thisComponent.tickerOnlySecFirstMin) {
		$('#'+componentId+' .hrs').addClass("hidden");
		$('#'+componentId+' .min').addClass("hidden");
		$('#'+componentId+' .sec').removeClass("hidden");
	}
	$('#'+componentId+' .timerbuttons > .btn').removeClass("disabled");
	thisComponent.setTicking(true);
};

_.components.timers.practice_timer.pause = function() {
	var thisComponent = this;
	thisComponent.timerStart = null;
	thisComponent.setTicking(false);
};

_.components.timers.practice_timer.resume = function() {
	var thisComponent = this;
	var currentDate = new Date();
	thisComponent.timerStart = new Date(currentDate.getTime() - (1000 * thisComponent.timerSec));
	thisComponent.setTicking(true);
};

_.components.timers.practice_timer.stop = function() {
	var thisComponent = this;
	thisComponent.timerStart = null;
	thisComponent.timerSec = 0;
	thisComponent.setTicking(false);
};

_.components.timers.practice_timer.tick = function() {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	var currentDate = new Date();
	var num = parseInt((currentDate.getTime() - thisComponent.timerStart) / 1000);
	var hrs;
	var min;
	var sec;
	if (num >= thisComponent.tickerMaxTotalSec) {
		num = thisComponent.tickerMaxTotalSec;
	}
	hrs = Math.floor(num / 3600);
	min = Math.floor((num - (hrs * 3600)) / 60);
	sec = num - (hrs * 3600) - (min * 60);
	thisComponent.timerSec = num;
	
	if (min < 10) {
		min = '0'+min;
	}
	if (sec < 10) {
		sec = '0'+sec;
	}
	if (thisComponent.timerSec >= 60) {
		if (thisComponent.tickerOnlySecFirstMin) {
			$('#'+componentId+' .hrs').removeClass("hidden");
			$('#'+componentId+' .min').removeClass("hidden");
		}
		if (thisComponent.tickerNoSecAfterFirstMin) {
			$('#'+componentId+' .sec').addClass("hidden");
		}
	}
    if ($('#'+componentId+' .hrs').text() != hrs+'h') {
	    $('#'+componentId+' .hrs').text(hrs+'h');
    }
    if ($('#'+componentId+' .min').text() != min+'m') {
	    $('#'+componentId+' .min').text(min+'m');
    }
    if ($('#'+componentId+' .sec').text() != sec+'s') {
	    $('#'+componentId+' .sec').text(sec+'s');
    }
	if (thisComponent.timerSec == thisComponent.tickerMaxTotalSec) {
		thisComponent.stop();
		$('#'+componentId+'_pause_btn').addClass("hidden");
		$('#'+componentId+'_resume_btn').removeClass("hidden").addClass("disabled");
	}
};

_.components.timers.practice_timer.setTicking = function(isTicking) {
	var thisComponent = this;
	var componentId = thisComponent.timerId;
	if (isTicking) {
		$('#'+componentId+' .timericon').attr("data-ticking", "1");
		thisComponent.timerInterval = setInterval(function() {
			thisComponent.tick();
		}, thisComponent.tickerIntervalMilliseconds);
	}
	else {
		$('#'+componentId+' .timericon').attr("data-ticking", "");
		clearInterval(thisComponent.timerInterval);
		thisComponent.timerInterval = null;
	}
};

_.components.timers.practice_timer.getTotalSec = function() {
	return this.timerSec;
};
_.components.timers.practice_timer.getTotalMin = function() {
	var totalMin = Math.floor(this.timerSec / 60);
	if (this.timerSec < 60 || !this.tickerNoSecAfterFirstMin) {
		if (this.timerSec > 30) {
			totalMin++;
		}
	}
	return totalMin;
};
