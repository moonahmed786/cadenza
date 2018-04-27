<?php
class Language {
	
	const USE_UNICODE_ELLIPSIS_CHARACTER = false;
	
	static $strings = array();
	static $lang = null;
	static $has_init_shorthand = false;
	
	static function init($set_lang='english') {
		if (static::$lang != $set_lang) {
			static::$strings = require dirname(__FILE__).'/langs/'.$set_lang.'.php';
			static::$lang = $set_lang;
		}
	}
	
	static function getStrings() {
		return static::$strings;
	}
	
	static function getStringsInCategory($category) {
		return isset(static::$strings[$category]) ? static::$strings[$category] : null;
	}
	
	static function getText($category, $key) {
		if (isset(static::$strings[$category][$key])) {
			// return the translated text corresponding to the given key
			return static::$strings[$category][$key];
		}
		// if we are here, the given key does not exist
		return '[ ' + $category + ', ' + $key + ' ]';
	}
	
}
