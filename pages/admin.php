<?php
require_once dirname(__FILE__).'/../_cadenza/Core.php';
Core::init();

if (Login::isLoggedIn()) {
	Redirect::set('index');
	Redirect::go();
}
else {
	Redirect::done();
	$twig = new Twig_Environment_Cadenza();
	$context = array(
		'href_login' => Core::cadenzaUrl('pages/login.php')
	);
	print $twig->render('pages/admin.html.twig', $context);
}