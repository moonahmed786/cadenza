if (!_.components) _.components = {};
if (!_.components.modals) _.components.modals = {};
_.components.modals.notemaker_import = {};

_.components.modals.notemaker_import.modalId = 'id_modal_notemaker_import';
_.components.modals.notemaker_import.userEmail = null;
_.components.modals.notemaker_import.successCallback = function() {};
_.components.modals.notemaker_import.selectedFile = null;

_.components.modals.notemaker_import.init = function() {
	thisComponent = this;
	thisComponent.reset();
	
	$("#"+thisComponent.modalId+"_save_btn").off("click");
	$("#"+thisComponent.modalId+"_save_btn").click(function(e) {
        e.preventDefault();
		thisComponent.close();
		thisComponent.successCallback(thisComponent.selectedFile);
	});
};

_.components.modals.notemaker_import.reset = function() {
	this.selectedFile = null;
	$("#"+this.modalId+" .table .success").removeClass("success");
	$("#"+this.modalId+"_save_btn").addClass('disabled');
};

_.components.modals.notemaker_import.setSelected = function(file) {
	this.selectedFile = file;
	$("#"+this.modalId+" .table .success").removeClass("success");
	$("#"+this.modalId+"_save_btn").removeClass('disabled');
};

_.components.modals.notemaker_import.reloadTable = function() {
	thisComponent = this;
	thisComponent.reset();
	
	$("#"+thisComponent.modalId+" .table tbody").empty();
	$("#"+thisComponent.modalId+" .table .table-loading").removeClass("hidden");
	$("#"+thisComponent.modalId+" .table .table-empty").addClass("hidden");
    
	_.drive.fetchNotemakerFiles(thisComponent.userEmail, function(files) {
		$("#"+thisComponent.modalId+" .table .table-loading").addClass("hidden");
		
        var is_empty = true;
        
		$.each(files, function(index, file) {
			var newFileRow = $('#'+thisComponent.modalId+'_dummy_row').clone().removeClass("hidden").removeAttr("id");
			newFileRow.data("file-id", file.id);
			
			newFileRow.find(".file_name").text(file.name);
			newFileRow.find(".created_date").text(_.drive.driveDateStringToCadenzaDateString(file.createdTime));
			if (file.thumbnailLink) {
				newFileRow.find(".media_thumbnail").html('<img src="'+file.thumbnailLink+'"/>');
			}
			
			newFileRow.click(function() {
				thisComponent.setSelected(file);
				newFileRow.addClass("success");
			});
			
			$("#"+thisComponent.modalId+" .table tbody").append(newFileRow);
            is_empty = false;
		});
        
        if (is_empty) {
	        $("#"+thisComponent.modalId+" .table .table-empty").removeClass("hidden");
        }
        
        $("#"+thisComponent.modalId).modal('handleUpdate');
	}, function(error) {
		_.drive.errorAlert(error);

		$("#"+thisComponent.modalId+" .table .table-loading").addClass("hidden");
	});
};

_.components.modals.notemaker_import.open = function(userEmail, successCallback) {
	this.reset();
	this.userEmail = userEmail;
	this.successCallback = successCallback;
	this.reloadTable();
	
	$('#'+this.modalId).modal({
		backdrop:'static'
	});
};

_.components.modals.notemaker_import.close = function() {
	$('#'+this.modalId).modal('hide');
};
