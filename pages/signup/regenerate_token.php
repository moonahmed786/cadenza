<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();
if(isset($_GET['message']))
{
	$message = $_GET['message'];
}
else
{
	$message = '';
}
if(isset($_POST['email']))
{
	$data = array(
			    'email' => Core::cleanInput($_POST['email'])
			);
	$error = validate($data);
	if ($error) {
		Redirect::done();
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'href_signup' => Core::cadenzaUrl('pages/signup/signup.php'),
			'href_login' => Core::cadenzaUrl('pages/login.php'),
			'errors' => $error,
			'messages' => $message
		);
		print $twig->render('pages/regenerate_token.html.twig', $context);
	}else{
		$token = md5(time());
		$user = UserGateway::findByEmail($data['email']);
		if ($user) {
			UserGateway::tokenUpdate($user['uid'],$token);
			$url = Core::cadenzaUrl('pages/signup/index.php')."?token=$token";
			$content = 'Your account verification link given below.click or open it in your browser to complete registration<br>';
			$content .= '<a href="'.$url.'">verify now</a>';
			$content = wordwrap($content, 70);
	        $headers = 'Content-type: text/html; charset=utf-8'. "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

	        mail($data['email'], 'Cadenza Sign Up', $content, $headers);
		}
		$messageLink = 'An email with verification link has been sent to your email address.';
		$redirect = Core::cadenzaUrl('pages/login.php');
		header('Location: '.$redirect.'?message='.$messageLink);
		exit;
	}
}else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
		'href_signup' => Core::cadenzaUrl('pages/signup/signup.php'),
		'href_login' => Core::cadenzaUrl('pages/login.php')
	);
	print $twig->render('pages/regenerate_token.html.twig', $context);
}
function validate($data){
	$error = [];
	if (!isset($data['email'])) {
		$error['email'] = 'Email is required';
	}
	return $error;
}