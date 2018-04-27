<?php
class LessonCommentNotification extends Notification {
		
	var $comment_id;
	var $lesson_id;
	var $lesson;
	var $lesson_created_date;
	var $lesson_created_date_local;
	
	function __construct($row) {
		if ($row['ref'] != 'lesson_comment') {
			trigger_error('Invalid ref: '.$row['ref'], E_USER_ERROR);
		}
		parent::__construct($row);
		
		$this->comment_id = $this->ref_id;
		
		$lesson_row = LessonGateway::findByLessonComment($this->comment_id);
		$this->lesson = new Lesson($lesson_row);
		$this->lesson_id = $this->lesson->lesson_id;
		$this->lesson_created_date = $this->lesson->created_date;
		$this->lesson_created_date_local = $this->lesson->created_date_local;
	}
	
	function isGoLocationAccessibleByUser(User $user) {
		return $user->isAllowedViewLesson($this->lesson);
	}
	
}
