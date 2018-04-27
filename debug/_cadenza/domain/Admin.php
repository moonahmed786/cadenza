<?php
class Admin {
	
	var $admin_id;
	var $username;
	
	function __construct($row) {
		$this->admin_id = $row['admin_id'];
		$this->username = $row['username'];
	}
	
}
