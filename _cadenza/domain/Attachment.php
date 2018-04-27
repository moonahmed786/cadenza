<?php
class Attachment {
	
	var $file_id;
	var $uid;
	var $category;
	var $lesson_id;
	var $task_id;
	var $practice_id;
	var $filename;
	var $filetype_long;
	var $filetype_short;
	var $is_task_attachment;
	var $is_practice_attachment;
	
	function __construct($row) {
		if ($row['category'] != 'attachment') {
			trigger_error('Invalid category: '.$row['category'], E_USER_ERROR);
		}
		$this->file_id = $row['file_id'];
		$this->uid = $row['uid'];
		$this->category = $row['category'];
		$this->lesson_id = $row['lesson_id'];
		$this->task_id = $row['task_id'];
		$this->practice_id = $row['practice_id'];
		$this->filename = $row['filename'];
		$this->filetype_long = $row['filetype'];
		
		$filetype_parts = explode('/', $this->filetype_long);
		$this->filetype_short = isset($filetype_parts[1]) ? $filetype_parts[1] : null;
		
		$this->is_task_attachment = ($this->lesson_id != null && $this->task_id != null && $this->practice_id == null);
		$this->is_practice_attachment = ($this->lesson_id != null && $this->task_id != null && $this->practice_id != null);
	}
	
}
