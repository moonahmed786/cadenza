<?php
class Redirect {
	
	static function go() {
		$url = Session::getRedirectUrl();
		if ($url != null) {
			header('Location: '.filter_var($url, FILTER_SANITIZE_URL));
			exit;
		}
	}
	
	static function done() {
		Session::clrRedirect();
	}
	
	static function set($page, $params=array(), $data=array()) {
		Session::setRedirect(static::generateUrl($page, $params), $data);
	}
	
	static function setPage($page, $params=array()) {
		Session::setRedirectUrl(static::generateUrl($page, $params));
	}
	
	static function getData() {
		return Session::getRedirectData();
	}
	static function setData($data=array()) {
		Session::setRedirectData($data);
	}
	static function getDataVal($dataKey) {
		return Session::getRedirectDataVal($dataKey);
	}
	static function setDataVal($dataKey, $dataVal) {
		Session::setRedirectDataVal($dataKey, $dataVal);
	}
	
	static function generateUrl($page, $params=array()) {
		$params_str = "";
		if (count($params) > 0) {
			$params_str = '?';
			$first = true;
			foreach ($params as $key => $val) {
				if (!$first) {
					$params_str .= '&';
				}
				$params_str .= $key.'='.$val;
				$first = false;
			}
		}
		return Core::cadenzaUrl('pages/'.$page.'.php').$params_str;
	}
	
}
