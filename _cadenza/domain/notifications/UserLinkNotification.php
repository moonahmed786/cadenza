<?php
class UserLinkNotification extends Notification {
	
	var $user_link_id;
	var $is_user_link_inactive;
	
	function __construct($row) {
		if ($row['ref'] != 'user_link') {
			trigger_error('Invalid ref: '.$row['ref'], E_USER_ERROR);
		}
		parent::__construct($row);
		
		$this->user_link_id = $this->ref_id;
		
		$user_link_row = UserLinkGateway::find($this->user_link_id);
		$status = $user_link_row['status'];
		$this->is_user_link_inactive = UserLinkGateway::isInactive($this->user_link_id);
	}
	
	function isGoLocationAccessibleByUser(User $user) {
		return false; // no "go" location associated with this notification
	}
	
}
