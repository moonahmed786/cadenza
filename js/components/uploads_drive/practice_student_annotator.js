if (!_.components) _.components = {};
if (!_.components.uploads_drive) _.components.uploads_drive = {};
_.components.uploads_drive.practice_student_annotator = {};

_.components.uploads_drive.practice_student_annotator.uploadId = 'id_upload_practice_student_annotator';

_.components.uploads_drive.practice_student_annotator.init = function() {
	_.driveuploads.initAnnotator(this.uploadId);
};
