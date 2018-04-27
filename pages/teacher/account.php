<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsTeacher()) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadTeacherNavbarData($user);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data
);
print $twig->render('pages/teacher/account.html.twig', $context);