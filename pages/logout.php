<?php
require_once dirname(__FILE__).'/../_cadenza/Core.php';
Core::init();

Session::close();

$redirect_uri = Core::cadenzaUrl('pages/index.php');
header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));