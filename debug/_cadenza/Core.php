<?php
class Core {
	
	static function init() {
		// set default timezone
		date_default_timezone_set('UTC');
		
		// get Cadenza root directory
		$cadenzaRootDir = static::cadenzaRootDir();
		
		// include setup classes
		require_once $cadenzaRootDir.'/_cadenza/_setup/Fileupload_UploadHandler_Cadenza.php';
		require_once $cadenzaRootDir.'/_cadenza/_setup/Google_Client_Cadenza.php';
		require_once $cadenzaRootDir.'/_cadenza/_setup/Google_Service_Drive_Cadenza.php';
		require_once $cadenzaRootDir.'/_cadenza/_setup/Twig_Environment_Cadenza.php';
		
		// include database classes
		require_once $cadenzaRootDir.'/_cadenza/database/_DbLink.php';
		require_once $cadenzaRootDir.'/_cadenza/database/_Tdg.php';
		require_once $cadenzaRootDir.'/_cadenza/database/AdminGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/AdminReportGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/AutocompleteGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/ChecklistItemGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/CommentGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/LessonGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/LessonReflectionGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/NotificationGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/PracticeFieldGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/PracticeGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/StudentGoalGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/StudentRewardGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/TaskGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/TeacherNotesGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/UserFileGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/UserGateway.php';
		require_once $cadenzaRootDir.'/_cadenza/database/UserLinkGateway.php';
		
		// include other technical services
		require_once $cadenzaRootDir.'/_cadenza/language/Language.php';
		require_once $cadenzaRootDir.'/_cadenza/ErrorHandler.php';
		require_once $cadenzaRootDir.'/_cadenza/Login.php';
		require_once $cadenzaRootDir.'/_cadenza/Redirect.php';
		require_once $cadenzaRootDir.'/_cadenza/Session.php';
		require_once $cadenzaRootDir.'/_cadenza/UI.php';
		require_once $cadenzaRootDir.'/_cadenza/AdminComponents.php';
		require_once $cadenzaRootDir.'/_cadenza/Components.php';
		require_once $cadenzaRootDir.'/_cadenza/Pagination.php';
		require_once $cadenzaRootDir.'/_cadenza/ServerInfo.php';
		require_once $cadenzaRootDir.'/_cadenza/Sortable.php';
		require_once $cadenzaRootDir.'/_cadenza/drive_api/DriveAPIFile.php';
		require_once $cadenzaRootDir.'/_cadenza/drive_api/DriveAPIPermission.php';
		require_once $cadenzaRootDir.'/_cadenza/drive_api/DriveAPIProperty.php';
		
		// include domain classes
		require_once $cadenzaRootDir.'/_cadenza/domain/Admin.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/AdminReport.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/Attachment.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/Comment.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/Lesson.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/Practice.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/StudentGoal.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/Task.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/User.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/_Notification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/_NotificationFactory.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/AnnotationNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/LessonCommentNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/PracticeCommentNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/PracticeNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/UserBlockedNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/UserLinkNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/UserUnblockedNotification.php';
		require_once $cadenzaRootDir.'/_cadenza/domain/notifications/UserDeletedNotification.php';
		
		// include actions
		require_once $cadenzaRootDir.'/actions/ActionHandler.php';
		require_once $cadenzaRootDir.'/actions/ActionHelper.php';
		require_once $cadenzaRootDir.'/actions/AdminActions.php';
		require_once $cadenzaRootDir.'/actions/UserActions.php';
		require_once $cadenzaRootDir.'/actions/StudentActions.php';
		require_once $cadenzaRootDir.'/actions/TeacherActions.php';
		
		// initialize language
		Language::init('english');
	
		// initialize error handler
		ErrorHandler::init();
	
		// initialize session
		Session::init();
		
		// if maintenance mode and not an action
		if (Core::isEnvironment('maintenance') && !(isset($_REQUEST['action']) && $_REQUEST['action'])) {
			$redirect_uri = Core::cadenzaUrl('index.php');
			header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
			exit;
		}
	}
	
	static function cadenzaRootDir() {
		return realpath(dirname(__FILE__).'/..');
	}
	static function cadenzaRealPath($filepath='') {
		$filepath = trim($filepath, '/');
		$cadenzaRootDir = static::cadenzaRootDir();
		$realpath = ($filepath == '') ? $cadenzaRootDir : realpath($cadenzaRootDir.'/'.$filepath);
		return $realpath;
	}
	static function cadenzaWebPath($filepath='') {
		$realpath = static::cadenzaRealPath($filepath);
		$webpath = substr($realpath, strlen($_SERVER['DOCUMENT_ROOT']));
		return $webpath;
	}
	static function cadenzaUrl($filepath='') {
		$webpath = static::cadenzaWebPath($filepath);
		$url = 'http://'.$_SERVER['HTTP_HOST'].$webpath;
		return $url;
	}
	static function cadenzaConfig() {
		static $config;
		if (!isset($config)) {
			$config = require static::cadenzaRootDir().'/config.dev.php';
		}
		return $config;
	}
	
	static function emailAddressContact() {
		return 'info@musictoolsuite.ca';
	}
	
	static function isEnvironment($environment) {
		$config = static::cadenzaConfig();
		return ($config['environment'] == $environment);
	}
	
	static function filestoreRootDir() {
		$config = static::cadenzaConfig();
		return realpath($config['filestore_path']);
	}
	
	static function fileuploadMaxChunkSize() {
		// for attachment uploads
		$upload_max_filesize_bytes = ServerInfo::uploadMaxFilesizeBytes();
		$post_max_size_bytes = ServerInfo::postMaxSizeBytes();
		return min($upload_max_filesize_bytes, $post_max_size_bytes);
	}
	
	static function fileuploadReadfileChunkSize() {
		// for attachment downloads
		return 10 * 1024 * 1024; // 10 MiB
	}
	
	static function twigTemplatesRootDir() {
		$config = static::cadenzaConfig();
		return realpath($config['twig_templates_path']);
	}
	static function twigCacheRootDir() {
		$config = static::cadenzaConfig();
		return realpath($config['twig_cache_path']);
	}
	
	static function googleAuthConfigFile() {
		$config = static::cadenzaConfig();
		return realpath($config['google_auth_json']);
	}
	static function googleAuthConfig() {
		static $googleAuthConfig;
		if (!isset($googleAuthConfig)) {
			$googleAuthConfig = file_get_contents(static::googleAuthConfigFile());
		}
		return $googleAuthConfig;
	}
	static function googleClientId() {
		$googleAuthConfig = static::googleAuthConfig();
		return json_decode($googleAuthConfig)->web->client_id;
	}
	static function googleBrowserApiKey() {
		$config = static::cadenzaConfig();
		return $config['google_browser_api_key'];
	}
	static function googleCadenzaFolderPropertyKey() {
		$config = static::cadenzaConfig();
        $defaultKey = 'cadenzaFolderProperty';
		return isset($config['google_cadenza_folder_property_key']) ? 
            $config['google_cadenza_folder_property_key'] : $defaultKey;
	}
	static function googleAnalyticsEnabled() {
		$config = static::cadenzaConfig();
		return ($config['google_analytics_enabled'] === true);
	}
	
	static function timezoneLocal() {
		$config = static::cadenzaConfig();
		return $config['timezone_local'];
	}
	
	static function utcToLocal($date) {
		// get the local timezone
		$timezoneLocal = Core::timezoneLocal();
		// if the given date is null or the local timezone is empty, just return the given date as-is
		if ($date == null || empty($timezoneLocal)) {
			return $date;
		}
		// convert the given date from UTC to the local timezone
		$obj = new DateTime($date, new DateTimeZone('UTC'));
		$obj->setTimezone(new DateTimeZone($timezoneLocal));
		return $obj->format('Y-m-d H:i:s');
	}
	
}