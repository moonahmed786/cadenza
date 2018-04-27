if (!_.components) _.components = {};
if (!_.components.uploads) _.components.uploads = {};
_.components.uploads.practice_student_attachments = {};

_.components.uploads.practice_student_attachments.uploadId = 'id_upload_practice_student_attachments';

_.components.uploads.practice_student_attachments.init = function() {
	var uploadAction = 'uploadAttachmentToPractice';
	_.fileuploads.initAttachments(this.uploadId, uploadAction);
};
