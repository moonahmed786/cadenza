<?php
class DriveAPIFile {
	
	static function deleteCadenzaAnnotatorFile(Google_Service_Drive_Cadenza $service, $fileId, $can_delete_notemaker_file = false) {
		// check for latest notemakerProperty, need to fetch file appProperties
		if (!$can_delete_notemaker_file) {
			$appProperties = DriveAPIProperty::getAppProperties($service, $fileId);
			if ($appProperties['notemakerProperty'] == "true") {
				// need to set cadenzaProperty to false instead of deleting the file
				$appProperties['cadenzaProperty'] = "false";
				$setResult = DriveAPIProperty::setAppProperties($service, $fileId, $appProperties);
				if ($setResult instanceof Exception) {
					// TODO: handle error or ignore
				}
				else {
					// file updated, nothing to do
					return;
				}
			}
		}
		
		// $can_delete_notemaker_file == true OR notemakerProperty == "false"
		$deleteResult = DriveAPIFile::deleteFile($service, $fileId);
		if ($deleteResult instanceof Exception) {
			// TODO: handle error or ignore
		}
		else {
			// file deleted, nothing to do
			return;
		}
	}
	
	static function fetchFile(Google_Service_Drive_Cadenza $service, $fileId) {
		try {
			$file = $service->files->get($fileId);
			return $file;
		}
		catch (Exception $e) {
			return $e;
		}
	}
	
	static function fetchFileWithAppProperties(Google_Service_Drive_Cadenza $service, $fileId, $appProperties=array()) {
		$file = null;
		$fetchResult = DriveAPIFile::fetchFilesWithAppProperties($service, $appProperties);
		if ($fetchResult instanceof Exception) {
			// TODO: handle error or ignore
		}
		else {
			if (count($fetchResult) > 0) {
				foreach ($fetchResult as $fetchResultFile) {
					if ($fetchResultFile->id == $fileId) {
						$file = $fetchResultFile;
						break;
					}
				}
			}
		}
		return $file;
	}
	
	static function deleteFile(Google_Service_Drive_Cadenza $service, $fileId) {
		try {
			$file = $service->files->delete($fileId);
			return $file;
		}
		catch (Exception $e) {
			return $e;
		}
	}
	
	// Cadenza folder functions
	static function moveFileIntoCadenzaFolder(Google_Service_Drive_Cadenza $service, $fileId) {
		$folderId = DriveAPIFile::fetchCadenzaFolderId($service);
		if (!$folderId) {
			// create folder
			$folderCreateResult = DriveAPIFile::createCadenzaFolder($service);
			if ($folderCreateResult instanceof Exception) {
				// TODO: handle error or ignore
			}
			else {
				$folderId = $folderCreateResult->id;
			}
		}
		
		// if we got a folder id, move the file into it
		if ($folderId) {
			$insertFileResult = DriveAPIFile::moveFileIntoFolder($service, $fileId, $folderId);
			if ($insertFileResult instanceof Exception) {
				// TODO: handle error or ignore
			}
			else {
				// success
			}
		}
	}
	
	static function moveFileIntoFolder(Google_Service_Drive_Cadenza $service, $fileId, $folderId) {
	    try {
	        $emptyFileMetadata = new Google_Service_Drive_DriveFile();
			// get existing parents
			$file = $service->files->get($fileId, array('fields'=>'parents'));
			$prevParents = join(',', $file->parents);
			// move the file by adding the new parent and removing the previous parents
	        $updatedFile = $service->files->update(
	            $fileId,
	            $emptyFileMetadata,
	            array('addParents'=>$folderId, 'removeParents'=>$prevParents, 'fields'=>'id, parents')
	        );
	        return $updatedFile;
	    }
	    catch (Exception $e) {
	        return $e;
	    }
	}
	
	static function fetchFilesWithAppProperties(Google_Service_Drive_Cadenza $service, $appProperties=array()) {
		$results = array();
	    $pageToken = null;
		$q = "";
	    $first = true;
		foreach ($appProperties as $key => $value) {
			if (!$first) {
				$q .= " AND ";
			}
			$q .= "appProperties has { key = '".$key."' AND value = '".$value."' }";
			$first = false;
		}
		$parameters = ($q != "") ? array('q' => $q) : array();
	
	    do {
	        // fetch next page if available
	        if ($pageToken) {
	            $parameters['pageToken'] = $pageToken;
	        }
	
	        try {
	            $files = $service->files->listFiles($parameters);
	            $results = array_merge($results, $files->getFiles());
	
	            $pageToken = $files->getNextPageToken();
	        }
	        catch (Exception $e) {
	            return $e;
	        }
	    } while ($pageToken);
	
	    return $results;
	}
	
	static function fetchCadenzaFolderId(Google_Service_Drive_Cadenza $service) {
		$folderId = null;
		$appProperties = array(Core::googleCadenzaFolderPropertyKey() => "true");
		$folderFetchResult = DriveAPIFile::fetchFilesWithAppProperties($service, $appProperties);
		if ($folderFetchResult instanceof Exception) {
			// TODO: handle error or ignore
		}
		else {
			if (count($folderFetchResult) > 0) {
				// folder exists, use first instance
				$folderId = $folderFetchResult[0]->id;
			}
		}
		return $folderId;
	}
	
	static function createCadenzaFolder(Google_Service_Drive_Cadenza $service) {
		$name = Core::isEnvironment('development') ? Language::getText('label', 'cadenza_folder_dev') : Language::getText('label', 'cadenza_folder');
		try {
			$fileMetadata = new Google_Service_Drive_DriveFile(array(
				'name' => $name,
				'mimeType' => 'application/vnd.google-apps.folder',
				'appProperties' => array(Core::googleCadenzaFolderPropertyKey() => "true")
			));
			$createdFolder = $service->files->create($fileMetadata, array(
				'fields' => 'id'
			));
			return $createdFolder;
		}
		catch (Exception $e) {
			return $e;
		}
	}
}