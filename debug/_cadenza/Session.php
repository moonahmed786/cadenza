<?php
class Session {
	
	const SESSION_NAME = 'Cadenza'; // must be alphanumeric
	
	static $inited = false;
	
	static function init() {
		if (!static::$inited) {
			if (session_name() != Session::SESSION_NAME) {
				session_name(Session::SESSION_NAME);
			}
			if (session_status() !== PHP_SESSION_ACTIVE) {
				session_start();
			}
			static::initRedirect();
			static::$inited = true;
		}
	}
	static function initRedirect() {
		if (!isset($_SESSION['redirect'])) {
			$_SESSION['redirect'] = array();
		}
		if (!isset($_SESSION['redirect']['url'])) {
			$_SESSION['redirect']['url'] = null;
		}
		if (!isset($_SESSION['redirect']['data'])) {
			$_SESSION['redirect']['data'] = array();
		}
	}
	static function clrRedirect() {
		$_SESSION['redirect']['url'] = null;
		$_SESSION['redirect']['data'] = array();
	}
	static function setRedirect($url, $data=array()) {
		$_SESSION['redirect']['url'] = $url;
		$_SESSION['redirect']['data'] = $data;
	}
	static function getRedirectUrl() {
		return $_SESSION['redirect']['url'];
	}
	static function setRedirectUrl($url) {
		$_SESSION['redirect']['url'] = $url;
	}
	static function getRedirectData() {
		return $_SESSION['redirect']['data'];
	}
	static function setRedirectData($data=array()) {
		$_SESSION['redirect']['data'] = $data;
	}
	static function getRedirectDataVal($dataKey) {
		return isset($_SESSION['redirect']['data'][$dataKey]) ? $_SESSION['redirect']['data'][$dataKey] : null;
	}
	static function setRedirectDataVal($dataKey, $dataVal) {
		$_SESSION['redirect']['data'][$dataKey] = $dataVal;
	}
	
	static function is($key) {
		return (isset($_SESSION[$key]) && $_SESSION[$key]);
	}
	
	static function get($key) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}
	static function set($key, $val) {
		$_SESSION[$key] = $val;
	}
	
	static function uid() {
		return (isset($_SESSION['uid']) ? $_SESSION['uid'] : null);
	}
	
	static function adminId() {
		return (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null);
	}
	
	static function userType() {
		return (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null);
	}
	
	static function remove($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}
	
	static function close() {
		$_SESSION = array();
		session_write_close();
		static::$inited = false;
	}
	
}
