<?php
class LessonReflectionGateway extends Tdg {
	
	const PRIMARY_KEY = 'lesson_reflection_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY);
	}
	
	static function find($lesson_reflection_id) {
		$sql = "SELECT lr.*, l.student_id, l.teacher_id"
			. " FROM lesson_reflections lr INNER JOIN lessons l ON lr.lesson_id = l.lesson_id"
			. " WHERE lr.lesson_reflection_id = :lesson_reflection_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_reflection_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByLessonId($lesson_id) {
		$sql = "SELECT lr.*, l.student_id, l.teacher_id"
			. " FROM lesson_reflections lr INNER JOIN lessons l ON lr.lesson_id = l.lesson_id"
			. " WHERE lr.lesson_id = :lesson_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT lr.*, l.student_id, l.teacher_id"
			. " FROM lesson_reflections lr INNER JOIN lessons l ON lr.lesson_id = l.lesson_id";
		if (isset($options['orderby'])) {
			$prefix = 'lr.';
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
	
	static function insert($lesson_id, $reflection_index, $reflection_text, $reflection_prompt) {
		$sql = "INSERT INTO lesson_reflections (lesson_id, reflection_index, reflection_text, reflection_prompt)"
			. " VALUES (:lesson_id, :reflection_index, :reflection_text, :reflection_prompt)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_index', $reflection_index, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_text', $reflection_text, PDO::PARAM_STR);
		$stmt->bindParam(':reflection_prompt', $reflection_prompt, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function update($lesson_reflection_id, $lesson_id, $reflection_index, $reflection_text, $reflection_prompt) {
		$sql = "UPDATE lesson_reflections"
			. " SET lesson_id = :lesson_id, reflection_index = :reflection_index, reflection_text = :reflection_text, reflection_prompt = :reflection_prompt"
			. " WHERE lesson_reflection_id = :lesson_reflection_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_reflection_id', $lesson_reflection_id, PDO::PARAM_INT);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_index', $reflection_index, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_text', $reflection_text, PDO::PARAM_STR);
		$stmt->bindParam(':reflection_prompt', $reflection_prompt, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateByLessonId($lesson_id, $reflection_index, $reflection_text, $reflection_prompt) {
		$sql = "UPDATE lesson_reflections"
			. " SET reflection_index = :reflection_index, reflection_text = :reflection_text, reflection_prompt = :reflection_prompt"
			. " WHERE lesson_id = :lesson_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_index', $reflection_index, PDO::PARAM_INT);
		$stmt->bindParam(':reflection_text', $reflection_text, PDO::PARAM_STR);
		$stmt->bindParam(':reflection_prompt', $reflection_prompt, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($lesson_reflection_id) {
		$stmt = DbLink::prepare("DELETE FROM lesson_reflections WHERE lesson_reflection_id = :lesson_reflection_id");
		$stmt->bindParam(':lesson_reflection_id', $lesson_reflection_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteByLessonId($lesson_id) {
		$stmt = DbLink::prepare("DELETE FROM lesson_reflections WHERE lesson_id = :lesson_id");
		$stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}