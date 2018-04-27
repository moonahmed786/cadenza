<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsTeacher()) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$uid = Session::uid();
$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
$students_user_status = 'active';
$count_connected_students = UserGateway::countStudentsWithStatusLinkedToTeacher($uid, $students_user_status, $link_statuses_connected);
$skipindex = ($count_connected_students > 0);
if ($skipindex) {
	Redirect::set('teacher/students');
	Redirect::go();
}
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadTeacherNavbarData($user, $count_connected_students);

$invite_rows = UserLinkGateway::findAllInvitesByTeacher($user->uid);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data
);
// $client = new Google_Client_Cadenza(true);
// $service = new Google_Service_Drive_Cadenza($client);
// $files = retrieveAllFiles($service);
// $context['google_files'] = $files;
// echo "<pre>";
// print_r($files);die;
print $twig->render('pages/teacher/index.html.twig', $context);


function retrieveAllFiles(Google_Service_Drive_Cadenza $service) {
  $result = array();
  $pageToken = NULL;

  do {
    try {
      $parameters = array();
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $files = $service->files->listFiles(array(
        'q' => "mimeType='application/vnd.google-apps.folder'",
        'spaces' => 'drive',
        // 'pageToken' => $pageToken,
        // 'fields' => 'nextPageToken, files(id, name)',
    ));
// echo "<pre>";
// print_r($files->getFiles());die;
      $result = array_merge($result, $files->getFiles());
      $pageToken = $files->getNextPageToken();
    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
      $pageToken = NULL;
    }
  } while ($pageToken);
  return $result;
}