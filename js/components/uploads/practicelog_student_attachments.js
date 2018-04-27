if (!_.components) _.components = {};
if (!_.components.uploads) _.components.uploads = {};
_.components.uploads.practicelog_student_attachments = {};

_.components.uploads.practicelog_student_attachments.uploadClass = 'upload-practicelog-student-attachments';

_.components.uploads.practicelog_student_attachments.init = function() {
	$('.upload-practicelog-student-attachments').each(function() {
		var uploadId = $(this).attr("id");
		var uploadAction = 'uploadAttachmentToPractice';
		_.fileuploads.initAttachments(uploadId, uploadAction);
	});
};
