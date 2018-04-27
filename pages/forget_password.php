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
if(isset($_POST['email']))
{
	$data = array(
			    'email' => cleanInput($_POST['email'])
			);
	$error = validate($data);
	if ($error) {
		Redirect::done();
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'href_login_admin' => Core::cadenzaUrl('pages/admin.php'),
			'href_signup' => Core::cadenzaUrl('pages/signup/signup.php'),
			'href_forget_password' => Core::cadenzaUrl('pages/forget_password.php'),
			'href_login' => Core::cadenzaUrl('pages/login.php'),
			'errors' => $error,
			'messages' => $message
		);
		print $twig->render('pages/forget_password.html.twig', $context);
	}else{
		$token = md5(time());
		$user = UserGateway::findByEmail($data['email']);
		if ($user) {
			UserGateway::tokenUpdate($user['uid'],$token);
			// $url = Core::cadenzaUrl('pages/signup/reset_password.php')."?token=$token";
			$url = Core::cadenzaUrl('pages/signup/reset_password.php')."?token=$token";
			// $content = 'Your account password reset link given below.<br>';
			// $content .= '<a href="'.$url.'">reset now</a>';
				 
			$content = '<table border="0" cellpadding="0" cellspacing="0" width="600" id=""><tbody><tr> <td align="center"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="" style="max-width:650px"><tr><td align="center" valign="top"><img src="https://hqdemo.com/cadenza/images/logo_landing.png" alt="CADENZA" width="100%" style="display:block " border="0"></td></tr>
			<tr> 
			<tbody><tr> <td bgcolor="#f3f3f3" style="padding-bottom:50px;padding-right:20px;padding-left:20px;padding-top:0px;"> 
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="" bgcolor="#f3f3f3"> 	<tbody><tr> <td align="center" valign="top" width="100%">

						<table bgcolor="#f3f3f3" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:30px;max-width:510px;border-top:5px solid #62a5ff" class=""> 
							<tbody> 
								<tr> 
								<td style="font-family:Open Sans,sans-serif;font-weight:400;line-height:28px;font-size:22px;padding:30px 30px 15px;text-align:left; background-color:#fff;" class=""> CADENZA RESET PASSWORD </td> 
								</tr> 
								<tr> 
								<td bgcolor="#ffffff" style="font-family:Open Sans,sans-serif;font-size:14px;padding:10px 30px;text-align:left" align="center" class=""> Hello! <br> <br>  </td> 
								</tr> 
								<tr> 
								<td bgcolor="#ffffff" style="font-family:Open Sans,sans-serif;font-size:14px;padding:10px 30px;text-align:left" align="center" class=""> You recently requested to reset your password for your "CADENZA" account. Use the button below to reset it. </td> 
								</tr> 
								<tr> 
								<td width="100%" bgcolor="#ffffff" style="font-family:Open Sans,sans-serif;font-size:14px;padding:10px 0 30px 0;text-align:center" align="center" class=""> 
								<table width="60%" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto" align="center"> 
									<tbody> 
										<tr> 
										<td align="center" bgcolor="#EF5B25" width="10%" style="border-radius:5px"> <a href="'.$url.'" style="padding:10px;width:300px;display:block;text-decoration:none;border:1px solid #f07f4a;text-align:center;font-weight:600;font-size:15px;font-family:Open Sans,sans-serif;color:#ffffff;background:#f07f4a;border-radius:5px;line-height:17px" class=""> Reset your password </a> </td> 
										</tr> 
									</tbody> 
								</table> </td></tr> 
								<tr> 
								<td bgcolor="#ffffff" style="font-family:Open Sans,sans-serif;font-size:14px;padding:10px 30px;text-align:left" align="center" class=""> For security this request was received from a Cadenza Administartor. If you did not request a password reset, please ignore this email or <a href="" style="font-family:verdana,geneva,sans-serif;color:#ef5b25;text-decoration:none" target="_blank" data-saferedirecturl="">contact support</a>  if you have questions. <br> <br> </td> </tr> 
								<tr> 
								<td bgcolor="#ffffff" style="font-family:Open Sans,sans-serif;font-size:14px;padding:0px 0px 20px 30px;text-align:left" align="center" class=""> The Music Tool Suite Team </td> 
								</tr> 
								<tr> 
								<td> 
									<table bgcolor="#f3f3f3" border="0" cellpadding="0" cellspacing="0" width="100%"> 
										<tbody style="background-color: #fff;"> 
											<tr> 
												<td style="font-family:Open Sans,sans-serif;font-size:10px;padding:25px 30px 5px 30px" class=""> Copyright <a href="https://musictoolsuite.ca/" rel="null" type="url" class="url-link">Music Tool Suite</a> ©2018</td></tr> 
										</tbody></table> </td></tr> 
									</tbody></table></td></tr> 
							</tbody></table></td></tr> 
						</tbody> 
					</table> 
				</td> 
			</tr>';
			$content .= '</tbody>';
			$content .= '</table>';
			$content = wordwrap($content, 70);
	        $headers = 'Content-type: text/html; charset=utf-8'. "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

	        mail($data['email'], 'Cadenza Password Reset', $content, $headers);
		}
		$messageLink = 'An email with password reset link has been sent to your email address.';
		// $response = Login::processLogin($data['username'], $data['password'],false);
		$redirect = Core::cadenzaUrl('pages/login.php');
		header('Location: '.$redirect.'?message='.$messageLink);
		exit;
	}
	// echo '<pre>';
	// print_r($_POST['email']);
	// exit;
}else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
		'href_login_admin' => Core::cadenzaUrl('pages/admin.php'),
		'href_signup' => Core::cadenzaUrl('pages/signup/signup.php'),
		'href_forget_password' => Core::cadenzaUrl('pages/forget_password.php'),
		'href_login' => Core::cadenzaUrl('pages/login.php')
	);
	print $twig->render('pages/forget_password.html.twig', $context);
}

function validate($data){
	$error = [];
	if (!isset($data['email'])) {
		$error['email'] = 'Email is required';
	}else{
		$user = UserGateway::findByEmail($data['email']);
		if (empty($user)) {
			$error['email'] = "No user found associated with $data[email].";
		}
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