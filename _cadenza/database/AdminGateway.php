<?php
class AdminGateway extends Tdg {
	
	const PRIMARY_KEY = 'admin_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'username');
	}
	
	static function find($admin_id) {
		$stmt = DbLink::prepare("SELECT * FROM admins WHERE admin_id = :admin_id");
		$stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByUsername($username) {
		$stmt = DbLink::prepare("SELECT * FROM admins WHERE username = :username");
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM admins";
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
	
}
