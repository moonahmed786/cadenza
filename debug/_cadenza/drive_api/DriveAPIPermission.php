<?php
class DriveAPIPermission {
	
	static function createPermission(Google_Service_Drive_Cadenza $service, $fileId, $emailAddress, $type, $role) {
		$newPermission = new Google_Service_Drive_Permission();
		$newPermission->setEmailAddress($emailAddress);
		$newPermission->setType($type);
		$newPermission->setRole($role);
		
		try {
			 $createResult = $service->permissions->create($fileId, $newPermission);
			 return $createResult;
		}
		catch (Exception $e) {
			return $e;
		}
	}
	
}