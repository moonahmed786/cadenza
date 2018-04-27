<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (!Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
if (Login::isLoggedInAsAdmin()) {
	Redirect::set('admin/invalid');
	Redirect::go();
}
elseif (Login::isLoggedInAsStudent()) {
	Redirect::set('student/invalid');
	Redirect::go();
}
elseif (Login::isLoggedInAsTeacher()) {
	Redirect::set('teacher/invalid');
	Redirect::go();
}
Redirect::done();

$client = new Google_Client_Cadenza();

$oauth2_service = new Google_Service_Oauth2($client);
$userinfo = $oauth2_service->userinfo->get();
$email_normalized = UserGateway::normalizeEmail($userinfo->email);

$user_row = UserGateway::findByEmailNormalized($email_normalized);
$user = new User($user_row);
$redirect_uri = Core::cadenzaUrl('pages/index.php');

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'js_alert_message' => Language::getText('error', 'invalid_navigation'),
	'is_redirect' => true,
	'destination' => filter_var($redirect_uri, FILTER_SANITIZE_URL)
);
print $twig->render('pages/signup/invalid.html.twig', $context);