<?php
class AdminReportGateway extends Tdg {
	
	const PRIMARY_KEY = 'admin_report_id';
	
	static function getOrderByWhitelist() {
		return array_merge(static::getOrderByWhitelistNoAliases(), static::getOrderByWhitelistAliases());
	}
	static function getOrderByWhitelistNoAliases() {
		return array(static::PRIMARY_KEY, 'report_date');
	}
	static function getOrderByWhitelistAliases() {
		return array('reporter_email');
	}
	
	static function find($admin_report_id) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.admin_report_id = :admin_report_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':admin_report_id', $admin_report_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findWithReporter($admin_report_id, $reporter_uid) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.admin_report_id = :admin_report_id AND reporter_uid = :reporter_uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':admin_report_id', $admin_report_id, PDO::PARAM_INT);
		$stmt->bindParam(':reporter_uid', $reporter_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findWithReported($admin_report_id, $reported_uid) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.admin_report_id = :admin_report_id AND reported_uid = :reported_uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':admin_report_id', $admin_report_id, PDO::PARAM_INT);
		$stmt->bindParam(':reported_uid', $reported_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid";
		if (isset($options['orderby'])) {
			$prefix = 'ar.';
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
	static function findAllByReporterUser($reporter_uid, $options=array()) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.reporter_uid = :reporter_uid";
		if (isset($options['orderby'])) {
			$prefix = 'ar.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':reporter_uid', $reporter_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByReportedUser($reported_uid, $options=array()) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.reported_uid = :reported_uid";
		if (isset($options['orderby'])) {
			$prefix = 'ar.';
			$aliases = static::getOrderByWhitelistAliases();
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix, $aliases);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':reported_uid', $reported_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllResolved($options=array()) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.is_resolved = 1";
		if (isset($options['orderby'])) {
			$prefix = 'ar.';
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
	static function findAllUnresolved($options=array()) {
		$sql = "SELECT"
			. " 	ar.*,"
			. " 	reporter.g_email AS reporter_email,"
			. " 	reporter.g_name AS reporter_name,"
			. " 	reporter.status AS reporter_user_status,"
			. " 	reported.g_email AS reported_email,"
			. " 	reported.g_name AS reported_name,"
			. " 	reported.status AS reported_user_status"
			. " FROM admin_reports ar"
			. " INNER JOIN users reporter ON ar.reporter_uid = reporter.uid"
			. " LEFT OUTER JOIN users reported ON ar.reported_uid = reported.uid"
			. " WHERE ar.is_resolved = 0";
		if (isset($options['orderby'])) {
			$prefix = 'ar.';
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
	
	static function countAll() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM admin_reports");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countResolved() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM admin_reports WHERE is_resolved = 1");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countUnresolved() {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM admin_reports WHERE is_resolved = 0");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	static function insert($reporter_uid, $reported_uid, $report_type, $report_text, $report_date, $is_resolved) {
		$sql = "INSERT INTO admin_reports (reporter_uid, reported_uid, report_type, report_text, report_date, is_resolved)"
			. " VALUES (:reporter_uid, :reported_uid, :report_type, :report_text, :report_date, :is_resolved)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':reporter_uid', $reporter_uid, PDO::PARAM_INT);
		$stmt->bindParam(':reported_uid', $reported_uid, PDO::PARAM_INT);
		$stmt->bindParam(':report_type', $report_type, PDO::PARAM_STR);
		$stmt->bindParam(':report_text', $report_text, PDO::PARAM_STR);
		$stmt->bindParam(':report_date', $report_date, PDO::PARAM_STR);
		$stmt->bindParam(':is_resolved', $is_resolved, PDO::PARAM_BOOL);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	static function insertNewIssueReport($reporter_uid, $reported_uid, $report_text, $report_date) {
		$report_type = 'issue';
		$is_resolved = false;
		return static::insert($reporter_uid, $reported_uid, $report_type, $report_text, $report_date, $is_resolved);
	}
	static function insertNewDeleteRequest($reporter_uid, $report_text, $report_date) {
		$reported_uid = null;
		$report_type = 'delete';
		$is_resolved = false;
		return static::insert($reporter_uid, $reported_uid, $report_type, $report_text, $report_date, $is_resolved);
	}
	
	static function updateIsResolved($admin_report_id, $is_resolved) {
		$sql = "UPDATE admin_reports"
			. " SET is_resolved = :is_resolved"
			. " WHERE admin_report_id = :admin_report_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':admin_report_id', $admin_report_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_resolved', $is_resolved, PDO::PARAM_BOOL);
		$stmt->execute();
	}
	
	static function delete($admin_report_id) {
		$stmt = DbLink::prepare("DELETE FROM admin_reports WHERE admin_report_id = :admin_report_id");
		$stmt->bindParam(':admin_report_id', $admin_report_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllWithReporter($reporter_uid) {
		$stmt = DbLink::prepare("DELETE FROM admin_reports WHERE reporter_uid = :reporter_uid");
		$stmt->bindParam(':reporter_uid', $reporter_uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllWithReported($reported_uid) {
		$stmt = DbLink::prepare("DELETE FROM admin_reports WHERE reported_uid = :reported_uid");
		$stmt->bindParam(':reported_uid', $reported_uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}
