<?php
class Task {
	
	var $task_id;
	var $lesson_id;
	var $title;
	var $target;
	var $category;
	var $category_other;
	var $description;
	var $student_id;
	var $teacher_id;
	var $is_saved;
	var $created_date;
	var $modified_date;
	var $count_practices;
	var $is_target_met;
	var $count_practices_extra;
	var $timer_mins_total;
	var $time_spent;
	var $teacher_attachments;
	
	function __construct($row) {
		$this->task_id = $row['task_id'];
		$this->lesson_id = $row['lesson_id'];
		$this->title = $row['title'];
		$this->target = $row['target'];
		$this->category = $row['category'];
		$this->category_other = $row['category_other'];
		$this->description = $row['description'];
		$this->student_id = $row['student_id'];
		$this->teacher_id = $row['teacher_id'];
		$this->is_saved = $row['is_saved'];
		$this->created_date = $row['created_date'];
		$this->modified_date = $row['modified_date'];
		
		$this->count_practices = PracticeGateway::countCompletedInTask($row['task_id']);
		$this->is_target_met = ($this->count_practices >= $this->target);
		$this->count_practices_extra = $this->is_target_met ? $this->count_practices - $this->target : 0;
		$this->timer_mins_total = PracticeGateway::sumTimerMinsInTask($row['task_id']);
		$hrs = floor($this->timer_mins_total / 60);
		$min = $this->timer_mins_total - ($hrs * 60);
		if ($min < 10) {
			$min = '0'.$min;
		}
		$this->time_spent = $hrs.'h '.$min.'m';
		
		$teacher_attachment_rows = UserFileGateway::findAllUserAttachmentsInTask($row['teacher_id'], $row['task_id']);
		$this->teacher_attachments = array();
		foreach ($teacher_attachment_rows as $attachment_row) {
			$this->teacher_attachments[] = new Attachment($attachment_row);
		}
	}

	function getLinkedUserId(User $user) {
		if ($user->user_type == 'student' && $user->uid == $this->student_id) {
			return $this->teacher_id;
		}
		elseif ($user->user_type == 'teacher' && $user->uid == $this->teacher_id) {
			return $this->student_id;
		}
		return null;
	}
	
}
