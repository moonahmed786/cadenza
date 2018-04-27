<?php namespace ltk;
class iniconfig { // From ltk-svn: ^/trunk/common/php/iniconfig.php@4098
	
	static function post_max_size($convert_to_bytes=false) {
		// Conversion to bytes based on the following ini_get() example from the PHP documentation:
		// http://php.net/manual/en/function.ini-get.php#example-566
		$val = ini_get('post_max_size');
        $val = preg_replace("/[^0-9,.]/", "", $val );
		if ($convert_to_bytes) {
			$modifier = strtoupper($val[strlen($val)-1]);
			switch ($modifier) {
				case 'G':
					$val *= 1024;
				case 'M':
					$val *= 1024;
				case 'K':
					$val *= 1024;
			}
		}
		return $val;
	}
	static function upload_max_filesize($convert_to_bytes=false) {
		// Conversion to bytes based on the following ini_get() example from the PHP documentation:
		// http://php.net/manual/en/function.ini-get.php#example-566
		$val = ini_get('upload_max_filesize');
		$val = preg_replace("/[^0-9,.]/", "", $val );
		if ($convert_to_bytes) {
			$modifier = strtoupper($val[strlen($val)-1]);
			switch ($modifier) {
				case 'G':
					$val *= 1024;
				case 'M':
					$val *= 1024;
				case 'K':
					$val *= 1024;
			}
		}
		return $val;
	}
	static function session_gc_maxlifetime() {
		return ini_get('session.gc_maxlifetime');
	}
	static function magic_quotes_gpc() {
		return ini_get('magic_quotes_gpc');
	}
	static function register_globals() {
		return ini_get('register_globals');
	}
	static function output_buffering() {
		return ini_get('output_buffering');
	}
	
}
