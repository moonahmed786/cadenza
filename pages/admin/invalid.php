<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (!Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
if (!Login::isLoggedInAsAdmin()) {
	if (Login::isLoggedInAsStudent()) {
		Redirect::set('student/invalid');
	}
	elseif (Login::isLoggedInAsTeacher()) {
		Redirect::set('teacher/invalid');
	}
	else {
		Redirect::set('signup/invalid');
	}
	Redirect::go();
}
Redirect::done();

$admin = Login::getCurrentAdmin();
$navbar_data = AdminComponents::loadAdminNavbarData($admin);
$redirect_uri = Core::cadenzaUrl('pages/index.php');

$twig = new Twig_Environment_Cadenza();
$context = array(
	'admin' => $admin,
	'navbar_data' => $navbar_data,
	'js_alert_message' => Language::getText('error', 'invalid_navigation'),
	'is_redirect' => true,
	'destination' => filter_var($redirect_uri, FILTER_SANITIZE_URL)
);
print $twig->render('pages/admin/invalid.html.twig', $context);
