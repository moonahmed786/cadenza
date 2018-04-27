_.driveuploads = {};

_.driveuploads.debug = true;
_.driveuploads.uploadCounter = 0;
_.driveuploads.uploadJQXHR = {};

_.driveuploads.initAnnotator = function(componentId) {
	var debug = _.driveuploads.debug;
	var isReadOnly = $('#'+componentId).data("readonly") ? true : false;
	var canAnnotate = $('#'+componentId).data("can-annotate") ? true : false;
	var isPracticing = $('#'+componentId).data("practicing") ? true : false;
	var userEmail = $('#'+componentId).data("user-email");
	var fileJson = $('#'+componentId+'_file_json').val();
	var annotatorData = fileJson ? JSON.parse(fileJson) : {};
	var inputSelector = '#' + componentId + '_input';
    var practiceId = $(inputSelector).data('practice-id');
    var url = _.page.url + '?action=addAnnotatorToPractice';
	var dataType = 'json';

	if (annotatorData.annotator_file_id) {
		var uploadingRowId = _.driveuploads.addAttachmentRow(componentId);
		_.driveuploads.setFileUploaded(uploadingRowId, annotatorData.practice_id, annotatorData.annotator_file_id, annotatorData.annotator_title, componentId, isReadOnly, canAnnotate, isPracticing);
	}
	
	// Initialize annotator input
	if (isReadOnly) {
		$('#'+componentId+'_btns').remove();
	}
	else {
	    $(inputSelector).fileupload({
	    	acceptFileTypes: /^((audio|video)\/(x-)?mpeg.*|audio\/mp3|video\/mp4|video\/quicktime)$/i,
            maxNumberOfFiles: 1,
            limitConcurrentUploads: 4,
	    	singleFileUploads: true,
	        url: url,
	        dataType: dataType,
            // getNumberOfFiles: function() {
            //     return _.driveuploads.getFileCount(componentId);
            // },
            processfail: function(e, data) {
	        	if (debug) { console.debug('driveuploads processfail BEGIN'); }
                if (data.files.length >= 1 && data.files[0].error) {
                    alert(_.translate('file_upload_type_not_allowed'));
                }
	        	if (debug) { console.debug('driveuploads processfail END'); }
            },
            submit(e, data){

            },
	        add: function(e, data) {
	        	if (debug) { console.debug('driveuploads submit BEGIN'); }
                // removing focus on file input fixes display issues in IE
                $(inputSelector).blur();
	        	var options = $(this).fileupload('option');
	        	// isXhrUpload logic from jquery.fileupload.js function _isXHRUpload
	        	var isXhrUpload = !options.forceIframeTransport &&
	                ((!options.multipart && $.support.xhrFileUpload) ||
	                $.support.xhrFormDataFileUpload);
	            var isIframeTransport = !isXhrUpload;

	            // only upload first file (if multiple files are present somehow)
	            if (data.files.length >= 1) {
		            var file = data.files[0];
		            var uploadingRowId = _.driveuploads.addAttachmentRow(componentId);
		             var jqXHR;
		        	data.formData = {
		        		iframe: (isIframeTransport ? 1 : 0),
		        		// lesson_id: (lessonId ? lessonId : 0),
		        		// task_id: (taskId ? taskId : 0),
		        		practice_id: (practiceId ? practiceId : 0),
		        		row_id: uploadingRowId
		        	};
		        	
		        	_.page.addUnsafeNavigationUpload(); // important, must have a corresponding _.page.removeUnsafeNavigationUpload(); (for success, failure, and abort)
		        	
		        	jqXHR = data.submit();

		        	_.driveuploads.uploadJQXHR[uploadingRowId] = jqXHR;

		        	if (debug) { console.debug('driveuploads upload begin'); }
		        	
		        	// Start of upload
		        	_.page.addUnsafeNavigationUpload(); // important, must have a corresponding _.page.removeUnsafeNavigationUpload(); for success, failure, and abort
		        	
		            // _.drive.insertFile(file, userEmail, uploadingRowId, function(file) {
			        	// if (debug) { console.debug('driveuploads upload end'); }
			        	// if (debug) { console.debug(file); }

		        		// // save the file's info to Cadenza db
		        		// var data = {
		        		// 	'row_id': uploadingRowId,
		        		// 	'practice_id': practiceId,
		        		// 	'annotator_file_id': file.id,
		        		// 	'annotator_title': file.name,
		        		// 	'uploadedWithinCadenza': true
		        		// };

			        	// if (debug) { console.debug('addAnnotatorToPractice action begin'); }
						// _.page.actionPost('addAnnotatorToPractice', data, function(response) {
				  //       	if (debug) { console.debug('addAnnotatorToPractice action end'); }
				  //       	if (debug) { console.debug(response); }
				        	
				  //       	// Upload successful
				  //       	_.page.removeUnsafeNavigationUpload(); // important, must have a corresponding _.page.addUnsafeNavigationUpload(); (handles success)

		    //     			_.driveuploads.setFileUploaded(response.row_id, practiceId, file.id, file.name, componentId, isReadOnly, isPracticing);
		    //     			if (response.refresh) {
				  //           	_.page.refreshComponents(response.refresh);
				  //           }
						// });
		   //          }
		   //          , function(error) {
		   //          	// handle upload error
					// 	console.log(error);
			  //       	// Upload Failure
			  //       	// _.page.removeUnsafeNavigationUpload(); // important, must have a corresponding _.page.addUnsafeNavigationUpload(); (handles failure)
		            	
		   //          }, function(progressEvent) {
		   //          	if (progressEvent.lengthComputable) {
			  //           	// handle upload progress
				 //            var progress = parseInt(progressEvent.loaded / progressEvent.total * 100, 10);
				 //            $("#"+uploadingRowId+" .progress .progress-bar").attr("aria-valuenow", progress);
				 //            $("#"+uploadingRowId+" .progress .progress-bar").css({ "width": progress + "%" });
		   //          	}
					// });
		            
		        	// $("#"+uploadingRowId+" .icon-remove").click(function(e) {
		        	// 	e.preventDefault();
		        	// 	_.drive.insertFileAbort(uploadingRowId, function() {
				       //  	// Upload Aborted
				       //  	_.page.removeUnsafeNavigationUpload(); // important, must have a corresponding _.page.addUnsafeNavigationUpload(); (handles abort)
				        	
		        	// 		$('#'+uploadingRowId).remove();
		        	// 		_.driveuploads.checkFileCount(componentId);
		        	// 	});
		        	// });
	            }
	        	
	        	if (debug) { console.debug('driveuploads submit END'); }
	            return false;
	        },
	         done: function(e, data) {
	        	if (debug) { console.debug('fileupload done BEGIN'); }
	        	
	        	var response;
	    		if (_.driveuploads.uploadJQXHR[data.formData.row_id]) {
		        	delete _.driveuploads.uploadJQXHR[data.formData.row_id];
	    		}
	        	response = data.result;
	        	_.page.handleResponse(response, function(response) {
	        		$.each(response.files, function(index, file) {
	        			if (file.error) {
	        				_.driveuploads.setFileUploadFailed(response.row_id, file.error, file.name);
	        			}
	        			else {
	        				_.driveuploads.setFileUploaded(response.row_id, file.id, file.name, componentId, isReadOnly);
	        			}
		            });
		            if (response.refresh) {
		            	_.page.refreshComponents(response.refresh);
		            }
	        	});
	        	if (debug) { console.debug('fileupload done END'); }
	        },
	        always: function(e, data) {
	        	if (debug) { console.debug('fileupload always BEGIN'); }
	        	
	        	_.page.removeUnsafeNavigationUpload(); // important, must have a corresponding _.page.addUnsafeNavigationUpload(); (handles success, failure, and abort)
	        	
	        	if (debug) { console.debug('fileupload always END'); }
	        },
	        progress: function(e, data) {
	        	if (debug) { console.debug('fileupload progress BEGIN'); }
	        	var row_id = data.formData.row_id;
	            var progress = parseInt(data.loaded / data.total * 100, 10);
	            
	            $("#"+row_id+" .progress .progress-bar").attr("aria-valuenow", progress);
	            $("#"+row_id+" .progress .progress-bar").css({ "width": progress + "%" });
	            if (debug) { console.debug('fileupload progress END'); }
	        }
	    }).prop('disabled', !$.support.fileInput)
	        .parent().addClass($.support.fileInput ? undefined : 'disabled');

	    $.each($(inputSelector), function() {
	    	var dropZoneId = componentId + '_upload_btn';
	    	$(this).fileupload('option', 'dropZone', $('#'+dropZoneId));
	    });
	    
	    // Notemaker imports
	    $('#'+componentId+"_import_btn").click(function(e) {
        	if (debug) { console.debug('notemaker_import modal open'); }
        	
	    	_.components.modals.notemaker_import.open(userEmail, function(file) {
	        	if (debug) { console.debug('notemaker_import modal select file success'); }
	        	if (debug) { console.debug(file); }
	        	
	            var uploadingRowId = _.driveuploads.addFileRow(componentId);

        		// save the file's info to Cadenza db
        		var data = {
        			'row_id': uploadingRowId,
        			'practice_id': practiceId,
        			'annotator_file_id': file.id,
        			'annotator_title': file.name
        		};

	        	if (debug) { console.debug('addAnnotatorToPractice action begin'); }
				_.page.actionPost('addAnnotatorToPractice', data, function(response) {
		        	if (debug) { console.debug('addAnnotatorToPractice action end'); }
		        	if (debug) { console.debug(response); }

        			_.driveuploads.setFileUploaded(response.row_id, practiceId, file.id, file.name, componentId, isReadOnly, canAnnotate, isPracticing);
        			if (response.refresh) {
		            	_.page.refreshComponents(response.refresh);
		            }
				});
	    	});
	    	
	    });
    }
};

_.driveuploads.addFileRow = function(componentId) {
	var uploadingRowId = componentId + '_file_new' + (++this.uploadCounter);
	$('#'+componentId+'_file_dummy').clone().attr("id", uploadingRowId).addClass("file-uploading").insertBefore($('#'+componentId+'_btns')).removeClass("hidden");
	this.checkFileCount(componentId);
	return uploadingRowId;
};

_.driveuploads.setFileUploaded = function(uploadingRowId, practiceId, fileId, fileName, componentId, isReadOnly, canAnnotate, isPracticing) {
	var rowId = componentId + '_file_' + practiceId;
	$('#'+uploadingRowId).attr("id", rowId).removeClass("file-uploading").addClass("file-uploaded");

	var fileIcon = $('<i class="fa fa-file-o"></i>');
	var fileIconLink = $('<a href="#"></a>').html(fileIcon);
	var titleLink = $('<a href="#"></a>').text(fileName);
	
	var openAnnotatorClickFunction = function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		$(this).blur();
		
		fileIconLink.html(_.btnIconHtml('loading'));
		
		_.components.modals.annotator.open(practiceId, fileId, fileName, isPracticing, canAnnotate, function() {
			fileIconLink.html(fileIcon);
		});
	};
	fileIconLink.click(openAnnotatorClickFunction);
	titleLink.click(openAnnotatorClickFunction);
	
	$('#'+rowId+' .fileicon').html(fileIconLink);
	$('#'+rowId+' .filename').html(titleLink);
	$('#'+rowId+' .icon-remove').remove();
	$('#'+rowId+' .progress').remove();
	if (isReadOnly) {
		$('#'+rowId+' .icon-delete').remove();
	}
	else {
		$('#'+rowId+' .icon-delete').removeClass("disabled").removeClass("hidden");
	}
	$('#'+rowId+' .icon-delete').click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		e.stopPropagation();
		$('#'+rowId).remove();
		_.driveuploads.checkFileCount(componentId);

		var data = { 'practice_id':practiceId };
		_.page.actionPost('deleteAnnotator', data, function(response) {
			if (response.refresh) {
            	_.page.refreshComponents(response.refresh);
            }
		});
	});
	$('#'+rowId).click(function(e) {
		e.preventDefault();
		e.stopPropagation();
	});

	// if should automatically open annotator modal, mimic title click
    if ($('#'+componentId).data("open-on-load")) {
    	$('#'+componentId).removeAttr("data-open-on-load");
    	titleLink.click();
    }
};

// force only one annotator file per practice
_.driveuploads.getFileCount = function(componentId) {
	var annotatorCount = $('#'+componentId+" .file-uploading").length + $('#'+componentId+" .file-uploaded").length;
	console.log(annotatorCount);
    return annotatorCount;
};


// force only one annotator file per practice
_.driveuploads.checkFileCount = function(componentId) {
	if (_.driveuploads.getFileCount(componentId) == 0) {
		$('#'+componentId+'_btns').removeClass('hidden');
	}
	else {
		$('#'+componentId+'_btns').addClass('hidden');
	}
};


// added by abdulmanan7

_.driveuploads.addAttachmentRow = function(componentId) {
	var uploadingRowId = componentId + '_file_new' + (++this.uploadCounter);
	$('#'+componentId+'_file_dummy').clone().attr("id", uploadingRowId).addClass("file-uploading").insertBefore($('#'+componentId+'_btns')).removeClass("hidden");
	this.checkFileCount(componentId);
	$("#"+uploadingRowId+" .icon-remove").click(function(e) {
		var jqXHR;
		e.preventDefault();
		if (_.driveuploads.uploadJQXHR[uploadingRowId]) {
			jqXHR = _.driveuploads.uploadJQXHR[uploadingRowId];
			jqXHR.abort();
			$('#'+uploadingRowId).remove();
		}
	});
	return uploadingRowId;
};
