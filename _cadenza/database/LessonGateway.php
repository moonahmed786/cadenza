<?php
class LessonGateway extends Tdg {
	
	const PRIMARY_KEY = 'lesson_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'created_date');
	}
	
	static function find($lesson_id) {
		$stmt = DbLink::prepare("SELECT * FROM lessons WHERE lesson_id = :lesson_id");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findSaved($lesson_id) {
		$stmt = DbLink::prepare("SELECT * FROM lessons WHERE lesson_id = :lesson_id AND is_saved = 1");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findNext($lesson_id, $student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MIN(lesson_id) FROM lessons WHERE lesson_id > :lesson_id AND student_id = :student_id AND teacher_id = :teacher_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findNextSaved($lesson_id, $student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MIN(lesson_id) FROM lessons WHERE lesson_id > :lesson_id AND student_id = :student_id AND teacher_id = :teacher_id AND is_saved = 1"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findPrev($lesson_id, $student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MAX(lesson_id) FROM lessons WHERE lesson_id < :lesson_id AND student_id = :student_id AND teacher_id = :teacher_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findPrevSaved($lesson_id, $student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MAX(lesson_id) FROM lessons WHERE lesson_id < :lesson_id AND student_id = :student_id AND teacher_id = :teacher_id AND is_saved = 1"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findLast($student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MAX(lesson_id) FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findLastSaved($student_id, $teacher_id) {
		$sql = "SELECT * FROM lessons WHERE lesson_id = ("
			. " 	SELECT MAX(lesson_id) FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id AND is_saved = 1"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByTask($task_id) {
		$stmt = DbLink::prepare("SELECT * FROM lessons WHERE lesson_id = (SELECT lesson_id FROM tasks WHERE task_id = :task_id)");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByPractice($practice_id) {
		$stmt = DbLink::prepare("SELECT * FROM lessons WHERE lesson_id = (SELECT lesson_id FROM practices WHERE practice_id = :practice_id)");
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByLessonComment($comment_id) {
		$sql = "SELECT l.*"
			. " FROM lessons l"
			. " INNER JOIN comments c ON l.lesson_id = c.ref_id AND c.ref = 'lesson'"
			. " WHERE c.comment_id = :comment_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM lessons";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
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
		$sql = "SELECT * FROM lessons WHERE is_saved = 1";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
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
	static function findAllByStudent($student_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE student_id = :student_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSavedByStudent($student_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE student_id = :student_id AND is_saved = 1";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByTeacher($teacher_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE teacher_id = :teacher_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSavedByTeacher($teacher_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE teacher_id = :teacher_id AND is_saved = 1";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByStudentTeacher($student_id, $teacher_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSavedByStudentTeacher($student_id, $teacher_id, $options=array()) {
		$sql = "SELECT * FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id AND is_saved = 1";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function countRowsOfStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id");
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countSavedOfStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM lessons WHERE student_id = :student_id AND teacher_id = :teacher_id AND is_saved = 1");
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	static function isSaved($lesson_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM lessons WHERE lesson_id = :lesson_id AND is_saved = 1");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	
	static function insert($student_id, $teacher_id, $is_saved, $created_date) {
		$sql = "INSERT INTO lessons (student_id, teacher_id, is_saved, created_date)"
			. " VALUES (:student_id, :teacher_id, :is_saved, :created_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_saved', $is_saved, PDO::PARAM_BOOL);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateIsSaved($lesson_id, $is_saved) {
		$sql = "UPDATE lessons"
			. " SET is_saved = :is_saved"
			. " WHERE lesson_id = :lesson_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_saved', $is_saved, PDO::PARAM_BOOL);
		$stmt->execute();
	}
	
	static function delete($lesson_id) {
		$stmt = DbLink::prepare("DELETE FROM lessons WHERE lesson_id = :lesson_id");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllLessonsOfStudent($student_id) {
		$stmt = DbLink::prepare("DELETE FROM lessons WHERE student_id = :student_id");
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllLessonsOfTeacher($teacher_id) {
		$stmt = DbLink::prepare("DELETE FROM lessons WHERE teacher_id = :teacher_id");
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}
