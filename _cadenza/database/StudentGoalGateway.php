<?php
class StudentGoalGateway extends Tdg {
	
	const PRIMARY_KEY = 'student_goal_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'created_date', 'modified_date');
	}
	
	static function find($student_goal_id) {
		$sql = "SELECT *, DATE_FORMAT(created_date, '%b %e, %Y') AS title"
			. " FROM student_goals"
			. " WHERE student_goal_id = :student_goal_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_goal_id', $student_goal_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT *, DATE_FORMAT(created_date, '%b %e, %Y') AS title"
			. " FROM student_goals";
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
	static function findAllByStudent($uid, $options=array()) {
		$sql = "SELECT *, DATE_FORMAT(created_date, '%b %e, %Y') AS title"
			. " FROM student_goals"
			. " WHERE uid = :uid";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByTeacher($teacher_id, $options=array()) {
		$sql = "SELECT *, DATE_FORMAT(created_date, '%b %e, %Y') AS title"
			. " FROM student_goals"
			. " WHERE teacher_id = :teacher_id";
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
	static function findAllByStudentTeacher($uid, $teacher_id, $options=array()) {
		$sql = "SELECT *, DATE_FORMAT(created_date, '%b %e, %Y') AS title"
			. " FROM student_goals"
			. " WHERE uid = :uid AND teacher_id = :teacher_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function insert($uid, $teacher_id, $text, $is_completed, $created_date, $modified_date) {
		$sql = "INSERT INTO student_goals (uid, teacher_id, text, is_completed, created_date, modified_date)"
			. " VALUES (:uid, :teacher_id, :text, :is_completed, :created_date, :modified_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->bindParam(':text', $text, PDO::PARAM_STR);
		$stmt->bindParam(':is_completed', $is_completed, PDO::PARAM_BOOL);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateText($student_goal_id, $text, $modified_date) {
		$sql = "UPDATE student_goals"
			. " SET text = :text, modified_date = :modified_date"
			. " WHERE student_goal_id = :student_goal_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_goal_id', $student_goal_id, PDO::PARAM_INT);
		$stmt->bindParam(':text', $text, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function updateIsCompleted($student_goal_id, $is_completed, $modified_date) {
		$sql = "UPDATE student_goals"
			. " SET is_completed = :is_completed, modified_date = :modified_date"
			. " WHERE student_goal_id = :student_goal_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_goal_id', $student_goal_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_completed', $is_completed, PDO::PARAM_BOOL);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($student_goal_id) {
		$stmt = DbLink::prepare("DELETE FROM student_goals WHERE student_goal_id = :student_goal_id");
		$stmt->bindParam(':student_goal_id', $student_goal_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllGoalsOfStudent($uid) {
		$stmt = DbLink::prepare("DELETE FROM student_goals WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}