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
$lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : null;
$isValidParams = (($teacher_id != null && is_numeric($teacher_id)) && ($lesson_id != null && (is_numeric($lesson_id) || $lesson_id == 'latest')));
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
if ($lesson_id == 'latest') {
	$lesson_row = LessonGateway::findLastSaved($uid, $teacher_id);
	if ($lesson_row) {
		$lesson = new Lesson($lesson_row);
		Redirect::set('student/view_lesson_overview', array('teacher_id'=>$teacher_id, 'lesson_id'=>$lesson->lesson_id));
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
	Redirect::set('student/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$navbar_data = Components::loadStudentNavbarData($user);

$teacher_row = UserGateway::findTeacher($teacher_id);
$teacher = new User($teacher_row);

$count_lessons = LessonGateway::countSavedOfStudentTeacher($user->uid, $teacher->uid);

$user_can_add_tasks = $user->isAllowedAddTaskToLesson($lesson);

$force_show_reflection_modal = Session::get('force_show_reflection_modal');
if ($force_show_reflection_modal) {
	$show_reflection_modal = $force_show_reflection_modal;
	Session::remove('force_show_reflection_modal');
}
else {
	$show_reflection_modal = false;
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'user_can_add_tasks' => $user_can_add_tasks,
	'navbar_data' => $navbar_data,
	'teacher' => $teacher,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'lesson' => $lesson,
	'show_reflection_modal' => $show_reflection_modal
);
print $twig->render('pages/student/view_lesson_overview.html.twig', $context);