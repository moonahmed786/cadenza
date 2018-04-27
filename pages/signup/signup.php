<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();


if (isset($_POST['name'])) {
	$data = array(
				'name' => cleanInput($_POST['name']),
			    'email' => cleanInput($_POST['email']),
			    'username' => cleanInput($_POST['username']),
			    'password' => cleanInput($_POST['password']),
			    'password_confirm' => cleanInput($_POST['password_confirm']),
			    'user_type' => cleanInput($_POST['user_type'])
			);
	$error = validate($data);
	if ($error) {
		Redirect::done();
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'form_url' => Core::cadenzaUrl('pages/signup/index.php'),
			'data' => $data,
			'errors' => $error
		);
		print $twig->render('pages/signup/signup.html.twig',$context);
	}else{
		$token = md5(time());
		$id = UserGateway::signup(
			$data['email'], 
			$data['name'], 
			$data['username'], 
			$data['user_type'],
			$data['password'],
			$token
		);
		if ($id) {
			$url = Core::cadenzaUrl('pages/signup/index.php')."?token=$token";
			$content = 'Your account has been created at cadenza click below link to verify your account.<br>';
			$content .= '<a href="'.$url.'">verify now</a>';
			$content = wordwrap($content, 70);
	        $headers = 'Content-type: text/html; charset=utf-8'. "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

	        mail($data['email'], 'Cadenza Sign Up', $content, $headers);
		}
		// $response = Login::processLogin($data['username'], $data['password'],false);
		$redirect = Core::cadenzaUrl('pages/login.php');
		header('Location: '.$redirect.'?message=An email with verification link has been sent to your email address.');
		exit;
	}
}
else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
			'form_url' => Core::cadenzaUrl('pages/signup/index.php'),
			'data' => array(
				'name' => '',
			    'email' => '',
			    'username' => '',
			    'password' => '',
			    'password_confirm' => '',
			    'user_type' => ''
			)
		);
	print $twig->render('pages/signup/signup.html.twig',$context);
}
function validate($data){
	$error = [];
	if (!isset($data['name'])) {
		$error['name'] = 'Name is required';
	}
	if (!isset($data['email'])) {
		$error['email'] = 'Email is required';
	}else{
		$user = UserGateway::findByEmail($data['email']);
		if (isset($user['uid'])) {
			$error['email'] = "Email $data[email] has been already taken, try another.";
		}
	}
	if (!isset($data['username'])) {
		$error['username'] = 'Username is required';
	}
	if (!isset($data['password'])) {
		$error['password_confirm'] = 'Confirm Password is required';
	}else{
		if ($data['password']!==$data['password_confirm']) {
			$error['password_confirm'] = 'Password does not match';
		}
	}
	if (!isset($data['user_type'])) {
		$error['user_type'] = 'User Type is required';
	}
	return $error;
}
function cleanInput($input)
{
	if (is_array($input)) {
		foreach ($input as $key => $value) {
			cleanInput($value);
		}
	}else{
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $input);
		$input = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $input);
		$input = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $input);
		$input = html_entity_decode($input, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$input = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $input);

		// Remove javascript: and vbscript: protocols
		$input = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $input);
		$input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $input);
		$input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $input);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $input);
		$input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $input);
		$input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $input);

		// Remove namespaced elements (we do not need them)
		$input = preg_replace('#</*\w+:\w[^>]*+>#i', '', $input);
	    $input = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $input);
		return $input;
	}
}