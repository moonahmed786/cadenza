<?php
class UserGateway extends Tdg {
	
	const PRIMARY_KEY = 'uid';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'g_email', 'g_name', 'g_given_name', 'g_family_name', 'last_login', 'status_date');
	}
	
	/**
	 * Return a normalized version of the given email address.
	 * 
	 * This function will convert the email addresses to lowercase and will strip any periods in gmail addresses.
	 * @param string $email_address the email address to be normalized. Must be either a string or null.
	 * @return string The resulting normalized email address.
	 */
	static function normalizeEmail($email_address) {
		if ($email_address !== null && !is_string($email_address)) {
			trigger_error('Invalid parameters: email_address must be either a string or null.', E_USER_ERROR);
		}
		if ($email_address !== null) {
			$email_address = strtolower($email_address);
			$email_parts = explode("@", $email_address);
			if (count($email_parts) != 2) {
				trigger_error('Invalid parameters: email_address must have a name and a client sperated by \'@\'.', E_USER_ERROR);
			}
			
			$email_name = $email_parts[0];
			$email_client = $email_parts[1];
			
			if ($email_client == "gmail.com") {
				$email_name = str_replace(".", "", $email_name);
				$email_address = $email_name."@".$email_client;
			}
		}
		return $email_address;
	}
	
	static function getStatusWhitelist() {
		return array('active', 'deleted', 'blocked');
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
	
	static function find($uid) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByToken($g_refresh_token) { //DATE(`timestamp`) = CURDATE() AND
		$stmt = DbLink::prepare("SELECT * FROM users WHERE g_refresh_token = :g_refresh_token");
		$stmt->bindParam(':g_refresh_token', $g_refresh_token, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findLinkAccount($uid) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE link_account = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByEmail($g_email) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE g_email = :g_email");
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByEmailNormalized($email_normalized) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE email_normalized = :email_normalized");
		$stmt->bindParam(':email_normalized', $email_normalized, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findStudent($uid) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE uid = :uid AND user_type = 'student'");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findStudentByEmail($g_email) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE g_email = :g_email AND user_type = 'student'");
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findStudentByEmailNormalized($email_normalized) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE email_normalized = :email_normalized AND user_type = 'student'");
		$stmt->bindParam(':email_normalized', $email_normalized, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findTeacher($uid) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE uid = :uid AND user_type = 'teacher'");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findTeacherByEmail($g_email) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE g_email = :g_email AND user_type = 'teacher'");
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findTeacherByEmailNormalized($email_normalized) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE email_normalized = :email_normalized AND user_type = 'teacher'");
		$stmt->bindParam(':email_normalized', $email_normalized, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM users";
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
	static function findAllExceptDeleted($options=array()) {
		$sql = "SELECT * FROM users WHERE status != 'deleted'";
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
	static function findAllWithStatus($status, $options=array()) {
		$sql = "SELECT * FROM users WHERE status = :status";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllStudents($options=array()) {
		$sql = "SELECT * FROM users WHERE user_type = 'student'";
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
	static function findAllStudentsWithStatus($status, $options=array()) {
		$sql = "SELECT * FROM users WHERE user_type = 'student' AND status = :status";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllTeachers($options=array()) {
		$sql = "SELECT * FROM users WHERE user_type = 'teacher'";
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
	static function findAllTeachersWithStatus($status, $options=array()) {
		$sql = "SELECT * FROM users WHERE user_type = 'teacher' AND status = :status";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllStudentsLinkedToTeacher($teacher_id, $link_statuses=array(), $options=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
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
	static function findAllTeachersLinkedToStudent($student_id, $link_statuses=array(), $options=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
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
	static function findAllStudentsWithStatusLinkedToTeacher($teacher_id, $status, $link_statuses=array(), $options=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE u.status = :status AND ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllTeachersWithStatusLinkedToStudent($student_id, $status, $link_statuses=array(), $options=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE u.status = :status AND ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllStudentsWithStatusesLinkedToTeacher($teacher_id, $statuses=array(), $link_statuses=array(), $options=array()) {
		if (!is_array($statuses) || count($statuses) == 0) {
			trigger_error('statuses must be a non-empty array', E_USER_ERROR);
		}
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE ".static::getStatusesCondition($statuses, 'u.')." AND ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
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
	static function findAllTeachersWithStatusesLinkedToStudent($student_id, $statuses=array(), $link_statuses=array(), $options=array()) {
		if (!is_array($statuses) || count($statuses) == 0) {
			trigger_error('statuses must be a non-empty array', E_USER_ERROR);
		}
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT u.*"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE ".static::getStatusesCondition($statuses, 'u.')." AND ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		if (isset($options['orderby'])) {
			/* NOTE: Since in this function we are allowing order by fields in the joined table,
			 * we need more fine-grained control over the prefix passed to the sanitize functions.
			 * First, we ensure that we have an array (will convert to one if needed), and then
			 * we loop through to look at each element.
			 */
			$orderby_arr = is_array($options['orderby']) ? $options['orderby'] : array($options['orderby']);
			$orderby = "";
			$first = true;
			foreach ($orderby_arr as $orderby_str) {
				if (!$first) {
					$orderby .= ", ";
				}
				$pieces = explode('.', $options['orderby'], 2);
				if (count($pieces) > 1 && $pieces[0] == 'user_links') {
					$prefix = 'ul.';
					$orderby .= static::sanitizeOrderByString($pieces[1], UserLinkGateway::getOrderByWhitelist(), $prefix);
				}
				else {
					$prefix = 'u.';
					$orderby .= static::sanitizeOrderByString($orderby_str, static::getOrderByWhitelist(), $prefix);
				}
				$first = false;
			}
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

	static function countAll() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countAllWithStatus($status) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE status = :status");
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countStudents() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE user_type = 'student'");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countStudentsWithStatus($status) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE user_type = 'student' AND status = :status");
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countTeachers() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE user_type = 'teacher'");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countTeachersWithStatus($status) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE user_type = 'teacher' AND status = :status");
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countStudentsLinkedToTeacher($teacher_id, $link_statuses=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countTeachersLinkedToStudent($student_id, $link_statuses=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countStudentsWithStatusLinkedToTeacher($teacher_id, $status, $link_statuses=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE u.status = :status AND ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countTeachersWithStatusLinkedToStudent($student_id, $status, $link_statuses=array()) {
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE u.status = :status AND ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countStudentsWithStatusesLinkedToTeacher($teacher_id, $statuses=array(), $link_statuses=array()) {
		if (!is_array($statuses) || count($statuses) == 0) {
			trigger_error('statuses must be a non-empty array', E_USER_ERROR);
		}
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.student_id = u.uid"
			. " WHERE ".static::getStatusesCondition($statuses, 'u.')." AND ul.teacher_id = :teacher_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countTeachersWithStatusesLinkedToStudent($student_id, $statuses=array(), $link_statuses=array()) {
		if (!is_array($statuses) || count($statuses) == 0) {
			trigger_error('statuses must be a non-empty array', E_USER_ERROR);
		}
		if (!is_array($link_statuses) || count($link_statuses) == 0) {
			trigger_error('link_statuses must be a non-empty array', E_USER_ERROR);
		}
		$sql = "SELECT COUNT(*)"
			. " FROM user_links ul INNER JOIN users u ON ul.teacher_id = u.uid"
			. " WHERE ".static::getStatusesCondition($statuses, 'u.')." AND ul.student_id = :student_id AND ".UserLinkGateway::getStatusesCondition($link_statuses, 'ul.');
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}

	static function isActive($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE uid = :uid AND status = 'active'");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isBlocked($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE uid = :uid AND status = 'blocked'");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	static function isDeleted($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM users WHERE uid = :uid AND status = 'deleted'");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0] > 0);
	}
	
	static function insert($email_normalized, $g_email, $g_name, $g_given_name, $g_family_name, $g_picture=NULL, $g_refresh_token=NULL, $user_type, $last_login=NULL, $status=NULL, $status_date=NULL, $created_date=NULL) {

		$g_picture = $g_picture?$g_picture:"https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg";
		$status = $status?$status:"active";
		$last_login = $last_login?$last_login:date('Y-m-d');
		$status_date = $status_date?$status_date:date('Y-m-d');
		$created_date = $created_date?$created_date:date('Y-m-d');
		$g_refresh_token = $g_refresh_token?$g_refresh_token:'not set';

		$sql = "INSERT INTO users (email_normalized, g_email, g_name, g_given_name, g_family_name, g_picture, g_refresh_token, user_type, last_login, status, status_date, created_date)"
			. " VALUES (:email_normalized, :g_email, :g_name, :g_given_name, :g_family_name, :g_picture, :g_refresh_token, :user_type, :last_login, :status, :status_date, :created_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':email_normalized', $email_normalized, PDO::PARAM_STR);
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->bindParam(':g_name', $g_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_given_name', $g_given_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_family_name', $g_family_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_picture', $g_picture, PDO::PARAM_STR);
		$stmt->bindParam(':g_refresh_token', $g_refresh_token, PDO::PARAM_STR);
		$stmt->bindParam(':user_type', $user_type, ($user_type === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
		$stmt->bindParam(':last_login', $last_login, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':status_date', $status_date, PDO::PARAM_STR);
		$stmt->bindParam(':created_date', $created_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	static function signup($g_email, $g_name, $username, $user_type,$password,$refresh_token) {

		$g_picture = "https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg";
		$status = 'disabled';
		$currentDate = date('Y-m-d');
		$password_hash = password_hash($password,PASSWORD_DEFAULT);
		
		$sql = "INSERT INTO users (email_normalized, g_email, g_name,g_username, g_given_name, g_family_name, g_picture,g_refresh_token, user_type, last_login, status, status_date, created_date,password_hash)"
			. " VALUES (:email_normalized, :g_email, :g_name,:g_username, :g_given_name, :g_family_name, :g_picture,:g_refresh_token, :user_type, :last_login, :status, :status_date, :created_date, :password_hash)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':email_normalized', $g_email, PDO::PARAM_STR);
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->bindParam(':g_name', $g_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_username', $username, PDO::PARAM_STR);
		$stmt->bindParam(':g_given_name', $username, PDO::PARAM_STR);
		$stmt->bindParam(':g_family_name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':g_picture', $g_picture, PDO::PARAM_STR);
		$stmt->bindParam(':g_refresh_token',$refresh_token, PDO::PARAM_STR);
		$stmt->bindParam(':user_type', $user_type,PDO::PARAM_STR);
		$stmt->bindParam(':last_login', $currentDate, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':status_date', $currentDate, PDO::PARAM_STR);
		$stmt->bindParam(':created_date', $currentDate, PDO::PARAM_STR);
		$stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
		$stmt->execute();
		$id = DbLink::lastInsertId();

		return $id;
	}
	
	static function updateEmailNormalized($uid, $email_normalized) {
		$stmt = DbLink::prepare("UPDATE users SET email_normalized = :email_normalized WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':email_normalized', $email_normalized, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function resetPassword($uid, $password_hash) {
		$stmt = DbLink::prepare("UPDATE users SET password_hash = :password_hash WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
		$stmt->execute();
	}

	static function tokenUpdate($uid, $token) {
		$stmt = DbLink::prepare("UPDATE users SET g_refresh_token = :g_refresh_token WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':g_refresh_token', $token, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function updateGoogleData($uid, $g_email, $g_name, $g_given_name, $g_family_name, $g_picture, $g_refresh_token) {
		$sql = "UPDATE users"
			. " SET g_email = :g_email, g_name = :g_name, g_given_name = :g_given_name, g_family_name = :g_family_name, g_picture = :g_picture, g_refresh_token = :g_refresh_token"
			. " WHERE uid = :uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':g_email', $g_email, PDO::PARAM_STR);
		$stmt->bindParam(':g_name', $g_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_given_name', $g_given_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_family_name', $g_family_name, PDO::PARAM_STR);
		$stmt->bindParam(':g_picture', $g_picture, PDO::PARAM_STR);
		$stmt->bindParam(':g_refresh_token', $g_refresh_token, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateGoogleUserInfo($uid, Google_Service_Oauth2_Userinfoplus $userinfo) {
		$sql = "UPDATE users"
			. " SET g_email = :g_email, g_name = :g_name, g_given_name = :g_given_name, g_family_name = :g_family_name, g_picture = :g_picture"
			. " WHERE uid = :uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':g_email', $userinfo->email, PDO::PARAM_STR);
		$stmt->bindParam(':g_name', $userinfo->name, PDO::PARAM_STR);
		$stmt->bindParam(':g_given_name', $userinfo->givenName, PDO::PARAM_STR);
		$stmt->bindParam(':g_family_name', $userinfo->familyName, PDO::PARAM_STR);
		$stmt->bindParam(':g_picture', $userinfo->picture, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateGoogleRefreshToken($uid, $g_refresh_token) {
		$sql = "UPDATE users"
			. " SET g_refresh_token = :g_refresh_token"
			. " WHERE uid = :uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':g_refresh_token', $g_refresh_token, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateUserType($uid, $user_type) {
		$stmt = DbLink::prepare("UPDATE users SET user_type = :user_type WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function linkAccount($uid, $link_account) {
		if ($uid==$link_account) {
			return;
		}
		$stmt = DbLink::prepare("UPDATE users SET link_account = :link_account WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':link_account', $link_account, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function updateLastLogin($uid, $last_login) {
		$stmt = DbLink::prepare("UPDATE users SET last_login = :last_login WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':last_login', $last_login, PDO::PARAM_STR);
		$stmt->execute();
	}
	static function findByUsername($username) {
		$stmt = DbLink::prepare("SELECT * FROM users WHERE g_username = :username");
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function updateStatus($uid, $status, $status_date) {
		$stmt = DbLink::prepare("UPDATE users SET status = :status, status_date = :status_date WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':status_date', $status_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
}
