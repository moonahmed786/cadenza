<?php
class UserDeletedNotification extends Notification {
	
	function __construct($row) {
		if ($row['ref'] != 'user_deleted') {
			trigger_error('Invalid ref: '.$row['ref'], E_USER_ERROR);
		}
		parent::__construct($row);
	}
	
	function isGoLocationAccessibleByUser(User $user) {
		return false; // no "go" location associated with this notification
	}
	
}
