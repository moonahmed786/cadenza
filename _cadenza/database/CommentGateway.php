<?php
class CommentGateway extends Tdg {
	
	const PRIMARY_KEY = 'comment_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'ref', 'ref_id', 'author_uid', 'created_date');
	}
	
	static function find($comment_id) {
		$sql = "SELECT c.*, a.g_name AS author_name"
			. " FROM comments c INNER JOIN users a ON c.author_uid = a.uid"
			. " WHERE c.comment_id = :comment_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT c.*, a.g_name AS author_name"
			. " FROM comments c INNER JOIN users a ON c.author_uid = a.uid";
		if (isset($options['orderby'])) {
			$prefix = 'c.';
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
	static function findAllByRef($ref, $ref_id, $options=array()) {
		$sql = "SELECT c.*, a.g_name AS author_name"
			. " FROM comments c INNER JOIN users a ON c.author_uid = a.uid"
			. " WHERE c.ref = :ref AND c.ref_id = :ref_id";
		if (isset($options['orderby'])) {
			$prefix = 'c.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_INT);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByPractice($practice_id, $options=array()) {
		return CommentGateway::findAllByRef('practice', $practice_id, $options);
	}
	static function findAllByLessonReflection($lesson_id, $options=array()) {
		return CommentGateway::findAllByRef('lesson', $lesson_id, $options);
	}
	
	static function insert($ref, $ref_id, $author_uid, $comment_text, $created_date, $modified_date) {
		$sql = "INSERT INTO comments (ref, ref_id, author_uid, comment_text, created_date, modified_date)"
			. " VALUES (:ref, :ref_id, :author_uid, :comment_text, :created_date, :modified_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->bindParam(':author_uid', $author_uid, PDO::PARAM_STR);
		$stmt->bindParam(':comment_text', $comment_text, PDO::PARAM_STR);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateCommentText($comment_id, $comment_text, $modified_date) {
		$sql = "UPDATE comments"
			. " SET comment_text = :comment_text, modified_date = :modified_date"
			. " WHERE comment_id = :comment_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
		$stmt->bindParam(':comment_text', $comment_text, PDO::PARAM_STR);
		$stmt->bindParam(':modified_date', $modified_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($comment_id) {
		$stmt = DbLink::prepare("DELETE FROM comments WHERE comment_id = :comment_id");
		$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllInPracticesOfStudent($student_id) {
		$sql = "DELETE FROM comments WHERE ref = 'practice' AND ref_id IN ("
			. " 	SELECT p.practice_id FROM practices p"
			. " 	INNER JOIN tasks t ON p.task_id = t.task_id"
			. " 	INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.student_id = :student_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllInPracticesOfTeacher($teacher_id) {
		$sql = "DELETE FROM comments WHERE ref = 'practice' AND ref_id IN ("
			. " 	SELECT p.practice_id FROM practices p"
			. " 	INNER JOIN tasks t ON p.task_id = t.task_id"
			. " 	INNER JOIN lessons l ON t.lesson_id = l.lesson_id"
			. " 	WHERE l.teacher_id = :teacher_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllInLessonReflectionsOfStudent($student_id) {
		$sql = "DELETE FROM comments WHERE ref = 'lesson' AND ref_id IN ("
			. " 	SELECT lesson_id FROM lessons l"
			. " 	WHERE l.student_id = :student_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllInLessonReflectionsOfTeacher($teacher_id) {
		$sql = "DELETE FROM comments WHERE ref = 'lesson' AND ref_id IN ("
			. " 	SELECT lesson_id FROM lessons l"
			. " 	WHERE l.teacher_id = :teacher_id"
			. " )";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}