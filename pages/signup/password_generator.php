<?php 
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

$users = UserGateway::findAll();
if (!empty($users)) {
	foreach ($users as $key => $user) {
		if ($user['password_hash']=="" || $user['password_hash']==null) {
			$password = '@Cadenza2018';
			$password_hash = password_hash($password,PASSWORD_DEFAULT);
			UserGateway::resetPassword($user['uid'],$password_hash);
		}
	}
}
