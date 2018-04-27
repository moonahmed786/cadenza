<?php
class Components {
	
	static function loadStudentNavbarData(User $user, $count_connected_teachers=null, $count_inactive_teachers=null) {
		if ($user->user_type != 'student') {
			trigger_error('Invalid user type \''.$user->user_type.'\'', E_USER_ERROR);
		}
		if ($count_connected_teachers == null) {
			$count_connected_teachers = UserLinkGateway::countConnectedTeachersOfStudent($user->uid);
		}
		if ($count_inactive_teachers == null) {
			$count_inactive_teachers = UserLinkGateway::countInactiveTeachersOfStudent($user->uid);
		}
		return array(
			'user' => $user,
			'count_connected_teachers' => $count_connected_teachers,
			'count_inactive_teachers' => $count_inactive_teachers
		);
	}
	static function loadTeacherNavbarData(User $user, $count_connected_students=null) {
		if ($user->user_type != 'teacher') {
			trigger_error('Invalid user type \''.$user->user_type.'\'', E_USER_ERROR);
		}
		if ($count_connected_students == null) {
			$count_connected_students = UserLinkGateway::countConnectedStudentsOfTeacher($user->uid);
		}
		$invite_rows = UserLinkGateway::findAllInvitesByTeacher($user->uid);
		return array(
			'user' => $user,
			'count_connected_students' => $count_connected_students,
			'invite_rows' => $invite_rows
		);
	}
	
	static function getRandomReflectionPrompt() {
		return Language::getText('label', 'reflection'.rand(1, 11));
	}
	
	static function renderActionbarStudent($uid, $teacher_id, $practice_id) {
		$debug = "renderSelectedTaskStudent($uid, $teacher_id, $practice_id)";
		$user = new User(UserGateway::find($uid));
		$teacher_row = UserGateway::findTeacher($teacher_id);
		$teacher = new User($teacher_row);
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $teacher_id);
		if ($practice_id != null) {
			$practice = new Practice(PracticeGateway::find($practice_id));
		}
		else {
			$practice = null;
		}
		$is_practicing = ($practice !== null);
		$count_lessons = LessonGateway::countSavedOfStudentTeacher($uid, $teacher_id);
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'teacher'=>$teacher,
			'is_link_connected'=>$is_link_connected,
			'is_practicing'=>$is_practicing,
			'count_lessons'=>$count_lessons,
			'debug'=>$debug
		);
		return $twig->render('components/actionbars/student.html.twig', $context);
	}
	
	static function renderActionbarTeacher($uid, $student_id, $lesson_id, $isEditLesson) {
		$debug = "renderActionbarTeacher($uid, $student_id, $lesson_id, $isEditLesson)";
		$user = new User(UserGateway::find($uid));
		$student_row = UserGateway::findStudent($student_id);
		$student = new User($student_row);
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		$count_lessons = LessonGateway::countSavedOfStudentTeacher($student_id, $uid);
		$lesson = new Lesson(LessonGateway::find($lesson_id));
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'student'=>$student,
			'is_link_connected'=>$is_link_connected,
			'count_lessons'=>$count_lessons,
			'lesson'=>$lesson,
			'is_edit_lesson'=>$isEditLesson,
			'debug'=>$debug
		);
		return $twig->render('components/actionbars/teacher.html.twig', $context);
	}
	
	static function renderFormAssignTask($uid, $task_id) {
		$debug = "Components::renderFormAssignTask($uid, $task_id)";
		$lesson_row = LessonGateway::findByTask($task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$student_id = $lesson->student_id;
			$user = new User(UserGateway::find($uid));
			$student = new User(UserGateway::find($student_id));
			$task = new Task(TaskGateway::find($task_id));
			$checklist_item_rows = ChecklistItemGateway::findAllByTask($task->task_id);
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'student'=>$student,
				'lesson'=>$lesson,
				'task'=>$task,
				'checklist_item_rows'=>$checklist_item_rows,
				'debug'=>$debug
			);
			return $twig->render('components/forms/assign_task.html.twig', $context);
		}
	}
	
	static function renderMiscPracticelog($uid, $task_id, $practice_id) {
		$debug = "renderMiscPracticelog($uid, $task_id, $practice_id)";
		$lesson_row = LessonGateway::findByTask($task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = new User(UserGateway::find($uid));
			$task = new Task(TaskGateway::find($task_id));
			$linked_user = null;
			if ($user->user_type == 'student') {
				$teacher_row = UserGateway::findTeacher($task->teacher_id);
				$linked_user = new User($teacher_row);
			}
			elseif ($user->user_type == 'teacher') {
				$student_row = UserGateway::findStudent($task->student_id);
				$linked_user = new User($student_row);
			}
			else {
				trigger_error('Invalid user type \''.$user->user_type.'\'', E_USER_ERROR);
			}
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($task->student_id, $task->teacher_id);
			$task_practice_rows = PracticeGateway::findAllCompletedByTask($task_id, array('orderby'=>'created_date DESC'));
			$task_practices = array();
			foreach ($task_practice_rows as $practice_row) {
				$task_practices[] = new Practice($practice_row);
			}
			$is_practicing = ($practice_id != null);
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'linked_user'=>$linked_user,
				'is_link_connected'=>$is_link_connected,
				'lesson'=>$lesson,
				'task'=>$task,
				'task_practices'=>$task_practices,
				'is_practicing'=>$is_practicing,
				'select_practice_id'=>null,
				'debug'=>$debug
			);
			return $twig->render('components/misc/practicelog.html.twig', $context);
		}
	}

	static function renderMiscPracticelogIndicators($uid, $practice_id) {
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = new User(UserGateway::find($uid));
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'practice'=>$practice
			);
			return $twig->render('components/misc/practicelog_indicators.html.twig', $context);
		}
	}

	static function renderMiscComments($uid, $ref, $ref_id, $is_readonly) {
		$debug = "renderMiscComments($uid, $ref, $ref_id, $is_readonly)";
		$user = new User(UserGateway::find($uid));
		
		$comments = array();
		$comment_rows = CommentGateway::findAllByRef($ref, $ref_id, array('orderby'=>'created_date ASC'));
		foreach ($comment_rows as $comment_row) {
			$comments[] = new Comment($comment_row);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'ref'=>$ref,
			'ref_id'=>$ref_id,
			'comments'=>$comments,
			'is_readonly'=>$is_readonly,
			'debug'=>$debug
		);
		return $twig->render('components/misc/comments.html.twig', $context);
	}
	
	static function renderMiscComment($comment_id, $ref, $ref_id, $uid, $is_readonly) {
		$debug = "renderMiscComment($comment_id, $ref, $ref_id, $uid, $is_readonly)";
		$user = new User(UserGateway::find($uid));
		
		$comment_row = CommentGateway::find($comment_id);
		if ($comment_row) {
			$comment = new Comment($comment_row);
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'ref'=>$ref,
				'ref_id'=>$ref_id,
				'comment'=>$comment,
				'is_new'=>false,
				'is_readonly'=>$is_readonly,
				'debug'=>$debug
			);
			return $twig->render('components/misc/comment.html.twig', $context);
		}
	}
	
	static function renderNavbarStudent($uid) {
		$debug = "renderNavbarStudent($uid)";
		$user = new User(UserGateway::find($uid));
		$navbar_data = static::loadStudentNavbarData($user);
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'navbar_data'=>$navbar_data,
			'debug'=>$debug
		);
		return $twig->render('components/navbars/student.html.twig', $context);
	}
	
	static function renderNavbarTeacher($uid) {
		$debug = "renderNavbarTeacher($uid)";
		$user = new User(UserGateway::find($uid));
		$navbar_data = static::loadTeacherNavbarData($user);
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'navbar_data'=>$navbar_data,
			'debug'=>$debug
		);
		return $twig->render('components/navbars/teacher.html.twig', $context);
	}
	
	static function renderSelectedTaskStudent($uid, $teacher_id, $task_id, $practice_id) {
		$debug = "renderSelectedTaskStudent($uid, $teacher_id, $task_id, $practice_id)";
		$lesson_row = LessonGateway::findByTask($task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = new User(UserGateway::find($uid));
			$teacher_row = UserGateway::findTeacher($teacher_id);
			$teacher = new User($teacher_row);
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $teacher_id);
			$task = new Task(TaskGateway::find($task_id));
			$user_can_practice_task = $user->isAllowedPracticeTask($task);
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
			if ($practice_id != null) {
				$practice = new Practice(PracticeGateway::find($practice_id));
			}
			else {
				$practice = null;
			}
			$is_practicing = ($practice !== null);
			$checklist_item_rows = ChecklistItemGateway::findAllByTask($task->task_id);
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'user_can_practice_task'=>$user_can_practice_task,
				'linked_user'=>$teacher,
				'is_link_connected'=>$is_link_connected,
				'lesson'=>$lesson,
				'task'=>$task,
				'task_num'=>$task_num,
				'task_practices'=>$task_practices,
				'practice'=>$practice,
				'is_practicing'=>$is_practicing,
				'checklist_item_rows' => $checklist_item_rows,
				'select_practicelog_practice_id' => null,
				'debug'=>$debug
			);
			return $twig->render('components/selectedtask/student.html.twig', $context);
		}
	}

	static function renderSelectedTaskTeacher($uid, $student_id, $task_id) {
		$debug = "renderSelectedTaskTeacher($uid, $student_id, $task_id)";
		$lesson_row = LessonGateway::findByTask($task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = new User(UserGateway::find($uid));
			$student_row = UserGateway::findStudent($student_id);
			$student = new User($student_row);
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
			$task = new Task(TaskGateway::find($task_id));
			$user_can_edit_task = $user->isAllowedEditTask($task);
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
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'user_can_edit_task'=>$user_can_edit_task,
				'linked_user'=>$student,
				'is_link_connected'=>$is_link_connected,
				'lesson'=>$lesson,
				'task'=>$task,
				'task_num'=>$task_num,
				'task_practices'=>$task_practices,
				'checklist_item_rows'=>$checklist_item_rows,
				'select_practicelog_practice_id'=>null,
				'debug'=>$debug
			);
			return $twig->render('components/selectedtask/teacher.html.twig', $context);
		}
	}

	static function renderTableListOfLessonsStudent($uid, $teacher_id, $page=null, $order_by=null, $order_direction=null) {
		$user_row = UserGateway::find($uid);
		$user = new User($user_row);
		
		$teacher_row = UserGateway::findTeacher($teacher_id);
		$teacher = new User($teacher_row);
		
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $teacher_id);
		
		$lesson_row_count = LessonGateway::countSavedOfStudentTeacher($uid, $teacher_id);
		$pagination = new Pagination('paginationSortableListOfLessons', $lesson_row_count, $page);
		
		$sortable_action = 'paginationSortableListOfLessons';
		$sortable_default_column = 'created_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'date' => new Sortable(Language::getText('label', 'date'), "created_date", "DESC")
		);
		
		$page_lesson_rows = LessonGateway::findAllSavedByStudentTeacher($uid, $teacher_id, array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params()));
		$page_lessons = array();
		foreach ($page_lesson_rows as $lesson_row) {
			$page_lessons[] = new Lesson($lesson_row);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'linked_user'=>$teacher,
			'is_link_connected'=>$is_link_connected,
			'page_lessons'=>$page_lessons,
			'pagination'=>$pagination,
			'sortable_options' => $sortable_options
		);
		return $twig->render('components/tables/list_of_lessons.html.twig', $context);
	}
	
	static function renderTableListOfLessonsTeacher($uid, $student_id, $page=null, $order_by=null, $order_direction=null) {
		$user_row = UserGateway::find($uid);
		$user = new User($user_row);
		
		$student_row = UserGateway::findStudent($student_id);
		$student = new User($student_row);
		
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		
		$lesson_row_count = LessonGateway::countSavedOfStudentTeacher($student_id, $uid);
		$pagination = new Pagination('paginationSortableListOfLessons', $lesson_row_count, $page);
		
		$sortable_action = 'paginationSortableListOfLessons';
		$sortable_default_column = 'created_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'date' => new Sortable(Language::getText('label', 'date'), "created_date", "DESC")
		);
		
		$page_lesson_rows = LessonGateway::findAllSavedByStudentTeacher($student_id, $uid, array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params()));
		$page_lessons = array();
		foreach ($page_lesson_rows as $lesson_row) {
			$page_lessons[] = new Lesson($lesson_row);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'linked_user'=>$student,
			'is_link_connected'=>$is_link_connected,
			'page_lessons'=>$page_lessons,
			'pagination'=>$pagination,
			'sortable_options' => $sortable_options
		);
		return $twig->render('components/tables/list_of_lessons.html.twig', $context);
	}
	
	static function renderTableListOfNotifications($uid, $page=null) {
		$user_row = UserGateway::find($uid);
		$user = new User($user_row);
		
		$notification_row_count = count($user->notifications);
		$pagination = new Pagination('paginationListOfNotifications', $notification_row_count, $page);
		
		$orderby_arr = array('priority ASC', 'notification_date DESC');
		$page_notification_rows = NotificationGateway::findAllSentToUser($uid, array('orderby'=>$orderby_arr, 'limit'=>$pagination->get_limit_params()));
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
			'user'=>$user,
			'page_notifications'=>$page_notifications,
			'page_notification_ids_accessible' => $page_notification_ids_accessible,
			'pagination'=>$pagination
		);
		return $twig->render('components/tables/list_of_notifications.html.twig', $context);
	}
	
	static function renderTableListOfStudents($uid, $page=null, $order_by=null, $order_direction=null) {
		$count_students = UserLinkGateway::countConnectedStudentsOfTeacher($uid);
		$user_row = UserGateway::find($uid);
		$user = new User($user_row);
		
		$sortable_action = 'paginationSortableListOfStudents';
		$sortable_default_column = 'last_login';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC"),
			'last_login' => new Sortable(Language::getText('label', 'last_login'), "last_login", "DESC"),
		);
		
		$pagination = new Pagination('paginationSortableListOfStudents', $count_students, $page);
		
		$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
		$students_user_status = 'active';
		$page_student_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
		$page_student_rows = UserGateway::findAllStudentsWithStatusLinkedToTeacher($uid, $students_user_status, $link_statuses_connected, $page_student_options);
		$page_students = array();
		$page_lesson_counts = array();
		foreach ($page_student_rows as $student_row) {
			$student = new User($student_row);
			$page_students[] = $student;
			$page_lesson_counts[$student->uid] = LessonGateway::countSavedOfStudentTeacher($student->uid, $uid);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'page_students'=>$page_students,
			'page_lesson_counts'=>$page_lesson_counts,
			'pagination'=>$pagination,
			'sortable_options' => $sortable_options
		);
		return $twig->render('components/tables/list_of_students.html.twig', $context);
	}
	
	static function renderTableListOfInvites($uid) {
		$debug = "renderTableListOfInvites($uid)";
		$invite_rows = UserLinkGateway::findAllInvitesByTeacher($uid);
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'invite_rows'=>$invite_rows,
			'debug'=>$debug
		);
		return $twig->render('components/tables/list_of_invites.html.twig', $context);
	}
	
	static function renderTableListOfTeachers($uid, $order_by=null, $order_direction=null) {
		$debug = "renderTableListOfTeachers($uid, $order_by, $order_direction)";
		$user = new User(UserGateway::find($uid));
		$lesson_counts = array();
		$latest_lesson_dates_local = array();
		
		$sortable_action = 'sortableListOfTeachers';
		$sortable_default_column = 'user_links.last_lesson_id';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			new Sortable(Language::getText('label', 'latest_lesson'), "user_links.last_lesson_id", "DESC"),
			new Sortable(Language::getText('label', 'name'), "u.g_name", "ASC"),
		);
		
		$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
		$link_statuses_inactive = UserLinkGateway::getStatusArrayInactive();
		$teachers_user_statuses = array('active', 'deleted');
		
		$connected_teacher_rows = UserGateway::findAllTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_connected, array('orderby'=>Sortable::get_order_by_string()));
		$connected_teachers = array();
		foreach ($connected_teacher_rows as $connected_teacher_row) {
			$connected_teacher = new User($connected_teacher_row);
			$connected_teachers[] = $connected_teacher;
			$lesson_counts[$connected_teacher->uid] = LessonGateway::countSavedOfStudentTeacher($uid, $connected_teacher->uid);
			$last_lesson_row = LessonGateway::findLastSaved($uid, $connected_teacher->uid);
			$latest_lesson_dates_local[$connected_teacher->uid] = $last_lesson_row ? Core::utcToLocal($last_lesson_row['created_date']) : null;
		}
		$inactive_teacher_rows = UserGateway::findAllTeachersWithStatusesLinkedToStudent($uid, $teachers_user_statuses, $link_statuses_inactive, array('orderby'=>Sortable::get_order_by_string()));
		$inactive_teachers = array();
		foreach ($inactive_teacher_rows as $inactive_teacher_row) {
			$inactive_teacher = new User($inactive_teacher_row);
			$inactive_teachers[] = $inactive_teacher;
			$lesson_counts[$inactive_teacher->uid] = LessonGateway::countSavedOfStudentTeacher($uid, $inactive_teacher->uid);
			$last_lesson_row = LessonGateway::findLastSaved($uid, $inactive_teacher->uid);
			$latest_lesson_dates_local[$inactive_teacher->uid] = $last_lesson_row ? Core::utcToLocal($last_lesson_row['created_date']) : null;
		}
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user,
			'connected_teachers'=>$connected_teachers,
			'inactive_teachers'=>$inactive_teachers,
			'lesson_counts'=>$lesson_counts,
			'latest_lesson_dates_local'=>$latest_lesson_dates_local,
			'sortable_options' => $sortable_options,
			'debug'=>$debug
		);
		return $twig->render('components/tables/list_of_teachers.html.twig', $context);
	}
	
	static function renderTableTaskSelector($uid, $task_id, $isLessonOverview, $isEditLesson) {
		$debug = "renderTableTaskSelector($uid, $task_id, $isLessonOverview, $isEditLesson)";
		$lesson_row = LessonGateway::findByTask($task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = new User(UserGateway::find($uid));
			$user_can_add_tasks = $user->isAllowedAddTaskToLesson($lesson);
			$linked_user = null;
			if ($user->user_type == 'student') {
				$teacher_id = $lesson->teacher_id;
				$linked_user = new User(UserGateway::find($teacher_id));
			}
			elseif ($user->user_type == 'teacher') {
				$student_id = $lesson->student_id;
				$linked_user = new User(UserGateway::find($student_id));
			}
			else {
				trigger_error('Invalid user type \''.$user->user_type.'\'', E_USER_ERROR);
			}
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($lesson->student_id, $lesson->teacher_id);
			$task = new Task(TaskGateway::find($task_id));
			$twig = new Twig_Environment_Cadenza();
			$context = array(
				'user'=>$user,
				'user_can_add_tasks'=>$user_can_add_tasks,
				'linked_user'=>$linked_user,
				'is_link_connected'=>$is_link_connected,
				'lesson'=>$lesson,
				'task'=>$task,
				'is_lesson_overview'=>$isLessonOverview,
				'is_edit_lesson'=>$isEditLesson,
				'debug'=>$debug
			);
			return $twig->render('components/tables/task_selector.html.twig', $context);
		}
	}
		
}