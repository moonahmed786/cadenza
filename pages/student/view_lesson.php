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
$select_task_id = isset($_REQUEST['select_task_id']) ? $_REQUEST['select_task_id'] : null;
$select_practice_id = isset($_REQUEST['select_practice_id']) ? $_REQUEST['select_practice_id'] : null;
$isValidParams = (
	($teacher_id != null && is_numeric($teacher_id))
	&& ($lesson_id != null && (is_numeric($lesson_id) || $lesson_id == 'latest'))
	&& ($select_task_id == null || is_numeric($select_task_id))
	&& ($select_practice_id == null || (is_numeric($select_practice_id) && $select_task_id != null))
);
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
	$lesson_row = LessonGateway::findLastSaved($student_id, $uid);
	if ($lesson_row) {
		$lesson = new Lesson($lesson_row);
		Redirect::set('student/view_lesson', array('teacher_id'=>$teacher_id, 'lesson_id'=>$lesson->lesson_id));
	}
	else {
		Redirect::set('index');
	}
	Redirect::go();
}
elseif ($select_task_id != null) {
	$data = ($select_practice_id != null) ?
		array('select_task_id'=>$select_task_id, 'select_practice_id'=>$select_practice_id)
		: array('select_task_id'=>$select_task_id);
	Redirect::set('student/view_lesson', array('teacher_id'=>$teacher_id, 'lesson_id'=>$lesson_id), $data);
	Redirect::go();
}
else {
	$select_task_id = Redirect::getDataVal('select_task_id');
	$select_practice_id = Redirect::getDataVal('select_practice_id');
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

$task = ($select_task_id == null) ? $lesson->first_saved_task : new Task(TaskGateway::find($select_task_id));
if (!$task->is_saved) {
	$task = $lesson->first_saved_task;
}

$user_can_practice_task = $user->isAllowedPracticeTask($task);
$user_can_add_tasks = $user->isAllowedAddTaskToLesson($lesson);

$task_num = 0;
foreach ($lesson->saved_tasks as $lesson_task) {
	$task_num++;
	if ($lesson_task->task_id == $task->task_id) {
		break;
	}
}

$task_practice_rows = PracticeGateway::findAllCompletedByTask($task->task_id, array('orderby'=>'created_date DESC'));
$task_practices = array();
foreach ($task_practice_rows as $practice_row) {
	$task_practices[] = new Practice($practice_row);
}

$checklist_item_rows = ChecklistItemGateway::findAllByTask($task->task_id);

$force_show_annotation_modal = Session::get('force_show_annotator_modal');
if ($force_show_annotation_modal) {
	$show_annotation_modal = $force_show_annotation_modal;
	Session::remove('force_show_annotator_modal');
}
else {
	$show_annotation_modal = false;
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'user_can_practice_task' => $user_can_practice_task,
	'user_can_add_tasks' => $user_can_add_tasks,
	'navbar_data' => $navbar_data,
	'teacher' => $teacher,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'lesson' => $lesson,
	'task' => $task,
	'task_num' => $task_num,
	'task_practices' => $task_practices,
	'checklist_item_rows' => $checklist_item_rows,
	'select_practicelog_practice_id' => $select_practice_id,
	'show_annotation_modal'=>$show_annotation_modal
);
print $twig->render('pages/student/view_lesson.html.twig', $context);