<?php
class AutocompleteGateway extends Tdg {
	
	const PRIMARY_KEY = 'autocomplete_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'autocomplete_text', 'autocomplete_date');
	}
	
	static function find($autocomplete_id) {
		$stmt = DbLink::prepare("SELECT * FROM autocomplete WHERE autocomplete_id = :autocomplete_id");
		$stmt->bindParam(':autocomplete_id', $autocomplete_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findByUidFieldText($uid, $autocomplete_field, $autocomplete_text) {
		$stmt = DbLink::prepare("SELECT * FROM autocomplete WHERE uid = :uid AND autocomplete_field = :autocomplete_field AND autocomplete_text = :autocomplete_text");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':autocomplete_field', $autocomplete_field, PDO::PARAM_STR);
		$stmt->bindParam(':autocomplete_text', $autocomplete_text, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findTaskTitleByUidText($uid, $autocomplete_text) {
		$autocomplete_field = 'task_title';
		return static::findByUidFieldText($uid, $autocomplete_field, $autocomplete_text);
	}
	static function findTaskCategoryOtherByUidText($uid, $autocomplete_text) {
		$autocomplete_field = 'task_category_other';
		return static::findByUidFieldText($uid, $autocomplete_field, $autocomplete_text);
	}
	static function findTaskChecklistItemByUidText($uid, $autocomplete_text) {
		$autocomplete_field = 'task_checklist_item';
		return static::findByUidFieldText($uid, $autocomplete_field, $autocomplete_text);
	}
	
	static function findAllTaskTitlesByUid($uid, $options=array()) {
		$sql = "SELECT * FROM autocomplete WHERE autocomplete_field = 'task_title' AND uid = :uid";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllTaskCategoryOthersByUid($uid, $options=array()) {
		$sql = "SELECT * FROM autocomplete WHERE autocomplete_field = 'task_category_other' AND uid = :uid";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllTaskChecklistItemsByUid($uid, $options=array()) {
		$sql = "SELECT * FROM autocomplete WHERE autocomplete_field = 'task_checklist_item' AND uid = :uid";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function insert($uid, $autocomplete_field, $autocomplete_text, $autocomplete_date) {
		$sql = "INSERT INTO autocomplete (uid, autocomplete_field, autocomplete_text, autocomplete_date)"
			. " VALUES (:uid, :autocomplete_field, :autocomplete_text, :autocomplete_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':autocomplete_field', $autocomplete_field, PDO::PARAM_STR);
		$stmt->bindParam(':autocomplete_text', $autocomplete_text, PDO::PARAM_STR);
		$stmt->bindParam(':autocomplete_date', $autocomplete_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	static function insertTaskTitle($uid, $autocomplete_text, $autocomplete_date) {
		$autocomplete_field = 'task_title';
		return static::insert($uid, $autocomplete_field, $autocomplete_text, $autocomplete_date);
	}
	static function insertTaskCategoryOther($uid, $autocomplete_text, $autocomplete_date) {
		$autocomplete_field = 'task_category_other';
		return static::insert($uid, $autocomplete_field, $autocomplete_text, $autocomplete_date);
	}
	static function insertTaskChecklistItem($uid, $autocomplete_text, $autocomplete_date) {
		$autocomplete_field = 'task_checklist_item';
		return static::insert($uid, $autocomplete_field, $autocomplete_text, $autocomplete_date);
	}
	
	static function updateDate($autocomplete_id, $autocomplete_date) {
		$sql = "UPDATE autocomplete SET autocomplete_date = :autocomplete_date WHERE autocomplete_id = :autocomplete_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':autocomplete_id', $autocomplete_id, PDO::PARAM_INT);
		$stmt->bindParam(':autocomplete_date', $autocomplete_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function delete($autocomplete_id) {
		$stmt = DbLink::prepare("DELETE FROM autocomplete WHERE autocomplete_id = :autocomplete_id");
		$stmt->bindParam(':autocomplete_id', $autocomplete_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllOfUid($uid) {
		$stmt = DbLink::prepare("DELETE FROM autocomplete WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}