<?php
class PracticeGateway extends Tdg {
	
	const PRIMARY_KEY = 'practice_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'created_date');
	}
	
	static function find($practice_id) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p INNER JOIN lessons l ON p.lesson_id = l.lesson_id"
			. " WHERE p.practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByPracticeComment($comment_id) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p "
			. " INNER JOIN lessons l ON p.lesson_id = l.lesson_id "
			. " INNER JOIN comments c ON p.practice_id = c.ref_id AND c.ref = 'practice'"
			. " WHERE c.comment_id = :comment_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p INNER JOIN lessons l ON p.lesson_id = l.lesson_id";
		if (isset($options['orderby'])) {
			$prefix = 'p.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByTask($task_id, $options=array()) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p INNER JOIN lessons l ON p.lesson_id = l.lesson_id"
			. " WHERE p.task_id = :task_id";
		if (isset($options['orderby'])) {
			$prefix = 'p.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllCompleted($options=array()) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p INNER JOIN lessons l ON p.lesson_id = l.lesson_id"
			. " WHERE p.timer_mins IS NOT NULL";
		if (isset($options['orderby'])) {
			$prefix = 'p.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllCompletedByTask($task_id, $options=array()) {
		$sql = "SELECT p.*, l.student_id, l.teacher_id"
			. " FROM practices p INNER JOIN lessons l ON p.lesson_id = l.lesson_id"
			. " WHERE p.timer_mins IS NOT NULL AND p.task_id = :task_id";
		if (isset($options['orderby'])) {
			$prefix = 'p.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function countCompletedInTask($task_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM practices WHERE timer_mins IS NOT NULL AND task_id = :task_id");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countCompletedInTaskAsOfPractice($task_id, $practice_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM practices WHERE timer_mins IS NOT NULL AND task_id = :task_id AND practice_id <= :practice_id");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countCompletedInLesson($lesson_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM practices WHERE timer_mins IS NOT NULL AND lesson_id = :lesson_id");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function sumTimerMinsInTask($task_id) {
		$stmt = DbLink::prepare("SELECT SUM(timer_mins) FROM practices WHERE timer_mins IS NOT NULL AND task_id = :task_id");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	static function insert($task_id, $lesson_id, $timer_mins, $reflection, $is_notified, $annotator_file_id, $annotator_title, $created_date) {
		$sql = "INSERT INTO practices (task_id, lesson_id, timer_mins, reflection, is_notified, annotator_file_id, annotator_title, created_date)"
			. " VALUES (:task_id, :lesson_id, :timer_mins, :reflection, :is_notified, :annotator_file_id, :annotator_title, :created_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':timer_mins', $timer_mins, PDO::PARAM_INT);
		$stmt->bindParam(':reflection', $reflection, PDO::PARAM_INT);
		$stmt->bindParam(':is_notified', $is_notified, PDO::PARAM_BOOL);
		$stmt->bindParam(':annotator_file_id', $annotator_file_id, PDO::PARAM_STR);
		$stmt->bindParam(':annotator_title', $annotator_title, PDO::PARAM_STR);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateTimer($practice_id, $timer_mins) {
		$sql = "UPDATE practices"
			. " SET timer_mins = :timer_mins"
			. " WHERE practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':timer_mins', $timer_mins, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function updateReflection($practice_id, $reflection) {
		$sql = "UPDATE practices"
			. " SET reflection = :reflection"
			. " WHERE practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':reflection', $reflection, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function updateTimerAndReflection($practice_id, $timer_mins, $reflection) {
		$sql = "UPDATE practices"
			. " SET timer_mins = :timer_mins, reflection = :reflection"
			. " WHERE practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':timer_mins', $timer_mins, PDO::PARAM_INT);
		$stmt->bindParam(':reflection', $reflection, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function updateIsNotified($practice_id, $is_notified) {
		$sql = "UPDATE practices"
			. " SET is_notified = :is_notified"
			. " WHERE practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_notified', $is_notified, PDO::PARAM_BOOL);
		$stmt->execute();
	}
	
	static function updateAnnotator($practice_id, $annotator_file_id, $annotator_title) {
		$sql = "UPDATE practices"
			. " SET annotator_file_id = :annotator_file_id, annotator_title = :annotator_title"
			. " WHERE practice_id = :practice_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':annotator_file_id', $annotator_file_id, PDO::PARAM_STR);
		$stmt->bindParam(':annotator_title', $annotator_title, PDO::PARAM_STR);
		$stmt->execute();
	}
}