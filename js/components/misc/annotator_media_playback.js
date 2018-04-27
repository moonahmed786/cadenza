if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.annotator_media_playback = {};

_.components.misc.annotator_media_playback.componentId = "id_annotator_media_playback";
_.components.misc.annotator_media_playback.videoJSPlayer = null;
_.components.misc.annotator_media_playback.annotionStartTimes = [];
_.components.misc.annotator_media_playback.file = null;

_.components.misc.annotator_media_playback.THUMB_SIZE_ESTIMATE = 7;

_.components.misc.annotator_media_playback.PLAYER_SETTINGS = {
	'controls': true,
	'preload': "auto",
	'techOrder': ['html5','flash'],
    'plugins': {
    	'videoJsRotationSwitcher': {}
    }
};

_.components.misc.annotator_media_playback.init = function() {
	this.reset();
};

_.components.misc.annotator_media_playback.reset = function() {
	if (this.videoJSPlayer) {
		this.videoJSPlayer.dispose();
	}
	this.videoJSPlayer = null;
	this.annotionStartTimes = [];
	this.file = null;
};

_.components.misc.annotator_media_playback.setupNewMediaPlayerWithError = function() {
	var thisComponent = this;
	var componentId = thisComponent.componentId;

	var domObject = $('<video></video>');

	domObject.attr("id", componentId+"_player");
	domObject.addClass("video-js");
	domObject.addClass("vjs-default-skin"); // default skin
	domObject.addClass("vjs-fluid");
	
	$("#"+componentId).html(domObject);
	
	videojs(componentId+"_player", thisComponent.PLAYER_SETTINGS, function() {
		thisComponent.videoJSPlayer = this;
		thisComponent.videoJSPlayer.error("Cannot find file. You no longer have access to this file.");
	});
};

_.components.misc.annotator_media_playback.setupNewMediaPlayer = function(file) {
	var thisComponent = this;
	var componentId = thisComponent.componentId;
	
	thisComponent.file = file;

	var isVideo = (thisComponent.file.mimeType.indexOf("video/") > -1);
	var domObject = isVideo ? $('<video></video>') : $('<audio></audio>');
	
	domObject.attr("id", componentId+"_player");
	domObject.addClass("video-js");
	domObject.addClass("vjs-default-skin"); // default skin
	domObject.addClass("vjs-fluid");
	domObject.addClass("vjs-big-play-centered");
	
	/* HACK: makes videos uploaded from iOS work for desktop */
	var playerSettings = thisComponent.PLAYER_SETTINGS;
	var mimeType = thisComponent.file.mimeType;
	
	// if dealing with .MOV, some settings need to be changed
	if (mimeType == "video/quicktime") {
		mimeType = "video/mp4";
        
        // HACK: Firefox only - switch tech order so flash is first. Required to play .MOV in Firefox on a Mac with videojs
        if (_.page.isFirefox()) {
		    playerSettings['techOrder'] = ['flash', 'html5'];
        }
	}
	
	domObject.html('<source src="'+thisComponent.getFixedDownloadingUrl()+'" type="'+mimeType+'" />');
	
	$("#"+componentId).html(domObject);
	
	videojs(componentId+"_player", playerSettings, function() {
		thisComponent.videoJSPlayer = this;
		
		if (thisComponent.videoJSPlayer.techName_.toLowerCase() == "flash") {
			// There are errors with videojs flash tech wrapper (flash.js) if the flash object
			// isn't loaded and there are calls like pause. In order to get around this, we must
			// play the video to force the flash to load. Since we do not want it to autoplay
			// after loading, we need to pause in a timeout (timeout required to make sure the
			// tech is loaded)
			thisComponent.play();
		    setTimeout(function() { thisComponent.pause(); }, 0);
		    
		    // The flash player can take a long time to load so its visually helpful to
		    // force the waiting (loading indicator) while waiting
			$("#"+componentId+"_player").addClass("vjs-waiting");
		}

		thisComponent.videoJSPlayer.on('timeupdate', function() {
			var currentTime = thisComponent.videoJSPlayer.currentTime();
			var showAll = currentTime == 0 || thisComponent.videoJSPlayer.ended();
			
			_.components.misc.annotator_annotations.updateVisibleAnnotations(currentTime, showAll, true);
			
			thisComponent.updateAnnotationPositions();
		});
		
		// show player when meta data is loaded
		thisComponent.videoJSPlayer.on('loadedmetadata', function() {
            thisComponent.videoJSPlayer.show();
            
            if (thisComponent.annotionStartTimes.length > 0) {
                thisComponent.resetAnnotationMarks();
                thisComponent.updateAnnotationPositions();
            }
            
            var videoHeight = thisComponent.videoJSPlayer.videoHeight();
            var videoWidth = thisComponent.videoJSPlayer.videoWidth();
            // if is video (has height and width) and is portrait video (height is larger than width)
            if (videoHeight > 0 && videoWidth > 0 && videoHeight > videoWidth) {
                // remove fluid layout and apply fixed height
                $("#"+componentId+"_player").removeClass("vjs-fluid");
                $("#"+componentId+"_player").addClass("portrait-video");
            }
            
            // fix in 'live' mode
            if (thisComponent.videoJSPlayer.duration() == 0 && thisComponent.videoJSPlayer.bufferedPercent() == 0) {
                _.components.misc.annotator_media_playback.fixLiveModeIssue();
            }
		});
		
		thisComponent.videoJSPlayer.on('play', function() {
			// remove causedPlaybackPause state whenever player starts playing
			// useful if user clicks play while media is paused during create/edit comment
			_.components.misc.annotator_write_annotation.causedPlaybackPause = null;
		});
	});
};

_.components.misc.annotator_media_playback.updateAnnotationPositions = function() {
	var thisComponent = this;
	
	var progressContainer = $("#"+thisComponent.componentId+" .vjs-progress-control .vjs-progress-holder");
	var progressWidth = progressContainer.find(".vjs-play-progress").width();
	
	var minWidthBound = progressWidth - (thisComponent.THUMB_SIZE_ESTIMATE / 2);
	var maxWidthBound = progressWidth + (thisComponent.THUMB_SIZE_ESTIMATE / 2);
	
	progressContainer.find(".annotation-marker").each(function() {
		var annotationMarker = $(this);
		annotationMarkerWidth = annotationMarker.width();
		
		if (annotationMarkerWidth >= minWidthBound && annotationMarkerWidth <= maxWidthBound) {
			// should be pushed up
			if (!annotationMarker.hasClass("pushed-up")) {
				annotationMarker.addClass("pushed-up");
				annotationMarker.animate({
					top: "-5px"
				}, 500);
			}
			
		}
		else {
			// should be in default state
			if (annotationMarker.hasClass("pushed-up")) {
				annotationMarker.removeClass("pushed-up");
				annotationMarker.animate({
					top: "0px"
				}, 500);
			}
		}
	});
};

_.components.misc.annotator_media_playback.setAnnotations = function(annotionStartTimes) {
	this.annotionStartTimes = annotionStartTimes;
	if (this.videoJSPlayer && this.videoJSPlayer.duration()) {
		this.resetAnnotationMarks();
		this.updateAnnotationPositions();
	}
};

_.components.misc.annotator_media_playback.resetAnnotationMarks = function() {
	var thisComponent = this;
	
	if (thisComponent.videoJSPlayer && thisComponent.videoJSPlayer.duration()) {
		var duration = thisComponent.videoJSPlayer.duration();
		var progressContainer = $("#"+thisComponent.componentId+" .vjs-progress-control .vjs-progress-holder");
		progressContainer.find(".annotation-marker").remove();
		
		$.each(thisComponent.annotionStartTimes, function(key, startTime) {
			var positionPercentage = (startTime / duration) * 100;
			var newMarker = $("<div></div>");
			newMarker.addClass("annotation-marker");
			newMarker.css({
				"width": positionPercentage + "%"
			});
			
			progressContainer.append(newMarker);
		});
	}
};

_.components.misc.annotator_media_playback.getFixedDownloadingUrl = function() {
	var downloadUrl = "https://www.googleapis.com/drive/v3/files/"+this.file.id+"?alt=media";

	downloadUrl +=  "&access_token="+_.drive.getAccessToken();
	
	return downloadUrl;
};

_.components.misc.annotator_media_playback.isPlaying = function() {
	if (this.videoJSPlayer) {
		return !this.videoJSPlayer.paused();
	}
};

_.components.misc.annotator_media_playback.isPaused = function() {
	if (this.videoJSPlayer) {
		return this.videoJSPlayer.paused();
	}
};

_.components.misc.annotator_media_playback.play = function() {
	if (this.videoJSPlayer) {
		return this.videoJSPlayer.play();
	}
};

_.components.misc.annotator_media_playback.pause = function() {
	if (this.videoJSPlayer) {
		return this.videoJSPlayer.pause();
	}
};

_.components.misc.annotator_media_playback.setCurrentTime = function(time) {
	if (this.videoJSPlayer) {
		return this.videoJSPlayer.currentTime(time);
	}
};

_.components.misc.annotator_media_playback.getCurrentTime = function() {
	if (this.videoJSPlayer) {
		return this.videoJSPlayer.currentTime();
	}
};

_.components.misc.annotator_media_playback.fixLiveModeIssue = function() {
	var thisComponent = this;
	var componentId = thisComponent.componentId;
    
    // is in 'live' mode
    if (thisComponent.videoJSPlayer.duration() == 0 && thisComponent.videoJSPlayer.bufferedPercent() == 0) {
        // disable play button until duration is known
        $("#"+componentId+"_player button.vjs-play-control").prop("disabled", true);
        
        // show loading spinner
		$("#"+componentId+"_player").addClass("vjs-seeking");
        
        // hide big play button
        thisComponent.videoJSPlayer.bigPlayButton.hide();
		
		thisComponent.videoJSPlayer.on('durationchange', function() {
            if (thisComponent.videoJSPlayer.bufferedPercent() != 0) {
				thisComponent.videoJSPlayer.off('durationchange');
                
                // enable play button
                $("#"+componentId+"_player button.vjs-play-control").prop("disabled", false);
                
                // hide loading spinner 
		        $("#"+componentId+"_player").removeClass("vjs-seeking");
                
                // show big play button
                thisComponent.videoJSPlayer.bigPlayButton.show();
                
                // update time ramining display
                thisComponent.videoJSPlayer.controlBar.remainingTimeDisplay.updateContent();
                
                if (thisComponent.annotionStartTimes.length > 0) {
                    thisComponent.resetAnnotationMarks();
                    thisComponent.updateAnnotationPositions();
                }
            }
		});
    }
};

// Rotate Video Plugin
(function() {
    var defaults = {};
    var currentRotation = null;
	
	var MenuItem = videojs.getComponent('MenuItem');
	var RotationMenuItem = videojs.extend(MenuItem, {
		constructor: function(player, options, onClickListener) {
			this.onClickListener = onClickListener;
			this.degrees = options.degrees;
			
			MenuItem.call(this, player, options);
			
			this.on('click', this.onClick);
			this.on('touchstart', this.onClick);
			
			if (options.initialySelected) {
				this.selected(true);
			}
		},
		onClick: function() {
			var scale = 1;
			var degrees = this.degrees;
			
			if (degrees == 90 || degrees == 270) {
				var videoHeight = this.player_.videoHeight();
				var videoWidth = this.player_.videoWidth();
				
				if (videoHeight > 0 && videoWidth > 0) {
					scale = (videoHeight * 1.0 / videoWidth).toFixed(5);
				}
			}
			
			var transformation_string = 'scale('+scale+') rotate('+degrees+'deg)';
	
		    $(this.player_.tech_.el_).css({
		    	'-moz-transform': transformation_string,
		    	'-webkit-transform': transformation_string,
		    	'-o-transform': transformation_string,
		    	'-ms-transform': transformation_string,
		    	'transform:rotate': transformation_string
		    });
			
			this.onClickListener(this);
		}
	});
	
	var MenuButton = videojs.getComponent('MenuButton');
	var RotationMenuButton = videojs.extend(MenuButton, {
		constructor: function(player, options, settings) {
			MenuButton.call(this, player, options, settings);
			this.controlText('Rotation');

			var staticLabel = document.createElement('span');
			staticLabel.classList.add('vjs-rotation-button');
			staticLabel.innerHTML = _.translate('rotate_degrees');
			this.el().appendChild(staticLabel);
		},
		createItems: function() {
			var menuItems = [];
			
			var onClickUnselectOthers = function(clickedItem) {
				menuItems.map(function(item) {
					item.selected(item === clickedItem);
				});
			};
			menuItems.push(new RotationMenuItem(this.player_, { 'label': _.translate('x_deg').replace('{x}', '0'), 'degrees': 0, 'initialySelected': true }, onClickUnselectOthers));
			menuItems.push(new RotationMenuItem(this.player_, { 'label': _.translate('x_deg').replace('{x}', '90'), 'degrees': 90, 'initialySelected': false }, onClickUnselectOthers));
			menuItems.push(new RotationMenuItem(this.player_, { 'label': _.translate('x_deg').replace('{x}', '180'), 'degrees': 180, 'initialySelected': false }, onClickUnselectOthers));
			menuItems.push(new RotationMenuItem(this.player_, { 'label': _.translate('x_deg').replace('{x}', '270'), 'degrees': 270, 'initialySelected': false }, onClickUnselectOthers));
			
			return menuItems;
		}
	});
	
	var videoJsRotationSwitcher = function(options) {
		var settings = options;
		var player = this;

        player.ready(function() {
			// Dispose old rotation menu button before adding new sources
			if (player.controlBar.rotationSwitcher) {
				player.controlBar.rotationSwitcher.dispose();
				delete player.controlBar.rotationSwitcher;
			}

			var menuButton = new RotationMenuButton(player, {}, settings);
			menuButton.el().classList.add('vjs-rotation-button');
			player.controlBar.rotationSwitcher = player.controlBar.el_.insertBefore(menuButton.el_, player.controlBar.getChild('fullscreenToggle').el_);
        });
	};

	videojs.plugin('videoJsRotationSwitcher', videoJsRotationSwitcher);
})();
