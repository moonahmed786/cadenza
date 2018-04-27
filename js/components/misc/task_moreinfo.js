if (!_.components) _.components = {};
if (!_.components.misc) _.components.misc = {};
_.components.misc.task_moreinfo = {};

_.components.misc.task_moreinfo.taskMoreinfoId = 'id_task_moreinfo';

_.components.misc.task_moreinfo.init = function() {
	$('#'+this.taskMoreinfoId+' .filename').each(function() {
		var jqObj = $(this);
		var fileId = jqObj.parent().attr('data-file-id');
		jqObj.attr("href", _.page.url + '?action=viewAttachment&file_id=' + fileId);
		jqObj.attr("target", "_blank");
		jqObj.click(function(e) {
			jqObj.blur();
		});
	});
};
