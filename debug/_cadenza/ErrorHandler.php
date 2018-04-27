<?php
require_once dirname(__FILE__).'/../ltk/bettererrors.php';

class ErrorHandler {
	
	static $outputType = 'html';

	static function init() {
		$options = array();
		$options['print-errors'] = false;
		$options['htmlentities-charset'] = 'UTF-8';
		$options['error-reporting-level'] = E_ERROR | E_WARNING | E_PARSE | E_NOTICE |
			E_CORE_ERROR | E_CORE_WARNING |	E_COMPILE_ERROR | E_COMPILE_WARNING |
			E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR;
		$options['fix-default-timezone'] = false; // timezone should already be set, let's not have bettererrors touch it
		\ltk\bettererrors::init($options);
		\ltk\bettererrors::addlistener('report-html', array('ErrorHandler', 'showError'));
	}
	
	static function outputTypes() {
		return array(
			'html',
			'iframe',
			'json'
		);
	}
	
	static function setOutputType($type) {
		$types = static::outputTypes();
		if (!in_array($type, $types)) {
			trigger_error("Invalid output type '$type'.",E_USER_ERROR);
		}
		static::$outputType = $type;
	}
	
	static function showError($html) {
		$type = static::$outputType;
		switch ($type) {
			case 'html':
				print $html;
				break;
			case 'iframe':
				print '<meta charset="UTF-8" /><textarea data-type="application/json">'.json_encode(array('result'=>'error')).'</textarea>';
				break;
			case 'json':
				print json_encode(array('result'=>'error', 'html'=>$html));
				break;
			default:
				trigger_error("Invalid output type '$type'.", E_USER_ERROR);
		}
	}
	
}