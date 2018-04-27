<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsAdmin()) {
	Redirect::set('admin/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$skipindex = true;
if ($skipindex) {
	Redirect::set('admin/users');
	Redirect::go();
}
Redirect::done();
