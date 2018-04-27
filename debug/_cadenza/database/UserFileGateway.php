<?php
class UserFileGateway extends Tdg {
	
	const PRIMARY_KEY = 'file_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'filename');
	}
	
	static function find($file_id) {
		$stmt = DbLink::prepare("SELECT * FROM user_files WHERE file_id = :file_id");
		$stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM user_files";
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
	
	static function findAllAttachments($options=array()) {
		$sql = "SELECT * FROM user_files WHERE category = 'attachment'";
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
	static function findAllAttachmentsInTask($task_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE category = 'attachment' AND task_id = :task_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
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
	static function findAllAttachmentsInPractice($practice_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE category = 'attachment' AND practice_id = :practice_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function findAllUserFiles($uid, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid";
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
	static function findAllUserAttachments($uid, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment'";
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
	static function findAllUserAttachmentsInTask($uid, $task_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND task_id = :task_id";
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
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllUserAttachmentsInTasksOfStudent($uid, $student_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND task_id IN ("
			. " 	SELECT t.task_id FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.student_id = :student_id"
			. " )";
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
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllUserAttachmentsInTasksOfTeacher($uid, $teacher_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND task_id IN ("
			. " 	SELECT t.task_id FROM tasks t INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.teacher_id = :teacher_id"
			. " )";
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
	static function findAllUserAttachmentsInPracticesOfStudent($uid, $student_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND practice_id IN ("
			. " 	SELECT p.practice_id FROM practices p"
			. " 	INNER JOIN tasks t ON p.task_id = t.task_id"
			. " 	INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.student_id = :student_id"
			. " )";
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
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllUserAttachmentsInPracticesOfTeacher($uid, $teacher_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND practice_id IN ("
			. " 	SELECT p.practice_id FROM practices p"
			. " 	INNER JOIN tasks t ON p.task_id = t.task_id"
			. " 	INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.teacher_id = :teacher_id"
			. " )";
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
	static function findAllUserAttachmentsInPractice($uid, $practice_id, $options=array()) {
		$sql = "SELECT * FROM user_files WHERE uid = :uid AND category = 'attachment' AND practice_id = :practice_id";
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
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function insertAttachment($uid, $lesson_id, $task_id, $practice_id, $filename, $filetype, $filesize) {
		$category = 'attachment';
		$sql = "INSERT INTO user_files (uid, lesson_id, task_id, practice_id, category, filename, filetype, filesize)"
			. " VALUES (:uid, :lesson_id, :task_id, :practice_id, :category, :filename, :filetype, :filesize)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':category', $category, PDO::PARAM_INT);
		$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
		$stmt->bindParam(':filetype', $filetype, PDO::PARAM_STR);
		$stmt->bindParam(':filesize', $filesize, PDO::PARAM_INT);
		$stmt->execute();
		return DbLink::lastInsertId();
	}

	static function delete($file_id) {
		$stmt = DbLink::prepare("DELETE FROM user_files WHERE file_id = :file_id");
		$stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}
