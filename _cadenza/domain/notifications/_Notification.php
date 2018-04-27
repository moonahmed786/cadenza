<?php
abstract class Notification {
	
	var $notification_id;
	var $notification_date;
	var $uid;
	var $sender_uid;
	var $sender_name;
	var $sender_picture;
	var $ref;
	var $ref_id;
	var $time_ago;
	var $is_new;
	var $is_unread;
	
	function __construct($row) {
		$this->notification_id = $row['notification_id'];
		$this->notification_date = $row['notification_date'];
		$this->uid = $row['uid'];
		$this->sender_uid = $row['sender_uid'];
		$this->sender_name = $row['sender_name'];
		$this->sender_picture = $row['sender_picture'];
		$this->ref = $row['ref'];
		$this->ref_id = $row['ref_id'];
		
		$interval = date_diff(new DateTime(), new DateTime($row['notification_date']));
		$totalDays = $interval->format('%a');
		if ($totalDays == 0) {
			$totalHours = $interval->format('%h'); // number of hours is total because interval is less than 1 day
			if ($totalHours == 0) {
				$this->time_ago = Language::getText('datetime', 'less_than_hour_ago');
			}
			else {
				$this->time_ago = $totalHours.' '.($totalHours == 1 ? Language::getText('datetime', 'hour_ago') : Language::getText('datetime', 'hours_ago'));
			}
		}
		else {
			$this->time_ago = $totalDays.' '.($totalDays == 1 ? Language::getText('datetime', 'day_ago') : Language::getText('datetime', 'days_ago'));
		}
		
		$this->is_new = $row['is_new'];
		$this->is_unread = $row['is_unread'];
	}
	
	abstract function isGoLocationAccessibleByUser(User $user);
	
}
