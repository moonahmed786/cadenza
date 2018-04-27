<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsStudent()) {
	Redirect::set('student/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
Redirect::done();

$user = Login::getCurrentUser();

// update new status for notifciations (only if a notification exists and is_new == true)
if ($user->last_notification && $user->last_notification->is_new) {
	NotificationGateway::markNewSentToUserAsNotNewUpToNotification($user->uid, $user->last_notification->notification_id);
	$user = Login::getCurrentUser(true); // refresh info
}

$navbar_data = Components::loadStudentNavbarData($user);

$notification_row_count = count($user->notifications);
$pagination = new Pagination('paginationListOfNotifications', $notification_row_count, $page);

$orderby_arr = array('priority ASC', 'notification_date DESC');
$page_notification_rows = NotificationGateway::findAllSentToUser($user->uid, array('orderby'=>$orderby_arr, 'limit'=>$pagination->get_limit_params()));
$page_notifications = array();
$page_notification_ids_accessible = array();
foreach ($page_notification_rows as $notification_row) {
	$notification = NotificationFactory::createNotificationObject($notification_row);
	$page_notifications[] = $notification;
	if ($notification->isGoLocationAccessibleByUser($user)) {
		$page_notification_ids_accessible[] = $notification->notification_id;
	}
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'page_notifications' => $page_notifications,
	'page_notification_ids_accessible' => $page_notification_ids_accessible,
	'pagination' => $pagination
);
print $twig->render('pages/student/notifications.html.twig', $context);
