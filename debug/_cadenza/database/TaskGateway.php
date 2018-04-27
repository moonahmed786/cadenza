<?php
class TaskGateway extends Tdg {
	
	const PRIMARY_KEY = 'task_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY);
	}
	
	static function find($task_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = :task_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findSaved($task_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = :task_id AND t.is_saved = 1";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findFirstInLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = (SELECT MIN(task_id) FROM tasks WHERE lesson_id = :lesson_id)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findFirstSavedInLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = (SELECT MIN(task_id) FROM tasks WHERE lesson_id = :lesson_id) AND t.is_saved = 1";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findLastInLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = (SELECT MAX(task_id) FROM tasks WHERE lesson_id = :lesson_id)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findLastSavedInLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = (SELECT MAX(task_id) FROM tasks WHERE lesson_id = :lesson_id) AND t.is_saved = 1";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByPractice($practice_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.task_id = (SELECT task_id FROM practices WHERE practice_id = :practice_id)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id";
		if (isset($options['orderby'])) {
			$prefix = 't.';
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
	static function findAllSaved($options=array()) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.is_saved = 1";
		if (isset($options['orderby'])) {
			$prefix = 't.';
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
	static function findAllByLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.lesson_id = :lesson_id";
		if (isset($options['orderby'])) {
			$prefix = 't.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSavedByLesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.lesson_id = :lesson_id AND t.is_saved = 1";
		if (isset($options['orderby'])) {
			$prefix = 't.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllUnsavedBylesson($lesson_id) {
		$sql = "SELECT t.*, l.student_id, l.teacher_id"
			. " FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " WHERE t.lesson_id = :lesson_id AND t.is_saved = 0";
		if (isset($options['orderby'])) {
			$prefix = 't.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function isSaved($task_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM tasks WHERE task_id = :task_id AND is_saved = 1");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	
	static function insertBlank($lesson_id, $date) {
		$sql = "INSERT INTO tasks (lesson_id, is_saved, created_date, modified_date)"
			. " VALUES (:lesson_id, 0, :created_date, :modified_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':created_date', $date, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function update($task_id, $lesson_id, $title, $target, $category, $category_other, $description, $is_saved, $created_date, $modified_date) {
		$sql = "UPDATE tasks"
			. " SET lesson_id = :lesson_id,"
			. " 	title = :title,"
			. " 	target = :target,"
			. " 	category = :category,"
			. " 	category_other = :category_other,"
			. " 	description = :description,"
			. " 	is_saved = :is_saved,"
			. " 	created_date = :created_date,"
			. " 	modified_date = :modified_date"
			. " WHERE task_id = :task_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':title', $title, PDO::PARAM_STR);
		$stmt->bindParam(':target', $target, PDO::PARAM_INT);
		$stmt->bindParam(':category', $category, PDO::PARAM_INT);
		$stmt->bindParam(':category_other', $category_other, PDO::PARAM_STR);
		$stmt->bindParam(':description', $description, PDO::PARAM_STR);
		$stmt->bindParam(':is_saved', $is_saved, PDO::PARAM_BOOL);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($task_id) {
		$stmt = DbLink::prepare("DELETE FROM tasks WHERE task_id = :task_id");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}