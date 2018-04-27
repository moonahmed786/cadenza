<?php
class NotificationGateway extends Tdg {
	
	const PRIMARY_KEY = 'notification_id';
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'notification_date', 'priority');
	}
	
	static function find($notification_id) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.notification_id = :notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findToUser($notification_id, $uid) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.notification_id = :notification_id AND n.uid = :uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findSentToUser($notification_id, $uid) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.notification_id = :notification_id AND n.uid = :uid AND n.is_sent = 1";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findFromSender($notification_id, $sender_uid) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.notification_id = :notification_id AND n.sender_uid = :sender_uid";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	static function findSentFromSender($notification_id, $sender_uid) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.notification_id = :notification_id AND n.sender_uid = :sender_uid AND n.is_sent = 1";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
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
	static function findAllToUser($uid, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.uid = :uid";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
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
	static function findAllSentToUser($uid, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.uid = :uid AND n.is_sent = 1";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
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
	static function findAllFromSender($sender_uid, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.sender_uid = :sender_uid";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSentFromSender($sender_uid, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.sender_uid = :sender_uid AND n.is_sent = 1";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllInRefId($ref, $ref_id, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.ref = :ref AND n.ref_id = :ref_id";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllSentInRefId($ref, $ref_id, $options=array()) {
		$sql = "SELECT n.*, s.g_name AS sender_name, s.g_picture AS sender_picture"
			. " FROM notifications n INNER JOIN users s ON n.sender_uid = s.uid"
			. " WHERE n.ref = :ref AND n.ref_id = :ref_id AND n.is_sent = 1";
		if (isset($options['orderby'])) {
			$prefix = 'n.';
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist(), $prefix);
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function countAllToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countSentToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid AND is_sent = 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countNewToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid AND is_new = 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countNewSentToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid AND is_new = 1 AND is_sent = 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countUnreadToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid AND is_unread = 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	static function countUnreadSentToUser($uid) {
		$stmt = DbLink::prepare("SELECT COUNT(*) FROM notifications WHERE uid = :uid AND is_unread = 1 AND is_sent = 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	static function insert($notification_date, $uid, $sender_uid, $ref, $ref_id, $priority, $is_new, $is_unread, $is_sent) {
		$sql = "INSERT INTO notifications (notification_date, uid, sender_uid, ref, ref_id, priority, is_new, is_unread, is_sent)"
			. " VALUES (:notification_date, :uid, :sender_uid, :ref, :ref_id, :priority, :is_new, :is_unread, :is_sent)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_date', $notification_date, PDO::PARAM_STR);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->bindParam(':priority', $priority, PDO::PARAM_INT);
		$stmt->bindParam(':is_new', $is_new, PDO::PARAM_BOOL);
		$stmt->bindParam(':is_unread', $is_unread, PDO::PARAM_BOOL);
		$stmt->bindParam(':is_sent', $is_sent, PDO::PARAM_BOOL);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	
	static function updateDate($notification_id, $notification_date) {
		$sql = "UPDATE notifications"
			. " SET notification_date = :notification_date"
			. " WHERE notification_id = :notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':notification_date', $notification_date, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	static function updateIsUnread($notification_id, $is_unread) {
		$sql = "UPDATE notifications"
			. " SET is_unread = :is_unread"
			. " WHERE notification_id = :notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_unread', $is_unread, PDO::PARAM_BOOL);
		$stmt->execute();
	}

	static function updateIsSent($notification_id, $is_sent) {
		$sql = "UPDATE notifications"
			. " SET is_sent = :is_sent"
			. " WHERE notification_id = :notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->bindParam(':is_sent', $is_sent, PDO::PARAM_BOOL);
		$stmt->execute();
	}
	
	static function delete($notification_id) {
		$stmt = DbLink::prepare("DELETE FROM notifications WHERE notification_id = :notification_id");
		$stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function deleteAllInRefId($ref, $ref_id) {
		$stmt = DbLink::prepare("DELETE FROM notifications WHERE ref = :ref AND ref_id = :ref_id");
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllToUser($uid) {
		$stmt = DbLink::prepare("DELETE FROM notifications WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllFromSender($sender_uid) {
		$stmt = DbLink::prepare("DELETE FROM notifications WHERE sender_uid = :sender_uid");
		$stmt->bindParam(':sender_uid', $sender_uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	static function deleteAllSentToUserExceptRefUpToNotification($uid, $keep_ref, $last_notification_id) {
		$stmt = DbLink::prepare("DELETE FROM notifications WHERE uid = :uid AND is_sent = 1 AND ref != :keep_ref AND notification_id <= :last_notification_id");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':keep_ref', $keep_ref, PDO::PARAM_STR);
		$stmt->bindParam(':last_notification_id', $last_notification_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function markUnsentInRefIdAsSent($ref, $ref_id) {
		$sql = "UPDATE notifications"
			. " SET is_sent = 1"
			. " WHERE ref = :ref AND ref_id = :ref_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
		$stmt->bindParam(':ref_id', $ref_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function markNewSentToUserAsNotNewUpToNotification($uid, $last_notification_id) {
		$sql = "UPDATE notifications"
			. " SET is_new = 0"
			. " WHERE uid = :uid AND is_new = 1 AND is_sent = 1 AND notification_id <= :last_notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':last_notification_id', $last_notification_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	static function markUnreadSentToUserAsReadUpToNotification($uid, $last_notification_id) {
		$sql = "UPDATE notifications"
			. " SET is_unread = 0"
			. " WHERE uid = :uid AND is_unread = 1 AND is_sent = 1 AND notification_id <= :last_notification_id";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':last_notification_id', $last_notification_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}
