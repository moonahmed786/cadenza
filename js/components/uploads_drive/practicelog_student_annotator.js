if (!_.components) _.components = {};
if (!_.components.uploads_drive) _.components.uploads_drive = {};
_.components.uploads_drive.practicelog_student_annotator = {};

_.components.uploads_drive.practicelog_student_annotator.uploadClass = 'upload-practicelog-student-annotator';

_.components.uploads_drive.practicelog_student_annotator.init = function() {
	$('.'+this.uploadClass).each(function() {
		var uploadId = $(this).attr("id");
		_.driveuploads.initAnnotator(uploadId);
	});
};
