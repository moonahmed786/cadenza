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
$lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : null;
$isValidParams = (($student_id != null && is_numeric($student_id)) && ($lesson_id != null && (is_numeric($lesson_id) || $lesson_id == 'latest')));
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
if ($lesson_id == 'latest') {
	$lesson_row = LessonGateway::findLastSaved($student_id, $uid);
	if ($lesson_row) {
		$lesson = new Lesson($lesson_row);
		Redirect::set('teacher/view_lesson_overview', array('student_id'=>$student_id, 'lesson_id'=>$lesson->lesson_id));
	}
	else {
		Redirect::set('index');
	}
	Redirect::go();
}
$user = Login::getCurrentUser();
$lesson_row = LessonGateway::find($lesson_id);
$lesson = new Lesson($lesson_row);
$isValidLesson = $user->isAllowedViewLesson($lesson);
if (!$isValidLesson) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$navbar_data = Components::loadTeacherNavbarData($user);

$student_row = UserGateway::findStudent($student_id);
$student = new User($student_row);

$count_lessons = LessonGateway::countSavedOfStudentTeacher($student->uid, $user->uid);

$user_can_add_tasks = $user->isAllowedAddTaskToLesson($lesson);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'user_can_add_tasks' => $user_can_add_tasks,
	'navbar_data' => $navbar_data,
	'student' => $student,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'lesson' => $lesson
);
print $twig->render('pages/teacher/view_lesson_overview.html.twig', $context);