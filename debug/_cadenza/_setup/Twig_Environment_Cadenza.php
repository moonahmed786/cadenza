<?php
require_once dirname(__FILE__).'/../../libs/Twig-1.32.0/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

class Twig_Environment_Cadenza extends Twig_Environment {
	
	var $isAddedJQuery = false;
	var $isAddedBootstrap = false;
	var $isAddedJQueryFileupload = false;
	var $isAddedAutosize = false;
	var $isAddedFontAwesome = false;
	var $isAddedVideoJS = false;
	var $isAddedTypeheadJS = false;
	var $isAddedModifiedCorsUploadSample = false;
	
	function __construct() {
		parent::__construct(
			new Twig_Loader_Filesystem(Core::twigTemplatesRootDir()),
			array(
				'cache' => Core::twigCacheRootDir(),
				'auto_reload' => true
			)
		);
		
		$this->addGlobal('APP_NAME', 'Cadenza');
		
		// Google Analytics
		$this->addGlobal('GOOGLE_ANALYTICS_ENABLED', Core::googleAnalyticsEnabled());
		
		// Google Client ID
		$this->addGlobal('GOOGLE_CLIENT_ID', Core::googleClientId());
		
		// Google Browser API Key
		$this->addGlobal('GOOGLE_BROWSER_API_KEY', Core::googleBrowserApiKey());
		
		// Fileupload Max Chunk Size
		$this->addGlobal('FILEUPLOAD_MAX_CHUNK_SIZE', Core::fileuploadMaxChunkSize());
		
		// Libs
		$this->addLibJQuery();
		$this->addLibBootstrap();
		$this->addLibJQueryFileupload();
		$this->addLibAutosize();
		$this->addLibFontAwesome();
		$this->addLibVideoJS();
		$this->addLibTypeheadJS();
		$this->addLibModifiedCorsUploadSample();
		
		// Assets
		$this->addGlobal('ASSETS_CSS_BOOTSTRAP_CADENZA', Core::cadenzaWebPath('assets/css/bootstrap-cadenza.css'));
		$this->addGlobal('ASSETS_CSS_CADENZA', Core::cadenzaWebPath('assets/css/cadenza.css'));
		$this->addGlobal('ASSETS_CSS_CADENZA_COMPONENTS', Core::cadenzaWebPath('assets/css/cadenza-components.css'));
		$this->addGlobal('ASSETS_CSS_CADENZA_COMPONENTS_ADMIN', Core::cadenzaWebPath('assets/css/cadenza-components-admin.css'));
		$this->addGlobal('ASSETS_FONTS_DIR', Core::cadenzaWebPath('assets/fonts'));
		$this->addGlobal('ASSETS_IMAGES_DIR', Core::cadenzaWebPath('assets/images'));
		
		// JS Scripts
		$this->addGlobal('CADENZA_JS', Core::cadenzaWebPath('js/_cadenza.js'));
		$this->addGlobal('CADENZA_PAGE_JS', Core::cadenzaWebPath('js/page.js'));
		$this->addGlobal('CADENZA_FILEUPLOADS_JS', Core::cadenzaWebPath('js/fileuploads.js'));
		$this->addGlobal('CADENZA_DRIVEUPLOADS_JS', Core::cadenzaWebPath('js/driveuploads.js'));
		$this->addGlobal('CADENZA_DRIVE_JS', Core::cadenzaWebPath('js/drive.js'));
		$this->addGlobal('CADENZA_AUTOCOMPLETE_JS', Core::cadenzaWebPath('js/autocomplete.js'));
		$this->addGlobal('CADENZA_ACTIONBUTTONS_JS', Core::cadenzaWebPath('js/actionbuttons.js'));
		
		// JS Dirs
		$this->addGlobal('CADENZA_ADMIN_COMPONENTS_JS_DIR', Core::cadenzaWebPath('js/admin_components'));
		$this->addGlobal('CADENZA_COMPONENTS_JS_DIR', Core::cadenzaWebPath('js/components'));
		$this->addGlobal('CADENZA_WIDGETS_JS_DIR', Core::cadenzaWebPath('js/widgets'));
		
		// URLs
		$this->addGlobal('CADENZA_URL', Core::cadenzaUrl());
		$this->addGlobal('CADENZA_URL_SELECT_ACCOUNT', Core::cadenzaUrl('pages/select_account.php'));
		$this->addGlobal('CADENZA_URL_LOGOUT', Core::cadenzaUrl('pages/logout.php'));
		$this->addGlobal('MUSICTOOLSUITE_URL_CADENZA', "http://www.musictoolsuite.ca/cadenza/");
		$this->addGlobal('MUSICTOOLSUITE_URL_CADENZA_PRIVACY_POLICY', "http://www.musictoolsuite.ca/cadenza-privacy-policy/");
		$this->addGlobal('MUSICTOOLSUITE_URL_NOTEMAKER', "http://www.musictoolsuite.ca/notemaker/");
		
		// Emails
		$this->addGlobal('EMAIL_CONTACT', Core::emailAddressContact());
		
		// Text
		$this->addGlobal('TEXT', Language::getStrings());
		
		// Icons
		$this->addGlobal('ICONS', UI::getIcons());
	}

	function addLibJQuery() {
		if (!$this->isAddedJQuery) {
			// js
			$this->addGlobal('JQUERY_JS', Core::cadenzaWebpath('libs/jquery/jquery-1.12.4.min.js'));
			// set added flag to true
			$this->isAddedJQuery = true;
		}
	}

	function addLibBootstrap() {
		if (!$this->isAddedBootstrap) {
			// dependencies
			$this->addLibJQuery();
			// css
			$this->addGlobal('BOOTSTRAP_CSS', Core::cadenzaWebpath('libs/bootstrap-3.3.7-custom/css/bootstrap.min.css'));
			// js
			$this->addGlobal('BOOTSTRAP_JS', Core::cadenzaWebpath('libs/bootstrap-3.3.7-custom/js/bootstrap.min.js'));
			// set added flag to true
			$this->isAddedBootstrap = true;
		}
	}

	function addLibJQueryFileupload() {
		if (!$this->isAddedJQueryFileupload) {
			// dependencies
			$this->addLibJQuery();
			$this->addLibBootstrap();
			// css
			$this->addGlobal('JQUERY_FILEUPLOAD_CSS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/css/jquery.fileupload.css'));
			// js
			$this->addGlobal('JQUERY_UI_WIDGET_JS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/js/vendor/jquery.ui.widget.js'));
			$this->addGlobal('JQUERY_IFRAME_TRANSPORT_JS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/js/jquery.iframe-transport.js'));
			$this->addGlobal('JQUERY_FILEUPLOAD_JS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/js/jquery.fileupload.js'));
			$this->addGlobal('JQUERY_FILEUPLOAD_PROCESS_JS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/js/jquery.fileupload-process.js'));
			$this->addGlobal('JQUERY_FILEUPLOAD_VALIDATION_JS', Core::cadenzaWebpath('libs/jQuery-File-Upload-9.17.0/js/jquery.fileupload-validate.js'));
			// set added flag to true
			$this->isAddedJQueryFileupload = true;
		}
	}
	function addLibAutosize() {
		if (!$this->isAddedAutosize) {
			// dependencies
			$this->addLibJQuery();
			// js
			$this->addGlobal('AUTOSIZE_JS', Core::cadenzaWebpath('libs/autosize-3.0.20/dist/autosize.min.js'));
			// set added flag to true
			$this->isAddedAutosize = true;
		}
	}

	function addLibFontAwesome() {
		if (!$this->isAddedFontAwesome) {
			// css
			$this->addGlobal('FONT_AWESOME_CSS', Core::cadenzaWebPath('libs/font-awesome-4.7.0/css/font-awesome.min.css'));
			// set added flag to true
			$this->isAddedFontAwesome = true;
		}
	}

	function addLibVideoJS() {
		if (!$this->isAddedVideoJS) {
			// css
			$this->addGlobal('VIDEOJS_CSS', Core::cadenzaWebPath('libs/video-js-5.11.6/video-js.min.css'));
			// js
			$this->addGlobal('VIDEOJS_JS', Core::cadenzaWebpath('libs/video-js-5.11.6/video.js'));
			// swf
			$this->addGlobal('VIDEOJS_SWF', Core::cadenzaWebpath('libs/video-js-5.11.6/video-js.swf'));
			// set added flag to true
			$this->isAddedVideoJS = true;
		}
	}
	
	function addLibTypeheadJS() {
		if (!$this->isAddedTypeheadJS) {
			// js
			$this->addGlobal('TYPEHEADJS_JS', Core::cadenzaWebpath('libs/typeahead.js-0.11.1/dist/typeahead.bundle.min.js'));
			// set added flag to true
			$this->isAddedTypeheadJS = true;
		}
	}
	
	function addLibModifiedCorsUploadSample() {
		if (!$this->isAddedModifiedCorsUploadSample) {
			// js
			$this->addGlobal('MODIFIED_CORS_UPLOAD_SAMPLE_JS', Core::cadenzaWebPath('libs/_modified/cors-upload-sample/upload.js'));
			// set added flag to true
			$this->isAddedModifiedCorsUploadSample = true;
		}
	}
	
}
