<?php
class StudentGoal {
	
	var $student_goal_id;
	var $uid;
	var $teacher_id;
	var $text;
	var $is_completed;
	var $title;
	
	function __construct($row) {
		$this->student_goal_id = $row['student_goal_id'];
		$this->uid = $row['uid'];
		$this->teacher_id = $row['teacher_id'];
		$this->text = $row['text'];
		$this->is_completed = $row['is_completed'];
		$this->title = $row['title'];
	}
	
}