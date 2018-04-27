<?php
class AnnotationNotification extends Notification {
		
	var $practice_id;
	var $practice;
	var $annotator_file_id;
	var $annotator_title;
	var $practice_num;
	var $task_id;
	var $task_title;
	var $lesson_id;
	
	function __construct($row) {
		if ($row['ref'] != 'annotation') {
			trigger_error('Invalid ref: '.$row['ref'], E_USER_ERROR);
		}
		parent::__construct($row);
		
		$this->practice_id = $this->ref_id;
		$practice_row = PracticeGateway::find($this->practice_id);
		$this->practice = new Practice($practice_row);
		$this->annotator_file_id = $this->practice->annotator_file_id;
		$this->annotator_title = $this->practice->annotator_title;
		
		$task_row = TaskGateway::findByPractice($this->practice_id);
		$this->practice_num = PracticeGateway::countCompletedInTaskAsOfPractice($task_row['task_id'], $this->practice_id);
		$this->task_id = $task_row['task_id'];
		$this->task_title = $task_row['title'];
		$this->lesson_id = $task_row['lesson_id'];
	}
	
	function isGoLocationAccessibleByUser(User $user) {
		return $user->isAllowedViewAnnotatorInPractice($this->practice);
	}
	
}
