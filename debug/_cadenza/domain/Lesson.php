<?php
class Lesson {
		
	var $lesson_id;
	var $student_id;
	var $teacher_id;
	var $is_saved;
	var $created_date;
	var $created_date_local;
	var $tasks;
	var $saved_tasks;
	var $count_saved_tasks_with_target_met;
	var $saved_tasks_timer_mins_total;
	var $time_spent;
	var $first_task;
	var $last_task;
	var $first_saved_task;
	var $last_saved_task;
	var $prev_saved_lesson_id;
	var $next_saved_lesson_id;
	var $is_latest_saved_lesson;
	var $has_saved_tasks_with_practices;
	var $has_reflection;
	var $reflection;
	
	function __construct($row) {
		$this->lesson_id = $row['lesson_id'];
		$this->student_id = $row['student_id'];
		$this->teacher_id = $row['teacher_id'];
		$this->is_saved = $row['is_saved'];
		$this->created_date = $row['created_date'];
		$this->created_date_local = Core::utcToLocal($row['created_date']);
		
		$task_rows = TaskGateway::findAllByLesson($row['lesson_id']);
		$this->tasks = array();
		$this->saved_tasks = array();
		$this->count_saved_tasks_with_target_met = 0;
		$this->saved_tasks_timer_mins_total = 0;
		$this->has_saved_tasks_with_practices = false;
		foreach ($task_rows as $task_row) {
			$task = new Task($task_row);
			$this->tasks[] = $task;
			if ($task->is_saved) {
				$this->saved_tasks[] = $task;
				$this->count_saved_tasks_with_target_met += $task->is_target_met ? 1 : 0;
				$this->saved_tasks_timer_mins_total += $task->timer_mins_total;
				if ($task->count_practices > 0) {
					$this->has_saved_tasks_with_practices = true;
				}
			}
		}
		$hrs = floor($this->saved_tasks_timer_mins_total / 60);
		$min = $this->saved_tasks_timer_mins_total - ($hrs * 60);
		if ($min < 10) {
			$min = '0'.$min;
		}
		$this->time_spent = $hrs.'h '.$min.'m';
		
		$first_task_row = TaskGateway::findFirstInLesson($row['lesson_id']);
		$last_task_row = TaskGateway::findLastInLesson($row['lesson_id']);
		$first_saved_task_row = TaskGateway::findFirstSavedInLesson($row['lesson_id']);
		$last_saved_task_row = TaskGateway::findLastSavedInLesson($row['lesson_id']);
		$this->first_task = $first_task_row ? new Task($first_task_row) : null;
		$this->last_task = $last_task_row ? new Task($last_task_row) : null;
		$this->first_saved_task = $first_saved_task_row ? new Task($first_saved_task_row) : null;
		$this->last_saved_task = $last_saved_task_row ? new Task($last_saved_task_row) : null;
		
		$prev_saved_lesson_row = LessonGateway::findPrevSaved($row['lesson_id'], $row['student_id'], $row['teacher_id']);
		$next_saved_lesson_row = LessonGateway::findNextSaved($row['lesson_id'], $row['student_id'], $row['teacher_id']);
		$this->prev_saved_lesson_id = $prev_saved_lesson_row ? $prev_saved_lesson_row['lesson_id'] : null;
		$this->next_saved_lesson_id = $next_saved_lesson_row ? $next_saved_lesson_row['lesson_id'] : null;
		$this->is_latest_saved_lesson = ($this->next_saved_lesson_id == null);
		
		$lesson_reflection_row = LessonReflectionGateway::findByLessonId($row['lesson_id']);
		if ($lesson_reflection_row) {
			$this->reflection = new LessonReflection($lesson_reflection_row);
			$this->has_reflection = true;
		}
		else {
			$this->reflection = null;
			$this->has_reflection = false;
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

class LessonReflection {
	
	var $lesson_reflection_id;
	var $lesson_id;
	var $student_id;
	var $teacher_id;
	var $reflection_index;
	var $reflection_text;
	var $reflection_prompt;
	
	function __construct($row) {
		$this->lesson_reflection_id = $row['lesson_reflection_id'];
		$this->lesson_id = $row['lesson_id'];
		$this->student_id = $row['student_id'];
		$this->teacher_id = $row['teacher_id'];
		$this->reflection_index = $row['reflection_index'];
		$this->reflection_text = $row['reflection_text'];
		$this->reflection_prompt = $row['reflection_prompt'];
	}
	
}
