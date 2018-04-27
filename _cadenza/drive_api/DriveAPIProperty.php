<?php
class DriveAPIProperty {
	
	static function getAppProperties(Google_Service_Drive_Cadenza $service, $fileId) {
		try {
			$file = $service->files->get($fileId, array('fields'=>'appProperties'));
			return $file->appProperties;
		}
		catch (Exception $e) {
			return $e;
		}
	}
	
	static function setAppProperties(Google_Service_Drive_Cadenza $service, $fileId, $appProperties=array()) {
		try {
			$file = $service->files->get($fileId, array('fields'=>'appProperties'));
			$file->setAppProperties($appProperties);
			$updatedFile = $service->files->update(
	            $fileId,
	            $file
	        );
			return $updatedFile;
		}
		catch (Exception $e) {
			return $e;
		}
	}
	
}