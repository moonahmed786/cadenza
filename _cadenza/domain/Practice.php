<?php
class Practice {
	
	var $practice_id;
	var $task_id;
	var $lesson_id;
	var $created_date;
	var $created_date_local;
	var $timer_mins;
	var $time_spent;
	var $reflection_index;
	var $is_notified;
	var $student_id;
	var $teacher_id;
	var $annotator_file_id;
	var $annotator_title;
	var $checklist_fields;
	var $checklist_fields_has_checked;
	var $comments;
	var $student_attachments;
	var $has_attachment;
	var $has_annotator;
	var $has_comment;
	
	function __construct($row) {
		$this->practice_id = $row['practice_id'];
		$this->task_id = $row['task_id'];
		$this->lesson_id = $row['lesson_id'];
		$this->created_date = $row['created_date'];
		$this->created_date_local = Core::utcToLocal($row['created_date']);
		$this->timer_mins = $row['timer_mins'];
		$hrs = floor($this->timer_mins / 60);
		$min = $this->timer_mins - ($hrs * 60);
		if ($min < 10) {
			$min = '0'.$min;
		}
		$this->time_spent = $hrs.'h '.$min.'m';
		$this->reflection_index = $row['reflection'];
		$this->is_notified = $row['is_notified'];
		$this->student_id = $row['student_id'];
		$this->teacher_id = $row['teacher_id'];
		$this->annotator_file_id = $row['annotator_file_id'];
		$this->annotator_title = $row['annotator_title'];
		
		$this->checklist_fields = array();
		$practice_checklist_item_rows = PracticeFieldGateway::findAllChecklistItemsByPractice($row['practice_id']);
		$this->checklist_fields_has_checked = false;
		foreach ($practice_checklist_item_rows as $practice_checklist_item) {
			$practiceChecklistField = new PracticeChecklistField($practice_checklist_item);
			$this->checklist_fields[] = $practiceChecklistField;
			if ($practiceChecklistField->is_checked) {
				$this->checklist_fields_has_checked = true;
			}
		}
		
		$this->comments = array();
		$comment_rows = CommentGateway::findAllByPractice($row['practice_id'], array('orderby'=>'created_date ASC'));
		foreach ($comment_rows as $comment_row) {
			$this->comments[] = new Comment($comment_row);
		}
		
		$student_attachment_rows = UserFileGateway::findAllUserAttachmentsInPractice($row['student_id'], $row['practice_id']);
		$this->student_attachments = array();
		foreach ($student_attachment_rows as $attachment_row) {
			$this->student_attachments[] = new Attachment($attachment_row);
		}
		
		$this->has_attachment = (count($this->student_attachments) > 0);
		$this->has_annotator = ($this->annotator_file_id !== null);
		$this->has_comment = (count($this->comments) > 0);
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

class PracticeChecklistField {
	
	var $checklist_item_id;
	var $text;
	var $target_type;
	var $target_val;
	var $is_checked;
	
	function __construct($row) {
		$this->checklist_item_id = $row['ref_id'];
		$this->text = $row['text'];
		$this->target_type = $row['target_type'];
		$this->target_val = $row['target_val'];
		$this->is_checked = ($row['field_value'] == 1);
	}
	
}