<?php
class User {
	
	var $uid;
	var $email;
	var $email_normalized;
	var $name;
	var $first_name;
	var $last_name;
	var $picture;
	var $refresh_token;
	var $user_type;
	var $last_login;
	var $status;
	var $status_date;
	var $status_date_local;
	var $created_date;
	var $created_date_local;
	var $notifications;
	var $new_notifications;
	var $unread_notifications;
	var $last_notification;
	var $count_new_notifications;
	var $count_unread_notifications;
	var $count_invite_notifications;
	var $reward_points;
	var $reward_badges;
	var $reward_badgeprogress_current;
	var $reward_badgeprogress_next;
	
	function __construct($row) {
		$this->uid = $row['uid'];
		$this->email = $row['g_email'];
		$this->email_normalized = $row['email_normalized'];
		$this->name = $row['g_name'];
		$this->first_name = $row['g_given_name'];
		$this->last_name = $row['g_family_name'];
		$this->picture = $row['g_picture'];
		$this->refresh_token = $row['g_refresh_token'];
		$this->user_type = $row['user_type'];
		
		$interval = date_diff(new DateTime(), new DateTime($row['last_login']));
		$totalDays = $interval->format('%a');
		if ($totalDays == 0) {
			$totalHours = $interval->format('%h'); // number of hours is total because interval is less than 1 day
			if ($totalHours == 0) {
				$this->last_login = Language::getText('datetime', 'less_than_hour_ago');
			}
			else {
				$this->last_login = $totalHours.' '.($totalHours == 1 ? Language::getText('datetime', 'hour_ago') : Language::getText('datetime', 'hours_ago'));
			}
		}
		else {
			$this->last_login = $totalDays.' '.($totalDays == 1 ? Language::getText('datetime', 'day_ago') : Language::getText('datetime', 'days_ago'));
		}
		
		$this->status = $row['status'];
		$this->status_date = $row['status_date'];
		$this->status_date_local = Core::utcToLocal($row['status_date']);
		$this->created_date = $row['created_date'];
		$this->created_date_local = Core::utcToLocal($row['created_date']);
		
		// Find all notifications to user
		$orderby_arr = array('priority ASC', 'notification_date DESC');
		$notification_rows = NotificationGateway::findAllSentToUser($row['uid'], array('orderby'=>$orderby_arr));
		// Instantiate the notifications
		$this->notifications = array();
		$this->new_notifications = array();
		$this->unread_notifications = array();
		$this->count_invite_notifications = 0;
		$this->last_notification = null;
		foreach ($notification_rows as $notification_row) {
			$notification = NotificationFactory::createNotificationObject($notification_row);
			$this->notifications[] = $notification;
			if ($notification->is_new) {
				$this->new_notifications[] = $notification;
			}
			if ($notification->is_unread) {
				$this->unread_notifications[] = $notification;
			}
			if ($notification->ref == 'user_link') {
				$this->count_invite_notifications++;
			}
			if ($this->last_notification == null || $notification->notification_id > $this->last_notification->notification_id) {
				$this->last_notification = $notification;
			}
		}
		// Count new/unread notifications
		$this->count_new_notifications = count($this->new_notifications);
		$this->count_unread_notifications = count($this->unread_notifications);
		
		// Rewards
		if ($this->user_type == 'student') {
			// Find all reward events
			$student_reward_rows = StudentRewardGateway::findAllByStudent($row['uid']);
			// Sum the reward points
			$this->reward_points = 0;
			foreach ($student_reward_rows as $reward_row) {
				$this->reward_points += $reward_row['reward_points'];
			}
			// Determine the number of badges
			$this->reward_badges = StudentRewardGateway::convertPointsToBadges($this->reward_points);
			// Determine the progress of the current badge
			$min_points_current_badge = StudentRewardGateway::convertBadgesToMinPoints($this->reward_badges);
			$min_points_next_badge = StudentRewardGateway::convertBadgesToMinPoints($this->reward_badges + 1);
			$this->reward_badgeprogress_current = $this->reward_points - $min_points_current_badge;
			$this->reward_badgeprogress_next = $min_points_next_badge - $min_points_current_badge;
		}
		else {
			$this->reward_points = null;
			$this->reward_badges = null;
			$this->reward_badgeprogress_current = null;
			$this->reward_badgeprogress_next = null;
		}
	}

	function isLinkConnectedAndBothActive($uid) {
		if ($this->user_type == 'student') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $uid);
			$is_student_active = UserGateway::isActive($this->uid);
			$is_teacher_active = UserGateway::isActive($uid);
			return ($is_link_connected && $is_student_active && $is_teacher_active);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($uid, $this->uid);
			$is_student_active = UserGateway::isActive($uid);
			$is_teacher_active = UserGateway::isActive($this->uid);
			return ($is_link_connected && $is_student_active && $is_teacher_active);
		}
		return false;
	}

	// ------------------------------------------------------------------------
	// Lesson Permissions
	// ------------------------------------------------------------------------

	function isAllowedViewLesson(Lesson $lesson) {
		if ($this->user_type == 'student') {
			return (!UserGateway::isBlocked($lesson->teacher_id) && $this->uid == $lesson->student_id);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($lesson->student_id, $this->uid);
			return (UserGateway::isActive($lesson->student_id) && $is_link_connected && $this->uid == $lesson->teacher_id);
		}
		return false;
	}
	function isAllowedEditLesson(Lesson $lesson) {
		// IMPORTANT: Practicing a task or editing a practice log entry DOES NOT COUNT as "edit lesson"
		if ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($lesson->student_id, $this->uid);
			$is_latest_or_unsaved = ($lesson->is_latest_saved_lesson || !$lesson->is_saved);
			$has_practices = $lesson->has_saved_tasks_with_practices;
			return (UserGateway::isActive($lesson->student_id) && $is_link_connected && $is_latest_or_unsaved && !$has_practices && $this->uid == $lesson->teacher_id);
		}
		return false;
	}
	function isAllowedReflectOnLesson(Lesson $lesson) {
		if ($this->user_type == 'student') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $lesson->teacher_id);
			return (UserGateway::isActive($lesson->teacher_id) && $is_link_connected && $this->uid == $lesson->student_id);
		}
		return false;
	}
	function isAllowedAddTaskToLesson(Lesson $lesson) {
		return $this->isAllowedEditLesson($lesson);
	}
	
	// ------------------------------------------------------------------------
	// Task Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedViewTask(Task $task) {
		if ($this->user_type == 'student') {
			$is_lesson_saved = LessonGateway::isSaved($task->lesson_id);
			$is_task_saved = $task->is_saved;
			return (!UserGateway::isBlocked($task->teacher_id) && $is_lesson_saved && $is_task_saved && $this->uid == $task->student_id);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($task->student_id, $this->uid);
			$is_lesson_saved = LessonGateway::isSaved($task->lesson_id);
			$is_task_saved = $task->is_saved;
			return (UserGateway::isActive($task->student_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $task->teacher_id);
		}
		return false;
	}
	function isAllowedEditTask(Task $task) {
		// IMPORTANT: Practicing a task or editing a practice log entry DOES NOT COUNT as "edit task"
		$lesson_row = LessonGateway::findByTask($task->task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			if ($this->user_type == 'teacher') {
				$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($task->student_id, $this->uid);
				$lesson_is_latest_or_unsaved = ($lesson->is_latest_saved_lesson || !$lesson->is_saved);
				$lesson_has_practices = $lesson->has_saved_tasks_with_practices;
				return (UserGateway::isActive($task->student_id) && $is_link_connected && $lesson_is_latest_or_unsaved && !$lesson_has_practices && $this->uid == $task->teacher_id);
			}
		}
		return false;
	}
	function isAllowedPracticeTask(Task $task) {
		$lesson_row = LessonGateway::findByTask($task->task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			if ($this->user_type == 'student') {
				$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $task->teacher_id);
				$lesson_is_latest_saved = $lesson->is_latest_saved_lesson;
				$is_task_saved = $task->is_saved;
				return (UserGateway::isActive($task->teacher_id) && $is_link_connected && $lesson_is_latest_saved && $is_task_saved && $this->uid == $task->student_id);
			}
		}
		return false;
	}
	function isAllowedPracticeAnyTasksAssignedByUser(User $user) {
		if ($this->user_type == 'student' && $user->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $user->uid);
			$lesson_saved_rows = LessonGateway::findAllSavedByStudentTeacher($this->uid, $user->uid);
			return (UserGateway::isActive($user->uid) && $is_link_connected && count($lesson_rows) > 0);
		}
		return false;
	}
	function isAllowedDeleteTask(Task $task) {
		$lesson_row = LessonGateway::findByTask($task->task_id);
		if ($lesson_row) {
			$lesson = new Lesson($lesson_row);
			if ($this->user_type == 'teacher') {
				$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($task->student_id, $this->uid);
				$lesson_is_latest_or_unsaved = ($lesson->is_latest_saved_lesson || !$lesson->is_saved);
				$lesson_has_practices = $lesson->has_saved_tasks_with_practices;
				return (UserGateway::isActive($task->student_id) && $is_link_connected && $lesson_is_latest_or_unsaved && !$lesson_has_practices && $this->uid == $task->teacher_id);
			}
		}
		return false;
	}
	
	// ------------------------------------------------------------------------
	// Practice Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedViewPractice(Practice $practice) {
		if ($this->user_type == 'student') {
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = TaskGateway::isSaved($practice->task_id);
			return (!UserGateway::isBlocked($practice->teacher_id) && $is_lesson_saved && $is_task_saved && $this->uid == $practice->student_id);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($practice->student_id, $this->uid);
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = TaskGateway::isSaved($practice->task_id);
			return (UserGateway::isActive($practice->student_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->teacher_id);
		}
		return false;
	}
	function isAllowedEditPractice(Practice $practice) {
		if ($this->user_type == 'student') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $practice->teacher_id);
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = TaskGateway::isSaved($practice->task_id);
			return (UserGateway::isActive($practice->teacher_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->student_id);
		}
		return false;
	}
	function isAllowedSavePractice(Practice $practice) {
		// Corresponds to clicking the "Save Practice" button after "Start Practice"
		$task_row = TaskGateway::find($practice->task_id);
		if ($task_row && $this->user_type == 'student') {
			$task = new Task($task_row);
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $practice->teacher_id);
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = $task->is_saved;
			$is_task_modified = ((new DateTime($task->modified_date)) > (new DateTime($practice->created_date)));
			return (UserGateway::isActive($practice->teacher_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && !$is_task_modified && $this->uid == $practice->student_id);
		}
		return false;
	}
	
	// ------------------------------------------------------------------------
	// Attachment Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedViewAttachment(Attachment $attachment) {
		if ($attachment->is_task_attachment) {
			$task = new Task(TaskGateway::find($attachment->task_id));
			// note: edit task may still be true even if view task is false, if it's a teacher creating a new lesson/task
			return ($this->isAllowedViewTask($task) || $this->isAllowedEditTask($task));
		}
		elseif ($attachment->is_practice_attachment) {
			$practice = new Practice(PracticeGateway::find($attachment->practice_id));
			return $this->isAllowedViewPractice($practice);
		}
		return false;
	}
	function isAllowedUploadAttachmentToTask(Task $task) {
		return $this->isAllowedEditTask($task);
	}
	function isAllowedUploadAttachmentToPractice(Practice $practice) {
		return $this->isAllowedEditPractice($practice);
	}
	function isAllowedDeleteAttachment(Attachment $attachment) {
		if ($this->uid == $attachment->uid) {
			if ($attachment->is_task_attachment) {
				$task = new Task(TaskGateway::find($attachment->task_id));
				return $this->isAllowedEditTask($task);
			}
			elseif ($attachment->is_practice_attachment) {
				$practice = new Practice(PracticeGateway::find($attachment->practice_id));
				return $this->isAllowedEditPractice($practice);
			}
		}
		return false;
	}
	
	// ------------------------------------------------------------------------
	// Annotator Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedViewAnnotatorInPractice(Practice $practice) {
		return ($this->isAllowedViewPractice($practice) && $practice->has_annotator);
	}
	function isAllowedAddAnnotatorToPractice(Practice $practice) {
		if ($this->user_type == 'student') {
			return ($this->isAllowedEditPractice($practice) && !$practice->has_annotator);
		}
		return false;
	}
	function isAllowedRemoveAnnotatorFromPractice(Practice $practice) {
		if ($this->user_type == 'student') {
			return ($this->isAllowedEditPractice($practice) && $practice->has_annotator);
		}
		return false;
	}
	function isAllowedNotifyAnnotationInPractice(Practice $practice) {
		if ($this->user_type == 'student') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $practice->teacher_id);
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = TaskGateway::isSaved($practice->task_id);
			return (UserGateway::isActive($practice->teacher_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->student_id);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($practice->student_id, $this->uid);
			$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
			$is_task_saved = TaskGateway::isSaved($practice->task_id);
			return (UserGateway::isActive($practice->student_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->teacher_id);
		}
		return false;
	}
	
	// ------------------------------------------------------------------------
	// Goal Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedViewStudentGoalsWithTeacher(User $student, User $teacher) {
		if ($this->user_type == 'student') {
			return ($teacher->status != 'blocked' && $this->uid == $student->uid);
		}
		elseif ($this->user_type == 'teacher') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student->uid, $this->uid);
			return ($student->status == 'active' && $is_link_connected && $this->uid == $teacher->uid);
		}
		return false;
	}
	function isAllowedEditStudentGoalsWithTeacher(User $student, User $teacher) {
		if ($this->user_type == 'student') {
			$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $teacher->uid);
			return ($teacher->status == 'active' && $is_link_connected && $this->uid == $student->uid);
		}
		return false;
	}
	function isAllowedEditStudentGoal(StudentGoal $studentGoal) {
		$student_row = UserGateway::findStudent($studentGoal->uid);
		$teacher_row = UserGateway::findTeacher($studentGoal->teacher_id);
		if ($student_row && $teacher_row) {
			$student = new User($student_row);
			$teacher = new User($teacher_row);
			return $this->isAllowedEditStudentGoalsWithTeacher($student, $teacher);
		}
		return false;
	}
	function isAllowedDeleteStudentGoal(StudentGoal $studentGoal) {
		$student_row = UserGateway::findStudent($studentGoal->uid);
		$teacher_row = UserGateway::findTeacher($studentGoal->teacher_id);
		if ($student_row && $teacher_row) {
			$student = new User($student_row);
			$teacher = new User($teacher_row);
			return $this->isAllowedEditStudentGoalsWithTeacher($student, $teacher);
		}
		return false;
	}
	
	// ------------------------------------------------------------------------
	// Comment Permissions
	// ------------------------------------------------------------------------
	
	function isAllowedAddCommentToRefId($ref, $ref_id) {
		switch ($ref) {
			case 'practice':
				$practice_row = PracticeGateway::find($ref_id);
				if ($practice_row) {
					$practice = new Practice($practice_row);
					if ($this->user_type == 'student') {
						$teacher_id = $practice->teacher_id;
						$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $teacher_id);
						$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
						$is_task_saved = TaskGateway::isSaved($practice->task_id);
						return (UserGateway::isActive($teacher_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->student_id);
					}
					elseif ($this->user_type == 'teacher') {
						$student_id = $practice->student_id;
						$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $this->uid);
						$is_lesson_saved = LessonGateway::isSaved($practice->lesson_id);
						$is_task_saved = TaskGateway::isSaved($practice->task_id);
						return (UserGateway::isActive($student_id) && $is_link_connected && $is_lesson_saved && $is_task_saved && $this->uid == $practice->teacher_id);
					}
				}
				break;
			case 'lesson':
				$lesson_row = LessonGateway::find($ref_id);
				if ($lesson_row) {
					$lesson = new Lesson($lesson_row);
					if ($this->user_type == 'student') {
						$teacher_id = $lesson->teacher_id;
						$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($this->uid, $teacher_id);
						$is_lesson_saved = $lesson->is_saved;
						return (UserGateway::isActive($teacher_id) && $is_link_connected && $is_lesson_saved && $this->uid == $lesson->student_id);
					}
					elseif ($this->user_type == 'teacher') {
						$student_id = $lesson->student_id;
						$is_link_connected = UserLinkGateway::isConnectedStudentTeacher($student_id, $this->uid);
						// note: not checking for is_saved here in case teacher for whatever reason decides to add comment on reflection while creating lesson
						return (UserGateway::isActive($student_id) && $is_link_connected && $this->uid == $lesson->teacher_id);
					}
				}
				break;
		}
		return false;
	}
	function isAllowedEditComment(Comment $comment) {
		if ($this->uid == $comment->author_uid) {
			return $this->isAllowedAddCommentToRefId($comment->ref, $comment->ref_id);
		}
		return false;
	}
	function isAllowedDeleteComment(Comment $comment) {
		if ($this->uid == $comment->author_uid) {
			return $this->isAllowedAddCommentToRefId($comment->ref, $comment->ref_id);
		}
		return false;
	}
	
}
