<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (!Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
if (!Login::isLoggedInAsStudent()) {
	if (Login::isLoggedInAsAdmin()) {
		Redirect::set('admin/invalid');
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

$uid = Session::uid();
$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
$link_statuses_inactive = UserLinkGateway::getStatusArrayInactive();
$teachers_user_statuses = array('active', 'deleted');
$count_connected_teachers = UserGateway::countTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_connected);
$count_inactive_teachers = UserGateway::countTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_inactive);
$count_all_teachers = $count_connected_teachers + $count_inactive_teachers;

$user = Login::getCurrentUser();
$navbar_data = Components::loadStudentNavbarData($user, $count_connected_teachers, $count_inactive_teachers);
$redirect_uri = Core::cadenzaUrl('pages/index.php');

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'js_alert_message' => Language::getText('error', 'invalid_navigation'),
	'is_redirect' => true,
	'destination' => filter_var($redirect_uri, FILTER_SANITIZE_URL)
);
print $twig->render('pages/student/invalid.html.twig', $context);
