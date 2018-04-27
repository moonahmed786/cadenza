<?php
class Sortable {
	
	static $action;
	static $default_column;
	static $default_direction;
	static $order_by;
	static $order_direction;
	
	var $label;
	var $column;
	var $column_default_direction;
	
	var $is_selected = false;
	var $direction;
	
	// Note: Sortable::init should be called before creating Sortable objects
	function __construct($label, $column, $column_default_direction = 'ASC') {
		$this->label = $label;
		$this->column = $column;
		
		if (is_string($column_default_direction)) {
			$this->column_default_direction = static::convert_direction_string_to_bool($column_default_direction);
		}
		elseif (is_bool($column_default_direction)) {
			$this->column_default_direction = $column_default_direction;
		}
		else {
			trigger_error('Invalid parameters: column_default_direction must be a string or a boolean.', E_USER_ERROR);
		}
		
		$this->is_selected = (static::get_current_column() == $column);
		if ($this->is_selected) {
			$this->direction = static::convert_direction_string_to_bool(static::get_current_direction());
		}
	}
	
	function get_action() {
		return static::$action;
	}
	
	function get_column() {
		return $this->column;
	}
	
	function get_default_direction() {
		return static::convert_direction_bool_to_string($this->column_default_direction);
	}
	
	function get_toggled_direction() {
		$direction_string = $this->is_selected ?
			static::convert_direction_bool_to_string(!$this->direction)
			: static::convert_direction_bool_to_string($this->column_default_direction);
		return $direction_string;
	}
	
	static function init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction) {
		static::$action = $sortable_action;
		static::$default_column = $sortable_default_column;
		static::$default_direction = $sortable_default_direction;
		static::$order_by = $order_by;
		static::$order_direction = $order_direction;
	}
	
	static function get_current_column() {
		$current_column = (static::$order_by != null) ? static::$order_by : static::$default_column;
		return $current_column;
	}
	static function get_current_direction() {
		$current_direction = (static::$order_direction != null) ? static::$order_direction : static::$default_direction;
		
		if (is_string($current_direction)) {
			$current_direction = $current_direction;
		}
		elseif (is_bool($default_direction)) {
			$current_direction = static::convert_direction_bool_to_string($current_direction);
		}
		else {
			$current_direction = 'ASC';
		}
		
		return $current_direction;
	}
	
	static function get_order_by_string() {
		$current_column = static::get_current_column();
		$current_direction = static::get_current_direction();
		return $current_column.' '.$current_direction;
	}
	
	static function convert_direction_string_to_bool($direction_string) {
		switch (strtoupper($direction_string)) {
			case 'ASC': return true;
			case 'DESC': return false;
			default: return true;
		}
	}
	
	static function convert_direction_bool_to_string($direction_bool) {
		return $direction_bool ? 'ASC' : 'DESC';
	}
	
}
