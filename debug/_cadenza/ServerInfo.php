<?php
require_once dirname(__FILE__).'/../ltk/iniconfig.php';

class ServerInfo {
	
	static function postMaxSizeBytes() {
		return \ltk\iniconfig::post_max_size(true);
	}
	
	static function uploadMaxFilesizeBytes() {
		return \ltk\iniconfig::upload_max_filesize(true);
	}
		
}
