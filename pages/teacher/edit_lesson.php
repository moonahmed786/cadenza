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
$select_task_id = isset($_REQUEST['select_task_id']) ? $_REQUEST['select_task_id'] : null;
$isValidParams = (($student_id != null && is_numeric($student_id)) && ($lesson_id != null && is_numeric($lesson_id)) && ($select_task_id == null || is_numeric($select_task_id)));
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
if ($select_task_id != null) {
	Redirect::set('teacher/edit_lesson', array('student_id'=>$student_id, 'lesson_id'=>$lesson_id), array('select_task_id'=>$select_task_id));
	Redirect::go();
}
else {
	$select_task_id = Redirect::getDataVal('select_task_id');
}
$user = Login::getCurrentUser();
$lesson_row = LessonGateway::find($lesson_id);
$lesson = new Lesson($lesson_row);
$isValidLesson = false; // initialize to false
if ($user->isAllowedEditLesson($lesson)) {
	$unsaved_task_rows = TaskGateway::findAllUnsavedBylesson($lesson_id);
	foreach ($unsaved_task_rows as $unsaved_task_row) {
		$unsaved_task = new Task($unsaved_task_row);
		if ($unsaved_task->task_id != $select_task_id) {
			TaskGateway::delete($unsaved_task->task_id);
			$unsaved_task_attachments = UserFileGateway::findAllAttachmentsInTask($unsaved_task->task_id);
			$handler = new Fileupload_UploadHandler_Cadenza();
			$force_delete = true;
			foreach ($unsaved_task_attachments as $attachment) {
				$handler->initiate_delete($unsaved_task->lesson_id, $unsaved_task->task_id, $attachment['practice_id'], 'attachment', $attachment['uid'], $attachment['file_id'], $attachment['filename'], $force_delete);
			}
		}
	}
	$user = Login::getCurrentUser(true); // refresh info
	$lesson = new Lesson(LessonGateway::find($lesson_id));
	if (count($lesson->tasks) == 0) {
		LessonGateway::delete($lesson->lesson_id);
		Redirect::set('teacher/lessons', array('student_id'=>$student_id));
		Redirect::go();
	}
	else {
		$isValidLesson = $user->isAllowedEditLesson($lesson);
	}
}
if (!$isValidLesson) {
	Redirect::set('teacher/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$navbar_data = Components::loadTeacherNavbarData($user);

$student_row = UserGateway::findStudent($student_id);
$student = new User($student_row);

$count_lessons = LessonGateway::countSavedOfStudentTeacher($student->uid, $user->uid);

$task = ($select_task_id == null) ? $lesson->first_task : new Task(TaskGateway::find($select_task_id));

$user_can_add_tasks = $user->isAllowedAddTaskToLesson($lesson);

$checklist_item_rows = ChecklistItemGateway::findAllByTask($task->task_id);

$twig = new Twig_Environment_Cadenza();
$context = array(
	'user' => $user,
	'user_can_add_tasks' => $user_can_add_tasks,
	'navbar_data' => $navbar_data,
	'student' => $student,
	'is_link_connected' => $is_link_connected,
	'count_lessons' => $count_lessons,
	'lesson' => $lesson,
	'task' => $task,
	'checklist_item_rows' => $checklist_item_rows
);
print $twig->render('pages/teacher/edit_lesson.html.twig', $context);