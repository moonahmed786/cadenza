<?php
class TeacherActions extends UserActions {
	
	static function loadNavbar() {
		// teacher always able to see their navbar
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		return array('html'=>Components::renderNavbarTeacher($uid));
	}
	
	static function uploadAttachmentToTask() {
		$category = 'attachment';
		$task_id = isset($_REQUEST['task_id']) ? $_REQUEST['task_id'] : null;
		$row_id = $_REQUEST['row_id'];
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedUploadAttachmentToTask($task)) {
				Session::set('action_ok', true); // action ok
				$handler = new Fileupload_UploadHandler_Cadenza();
				$handler->initiate_upload($task->lesson_id, $task_id, null, $category, $user->uid);
				$response = $handler->get_response();
				$response['row_id'] = $row_id;
				return $response;
			}
			elseif ($user->isAllowedViewTask($task)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$task->student_id.'&lesson_id='.$task->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
            }
		}
	}
	
	static function sendInvite() {
		// teacher can always *attempt* to invite any email address, and the invitation may or may not be sent
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$email = $_REQUEST['email'];
		$email_normalized = UserGateway::normalizeEmail($email);
		$student = UserGateway::findStudentByEmailNormalized($email_normalized);
		if (!$student) {
			return array('found'=>false, 'invited'=>false, 'connected'=>false);
		}
		else {
			$student_id = $student['uid'];
			$teacher_links = UserLinkGateway::findAllByTeacher($uid);
			$user_link_id = null;
			$status = null;
			foreach ($teacher_links as $user_link) {
				if ($user_link['student_id'] == $student_id) {
					if ($user_link['status'] == 'connected') {
						// Student is already on the teacher's list of students
						return array('found'=>true, 'invited'=>false, 'connected'=>true);
					}
					elseif ($user_link['status'] == 'pending' || $user_link['status'] == 'pending-inactive') {
						// Student is already on the teacher's list of pending invitations
						return array('found'=>true, 'invited'=>false, 'connected'=>false);
					}
					else {
						$user_link_id = $user_link['user_link_id'];
						$status = $user_link['status'];
						break;
					}
				}
			}
			// Add student to teacher's list of pending invitations
			if ($user_link_id != null) {
				if ($status == 'rejected') {
					UserLinkGateway::updateStatus($user_link_id, 'pending', date('Y-m-d H:i:s'));
				}
				elseif ($status == 'rejected-inactive' || $status == 'disconnected-inactive') {
					UserLinkGateway::updateStatus($user_link_id, 'pending-inactive', date('Y-m-d H:i:s'));
				}
			}
			else {
				$user_link_id = UserLinkGateway::insert($student_id, $uid, 'pending', date('Y-m-d H:i:s'), null);
			}
			// Insert/update notification
			$notification_id = null;
			$notification_rows = NotificationGateway::findAllSentInRefId('user_link', $user_link_id);
			foreach ($notification_rows as $row) {
				$notification_id = $row['notification_id'];
				NotificationGateway::updateDate($notification_id, date('Y-m-d H:i:s'));
			}
			if ($notification_id == null) {
				$priority = 1;
				NotificationGateway::insert(date('Y-m-d H:i:s'), $student_id, $uid, 'user_link', $user_link_id, $priority, true, null, true);
			}
			$refresh = array(
				'tables/list_of_invites'=>Components::renderTableListOfInvites($uid)
			);
			return array('found'=>true, 'invited'=>true, 'connected'=>false, 'refresh'=>$refresh);
		}
	}
	static function deleteInvite() {
		$uid = Session::uid();
		$student_id = $_REQUEST['student_id'];
		$invite_row = UserLinkGateway::findInviteByStudentTeacher($student_id, $uid);
		if ($invite_row) {
			Session::set('action_ok', true); // action ok
			$user_link_id = $invite_row['user_link_id'];
			if ($invite_row['status'] == 'pending-inactive' || $invite_row['status'] == 'rejected-inactive') {
				UserLinkGateway::updateStatus($user_link_id, 'disconnected-inactive', date('Y-m-d H:i:s'));
			}
			else {
				UserLinkGateway::delete($user_link_id);
			}
			$notification_rows = NotificationGateway::findAllSentInRefId('user_link', $user_link_id);
			foreach ($notification_rows as $notification_row) {
				NotificationGateway::delete($notification_row['notification_id']);
			}
			$refresh = array(
				'tables/list_of_invites'=>Components::renderTableListOfInvites($uid)
			);
			return array('refresh'=>$refresh);
		}
	}
	
	static function getNotesOnStudent() {
		$uid = Session::uid();
		$student_id = $_REQUEST['student_id'];
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		if (UserGateway::isActive($student_id) && $is_link_connected) {
			Session::set('action_ok', true); // action ok
			$teacher_note = TeacherNotesGateway::findByTeacherStudent($uid, $student_id);
			$notes_on_student = $teacher_note ? $teacher_note['notes_on_student'] : '';
			return array('notesOnStudent'=>$notes_on_student);
		}
	}
	
	static function saveNotesOnStudent() {
		$uid = Session::uid();
		$student_id = $_REQUEST['student_id'];
		$notes_on_student = $_REQUEST['notes_on_student'];
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		if (UserGateway::isActive($student_id) && $is_link_connected) {
			Session::set('action_ok', true); // action ok
			$teacher_note = TeacherNotesGateway::findByTeacherStudent($uid, $student_id);
			if (!$teacher_note) {
				TeacherNotesGateway::insert($uid, $student_id, $notes_on_student);
			}
			else {
				TeacherNotesGateway::update($teacher_note['teacher_note_id'], $uid, $student_id, $notes_on_student);
			}
		}
	}
	
	static function createNewLesson() {
		$uid = Session::uid();
		$student_id = $_REQUEST['student_id'];
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		if (UserGateway::isActive($student_id) && $is_link_connected) {
			Session::set('action_ok', true); // action ok
			$lesson_id = LessonGateway::insert($student_id, Session::uid(), false, date('Y-m-d H:i:s'));
			$task_id = TaskGateway::insertBlank($lesson_id, date('Y-m-d H:i:s'));
			$redirect_uri = Core::cadenzaUrl('pages/teacher/edit_lesson.php').'?student_id='.$student_id.'&lesson_id='.$lesson_id.'&select_task_id='.$task_id;
			return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
		}
	}
	
	static function editLesson() {
		$student_id = $_REQUEST['student_id'];
		$lesson_id = $_REQUEST['lesson_id'];
		$task_id = isset($_REQUEST['task_id']) ? $_REQUEST['task_id'] : null;
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditLesson($lesson)) {
				Session::set('action_ok', true); // action ok
				if ($task_id == null) {
					$redirect_uri = Core::cadenzaUrl('pages/teacher/edit_lesson.php').'?student_id='.$student_id.'&lesson_id='.$lesson_id;
				}
				else {
					$redirect_uri = Core::cadenzaUrl('pages/teacher/edit_lesson.php').'?student_id='.$student_id.'&lesson_id='.$lesson_id.'&select_task_id='.$task_id;
				}
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
			}
			elseif ($user->isAllowedViewLesson($lesson)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$lesson->student_id.'&lesson_id='.$lesson->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
            }
		}
	}
	
	static function selectTask() {
		$task_id = $_REQUEST['task_id'];
		$edit = $_REQUEST['edit'];
		$task_form_data = isset($_REQUEST['task_form_data']) ? $_REQUEST['task_form_data'] : null;
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			if (($edit && $user->isAllowedEditTask($task)) || (!$edit && $user->isAllowedViewTask($task))) {
				if ($edit && $task_form_data != null) {
					$selected_task_id = $task_form_data['task_id'];
					$title = trim($task_form_data['title']);
					$target = $task_form_data['targetnum'];
					$category = $task_form_data['category'];
					$category_other = trim($task_form_data['category_other']);
					$description = trim($task_form_data['description']);
					$checklist = $task_form_data['checklist'];
					$selected_task_row = TaskGateway::find($selected_task_id);
					if ($selected_task_row) {
						$selectedTask = new Task($selected_task_row);
						if ($user->isAllowedEditTask($selectedTask)) {
							if (ActionHelper::isTeacherTaskFormBlank($user, $selected_task_id, $title, $target, $category, $category_other, $description, $checklist)) {
								trigger_error('Task cannot be blank.', E_USER_ERROR);
							}
							Session::set('action_ok', true); // action ok
							$savedTask = ActionHelper::saveTeacherTaskForm($user, $selectedTask, $title, $target, $category, $category_other, $description, $checklist);
						}
					}
				}
				elseif (!$edit) {
					Session::set('action_ok', true); // action ok
				}
				$refresh = array();
				$refresh['tables/task_selector'] = Components::renderTableTaskSelector($user->uid, $task_id, false, $edit);
				if ($edit) {
					$refresh['actionbars/teacher'] = Components::renderActionbarTeacher($user->uid, $task->student_id, $task->lesson_id, $edit);
					$refresh['forms/assign_task'] = Components::renderFormAssignTask($user->uid, $task_id);
				}
				else {
					$refresh['selectedtask/teacher'] = Components::renderSelectedTaskTeacher($user->uid, $task->student_id, $task_id);
				}
				return array('task_id'=>$task_id, 'refresh'=>$refresh);
			}
			elseif ($user->isAllowedViewTask($task)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$task->student_id.'&lesson_id='.$task->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
            }
		}
	}
	
	static function deleteTask() {
		$task_id = $_REQUEST['task_id'];
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedDeleteTask($task)) {
				Session::set('action_ok', true); // action ok
				$student_id = $task->student_id;
				TaskGateway::delete($task->task_id);
				$task_attachments = UserFileGateway::findAllAttachmentsInTask($task->task_id);
				$handler = new Fileupload_UploadHandler_Cadenza();
				foreach ($task_attachments as $attachment) {
					$handler->initiate_delete($task->lesson_id, $task->task_id, $attachment['practice_id'], 'attachment', $attachment['uid'], $attachment['file_id'], $attachment['filename']);
				}
				$first_task_row = TaskGateway::findFirstInLesson($task->lesson_id);
				if ($first_task_row) {
					$first_task = new Task($first_task_row);
					$refresh = array(
						'actionbars/teacher'=>Components::renderActionbarTeacher($user->uid, $first_task->student_id, $first_task->lesson_id, true),
						'tables/task_selector'=>Components::renderTableTaskSelector($user->uid, $first_task->task_id, false, true),
						'forms/assign_task'=>Components::renderFormAssignTask($user->uid, $first_task->task_id)
					);
					return array('task_id'=>$first_task->task_id, 'refresh'=>$refresh);
				}
				else {
					// Lesson cannot exist without any tasks
					LessonGateway::delete($task->lesson_id);
					$last_lesson_row = LessonGateway::findLastSaved($student_id, $user->uid);
					$last_lesson_id = $last_lesson_row ? $last_lesson_row['lesson_id'] : null;
					UserLinkGateway::updateLastLessonByStudentTeacher($student_id, $user->uid, $last_lesson_id);
					$redirect_uri = Core::cadenzaUrl('pages/teacher/lessons.php').'?student_id='.$student_id;
					return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
				}
			}
			elseif ($user->isAllowedViewTask($task)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$task->student_id.'&lesson_id='.$task->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
            }
		}
	}
	
	static function saveTask() {
		$task_form_data = $_REQUEST['task_form_data'];
		$task_id = $task_form_data['task_id'];
		$title = trim($task_form_data['title']);
		$target = $task_form_data['targetnum'];
		$category = $task_form_data['category'];
		$category_other = trim($task_form_data['category_other']);
		$description = trim($task_form_data['description']);
		$checklist = $task_form_data['checklist'];
		$task_row = TaskGateway::find($task_id);
		if ($task_row) {
			$task = new Task($task_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditTask($task)) {
				if (ActionHelper::isTeacherTaskFormBlank($user, $task_id, $title, $target, $category, $category_other, $description, $checklist)) {
					trigger_error('Task cannot be blank.', E_USER_ERROR);
				}
				Session::set('action_ok', true); // action ok
				$savedTask = ActionHelper::saveTeacherTaskForm($user, $task, $title, $target, $category, $category_other, $description, $checklist);
				$redirect_params = '?student_id='.$savedTask->student_id.'&lesson_id='.$savedTask->lesson_id.'&select_task_id='.$savedTask->task_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
			}
			elseif ($user->isAllowedViewTask($task)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$task->student_id.'&lesson_id='.$task->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
            }
		}
	}
	
	static function createNewTask() {
		$lesson_id = $_REQUEST['lesson_id'];
		$edit = $_REQUEST['edit'];
		$task_form_data = isset($_REQUEST['task_form_data']) ? $_REQUEST['task_form_data'] : null;
		$lesson_row = LessonGateway::find($lesson_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedAddTaskToLesson($lesson)) {
				if ($edit && $task_form_data != null) {
					$task_id = $task_form_data['task_id'];
					$title = trim($task_form_data['title']);
					$target = $task_form_data['targetnum'];
					$category = $task_form_data['category'];
					$category_other = trim($task_form_data['category_other']);
					$description = trim($task_form_data['description']);
					$checklist = $task_form_data['checklist'];
					$task_row = TaskGateway::find($task_id);
					if ($task_row) {
						$task = new Task($task_row);
						$user = Login::getCurrentUser();
						if ($user->isAllowedEditTask($task)) {
							if (ActionHelper::isTeacherTaskFormBlank($user, $task_id, $title, $target, $category, $category_other, $description, $checklist)) {
								trigger_error('Task cannot be blank.', E_USER_ERROR);
							}
							Session::set('action_ok', true); // action ok
							$savedTask = ActionHelper::saveTeacherTaskForm($user, $task, $title, $target, $category, $category_other, $description, $checklist);
							$new_task_id = TaskGateway::insertBlank($lesson->lesson_id, date('Y-m-d H:i:s'));
							$refresh = array(
								'actionbars/teacher'=>Components::renderActionbarTeacher($user->uid, $task->student_id, $task->lesson_id, true),
								'tables/task_selector'=>Components::renderTableTaskSelector($user->uid, $new_task_id, false, true),
								'forms/assign_task'=>Components::renderFormAssignTask($user->uid, $new_task_id)
							);
							return array('task_id'=>$new_task_id, 'refresh'=>$refresh);
						}
						elseif ($user->isAllowedViewTask($task)) {
			                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
							Session::set('action_ok', true); // action ok
							$redirect_params = '?student_id='.$task->student_id.'&lesson_id='.$task->lesson_id;
							$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
							return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
			            }
					}
				}
				elseif (!$edit) {
					Session::set('action_ok', true); // action ok
					$new_task_id = TaskGateway::insertBlank($lesson->lesson_id, date('Y-m-d H:i:s'));
					$redirect_uri = Core::cadenzaUrl('pages/teacher/edit_lesson.php').'?student_id='.$lesson->student_id.'&lesson_id='.$lesson->lesson_id.'&select_task_id='.$new_task_id;
					return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
				}
			}
			elseif ($user->isAllowedViewLesson($lesson)) {
                // expected problem - occurs when teacher is actively editing a lesson while student has completed practicing a task.
				Session::set('action_ok', true); // action ok
				$redirect_params = '?student_id='.$lesson->student_id.'&lesson_id='.$lesson->lesson_id;
				$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'edit_lesson_has_practices'), 'disable_unsafe_navigation'=>true);
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
				$modalData = array();
				$modalData['isEditReflection'] = false;
				$modalData['canEditReflection'] = false;
				$modalData['lessonId'] = $lesson->lesson_id;
				$modalData['lessonTitle'] = date('M j, Y', strtotime($lesson->created_date));
				$modalData['lessonTargets'] = $lesson->count_saved_tasks_with_target_met.'/'.count($lesson->saved_tasks);
				$modalData['lessonTimeSpent'] = $lesson->time_spent;
				$modalData['reflectionIndex'] = $reflection_index;
				$modalData['reflectionText'] = $reflection_text;
				if ($modalData['reflectionText'] == "") {
					$modalData['reflectionNoText'] = Language::getText('hint', 'no_reflection_text_teacher').'.';
				}
				$modalData['reflectionPrompt'] = $reflection_prompt;
				
				$modalData['commentsHTML'] = Components::renderMiscComments($user->uid, "lesson", $lesson->lesson_id, false);
				
				return array('modalData'=>$modalData);
			}
		}
	}
	
	static function getGoalsModalData() {
		$student_id = $_REQUEST['linked_user_id'];
		$student_row = UserGateway::find($student_id);
		if ($student_row) {
			$student = new User($student_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedViewStudentGoalsWithTeacher($student, $user)) {
				Session::set('action_ok', true); // action ok
				$student_goal_rows = StudentGoalGateway::findAllByStudentTeacher($student->uid, $user->uid, array('orderby'=>'created_date DESC'));
				$student_goals = array();
				foreach ($student_goal_rows as $student_goal_row) {
					$student_goals[] = new StudentGoal($student_goal_row);
				}
				$modalData = array();
				$modalData['isEditGoals'] = false;
				$modalData['studentGoals'] = $student_goals;
				$modalData['noGoalsStrings'] = array(
					'all' => Language::getText('hint', 'no_goals_set_teacher').'.',
					'incomplete' => Language::getText('hint', 'no_goals_incomplete_teacher').'.',
					'completed' => Language::getText('hint', 'no_goals_completed_teacher').'.'
				);
				return array('modalData'=>$modalData);
			}
		}
	}
	
	static function paginationSortableListOfLessons() {
		$student_id = $_REQUEST['student_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$uid = Session::uid();
		$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $uid);
		if (UserGateway::isActive($student_id) && $is_link_connected) {
			Session::set('action_ok', true); // action ok
			$refresh = array(
				'tables/list_of_lessons'=>Components::renderTableListOfLessonsTeacher($uid, $student_id, $page, $order_by, $order_direction)
			);
			return array('refresh'=>$refresh);
		}
	}
	
	static function paginationSortableListOfStudents() {
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$uid = Session::uid();
		$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
		$students_user_status = 'active';
		Session::set('action_ok', true); // action ok
		$refresh = array(
			'tables/list_of_students'=>Components::renderTableListOfStudents($uid, $page, $order_by, $order_direction)
		);
		return array('refresh'=>$refresh);
	}
	
	static function paginationListOfNotifications() {
		// teacher is always allowed to view their own notifications
		Session::set('action_ok', true); // action ok
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$refresh = array(
			'navbars/teacher'=>Components::renderNavbarTeacher($uid),
			'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
		);
		return array('refresh'=>$refresh);
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
			$comment_id = CommentGateway::insert($ref, $ref_id, $user->uid, $comment_text, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
			switch ($ref) {
				case 'practice':
					$practice_row = PracticeGateway::find($ref_id);
					if ($practice_row) {
						$practice = new Practice($practice_row);
						$priority = 2;
						NotificationGateway::insert(date('Y-m-d H:i:s'), $practice->student_id, $user->uid, 'practice_comment', $comment_id, $priority, true, true, true);
					}
					break;
				case 'lesson':
					$lesson_row = LessonGateway::find($ref_id);
					if ($lesson_row) {
						$lesson = new Lesson($lesson_row);
						$priority = 2;
						NotificationGateway::insert(date('Y-m-d H:i:s'), $lesson->student_id, $user->uid, 'lesson_comment', $comment_id, $priority, true, true, true);
					}
					break;
			}
			return array(
				'added'=>true, 'html'=>Components::renderMiscComment($comment_id, $ref, $ref_id, $user->uid, false)
			);
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
				NotificationGateway::insert(date('Y-m-d H:i:s'), $practice->student_id, $user->uid, 'annotation', $practice->practice_id, $priority, true, true, true);
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
					case 'practice':
						$practiceNotification = new PracticeNotification($notification_row);
						$student_id = $practiceNotification->sender_uid;
						$lesson_id = $practiceNotification->lesson_id;
						$select_task_id = $practiceNotification->task_id;
						$select_practice_id = $practiceNotification->practice_id;
						$redirect_params = '?student_id='.$student_id.'&lesson_id='.$lesson_id.'&select_task_id='.$select_task_id.'&select_practice_id='.$select_practice_id;
						$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
						break;
					case 'annotation':
						$annotationNotification = new AnnotationNotification($notification_row);
						$student_id = $annotationNotification->sender_uid;
						$lesson_id = $annotationNotification->lesson_id;
						$select_task_id = $annotationNotification->task_id;
						$select_practice_id = $annotationNotification->practice_id;
						$redirect_params = '?student_id='.$student_id.'&lesson_id='.$lesson_id.'&select_task_id='.$select_task_id.'&select_practice_id='.$select_practice_id;
						$redirect_uri = Core::cadenzaUrl('pages/teacher/view_lesson.php').$redirect_params;
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
			elseif ($notification->ref == 'practice' || $notification->ref == 'annotation') {
				// expected problem - occurs when notification is no longer accessible.
				Session::set('action_ok', true); // action ok
				$redirect_params = ($page != null) ? '?page='.$page : '';
				$redirect_uri = Core::cadenzaUrl('pages/teacher/notifications.php').$redirect_params;
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'notification_no_longer_accessible'));
			}
		}
	}
	
	static function deleteNotification() {
		$notification_id = $_REQUEST['notification_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$uid = Session::uid();
		$notification_row = NotificationGateway::findSentToUser($notification_id, $uid);
		if ($notification_row) {
			Session::set('action_ok', true); // action ok
			NotificationGateway::delete($notification_id);
			$refresh = array(
				'navbars/teacher'=>Components::renderNavbarTeacher($uid),
				'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
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
				'navbars/teacher'=>Components::renderNavbarTeacher($uid),
				'tables/list_of_notifications'=>Components::renderTableListOfNotifications($uid, $page)
			);
			return array('refresh'=>$refresh);
		}
	}

	static function disconnectUser() {
		$uid = Session::uid();
		$student_id = $_REQUEST['connected_user_id'];
		$connected_user_link_row = UserLinkGateway::findConnectedByStudentTeacher($student_id, $uid);
		if (UserGateway::isActive($student_id) && $connected_user_link_row) {
			Session::set('action_ok', true); // action ok
			$user_link_id = $connected_user_link_row['user_link_id'];
			UserLinkGateway::updateStatus($user_link_id, 'disconnected-inactive', date('Y-m-d H:i:s'));
			$redirect_uri = Core::cadenzaUrl('pages/teacher/index.php');
			return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
		}
	}
	
	static function reportUser() {
		$uid = Session::uid();
		$student_id = $_REQUEST['report_who_uid'];
		$report_text = $_REQUEST['report_text'];
		
		$connected_user_link_row = UserLinkGateway::findConnectedByStudentTeacher($student_id, $uid);
		$has_text = (trim($report_text) != '');
		if (UserGateway::isActive($student_id) && $connected_user_link_row && $has_text) {
			Session::set('action_ok', true); // action ok
			AdminReportGateway::insertNewIssueReport($uid, $student_id, $report_text, date('Y-m-d H:i:s'));
		}
	}
	
	static function getStudentSearchData() {
		// teacher always able to search
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$studentSearchData = array();
		$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
		$students_user_status = 'active';
		$student_rows = UserGateway::findAllStudentsWithStatusLinkedToTeacher($uid, $students_user_status, $link_statuses_connected, array('orderby'=>'g_name ASC'));
		foreach ($student_rows as $student_row) {
			$picture = ($student_row['g_picture'] != null) ? $student_row['g_picture'] : Core::cadenzaWebPath('assets/images/default_profile_picture.png');
			$studentSearchData[] = array(
				'uid'=>$student_row['uid'],
				'email'=>$student_row['g_email'],
				'name'=>$student_row['g_name'],
				'picture'=>$picture
			);
		}
		return array('studentSearchData'=>$studentSearchData);
	}
	
	static function getTaskTitleAutocompleteData() {
		// autocomplete data is not associated with any particular task or student
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$autocompleteData = array();
		$orderby_arr = array('autocomplete_date DESC', 'autocomplete_text ASC');
		$autocomplete_rows = AutocompleteGateway::findAllTaskTitlesByUid($uid, array('orderby'=>$orderby_arr));
		foreach ($autocomplete_rows as $autocomplete_row) {
			$autocompleteData[] = array(
				'text'=>$autocomplete_row['autocomplete_text']
			);
		}
		return array('autocompleteData'=>$autocompleteData);
	}
	
	static function getTaskCategoryOtherAutocompleteData() {
		// autocomplete data is not associated with any particular task or student
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$autocompleteData = array();
		$orderby_arr = array('autocomplete_date DESC', 'autocomplete_text ASC');
		$autocomplete_rows = AutocompleteGateway::findAllTaskCategoryOthersByUid($uid, array('orderby'=>$orderby_arr));
		foreach ($autocomplete_rows as $autocomplete_row) {
			$autocompleteData[] = array(
				'text'=>$autocomplete_row['autocomplete_text']
			);
		}
		return array('autocompleteData'=>$autocompleteData);
	}
	
	static function getTaskChecklistItemAutocompleteData() {
		// autocomplete data is not associated with any particular task or student
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$autocompleteData = array();
		$orderby_arr = array('autocomplete_date DESC', 'autocomplete_text ASC');
		$autocomplete_rows = AutocompleteGateway::findAllTaskChecklistItemsByUid($uid, array('orderby'=>$orderby_arr));
		foreach ($autocomplete_rows as $autocomplete_row) {
			$autocompleteData[] = array(
				'text'=>$autocomplete_row['autocomplete_text']
			);
		}
		return array('autocompleteData'=>$autocompleteData);
	}

}
