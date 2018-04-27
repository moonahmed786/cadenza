<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (!Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
if (!Login::isLoggedInAsTeacher()) {
	if (Login::isLoggedInAsAdmin()) {
		Redirect::set('admin/invalid');
	}
	elseif (Login::isLoggedInAsStudent()) {
		Redirect::set('student/invalid');
	}
	else {
		Redirect::set('signup/invalid');
	}
	Redirect::go();
}
Redirect::done();

$uid = Session::uid();
$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
$students_user_status = 'active';
$count_connected_students = UserGateway::countStudentsWithStatusLinkedToTeacher($uid, $students_user_status, $link_statuses_connected);

$user = Login::getCurrentUser();
$navbar_data = Components::loadTeacherNavbarData($user, $count_connected_students);
$redirect_uri = Core::cadenzaUrl('pages/index.php');

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'js_alert_message' => Language::getText('error', 'invalid_navigation'),
	'is_redirect' => true,
	'destination' => filter_var($redirect_uri, FILTER_SANITIZE_URL)
);
print $twig->render('pages/teacher/invalid.html.twig', $context);
