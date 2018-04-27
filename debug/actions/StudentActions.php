<?php
class StudentActions extends UserActions {
	
	static function uploadAttachmentToPractice() {
		$category = 'attachment';
		$practice_id = isset($_REQUEST['practice_id']) ? $_REQUEST['practice_id'] : null;
		$row_id = $_REQUEST['row_id'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedUploadAttachmentToPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$handler = new Fileupload_UploadHandler_Cadenza();
				$handler->initiate_upload($practice->lesson_id, $practice->task_id, $practice_id, $category, $user->uid);
				$response = $handler->get_response();
				$response['row_id'] = $row_id;
				// TODO: place this refresh in an "if practicelog" (if it's possible to determine here)
				$response['refresh'] = array(
					'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($user->uid, $practice_id)
				);
				return $response;
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student is actively practicing a task.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}
	
	static function addAnnotatorToPractice() {
		$row_id = $_REQUEST['row_id'];
		$practice_id = isset($_REQUEST['practice_id']) ? $_REQUEST['practice_id'] : null;
		$drive_file_id = isset($_REQUEST['annotator_file_id']) ? $_REQUEST['annotator_file_id'] : null;
		$annotator_title = isset($_REQUEST['annotator_title']) ? $_REQUEST['annotator_title'] : null;
		$uploadedWithinCadenza = isset($_REQUEST['uploadedWithinCadenza']) ? $_REQUEST['uploadedWithinCadenza'] : false;
		
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedAddAnnotatorToPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$is_action = true;
				$client = new Google_Client_Cadenza($is_action);
				$service = new Google_Service_Drive_Cadenza($client);
				
				PracticeGateway::updateAnnotator($practice_id, $drive_file_id, $annotator_title);
				
				$teacher_row = UserGateway::find($practice->teacher_id);
				if ($teacher_row) {
					$teacher = new User($teacher_row);
					// share with teacher
					DriveAPIPermission::createPermission($service, $drive_file_id, $teacher->email, "user", "writer");
				}
				
				if ($uploadedWithinCadenza) {
					// move file into "Cadenza" folder
					DriveAPIFile::moveFileIntoCadenzaFolder($service, $drive_file_id);
				}
				
				$response = array();
				$response['row_id'] = $row_id;
				// TODO: place this refresh in an "if practicelog" (if it's possible to determine here)
				$response['refresh'] = array(
					'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($user->uid, $practice_id)
				);
				return $response;
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student is actively practicing a task.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}
	static function deleteAnnotator() {
		$practice_id = isset($_REQUEST['practice_id']) ? $_REQUEST['practice_id'] : null;
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedRemoveAnnotatorFromPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$is_action = true;
				$client = new Google_Client_Cadenza($is_action);
				$service = new Google_Service_Drive_Cadenza($client);
				DriveAPIFile::deleteCadenzaAnnotatorFile($service, $practice->annotator_file_id);
				PracticeGateway::updateAnnotator($practice_id, null, null); // delete annotator
				// delete notifications for deleted annotator
				NotificationGateway::deleteAllInRefId('annotation', $practice_id);
				$response = array();
				// TODO: place this refresh in an "if practicelog" (if it's possible to determine here)
				$response['refresh'] = array(
					'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($user->uid, $practice_id)
				);
				return $response;
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student is actively practicing a task.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}
	
	static function rejectInvite() {
		$teacher_id = $_REQUEST['teacher_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$invite_row = UserLinkGateway::findInviteByStudentTeacher($uid, $teacher_id);
		if ($invite_row) {
			$user_link_id = $invite_row['user_link_id'];
			if ($invite_row['status'] == 'pending' || $invite_row['status'] == 'pending-inactive') {
				// note: can only reject from the notification board (and not directly from the notifications bell)
				$pagename = basename($_SERVER["SCRIPT_FILENAME"], '.php');
				if ($pagename == 'notifications') {
					Session::set('action_ok', true); // action ok
					$setStatus = ($invite_row['status'] == 'pending') ? 'rejected' : 'rejected-inactive';
					UserLinkGateway::updateStatus($user_link_id, $setStatus, date('Y-m-d H:i:s'));
					$notification_rows = NotificationGateway::findAllSentInRefId('user_link', $user_link_id);
					foreach ($notification_rows as $notification_row) {
						NotificationGateway::delete($notification_row['notification_id']);
					}
					$refresh = array(
						'navbars/student'=>Components::renderNavbarStudent($uid),
						'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
					);
					return array('refresh'=>$refresh);
				}
			}
		}
	}
	
	static function acceptInvite() {
		$teacher_id = $_REQUEST['teacher_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$invite_row = UserLinkGateway::findInviteByStudentTeacher($uid, $teacher_id);
		if ($invite_row) {
			$user_link_id = $invite_row['user_link_id'];
			if ($invite_row['status'] == 'pending' || $invite_row['status'] == 'pending-inactive') {
				// note: can only accept from the notification board (and not directly from the notifications bell)
				$pagename = basename($_SERVER["SCRIPT_FILENAME"], '.php');
				if ($pagename == 'notifications') {
					Session::set('action_ok', true); // action ok
					UserLinkGateway::updateStatus($user_link_id, 'connected', date('Y-m-d H:i:s'));
					$notification_rows = NotificationGateway::findAllSentInRefId('user_link', $user_link_id);
					foreach ($notification_rows as $notification_row) {
						NotificationGateway::delete($notification_row['notification_id']);
					}
					$refresh = array(
						'navbars/student'=>Components::renderNavbarStudent($uid),
						'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
					);
					return array('refresh'=>$refresh);
				}
			}
		}
	}

	static function selectTask() {
		$task_id = $_REQUEST['task_id'];
		$edit = $_REQUEST['edit'];
		if ($edit) {
			return; // only teachers can modify lessons
		}
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedViewTask($task)) {
				Session::set('action_ok', true); // action ok
				$refresh = array();
				$refresh['tables/task_selector'] = Components::renderTableTaskSelector($user->uid, $task->task_id, false, $edit);
				if ($edit) {
					$refresh['forms/assign_task'] = Components::renderFormAssignTask($user->uid, $task->task_id);
				}
				else {
					$refresh['selectedtask/student'] = Components::renderSelectedTaskStudent($user->uid, $task->teacher_id, $task->task_id, null);
				}
				return array('task_id'=>$task->task_id, 'refresh'=>$refresh);
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student wants to navigate to the page.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}

	static function startPractice() {
		$task_id = $_REQUEST['task_id'];
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			$teacher_row = UserGateway::find($task->teacher_id);
			if ($teacher_row) {
				$teacher = new User($teacher_row);
				if ($user->isAllowedPracticeTask($task)) {
					Session::set('action_ok', true); // action ok
					$practice_id = PracticeGateway::insert($task->task_id, $task->lesson_id, null, null, false, null, null, date('Y-m-d H:i:s'));
					$refresh = array(
						'actionbars/student'=>Components::renderActionbarStudent($user->uid, $task->teacher_id, $practice_id),
						'selectedtask/student'=>Components::renderSelectedTaskStudent($user->uid, $task->teacher_id, $task->task_id, $practice_id)
					);
					return array('refresh'=>$refresh);
				}
				elseif ($user->isAllowedViewTask($task) && $user->isAllowedPracticeAnyTasksAssignedByUser($teacher)) {
					// expected problem - occurs when teacher creates a new lesson while student is actively practicing a task.
					Session::set('action_ok', true); // action ok
					$redirect_params = '?teacher_id='.$task->teacher_id.'&lesson_id='.$task->lesson_id.'&select_task_id='.$task->task_id;
					$redirect_uri = Core::cadenzaUrl('pages/student/view_lesson.php').$redirect_params;
					return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_not_latest_lesson'));
				}
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student is actively practicing a task.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}
	
	static function savePractice() {
		$practice_id = $_REQUEST['practice_id'];
		$checklist = isset($_REQUEST['checklist']) ? $_REQUEST['checklist'] : array();
		$timer_mins = $_REQUEST['timer_mins'];
		$reflection = ($_REQUEST['reflection'] > 0) ? $_REQUEST['reflection'] : null;
		$comment_text = isset($_REQUEST['comment_text']) && !empty($_REQUEST['comment_text']) ? $_REQUEST['comment_text'] : null;
		$practice_row = PracticeGateway::find($practice_id);
		$task_row = TaskGateway::findByPractice($practice_id);
		$lesson_row = LessonGateway::findByPractice($practice_id);
        
		if ($practice_row && $task_row && $lesson_row) {
			$practice = new Practice($practice_row);
			$task = new Task($task_row);
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedSavePractice($practice)) {
				Session::set('action_ok', true); // action ok
				$task_id = $task->task_id;
				$lesson_id = $lesson->lesson_id;
				$count_task_practices = $task->count_practices;
				$count_lesson_saved_tasks_with_target_met = $lesson->count_saved_tasks_with_target_met;
				PracticeGateway::updateTimerAndReflection($practice_id, $timer_mins, $reflection); // will mark practice as "complete"
				foreach ($checklist as $checklist_item) {
					$checklist_item_id = $checklist_item['checklist_item_id'];
					$field_value = $checklist_item['field_value'];
					PracticeFieldGateway::insertChecklistItem($practice_id, $checklist_item_id, $field_value);
				}
				if ($comment_text) {
					CommentGateway::insert('practice', $practice_id, $user->uid, $comment_text, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
				}
				// Pending Notifications for Annotations
				NotificationGateway::markUnsentInRefIdAsSent('annotation', $practice_id);
				// Rewards
				$updatedTask = new Task(TaskGateway::find($task_id));
				$updatedLesson = new Lesson(LessonGateway::find($lesson_id));
				$task_target = $updatedTask->target;
				$lesson_target = count($updatedLesson->saved_tasks);
				StudentRewardGateway::insertTaskPractice($user->uid, date('Y-m-d H:i:s'));
				if ($count_task_practices < $task_target && $updatedTask->count_practices >= $task_target) {
					StudentRewardGateway::insertTaskTarget($user->uid, date('Y-m-d H:i:s'));
				}
				if ($count_lesson_saved_tasks_with_target_met < $lesson_target && $updatedLesson->count_saved_tasks_with_target_met >= $lesson_target) {
					StudentRewardGateway::insertLessonTarget($user->uid, date('Y-m-d H:i:s'));
				}
				// Refresh
				$refresh = array(
					'navbars/student'=>Components::renderNavbarStudent($user->uid),
					'actionbars/student'=>Components::renderActionbarStudent($user->uid, $updatedTask->teacher_id, null),
					'tables/task_selector'=>Components::renderTableTaskSelector($user->uid, $updatedTask->task_id, false, false),
					'selectedtask/student'=>Components::renderSelectedTaskStudent($user->uid, $updatedTask->teacher_id, $updatedTask->task_id, null)
				);
				return array('refresh'=>$refresh);
			}
			elseif ($user->isAllowedPracticeTask($task)) {
				// expected problem - occurs when teacher modifies a task while student is actively practicing it.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?teacher_id='.$task->teacher_id.'&lesson_id='.$task->lesson_id.'&select_task_id='.$task->task_id;
				$redirect_uri = Core::cadenzaUrl('pages/student/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'practice_task_modified'), 'disable_unsafe_navigation'=>true);
			}
		}
		else {
            // expected problem - occurs when teacher deletes a task/lesson while student is actively practicing a task.
            Session::set('action_ok', true); // action ok
            $redirect_uri = Core::cadenzaUrl('pages/student/teachers.php');
            return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'task_does_not_exist'), 'disable_unsafe_navigation'=>true);
        }
	}

	static function saveLessonReflectionIndex() {
		$lesson_id = $_REQUEST['lesson_id'];
		$reflection_index = ($_REQUEST['reflection_index'] > 0) ? $_REQUEST['reflection_index'] : null;
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedReflectOnLesson($lesson)) {
				Session::set('action_ok', true); // action ok
				$reflection_text = ($lesson->has_reflection) ? $lesson->reflection->reflection_text : "";
				$reflection_prompt = ($lesson->has_reflection) ? $lesson->reflection->reflection_prompt : null;
				$is_blank = ($reflection_index == null && $reflection_text == "");
				if ($is_blank) {
					LessonReflectionGateway::deleteByLessonId($lesson_id);
				}
				else {
					if (!$lesson->has_reflection) {
						LessonReflectionGateway::insert($lesson->lesson_id, $reflection_index, $reflection_text, $reflection_prompt);
						StudentRewardGateway::insertLessonReflection($user->uid, date('Y-m-d H:i:s'));
						$refresh = array(
							'navbars/student'=>Components::renderNavbarStudent($user->uid)
						);
						return array('refresh'=>$refresh);
					}
					else {
						LessonReflectionGateway::update($lesson->reflection->lesson_reflection_id, $lesson->lesson_id, $reflection_index, $reflection_text, $reflection_prompt);
					}
				}
			}
		}
	}

	static function saveLessonReflectionText() {
		$lesson_id = $_REQUEST['lesson_id'];
		$reflection_text = $_REQUEST['reflection_text'];
		$reflection_prompt = ($reflection_text != "") ? $_REQUEST['reflection_prompt'] : null;
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedReflectOnLesson($lesson)) {
				Session::set('action_ok', true); // action ok
				$reflection_index = ($lesson->has_reflection) ? $lesson->reflection->reflection_index : null;
				$is_blank = ($reflection_index == null && $reflection_text == "");
				if ($is_blank) {
					LessonReflectionGateway::deleteByLessonId($lesson_id);
				}
				else {
					if (!$lesson->has_reflection) {
						LessonReflectionGateway::insert($lesson->lesson_id, $reflection_index, $reflection_text, $reflection_prompt);
						StudentRewardGateway::insertLessonReflection($user->uid, date('Y-m-d H:i:s'));
						$refresh = array(
							'navbars/student'=>Components::renderNavbarStudent($user->uid)
						);
						return array('refresh'=>$refresh);
					}
					else {
						LessonReflectionGateway::update($lesson->reflection->lesson_reflection_id, $lesson->lesson_id, $reflection_index, $reflection_text, $reflection_prompt);
					}
				}
			}
		}
	}

	static function getNewRandomReflectionPrompt() {
		$lesson_id = $_REQUEST['lesson_id'];
		$current_reflection_prompt = $_REQUEST['current_reflection_prompt'];
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedReflectOnLesson($lesson)) {
				Session::set('action_ok', true); // action ok
				$reflectionPromptRandom = Components::getRandomReflectionPrompt();
		
				// make sure you get a different one
				while ($current_reflection_prompt == $reflectionPromptRandom) {
					$reflectionPromptRandom = Components::getRandomReflectionPrompt();
				}
				
				return array('reflectionPromptRandom'=>$reflectionPromptRandom);
			}
		}
	}

	static function getReflectionModalData() {
		$lesson_id = $_REQUEST['lesson_id'];
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedViewLesson($lesson)) {
				Session::set('action_ok', true); // action ok
				$edit = $user->isAllowedReflectOnLesson($lesson);
				if ($lesson->has_reflection) {
					$reflection_index = $lesson->reflection->reflection_index;
					$reflection_text = $lesson->reflection->reflection_text;
					$reflection_prompt = $lesson->reflection->reflection_prompt;
				}
				else {
					$reflection_index = null;
					$reflection_text = "";
					$reflection_prompt = null;
				}
				$reflection_prompt_rand = Components::getRandomReflectionPrompt();
				$modalData = array();
				$modalData['isEditReflection'] = $edit;
				$modalData['lessonId'] = $lesson->lesson_id;
				$modalData['lessonTitle'] = date('M j, Y', strtotime($lesson->created_date));
				$modalData['lessonTargets'] = $lesson->count_saved_tasks_with_target_met.'/'.count($lesson->saved_tasks);
				$modalData['lessonTimeSpent'] = $lesson->time_spent;
				$modalData['reflectionIndex'] = $reflection_index;
				$modalData['reflectionText'] = $reflection_text;
				if ($modalData['reflectionText'] == "") {
					$modalData['reflectionNoText'] = Language::getText('hint', 'no_reflection_text_student').'.';
				}
				$modalData['reflectionPrompt'] = $reflection_prompt;
				if ($modalData['isEditReflection']) {
					$modalData['reflectionPromptRandom'] = $reflection_prompt_rand;
				}
				$modalData['commentsHTML'] = Components::renderMiscComments($user->uid, 'lesson', $lesson->lesson_id, !$edit);
				
				return array('modalData'=>$modalData);
			}
		}
	}

	static function getGoalsModalData() {
		$teacher_id = $_REQUEST['linked_user_id'];
		$teacher_row = UserGateway::findTeacher($teacher_id);
		if ($teacher_row) {
			$teacher = new User($teacher_row);
			$user = Login::getCurrentUser();
			$edit = UserLinkGateway::isConnectedStudentTeacher($user->uid, $teacher->uid);
			if (($edit && $user->isAllowedEditStudentGoalsWithTeacher($user, $teacher)) || (!$edit && $user->isAllowedViewStudentGoalsWithTeacher($user, $teacher))) {
				Session::set('action_ok', true); // action ok
				$student_goal_rows = StudentGoalGateway::findAllByStudentTeacher($user->uid, $teacher->uid, array('orderby'=>'created_date DESC'));
				$student_goals = array();
				foreach ($student_goal_rows as $student_goal_row) {
					$student_goals[] = new StudentGoal($student_goal_row);
				}
				
				$modalData = array();
				$modalData['isEditGoals'] = $edit;
				$modalData['studentGoals'] = $student_goals;
				if ($modalData['isEditGoals']) {
					$modalData['teacherId'] = $teacher_id;
					$modalData['newGoalTitle'] = date('M j, Y');
				}
				else {
					$modalData['noGoalsStrings'] = array(
						'all' => Language::getText('hint', 'no_goals_set_student').'.',
						'incomplete' => Language::getText('hint', 'no_goals_incomplete_student').'.',
						'completed' => Language::getText('hint', 'no_goals_completed_student').'.'
					);
				}
				return array('modalData'=>$modalData);
			}
		}
	}
	
	static function addGoal() {
		$teacher_id = $_REQUEST['teacher_id'];
		$text = trim($_REQUEST['text']);
		$teacher_row = UserGateway::findTeacher($teacher_id);
		if ($teacher_row) {
			$teacher = new User($teacher_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditStudentGoalsWithTeacher($user, $teacher)) {
				Session::set('action_ok', true); // action ok
				if ($text == "") {
					return array('added'=>false);
				}
				$student_goal_id = StudentGoalGateway::insert($user->uid, $teacher_id, $text, false, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
				$student_goal = new StudentGoal(StudentGoalGateway::find($student_goal_id));
				StudentRewardGateway::insertGoal($user->uid, date('Y-m-d H:i:s'));
				$refresh = array(
					'navbars/student'=>Components::renderNavbarStudent($user->uid)
				);
				return array('added'=>true, 'studentGoal'=>$student_goal, 'refresh'=>$refresh);
			}
		}
	}

	static function deleteGoal() {
		$student_goal_id = $_REQUEST['student_goal_id'];
		$student_goal_row = StudentGoalGateway::find($student_goal_id);
		if ($student_goal_row) {
			$studentGoal = new StudentGoal($student_goal_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedDeleteStudentGoal($studentGoal)) {
				Session::set('action_ok', true); // action ok
				StudentGoalGateway::delete($student_goal_id);
			}
		}
	}
	
	static function updateGoal() {
		$student_goal_id = $_REQUEST['student_goal_id'];
		$text = trim($_REQUEST['text']);
		$student_goal_row = StudentGoalGateway::find($student_goal_id);
		if ($student_goal_row) {
			$studentGoal = new StudentGoal($student_goal_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditStudentGoal($studentGoal)) {
				Session::set('action_ok', true); // action ok
				if ($text == "") {
					return array('updated'=>false);
				}
				StudentGoalGateway::updateText($student_goal_id, $text, date('Y-m-d H:i:s'));
				return array('updated'=>true, 'updatedText'=>$text);
			}
		}
	}
	
	static function updateGoalCompleted() {
		$student_goal_id = $_REQUEST['student_goal_id'];
		$is_completed = $_REQUEST['completed'];
		$student_goal_row = StudentGoalGateway::find($student_goal_id);
		if ($student_goal_row) {
			$studentGoal = new StudentGoal($student_goal_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditStudentGoal($studentGoal)) {
				Session::set('action_ok', true); // action ok
				StudentGoalGateway::updateIsCompleted($student_goal_id, $is_completed, date('Y-m-d H:i:s'));
				return array('updatedCompleted'=>$is_completed);
			}
		}
	}
	
	static function sortableListOfTeachers() {
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$uid = Session::uid();
		Session::set('action_ok', true); // action ok
		$refresh = array(
			'tables/list_of_teachers'=>Components::renderTableListOfTeachers($uid, $order_by, $order_direction)
		);
		return array('refresh'=>$refresh);
	}
	
	static function paginationSortableListOfLessons() {
		$teacher_id = $_REQUEST['teacher_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$uid = Session::uid();
		$is_link_connected_or_inactive = UserLinkGateway::isConnectedOrInactiveStudentTeacher($uid, $teacher_id);
		if (!UserGateway::isBlocked($teacher_id) && $is_link_connected_or_inactive) {
			Session::set('action_ok', true); // action ok
			$refresh = array(
				'tables/list_of_lessons'=>Components::renderTableListOfLessonsStudent($uid, $teacher_id, $page, $order_by, $order_direction)
			);
			return array('refresh'=>$refresh);
		}
	}
	
	static function paginationListOfNotifications() {
		// student is always allowed to view their own notifications
		Session::set('action_ok', true); // action ok
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$refresh = array(
			'navbars/student'=>Components::renderNavbarStudent($uid),
			'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
		);
		return array('refresh'=>$refresh);
	}
	
	static function getEditPracticelogTimerModalData() {
		$practice_id = $_REQUEST['practice_id'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$modalData = array();
				$modalData['practiceId'] = $practice->practice_id;
				$modalData['timerMins'] = $practice->timer_mins;
				return array('modalData'=>$modalData);
			}
		}
	}
	
	static function savePracticelogReflection() {
		$practice_id = $_REQUEST['practice_id'];
		$reflection_index = ($_REQUEST['reflection_index'] > 0) ? $_REQUEST['reflection_index'] : null;
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditPractice($practice)) {
				Session::set('action_ok', true); // action ok
				PracticeGateway::updateReflection($practice_id, $reflection_index);
				$updatedPractice = new Practice(PracticeGateway::find($practice_id));
				return array('reflection_index'=>$updatedPractice->reflection_index);
			}
		}
	}
	
	static function savePracticelogTimer() {
		$practice_id = $_REQUEST['practice_id'];
		$timer_mins = $_REQUEST['timer_mins'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditPractice($practice)) {
				Session::set('action_ok', true); // action ok
				PracticeGateway::updateTimer($practice_id, $timer_mins);
				$updatedPractice = new Practice(PracticeGateway::find($practice_id));
				return array('time_spent'=>$updatedPractice->time_spent);
			}
		}
	}

	static function notifyTeacher() {
		$uid = Session::uid();
		$practice_id = $_REQUEST['practice_id'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$teacher_id = $practice->teacher_id;
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $teacher_id);
			if (UserGateway::isActive($teacher_id) && $is_link_connected) {
				Session::set('action_ok', true); // action ok
				$priority = 2;
				NotificationGateway::insert(date('Y-m-d H:i:s'), $teacher_id, $uid, 'practice', $practice_id, $priority, true, true, true);
				PracticeGateway::updateIsNotified($practice_id, true);
				$refresh = array(
					'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($uid, $practice_id)
				);
				return array('refresh'=>$refresh);
			}
		}
	}
	
	static function addComment() {
		$ref = $_REQUEST['ref'];
		$ref_id = $_REQUEST['ref_id'];
		$comment_text = trim($_REQUEST['comment_text']);
		
		$user = Login::getCurrentUser();
		if ($user->isAllowedAddCommentToRefId($ref, $ref_id)) {
			Session::set('action_ok', true); // action ok
			if ($comment_text == "") {
				return array('added'=>false);
			}
			$response = array();
			$comment_id = CommentGateway::insert($ref, $ref_id, $user->uid, $comment_text, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
			switch ($ref) {
				case 'practice':
					$practice_row = PracticeGateway::find($ref_id);
					if ($practice_row) {
						$practice = new Practice($practice_row);
						// NOTE: No notification to send; notifications for addComment are only from Teacher to Student
						
						// TODO: place this refresh in an "if practicelog" (if it's possible to determine here)
						$response['refresh'] = array(
							'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($user->uid, $practice->practice_id)
						);
					}
					break;
				case 'lesson':
					$lesson_row = LessonGateway::find($ref_id);
					if ($lesson_row) {
						$lesson = new Lesson($lesson_row);
						
						// NOTE: No notification to send; notifications for addComment are only from Teacher to Student
					}
					break;
			}
			$response['added'] = true;
			$response['html'] = Components::renderMiscComment($comment_id, $ref, $ref_id, $user->uid, false);
			return $response;
		}
	}
	
	static function addAnnotationNotification() {
		$practice_id = $_REQUEST['practice_id'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedNotifyAnnotationInPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$priority = 2;
				NotificationGateway::insert(date('Y-m-d H:i:s'), $practice->teacher_id, $user->uid, 'annotation', $practice->practice_id, $priority, true, true, true);
			}
		}
	}
	
	static function addAnnotationNotificationPracticing() {
		$practice_id = $_REQUEST['practice_id'];
		$practice_row = PracticeGateway::find($practice_id);
		if ($practice_row) {
			$practice = new Practice($practice_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedNotifyAnnotationInPractice($practice)) {
				Session::set('action_ok', true); // action ok
				$priority = 2;
				$is_sent = false;
				NotificationGateway::insert(date('Y-m-d H:i:s'), $practice->teacher_id, $user->uid, 'annotation', $practice->practice_id, $priority, true, true, $is_sent);
			}
		}
	}
	
	static function openNotification() {
		$uid = Session::uid();
		$notification_id = $_REQUEST['notification_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$notification_row = NotificationGateway::findSentToUser($notification_id, $uid);
		if ($notification_row) {
			$notification = NotificationFactory::createNotificationObject($notification_row);
			$user = Login::getCurrentUser();
			if ($notification->isGoLocationAccessibleByUser($user)) {
				Session::set('action_ok', true); // action ok
				$redirect_uri = null;
				switch ($notification->ref) {
					case 'practice_comment':
						$practiceCommentNotification = new PracticeCommentNotification($notification_row);
						$teacher_id = $practiceCommentNotification->sender_uid;
						$lesson_id = $practiceCommentNotification->lesson_id;
						$select_task_id = $practiceCommentNotification->task_id;
						$select_practice_id = $practiceCommentNotification->practice_id;
						$redirect_params = '?teacher_id='.$teacher_id.'&lesson_id='.$lesson_id.'&select_task_id='.$select_task_id.'&select_practice_id='.$select_practice_id;
						$redirect_uri = Core::cadenzaUrl('pages/student/view_lesson.php').$redirect_params;
						break;
					case 'lesson_comment':
						$lessonCommentNotification = new LessonCommentNotification($notification_row);
						$teacher_id = $lessonCommentNotification->sender_uid;
						$lesson_id = $lessonCommentNotification->lesson_id;
						$redirect_params = '?teacher_id='.$teacher_id.'&lesson_id='.$lesson_id;
						$redirect_uri = Core::cadenzaUrl('pages/student/view_lesson_overview.php').$redirect_params;
						Session::set("force_show_reflection_modal", true);
						break;
					case 'annotation':
						$annotationNotification = new AnnotationNotification($notification_row);
						$teacher_id = $annotationNotification->sender_uid;
						$lesson_id = $annotationNotification->lesson_id;
						$select_task_id = $annotationNotification->task_id;
						$select_practice_id = $annotationNotification->practice_id;
						$redirect_params = '?teacher_id='.$teacher_id.'&lesson_id='.$lesson_id.'&select_task_id='.$select_task_id.'&select_practice_id='.$select_practice_id;
						$redirect_uri = Core::cadenzaUrl('pages/student/view_lesson.php').$redirect_params;
						Session::set("force_show_annotator_modal", true);
						break;
					default:
						trigger_error('Unhandled notification ref: '.$notification_row['ref'], E_USER_ERROR);
				}
				if ($redirect_uri != null) {
					NotificationGateway::updateIsUnread($notification_id, false);
					return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
				}
			}
			elseif ($notification->ref == 'practice_comment' || $notification->ref == 'lesson_comment' || $notification->ref == 'annotation') {
				// expected problem - occurs when notification is no longer accessible.
				Session::set('action_ok', true); // action ok
				$redirect_params = ($page != null) ? '?page='.$page : '';
				$redirect_uri = Core::cadenzaUrl('pages/student/notifications.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'notification_no_longer_accessible'));
			}
		}
	}
	
	static function deleteNotification() {
		$uid = Session::uid();
		$notification_id = $_REQUEST['notification_id'];
		$notification_row = NotificationGateway::findSentToUser($notification_id, $uid);
		if ($notification_row) {
			Session::set('action_ok', true); // action ok
			NotificationGateway::delete($notification_id);
			$refresh = array(
				'navbars/student'=>Components::renderNavbarStudent($uid),
				'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid)
			);
			return array('refresh'=>$refresh);
		}
	}
	
	static function deleteAllNotifications() {
		$last_notification_id = $_REQUEST['last_notification_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$last_notification_row = NotificationGateway::findSentToUser($last_notification_id, $uid);
		if ($last_notification_row) {
			Session::set('action_ok', true); // action ok
			$keep_ref = 'user_link';
			NotificationGateway::deleteAllSentToUserExceptRefUpToNotification($uid, $keep_ref, $last_notification_id);
			$refresh = array(
				'navbars/student'=>Components::renderNavbarStudent($uid),
				'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
			);
			return array('refresh'=>$refresh);
		}
	}

	static function disconnectUser() {
		$uid = Session::uid();
		$teacher_id = $_REQUEST['connected_user_id'];
		$connected_user_link_row = UserLinkGateway::findConnectedByStudentTeacher($uid, $teacher_id);
		if (UserGateway::isActive($teacher_id) && $connected_user_link_row) {
			Session::set('action_ok', true); // action ok
			$user_link_id = $connected_user_link_row['user_link_id'];
			UserLinkGateway::updateStatus($user_link_id, 'disconnected-inactive', date('Y-m-d H:i:s'));
			$redirect_uri = Core::cadenzaUrl('pages/student/index.php');
			return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
		}
	}
	
	static function reportUser() {
		$uid = Session::uid();
		$teacher_id = $_REQUEST['report_who_uid'];
		$report_text = $_REQUEST['report_text'];
		
		$connected_user_link_row = UserLinkGateway::findConnectedByStudentTeacher($uid, $teacher_id);
		$has_text = (trim($report_text) != '');
		if (UserGateway::isActive($teacher_id) && $connected_user_link_row && $has_text) {
			Session::set('action_ok', true); // action ok
			AdminReportGateway::insertNewIssueReport($uid, $teacher_id, $report_text, date('Y-m-d H:i:s'));
		}
	}
	
}
