<?php
/**
 * Base TDG class to hold common functionality.
 * 
 * TDG = Table Data Gateway, a software design pattern used to separate the database queries
 * from the application logic.
 */
abstract class Tdg {
	
	/**
	 * Return a sanitized version of the given orderby string or array.
	 * 
	 * This function delegates to sanitizeOrderByArray or sanitizeOrderByString,
	 * depending on the type of the given orderby parameter.
	 */
	protected static function sanitizeOrderBy($orderby, $whitelist, $prefix='', $aliases=array()) {
		if (is_array($orderby)) {
			return static::sanitizeOrderByArray($orderby, $whitelist, $prefix, $aliases);
		}
		return static::sanitizeOrderByString($orderby, $whitelist, $prefix, $aliases);
	}
	
	/**
	 * Return a sanitized version of each element in the given orderby array, separated by commas.
	 * 
	 * This function calls sanitizeOrderByString on each element in the given orderby array,
	 * and returns the result separated by commas.
	 * @param array $orderby_arr The orderby array to be sanitized. Must not be empty.
	 * @param array $whitelist List of valid fields. Must not be empty.
	 * @param string $prefix Optional prefix for field name.
	 * @param array $aliases Optional list of alias fields. Aliases ignore the prefix.
	 * @return string The resulting sanitized string.
	 */
	protected static function sanitizeOrderByArray($orderby_arr, $whitelist, $prefix='', $aliases=array()) {
		if (!is_array($orderby_arr) || empty($orderby_arr)) {
			trigger_error('Invalid parameters: orderby_arr must be a non-empty array.', E_USER_ERROR);
		}
		if (!is_array($whitelist) || empty($whitelist)) {
			trigger_error('Invalid parameters: whitelist must be a non-empty array.', E_USER_ERROR);
		}
		$orderby = "";
		$first = true;
		foreach ($orderby_arr as $orderby_element) {
			if (!$first) {
				$orderby .= ", ";
			}
			$orderby .= static::sanitizeOrderBy($orderby_element, $whitelist, $prefix, $aliases);
			$first = false;
		}
		return $orderby;
	}
	
	/**
	 * Return a sanitized version of the given orderby string.
	 * 
	 * The field name in the given orderby is checked against the given whitelist array.
	 * If no match is found, simply return the current element of the array; otherwise,
	 * check if orderby also has a direction. If a direction is included, it must equal
	 * either 'ASC' or 'DESC'. If the prefix parameter is specified, it will be placed
	 * in front of the field name in the returned value; for example, prefix could be
	 * a table-name shorthand followed by a dot.
	 * @param string $orderby_str The orderby string to be sanitized.
	 * @param array $whitelist List of valid fields. Must not be empty.
	 * @param string $prefix Optional prefix for field name.
	 * @param array $aliases Optional list of alias fields. Aliases ignore the prefix.
	 * @return string The resulting sanitized string.
	 */
	protected static function sanitizeOrderByString($orderby_str, $whitelist, $prefix='', $aliases=array()) {
		if (!is_array($whitelist) || empty($whitelist)) {
			trigger_error('Invalid parameters: whitelist must be a non-empty array.', E_USER_ERROR);
		}
		// remove table name if specified
		$parts = explode('.', $orderby_str, 2);
		if (count($parts) > 1) {
			$orderby_str = $parts[1];
		}
		// get field name, and direction if specified
		$parts = explode(' ', $orderby_str);
		if (isset($parts[0]) && in_array($parts[0], $whitelist)) {
			$name = $parts[0];
			$direction = (isset($parts[1]) && ($parts[1] == 'ASC' || $parts[1] == 'DESC')) ? $parts[1] : 'ASC';
			return in_array($parts[0], $aliases) ? $name.' '.$direction : $prefix.$name.' '.$direction;
		}
		return in_array($parts[0], $aliases) ? current($whitelist) : $prefix.current($whitelist);
	}
	
	/**
	 * Return a sanitized version of the given limit array as a string.
	 * 
	 * The limit parameter must be a non-empty array that has one or two elements as
	 * follows. If limit contains only one element, this element (i.e. $limit[0])
	 * corresponds to the desired fetch count. If limit contains two elements, the first
	 * element (i.e. $limit[0]) corresponds to the desired offset and the second element
	 * (i.e. $limit[1]) corresponds to the desired fetch count.
	 * @param array $limit The limit array to be sanitized.
	 * @return string The resulting sanitized string.
	 */
	protected static function sanitizeLimit($limit) {
		if (!is_array($limit) || empty($limit)) {
			trigger_error('Invalid parameters: limit must be a non-empty array.', E_USER_ERROR);
		}
		elseif (count($limit) > 2) {
			trigger_error('Invalid parameters: limit must have one or two elements.', E_USER_ERROR);
		}
		
		$offset = null;
		$fetch_count = null;
		
		if (count($limit) == 1) {
			$fetch_count = $limit[0];
		}
		elseif (count($limit) == 2) {
			$offset = $limit[0];
			$fetch_count = $limit[1];
		}
		
		if (!is_integer($fetch_count) || $fetch_count < 1) {
			trigger_error('Invalid parameters: fetch_count must be an integer greater than or equal to 1.', E_USER_ERROR);
		}
		elseif ($offset != null && (!is_integer($offset) || $offset < 0)) {
			trigger_error('Invalid parameters: offset must be an integer greater than or equal to 0.', E_USER_ERROR);
		}
		
		if ($offset != null) {
			return "$offset,$fetch_count";
		}
		return "$fetch_count";
	}
}
