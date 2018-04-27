<?php
class UserLinkGateway extends Tdg {
	
	const PRIMARY_KEY = 'user_link_id';
	
	static function getOrderByWhitelist() {
		return array_merge(static::getOrderByWhitelistNoAliases(), static::getOrderByWhitelistAliases());
	}
	static function getOrderByWhitelistNoAliases() {
		return array(static::PRIMARY_KEY, 'status_date', 'last_lesson_id');
	}
	static function getOrderByWhitelistAliases() {
		return array('student_email', 'teacher_email');
	}
	
	static function getStatusArrayConnected() {
		return array('connected');
	}
	static function getStatusArrayInactive() {
		return array('disconnected-inactive', 'pending-inactive', 'rejected-inactive');
	}
	static function getStatusArrayInvite() {
		return array('pending', 'rejected', 'pending-inactive', 'rejected-inactive');
	}
	static function getStatusWhitelist() {
		return array('pending', 'rejected', 'connected', 'disconnected-inactive', 'pending-inactive', 'rejected-inactive');
	}
	
	static function getIsConnectedCondition($prefix='') {
		return static::getStatusesCondition(static::getStatusArrayConnected(), $prefix);
	}
	static function getIsInactiveCondition($prefix='') {
		return static::getStatusesCondition(static::getStatusArrayInactive(), $prefix);
	}
	static function getIsInviteCondition($prefix='') {
		return static::getStatusesCondition(static::getStatusArrayInvite(), $prefix);
	}
	
	static function getStatusesCondition($statuses=array(), $prefix='') {
		$status_whitelist = static::getStatusWhitelist();
		$sql = "";
		$count = 0;
		foreach ($statuses as $status) {
			if (in_array($status, $status_whitelist)) {
				if (++$count > 1) {
					$sql .= " OR ";
				}
				$sql .= "${prefix}status = '${status}'";
			}
		}
		if ($sql != "") {
			return ($count > 1) ? '('.$sql.')' : $sql;
		}
		return null;
	}
	
	static function find($user_link_id) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.user_link_id = :user_link_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByStudentTeacher($student_id, $teacher_id) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id AND ul.teacher_id = :teacher_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findConnectedByStudentTeacher($student_id, $teacher_id) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id AND ul.teacher_id = :teacher_id AND ".static::getIsConnectedCondition('ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findInviteByStudentTeacher($student_id, $teacher_id) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id AND ul.teacher_id = :teacher_id AND ".static::getIsInviteCondition('ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid";
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id";
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.teacher_id = :teacher_id";
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
	static function findAllConnectedByStudent($student_id, $options=array()) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id AND ".static::getIsConnectedCondition('ul.');
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
	static function findAllConnectedByTeacher($teacher_id, $options=array()) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.teacher_id = :teacher_id AND ".static::getIsConnectedCondition('ul.');
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
	static function findAllInvitesByStudent($student_id, $options=array()) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.student_id = :student_id AND ".static::getIsInviteCondition('ul.');
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
	static function findAllInvitesByTeacher($teacher_id, $options=array()) {
		$sql = "SELECT ul.*, s.g_email AS student_email, s.status AS student_user_status, t.g_email AS teacher_email, t.status AS teacher_user_status"
			. " FROM user_links ul INNER JOIN users s ON ul.student_id = s.uid INNER JOIN users t ON ul.teacher_id = t.uid"
			. " WHERE ul.teacher_id = :teacher_id AND ".static::getIsInviteCondition('ul.');
		if (isset($options['orderby'])) {
			$prefix = 'ul.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
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
	
	static function countConnectedStudentsOfTeacher($teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE teacher_id = :teacher_id AND ".static::getIsConnectedCondition());
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countConnectedTeachersOfStudent($student_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND ".static::getIsConnectedCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countInactiveStudentsOfTeacher($teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE teacher_id = :teacher_id AND ".static::getIsInactiveCondition());
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countInactiveTeachersOfStudent($student_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND ".static::getIsInactiveCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countInvitesOfStudent($student_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND ".static::getIsInviteCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countInvitesOfTeacher($teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE teacher_id = :teacher_id AND ".static::getIsInviteCondition());
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	static function isConnected($user_link_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE user_link_id = :user_link_id AND ".static::getIsConnectedCondition());
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isConnectedStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND teacher_id = :teacher_id AND ".static::getIsConnectedCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isInactive($user_link_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE user_link_id = :user_link_id AND ".static::getIsInactiveCondition());
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isInactiveStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND teacher_id = :teacher_id AND ".static::getIsInactiveCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isConnectedOrInactive($user_link_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE user_link_id = :user_link_id AND (".static::getIsConnectedCondition()." OR ".static::getIsInactiveCondition().")");
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isConnectedOrInactiveStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND teacher_id = :teacher_id AND (".static::getIsConnectedCondition()." OR ".static::getIsInactiveCondition().")");
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isInvite($user_link_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE user_link_id = :user_link_id AND ".static::getIsInviteCondition());
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isInviteStudentTeacher($student_id, $teacher_id) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM user_links WHERE student_id = :student_id AND teacher_id = :teacher_id AND ".static::getIsInviteCondition());
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	
	static function insert($student_id, $teacher_id, $status, $status_date, $last_lesson_id) {
		$sql = "INSERT INTO user_links (student_id, teacher_id, status, status_date, last_lesson_id)"
			. " VALUES (:student_id, :teacher_id, :status, :status_date, :last_lesson_id)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':status_date', $status_date, PDO::PARAM_STR);
		$stmt->bindParam(':last_lesson_id', $last_lesson_id, PDO::PARAM_INT);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateStatus($user_link_id, $status, $status_date) {
		$sql = "UPDATE user_links"
			. " SET status = :status, status_date = :status_date"
			. " WHERE user_link_id = :user_link_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':status_date', $status_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateLastLesson($user_link_id, $last_lesson_id) {
		$sql = "UPDATE user_links"
			. " SET last_lesson_id = :last_lesson_id"
			. " WHERE user_link_id = :user_link_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->bindParam(':last_lesson_id', $last_lesson_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function updateLastLessonByStudentTeacher($student_id, $teacher_id, $last_lesson_id) {
		$sql = "UPDATE user_links"
			. " SET last_lesson_id = :last_lesson_id"
			. " WHERE student_id = :student_id AND teacher_id = :teacher_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->bindParam(':last_lesson_id', $last_lesson_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function delete($user_link_id) {
		$stmt = DbLink::prepare("DELETE FROM user_links WHERE user_link_id = :user_link_id");
		$stmt->bindParam(':user_link_id', $user_link_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}