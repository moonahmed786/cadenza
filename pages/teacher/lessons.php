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
$student_id = isset($_REQUEST['student_id']) ? $_REQUEST['student_id'] : null;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
$isValidParams = ($student_id != null && is_numeric($student_id));
if (!$isValidParams) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$uid = Session::uid();
$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
$isValidStudent = (UserGateway::isActive($student_id) && $is_link_connected);
if (!$isValidStudent) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadTeacherNavbarData($user);

$student_row = UserGateway::findStudent($student_id);
$student = new User($student_row);

$count_lessons = LessonGateway::countSavedOfStudentTeacher($student->uid, $user->uid);
$pagination = new Pagination('paginationSortableListOfLessons', $count_lessons, $page);

$sortable_action = 'paginationSortableListOfLessons';
$sortable_default_column = 'created_date';
$sortable_default_direction = 'DESC';
Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);

$sortable_options = array(
	'date' => new Sortable(Language::getText('label', 'date'), "created_date", "DESC")
);

$page_lesson_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
$page_lesson_rows = LessonGateway::findAllSavedByStudentTeacher($student->uid, $user->uid, $page_lesson_options);
$page_lessons = array();
foreach ($page_lesson_rows as $lesson_row) {
	$page_lessons[] = new Lesson($lesson_row);
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'student' => $student,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'page_lessons' => $page_lessons,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options
);
print $twig->render('pages/teacher/lessons.html.twig', $context);