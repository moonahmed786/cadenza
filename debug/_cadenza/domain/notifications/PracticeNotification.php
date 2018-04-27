<?php
class PracticeNotification extends Notification {
	
	var $practice_id;
	var $practice;
	var $practice_num;
	var $task_id;
	var $task_title;
	var $lesson_id;
	
	function __construct($row) {
		if ($row['ref'] != 'practice') {
			trigger_error('Invalid ref: '.$row['ref'], E_USER_ERROR);
		}
		parent::__construct($row);
		
		$this->practice_id = $this->ref_id;
		
		$practice_row = PracticeGateway::find($this->practice_id);
		$this->practice = new Practice($practice_row);
		
		$task_row = TaskGateway::findByPractice($this->practice_id);
		$this->practice_num = PracticeGateway::countCompletedInTaskAsOfPractice($task_row['task_id'], $this->practice_id);
		$this->task_id = $task_row['task_id'];
		$this->task_title = $task_row['title'];
		$this->lesson_id = $task_row['lesson_id'];
	}
	
	function isGoLocationAccessibleByUser(User $user) {
		return $user->isAllowedViewPractice($this->practice);
	}
	
}
