<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();




$client = new Google_Client_Cadenza();

$oauth2_service = new Google_Service_Oauth2($client);
$userinfo = $oauth2_service->userinfo->get();
$email_normalized = UserGateway::normalizeEmail($userinfo->email);

$user_row = UserGateway::findByEmailNormalized($email_normalized);
$user = new User($user_row);
if ($user->user_type !== null) {
	Redirect::set('index');
	Redirect::go();
}

if (isset($_REQUEST['user_type']) && ($_REQUEST['user_type'] == 'student' || $_REQUEST['user_type'] == 'teacher')) {
	$user_type = $_REQUEST['user_type'];
	UserGateway::updateUserType($user->uid, $user_type);
	Session::set('user_type', $user_type);
	Redirect::set('index');
	Redirect::go();
}
else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
			// 'form_url' => Core::cadenzaUrl('pages/signup/index.php'),
			'data' => array(
				'name' => $user->name,
			    'email' => $user->email,
			    'username' => strtolower($user->first_name),
			    'password' => '',
			    'password_confirm' => '',
			    'user_type' => ''
			)
	);
	print $twig->render('pages/signup/signup.html.twig',$context);
}