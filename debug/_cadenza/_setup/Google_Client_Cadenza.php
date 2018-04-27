<?php
require_once dirname(__FILE__).'/../../libs/google-api-php-client-2.0.3/vendor/autoload.php';

class Google_Client_Cadenza extends Google_Client {
	
	function __construct($is_action=false) {
		parent::__construct();
		
		$this->setAuthConfigFile(Core::googleAuthConfigFile());
		
		$this->addScope(Google_Service_Drive::DRIVE);
		$this->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
		$this->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
		
		$this->setRedirectUri(Core::cadenzaUrl('pages/login.php'));
		$this->setAccessType('offline'); // for refresh token
		
		if (Login::isLoggedIn()) {
			$this->setAccessToken(Session::get('access_token'));
			if ($this->isAccessTokenExpired()) {
				$user_row = UserGateway::find(Session::uid());
				try {
					$this->refreshToken($user_row['g_refresh_token']);
					Session::set('access_token', $this->getAccessToken());
				}
				catch (Google_Auth_Exception $e) {
					// refreshToken didn't work, so we'll need to get a new refresh token
					Session::close();
					Session::init();
					Session::set('reprompt', true);
					$redirect_uri = Core::cadenzaUrl('pages/index.php');
					if ($is_action) {
						// Generate an AJAX response
						$response = array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
						print json_encode($response);
						exit;
					}
					else {
						header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
						exit;
					}
				}
			}
		}
	}
	
}
