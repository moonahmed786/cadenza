<?php
class ChecklistItemGateway extends Tdg {
	
	const PRIMARY_KEY = 'checklist_item_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY);
	}
	
	static function find($checklist_item_id) {
		$stmt = DbLink::prepare("SELECT * FROM checklist_items WHERE checklist_item_id = :checklist_item_id");
		$stmt->bindParam(':checklist_item_id', $checklist_item_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM checklist_items";
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
	static function findAllByTask($task_id, $options=array()) {
		$sql = "SELECT * FROM checklist_items WHERE task_id = :task_id";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function insert($task_id, $text, $target_type, $target_val) {
		$sql = "INSERT INTO checklist_items (task_id, text, target_type, target_val)"
			. " VALUES (:task_id, :text, :target_type, :target_val)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':text', $text, PDO::PARAM_STR);
		$stmt->bindParam(':target_type', $target_type, PDO::PARAM_INT);
		$stmt->bindParam(':target_val', $target_val, PDO::PARAM_INT);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function update($checklist_item_id, $task_id, $text, $target_type, $target_val) {
		$sql = "UPDATE checklist_items"
			. " SET task_id = :task_id, text = :text, target_type = :target_type, target_val = :target_val"
			. " WHERE checklist_item_id = :checklist_item_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':checklist_item_id', $checklist_item_id, PDO::PARAM_INT);
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->bindParam(':text', $text, PDO::PARAM_STR);
		$stmt->bindParam(':target_type', $target_type, PDO::PARAM_INT);
		$stmt->bindParam(':target_val', $target_val, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function delete($checklist_item_id) {
		$stmt = DbLink::prepare("DELETE FROM checklist_items WHERE checklist_item_id = :checklist_item_id");
		$stmt->bindParam(':checklist_item_id', $checklist_item_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllInTask($task_id) {
		$stmt = DbLink::prepare("DELETE FROM checklist_items WHERE task_id = :task_id");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllInTaskExcept($task_id, $keep_checklist_item_ids) {
		if (count($keep_checklist_item_ids) == 0) {
			static::deleteAllInTask($task_id);
			return;
		}
		$in = implode(',', array_map('intval', $keep_checklist_item_ids));
		$stmt = DbLink::prepare("DELETE FROM checklist_items WHERE task_id = :task_id AND checklist_item_id NOT IN ($in)");
		$stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}