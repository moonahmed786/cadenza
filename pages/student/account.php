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
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadStudentNavbarData($user);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data
);
print $twig->render('pages/student/account.html.twig', $context);