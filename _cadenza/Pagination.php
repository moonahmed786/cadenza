<?php
class Pagination {
	
	const RECORDS_PER_PAGE_LONG = 9;
	const RECORDS_PER_PAGE_REG = 8;
	const RECORDS_PER_PAGE_SHORT = 5;
	
	var $action;
	var $total_record_count;
	var $records_per_page;
	
	var $total_pages;
	var $current_page;
	var $db_start_index;
	
	// page links
	var $first_with_ellipses;
	var $last_with_ellipses;
	var $start_page_number;
	var $end_page_number;
	
	function __construct($action, $total_record_count, $page=null, $records_per_page=Pagination::RECORDS_PER_PAGE_REG) {
		$this->action = $action;
		$this->total_record_count = intval($total_record_count);
		if (!is_int($records_per_page) || $records_per_page < 1) {
			trigger_error('Invalid parameters: records_per_page must be an integer greater than or equal to 1.', E_USER_ERROR);
		}
		$this->records_per_page = $records_per_page;
		
		if ($this->total_record_count == 0) {
			$this->total_pages = 1;
		}
		else {
			$this->total_pages = intval(ceil($this->total_record_count / $this->records_per_page));
		}
		
		if ($page != null) {
			$page = intval($page);
			if ($page <= 0) {
				$page = 1;
			}
		}
		else {
			$page = 1;
		}
		$this->current_page = $page;
		
		if ($this->current_page > $this->total_pages) {
			$this->current_page = $this->total_pages;
		}
		
		$this->db_start_index = ($this->current_page - 1) * $this->records_per_page;
		
		$this->first_with_ellipses = false;
		$this->last_with_ellipses = false;
		$this->start_page_number = 1;
		$this->end_page_number = 1;

		// if first or last are out of range, then - 2 for each
		$total_page_numbers_to_show = 9;
		$half = ceil($total_page_numbers_to_show / 2);
		
		// if the total page count fit within the number of pages to display
		if ($this->total_pages <= $total_page_numbers_to_show) {
			$this->end_page_number = $this->total_pages;
		}
		// else if current page is near the beginning
		elseif ($this->current_page <= $half) {
			$this->last_with_ellipses = true;
			$total_page_numbers_to_show -= 2;
			
			$this->end_page_number = $total_page_numbers_to_show;
		}
		// else if current page is near the end
		elseif ($this->current_page >= ($this->total_pages - $half + 1)) {
			$this->first_with_ellipses = true;
			$total_page_numbers_to_show -= 2;
			
			$this->start_page_number = $this->total_pages - $total_page_numbers_to_show + 1;
			$this->end_page_number = $this->total_pages;
		}
		// else it is somewhere in the middle
		else {
			$this->first_with_ellipses = true;
			$this->last_with_ellipses = true;
			$total_page_numbers_to_show -= 4;
			$half = ceil($total_page_numbers_to_show / 2);
			
			$this->start_page_number = $this->current_page - ($half - 1);
			$this->end_page_number = $this->current_page + ($half - 1);
		}
	}
	
	function get_limit_params() {
		return array($this->db_start_index, $this->records_per_page);
	}
	
}
