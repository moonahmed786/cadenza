<?php
class Login {
	
	private static $currentAdmin = null;
	private static $currentAdminInited = false;
	
	private static $currentUser = null;
	private static $currentUserInited = false;
	
	static function getCurrentAdmin($refreshInfo=false) {
		if (!static::$currentAdminInited || $refreshInfo) {
			$row = AdminGateway::find(Session::adminId());
			if ($row && static::isLoggedInAsAdmin()) {
				static::$currentAdmin = new Admin($row);
			}
			else {
				static::$currentAdmin = null;
			}
			static::$currentAdminInited = true;
		}
		return static::$currentAdmin;
	}
	
	static function getCurrentUser($refreshInfo=false) {
		if (!static::$currentUserInited || $refreshInfo) {
			$row = UserGateway::find(Session::uid());
			if ($row && static::isLoggedInWithGoogle()) {
				static::$currentUser = new User($row);
			}elseif($row && static::isLoggedInUser()){ #elseif added by troonabdul
				static::$currentUser = new User($row);
			}else {
				static::$currentUser = null;
			}
			static::$currentUserInited = true;
		}
		return static::$currentUser;
	}
	
	static function isUserDeleted() {
		return (Session::is('access_token') && Session::is('uid') && UserGateway::isDeleted(Session::uid()));
	}
	
	static function isUserBlocked() {
		return (Session::is('access_token') && Session::is('uid') && UserGateway::isBlocked(Session::uid()));
	}
	
	static function isLoggedIn() {
		return (static::isLoggedInWithGoogle() || static::isLoggedInAsAdmin());
	}
	
	static function isLoggedInWithGoogle() {
		return (Session::is('access_token') && Session::is('uid') && UserGateway::isActive(Session::uid()));
	}
	
	static function isLoggedInAsStudent() {
		return (Session::is('access_token') && Session::is('uid') && Session::userType() == 'student' && UserGateway::isActive(Session::uid()));
	}
	
	static function isLoggedInAsTeacher() {
		return (Session::is('access_token') && Session::is('uid') && Session::userType() == 'teacher' && UserGateway::isActive(Session::uid()));
	}
	
	static function isLoggedInAsAdmin() {
		return Session::is('admin_id');
	}
	
	static function processLoginGoogle(Google_Client_Cadenza $client) {
		$prompt = "";
		if (Session::is('select_account')) {
			if ($prompt != "") {
				$prompt .= ' ';
			}
			$prompt .= 'select_account'; // allow user to select a google account
		}
		if (Session::is('reprompt')) {
			if ($prompt != "") {
				$prompt .= ' ';
			}
			$prompt .= 'consent'; // ask user for consent (google permissions)
		}
		
		if ($prompt != "") {
			$client->setPrompt($prompt);
		}
		if (!isset($_GET['code'])) {
			$auth_url = $client->createAuthUrl();
			header('Location: '.filter_var($auth_url, FILTER_SANITIZE_URL));
			exit;
		}
		else {
			$client->authenticate($_GET['code']);
			Session::set('access_token', $client->getAccessToken());
			
			$oauth2_service = new Google_Service_Oauth2($client);
			$userinfo = $oauth2_service->userinfo->get();
			
			$email_normalized = UserGateway::normalizeEmail($userinfo->email);
			$user_row = UserGateway::findByEmailNormalized($email_normalized);
			
			if ($user_row) {
				// Update existing user
				if (Session::is('reprompt')) {
					$g_refresh_token = $client->getRefreshToken();
					UserGateway::updateGoogleRefreshToken($user_row['uid'], $g_refresh_token);
				}
				UserGateway::updateGoogleUserInfo($user_row['uid'], $userinfo);
				$user_row = UserGateway::find($user_row['uid']); // get updated info
			}
			else {
				// Insert new user
				$g_email = $userinfo->email;
				$g_name = $userinfo->name;
				$g_given_name = $userinfo->givenName;
				$g_family_name = $userinfo->familyName;
				$g_picture = $userinfo->picture;
				$g_refresh_token = $client->getRefreshToken();
				if (!Session::is('reprompt') && $g_refresh_token == null) {
					// no refresh token, so we'll need to get a new one
					Session::close();
					Session::init();
					Session::set('reprompt', true);
					$redirect_uri = Core::cadenzaUrl('pages/index.php');
					header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
					exit;
				}
				UserGateway::insert($email_normalized, $g_email, $g_name, $g_given_name, $g_family_name, $g_picture, $g_refresh_token, null, null, 'active', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
				$user_row = UserGateway::findByEmailNormalized($email_normalized); // get inserted info
			}
			
			if ($user_row['status'] == 'active') {
				if (!Session::is('reprompt') && $user_row['g_refresh_token'] == null) {
					// no refresh token, so we'll need to get a new one
					Session::close();
					Session::init();
					Session::set('reprompt', true);
					$redirect_uri = Core::cadenzaUrl('pages/index.php');
					header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
					exit;
				}
				UserGateway::updateLastLogin($user_row['uid'], date('Y-m-d H:i:s'));
			}
			Session::remove('select_account');
			Session::remove('reprompt');
			Session::set('uid', $user_row['uid']);
			Session::set('user_type', $user_row['user_type']);
			
			// The index page will determine where to take the user next
			$redirect_uri = Core::cadenzaUrl('pages/index.php');
			header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
			exit;
		}
	}

	static function processLoginAdmin($username, $password) {
		$admin_row = AdminGateway::findByUsername($username);
		$result = 'success'; // ajax result status
		$destination = null; // redirect destination
		$loginsuccess = false; // whether or not the login was successful
		if ($admin_row) {
			$password_hash = $admin_row['password_hash'];
			/* Use password_verify to check if the hash matches the given password.
			 * NOTE: This function is PHP 5 >= 5.5.0; for more details, see
			 * http://php.net/manual/en/function.password-verify.php
			 */
			if (password_verify($password, $password_hash)) {
				Session::set('admin_id', $admin_row['admin_id']);
				$redirect_uri = Core::cadenzaUrl('pages/admin/index.php');
				$result = 'redirect';
				$destination = filter_var($redirect_uri, FILTER_SANITIZE_URL);
				$loginsuccess = true;
			}
		}
		// Generate an AJAX response
		$response = array('result'=>$result, 'destination'=>$destination, 'loginsuccess'=>$loginsuccess);
		print json_encode($response);
	}
	
}
