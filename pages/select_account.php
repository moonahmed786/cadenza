<?php
require_once dirname(__FILE__).'/../_cadenza/Core.php';
Core::init();

Redirect::done();
$twig = new Twig_Environment_Cadenza();
if (!Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
$currentuid = $_SESSION['uid'];
if (isset($_POST['password'])) {
	$username = isset($_POST['username']) ? $_POST['username'] : null;
	$password = isset($_POST['password']) ? $_POST['password'] : null;
	$response = Login::processLogin($username, $password,false);
	if ($response['user']['uid']) {
		UserGateway::linkAccount($currentuid,$response['user']['uid']);
		UserGateway::linkAccount($response['user']['uid'],$currentuid);
		header('Location: '.$response['destination']);
		exit;
	}
}
if (isset($_POST['uid']) && isset($_POST['currentuid'])) {
	$uid = $_POST['uid'];
	$currentuid = $_POST['currentuid'];
	$response = Login::swtichAccount($uid, $currentuid);
	exit;
}
$user = UserGateway::findLinkAccount($currentuid);
$context = array(
	'href_login' => Core::cadenzaUrl('pages/login.php'),
	'user'=>$user,
	'uid'=>$currentuid
);
print $twig->render('pages/switch_account.html.twig', $context);