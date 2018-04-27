<?php
class TeacherNotesGateway extends Tdg {
	
	const PRIMARY_KEY = 'teacher_note_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY);
	}
	
	static function find($teacher_note_id) {
		$stmt = DbLink::prepare("SELECT * FROM teacher_notes WHERE teacher_note_id = :teacher_note_id");
		$stmt->bindParam(':teacher_note_id', $teacher_note_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByTeacherStudent($uid, $student_id) {
		$stmt = DbLink::prepare("SELECT * FROM teacher_notes WHERE uid = :uid AND student_id = :student_id");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM teacher_notes";
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
		$sql = "SELECT * FROM teacher_notes WHERE student_id = :student_id";
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
	static function findAllByTeacher($uid, $options=array()) {
		$sql = "SELECT * FROM teacher_notes WHERE uid = :uid";
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
	
	static function insert($uid, $student_id, $notes_on_student) {
		$sql = "INSERT INTO teacher_notes (uid, student_id, notes_on_student)"
			. " VALUES (:uid, :student_id, :notes_on_student)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':notes_on_student', $notes_on_student, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function update($teacher_note_id, $uid, $student_id, $notes_on_student) {
		$sql = "UPDATE teacher_notes"
			. " SET uid = :uid, student_id = :student_id, notes_on_student = :notes_on_student"
			. " WHERE teacher_note_id = :teacher_note_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_note_id', $teacher_note_id, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':notes_on_student', $notes_on_student, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($teacher_note_id) {
		$stmt = DbLink::prepare("DELETE FROM teacher_notes WHERE teacher_note_id = :teacher_note_id");
		$stmt->bindParam(':teacher_note_id', $teacher_note_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteByTeacherStudent($uid, $student_id) {
		$stmt = DbLink::prepare("DELETE FROM teacher_notes WHERE uid = :uid AND student_id = :student_id");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllNotesOfTeacher($uid) {
		$stmt = DbLink::prepare("DELETE FROM teacher_notes WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}