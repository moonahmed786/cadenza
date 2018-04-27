<?php
if (version_compare(phpversion(), '5.5.0', '<')) {
	print 'This version of PHP is too old to run Cadenza.';
}
else {
	require_once dirname(__FILE__).'/_cadenza/Core.php';
	if (Core::isEnvironment('maintenance')) {
		print 'Cadenza is currently down for maintenance. Please check back later.';
	}
	else {
		Core::init();
		
		$redirect_uri = Core::cadenzaUrl('pages/index.php');
		header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
}