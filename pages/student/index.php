<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsStudent()) {
	Redirect::set('student/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$uid = Session::uid();
$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
$link_statuses_inactive = UserLinkGateway::getStatusArrayInactive();
$teachers_user_statuses = array('active', 'deleted');
$count_connected_teachers = UserGateway::countTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_connected);
$count_inactive_teachers = UserGateway::countTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_inactive);
$count_all_teachers = $count_connected_teachers + $count_inactive_teachers;
$skipindex = ($count_all_teachers > 0);
if ($skipindex) {
	Redirect::set('student/teachers');
	Redirect::go();
}
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadStudentNavbarData($user, $count_connected_teachers, $count_inactive_teachers);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data
);
print $twig->render('pages/student/index.html.twig', $context);