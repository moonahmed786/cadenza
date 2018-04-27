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
$teacher_id = isset($_REQUEST['teacher_id']) ? $_REQUEST['teacher_id'] : null;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
$isValidParams = ($teacher_id != null && is_numeric($teacher_id));
if (!$isValidParams) {
	Redirect::set('student/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$uid = Session::uid();
$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $teacher_id);
$is_link_inactive = UserLinkGateway::isInactiveStudentTeacher($uid, $teacher_id);
$isValidTeacher = (!UserGateway::isBlocked($teacher_id) && ($is_link_connected || $is_link_inactive));
if (!$isValidTeacher) {
	Redirect::set('student/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadStudentNavbarData($user);

$teacher_row = UserGateway::findTeacher($teacher_id);
$teacher = new User($teacher_row);

$count_lessons = LessonGateway::countSavedOfStudentTeacher($user->uid, $teacher->uid);
$pagination = new Pagination('paginationSortableListOfLessons', $count_lessons, $page);

$sortable_action = 'paginationSortableListOfLessons';
$sortable_default_column = 'created_date';
$sortable_default_direction = 'DESC';
Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);

$sortable_options = array(
	'date' => new Sortable(Language::getText('label', 'date'), "created_date", "DESC")
);

$page_lesson_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
$page_lesson_rows = LessonGateway::findAllSavedByStudentTeacher($user->uid, $teacher->uid, $page_lesson_options);
$page_lessons = array();
foreach ($page_lesson_rows as $lesson_row) {
	$page_lessons[] = new Lesson($lesson_row);
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'teacher' => $teacher,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'page_lessons' => $page_lessons,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options
);
print $twig->render('pages/student/lessons.html.twig', $context);