_.fileuploads = {};

_.fileuploads.debug = false;
_.fileuploads.uploadCounter = 0;
_.fileuploads.uploadJQXHR = {};

_.fileuploads.initAttachments = function(uploadId, uploadAction) {
	var debug = _.fileuploads.debug;
	var componentId = uploadId;
	var isReadOnly = $('#'+componentId).attr("data-readonly") ? true : false;
	var attachmentsJson = $('#'+componentId+'_files_json').val();
	var attachmentRows = attachmentsJson ? JSON.parse(attachmentsJson) : [];
	var url = _.page.url + '?action='+uploadAction;
	var dataType = 'json';
	var inputSelector = '#' + componentId + '_input';//console.log(inputSelector);
	var i;
	var uploadingRowId;
	// Initialize attachment rows
	for (i = 0; i < attachmentRows.length; i++) {
		uploadingRowId = _.fileuploads.addAttachmentRow(componentId);
		_.fileuploads.setFileUploaded(uploadingRowId, attachmentRows[i].file_id, attachmentRows[i].filename, componentId, isReadOnly);
	}
	// Initialize attachments input
	if (isReadOnly) {
		$('#'+componentId+'_btn').remove();
	}
	else {
	    $(inputSelector).fileupload({
			// maxChunkSize: _.getFileuploadMaxChunkSize(),
			limitConcurrentUploads: 4,
	    	singleFileUploads: true,
	        url: url,
	        dataType: dataType,
	        submit: function(e, data) {
	        	if (debug) { console.debug('fileupload submit BEGIN'); }
	        	if (debug) { console.debug('fileupload submit END'); }
	        },
	        send: function(e, data) {
	        	if (debug) { console.debug('fileupload send BEGIN'); }
	        	if (debug) { console.debug('fileupload send END'); }
	        },
	        add: function(e, data) {
	        	if (debug) { console.debug('fileupload add BEGIN'); }
	        	var options = $(this).fileupload('option');
	        	// isXhrUpload logic from jquery.fileupload.js function _isXHRUpload
	        	var isXhrUpload = !options.forceIframeTransport &&
	                ((!options.multipart && $.support.xhrFileUpload) ||
	                $.support.xhrFormDataFileUpload);
	            var isIframeTransport = !isXhrUpload;
	            var lessonId = $(inputSelector).attr('data-lesson-id');
	            var taskId = $(inputSelector).attr('data-task-id');
	            var practiceId = $(inputSelector).attr('data-practice-id');
	            var uploadingRowId = _.fileuploads.addAttachmentRow(componentId);
	            var jqXHR;
	        	data.formData = {
	        		iframe: (isIframeTransport ? 1 : 0),
	        		lesson_id: (lessonId ? lessonId : 0),
	        		task_id: (taskId ? taskId : 0),
	        		practice_id: (practiceId ? practiceId : 0),
	        		row_id: uploadingRowId
	        	};
	        	
	        	_.page.addUnsafeNavigationUpload(); // important, must have a corresponding _.page.removeUnsafeNavigationUpload(); (for success, failure, and abort)
	        	
	        	jqXHR = data.submit();
	        	_.fileuploads.uploadJQXHR[uploadingRowId] = jqXHR;
	        	if (debug) { console.debug('fileupload add END'); }
	        },
	        done: function(e, data) {
	        	if (debug) { console.debug('fileupload done BEGIN'); }
	        	
	        	var response;
	    		if (_.fileuploads.uploadJQXHR[data.formData.row_id]) {
		        	delete _.fileuploads.uploadJQXHR[data.formData.row_id];
	    		}
	        	response = data.result;
	        	_.page.handleResponse(response, function(response) {
	        		$.each(response.files, function(index, file) {
	        			if (file.error) {
	        				_.fileuploads.setFileUploadFailed(response.row_id, file.error, file.name);
	        			}
	        			else {
	        				_.fileuploads.setFileUploaded(response.row_id, file.id, file.name, componentId, isReadOnly);
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
	    // Set "dropZone" option
	    $.each($(inputSelector), function() {
	    	var dropZoneId = componentId + '_btn';
	    	$(this).fileupload('option', 'dropZone', $('#'+dropZoneId));
	    });
    }
};

_.fileuploads.addAttachmentRow = function(componentId) {
	var uploadingRowId = componentId + '_file_new' + (++this.uploadCounter);
	$('#'+componentId+'_file_dummy').clone().attr("id", uploadingRowId).addClass("file-uploading").insertBefore($('#'+componentId+'_btn')).removeClass("hidden");
	
	$("#"+uploadingRowId+" .icon-remove").click(function(e) {
		var jqXHR;
		e.preventDefault();
		if (_.fileuploads.uploadJQXHR[uploadingRowId]) {
			jqXHR = _.fileuploads.uploadJQXHR[uploadingRowId];
			jqXHR.abort();
			$('#'+uploadingRowId).remove();
		}
	});
	
	return uploadingRowId;
};

_.fileuploads.setFileUploadFailed = function(uploadingRowId, fileError, fileName) {
	var rowId = uploadingRowId;
	$('#'+rowId).addClass("has-error");
	$('#'+rowId+' .fileicon .uploading').html(_.btnIconHtml('file'));
	$('#'+rowId+' .filename .uploading').text(_.translate('file_upload_failed')+'.');
	$('#'+rowId+' .progress').remove();
	
	$("#"+rowId+" .icon-remove").click(function(e) {
		var jqObj = $(this);
		e.preventDefault();
		jqObj.blur();
		$('#'+rowId).remove();
	});
};

_.fileuploads.setFileUploaded = function(uploadingRowId, fileId, fileName, componentId, isReadOnly) {
	var rowId = componentId + '_file_' + fileId;
	$('#'+uploadingRowId).attr("id", rowId).removeClass("file-uploading").addClass("file-uploaded");
	$('#'+rowId+' .fileicon .uploading').remove();
	$('#'+rowId+' .fileicon .download-file').removeClass("disabled").removeClass("hidden");
	$('#'+rowId+' .filename .uploading').remove();
	$('#'+rowId+' .filename .download-file').text(fileName);
	$('#'+rowId+' .filename .download-file').removeClass("disabled").removeClass("hidden");
	$('#'+rowId+' .progress').remove();
	$('#'+rowId+' .icon-remove').remove();
	if (isReadOnly) {
		$('#'+rowId+' .icon-delete').remove();
	}
	else {
		$('#'+rowId+' .icon-delete').removeClass("disabled").removeClass("hidden");
	}
	$('#'+rowId+' .download-file').attr("href", _.page.url + '?action=viewAttachment&file_id=' + fileId);
	$('#'+rowId+' .download-file').attr("target", "_blank");
	$('#'+rowId+' .download-file').click(function(e) {
		var jqObj = $(this);
		jqObj.blur();
	});
	$('#'+rowId+' .icon-delete').click(function(e) {
		var jqObj = $(this);
		var data = { file_id:fileId };
		e.preventDefault();
		$('#'+rowId).remove();
		_.page.actionPost('deleteAttachment', data, function(response) {
			if (response.refresh) {
            	_.page.refreshComponents(response.refresh);
            }
		});
	});
};

_.fileuploads.abortAllUploads = function(cleanupHTML) {
	$.each( this.uploadJQXHR , function( uploadingRowId, JQXHR ) {
        JQXHR.abort();
        if (cleanupHTML) {
		    $('#'+uploadingRowId).remove();
        }
    });
};