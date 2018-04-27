<?php
class PracticeFieldGateway extends Tdg {
	
	const PRIMARY_KEY = 'practice_field_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'ref_id', 'field_value');
	}
	
	static function find($practice_field_id) {
		$stmt = DbLink::prepare("SELECT * FROM practice_fields WHERE practice_field_id = :practice_field_id");
		$stmt->bindParam(':practice_field_id', $practice_field_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM practice_fields";
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
	static function findAllByPractice($practice_id, $options=array()) {
		$sql = "SELECT * FROM practice_fields WHERE practice_id = :practice_id";
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
	static function findAllChecklistItems($options=array()) {
		$sql = "SELECT pf.*, c.text, c.target_type, c.target_val"
			. " FROM practice_fields pf INNER JOIN checklist_items c ON pf.ref = 'checklist_item' AND pf.ref_id = c.checklist_item_id";
		if (isset($options['orderby'])) {
			$prefix = 'pf.';
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
	static function findAllChecklistItemsByPractice($practice_id, $options=array()) {
		$sql = "SELECT pf.*, c.text, c.target_type, c.target_val"
			. " FROM practice_fields pf INNER JOIN checklist_items c ON pf.ref = 'checklist_item' AND pf.ref_id = c.checklist_item_id"
			. " WHERE practice_id = :practice_id";
		if (isset($options['orderby'])) {
			$prefix = 'pf.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
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
	
	static function insert($practice_id, $ref, $ref_id, $field_value) {
		$sql = "INSERT INTO practice_fields (practice_id, ref, ref_id, field_value)"
			. " VALUES (:practice_id, :ref, :ref_id, :field_value)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->bindParam(':field_value', $field_value, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	static function insertChecklistItem($practice_id, $checklist_item_id, $field_value) {
		$sql = "INSERT INTO practice_fields (practice_id, ref, ref_id, field_value)"
			. " VALUES (:practice_id, 'checklist_item', :ref_id, :field_value)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':practice_id', $practice_id, PDO::PARAM_INT);
		$stmt->bindParam(':ref_id', $checklist_item_id, PDO::PARAM_INT);
		$stmt->bindParam(':field_value', $field_value, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
}