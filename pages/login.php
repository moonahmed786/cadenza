<?php
require_once dirname(__FILE__).'/../_cadenza/Core.php';
Core::init();
if(isset($_GET['message']))
{
	$message = $_GET['message'];
}
else
{
	$message = '';
}
if (isset($_GET['admin']) && $_GET['admin']) {
	$username = isset($_POST['username']) ? $_POST['username'] : null;
	$password = isset($_POST['password']) ? $_POST['password'] : null;
	Login::processLoginAdmin($username, $password);
}
elseif(isset($_POST['password']) && $_POST['password']){
	$username = isset($_POST['username']) ? $_POST['username'] : null;
	$password = isset($_POST['password']) ? $_POST['password'] : null;
	Login::processLogin($username, $password);
}elseif(isset($_GET['code'])){
	$client = new Google_Client_Cadenza();
	Login::processLoginGoogle($client);
	
}else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
		'href_login_admin' => Core::cadenzaUrl('pages/admin.php'),
		'href_signup' => Core::cadenzaUrl('pages/signup/signup.php'),
		'href_forget_password' => Core::cadenzaUrl('pages/forget_password.php'),
		'href_login' => Core::cadenzaUrl('pages/login.php'),
		'messages' => $message
	);
	print $twig->render('pages/user.html.twig', $context);
}
