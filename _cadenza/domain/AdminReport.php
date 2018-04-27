<?php
class AdminReport {
	
	var $admin_report_id;
	var $reporter_uid;
	var $reporter_email;
	var $reporter_name;
	var $reporter_user_status;
	var $reported_uid;
	var $reported_email;
	var $reported_name;
	var $reported_user_status;
	var $report_type;
	var $report_text;
	var $report_date;
	var $report_date_local;
	var $is_resolved;
	
	function __construct($row) {
		$this->admin_report_id = $row['admin_report_id'];
		$this->reporter_uid = $row['reporter_uid'];
		$this->reporter_email = $row['reporter_email'];
		$this->reporter_name = $row['reporter_name'];
		$this->reporter_user_status = $row['reporter_user_status'];
		$this->reported_uid = $row['reported_uid'];
		$this->reported_email = $row['reported_email'];
		$this->reported_name = $row['reported_name'];
		$this->reported_user_status = $row['reported_user_status'];
		$this->report_type = $row['report_type'];
		$this->report_text = $row['report_text'];
		$this->report_date = $row['report_date'];
		$this->report_date_local = Core::utcToLocal($row['report_date']);
		$this->is_resolved = $row['is_resolved'];
	}
	
}
