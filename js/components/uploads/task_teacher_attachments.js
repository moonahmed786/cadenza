if (!_.components) _.components = {};
if (!_.components.uploads) _.components.uploads = {};
_.components.uploads.task_teacher_attachments = {};

_.components.uploads.task_teacher_attachments.uploadId = 'id_upload_task_teacher_attachments';

_.components.uploads.task_teacher_attachments.init = function() {
	var uploadAction = 'uploadAttachmentToTask';
	_.fileuploads.initAttachments(this.uploadId, uploadAction);
};

_.components.uploads.task_teacher_attachments.countUploaded = function() {
	return $('#'+this.uploadId+' .upload-file.file-uploaded').length;
};
