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
if ($count_connected_students == 0) {
	Redirect::set('index');
	Redirect::go();
}
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
Redirect::done();

$user = Login::getCurrentUser();
$navbar_data = Components::loadTeacherNavbarData($user, $count_connected_students);

$sortable_action = 'paginationSortableListOfStudents';
$sortable_default_column = 'last_login';
$sortable_default_direction = 'DESC';
Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);

$sortable_options = array(
	'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC"),
	'last_login' => new Sortable(Language::getText('label', 'last_login'), "last_login", "DESC"),
);

$pagination = new Pagination('paginationSortableListOfStudents', $count_connected_students, $page);

$page_student_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
$page_student_rows = UserGateway::findAllStudentsWithStatusLinkedToTeacher($user->uid, $students_user_status, $link_statuses_connected, $page_student_options);
$page_students = array();
$page_lesson_counts = array();
foreach ($page_student_rows as $student_row) {
	$student = new User($student_row);
	$page_students[] = $student;
	$page_lesson_counts[$student->uid] = LessonGateway::countSavedOfStudentTeacher($student->uid, $user->uid);
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'navbar_data' => $navbar_data,
	'page_students' => $page_students,
	'page_lesson_counts' => $page_lesson_counts,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options
);
print $twig->render('pages/teacher/students.html.twig', $context);