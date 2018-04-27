<?php
class AdminActions {
	
	static function paginationSortableFilterableListOfBlockedUsers() {
		// admin always able to see blocked users
		Session::set('action_ok', true); // action ok
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$filter_user_type = isset($_REQUEST['filter_user_type']) ? $_REQUEST['filter_user_type'] : null;
		if ($filter_user_type != 'student' && $filter_user_type != 'teacher') {
			$filter_user_type = null;
		}
		$refreshAdmin = array(
			'tables/list_of_blocked_users'=>AdminComponents::renderTableListOfBlockedUsers(Session::adminId(), $page, $order_by, $order_direction, $filter_user_type)
		);
		return array('refreshAdmin'=>$refreshAdmin);
	}
	
	static function paginationSortableFilterableListOfUsers() {
		// admin always able to see list of users
		Session::set('action_ok', true); // action ok
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$filter_user_type = isset($_REQUEST['filter_user_type']) ? $_REQUEST['filter_user_type'] : null;
		if ($filter_user_type != 'student' && $filter_user_type != 'teacher') {
			$filter_user_type = null;
		}
		$refreshAdmin = array(
			'tables/list_of_users'=>AdminComponents::renderTableListOfUsers(Session::adminId(), $page, $order_by, $order_direction, $filter_user_type)
		);
		return array('refreshAdmin'=>$refreshAdmin);
	}
	
	static function paginationSortableFilterableListOfReports() {
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$filter_report_type = isset($_REQUEST['filter_report_type']) ? $_REQUEST['filter_report_type'] : null;
		if ($filter_report_type != 'resolved' && $filter_report_type != 'unresolved') {
			$filter_report_type = null;
		}
		Session::set('action_ok', true); // action ok
		$refreshAdmin = array(
			'tables/list_of_reports'=>AdminComponents::renderTableListOfReports(Session::adminId(), $page, $order_by, $order_direction, $filter_report_type)
		);
		return array('refreshAdmin'=>$refreshAdmin);
	}
	
	static function paginationSortableListOfUserConnectedUsers() {
		$uid = $_REQUEST['uid'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			Session::set('action_ok', true); // action ok
			$user = new User($user_row);
			$refreshAdmin = array(
				'tables/list_of_user_connected_users'=>AdminComponents::renderTableListOfUserConnectedUsers(Session::adminId(), $user, $page, $order_by, $order_direction)
			);
			return array('refreshAdmin'=>$refreshAdmin);
		}
	}
	
	static function paginationSortableListOfUserInvitations() {
		$uid = $_REQUEST['uid'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			Session::set('action_ok', true); // action ok
			$user = new User($user_row);
			$refreshAdmin = array(
				'tables/list_of_user_invitations'=>AdminComponents::renderTableListOfUserInvitations(Session::adminId(), $user, $page, $order_by, $order_direction)
			);
			return array('refreshAdmin'=>$refreshAdmin);
		}
	}
	
	static function sortableListOfUserFlags() {
		$uid = $_REQUEST['uid'];
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			Session::set('action_ok', true); // action ok
			$user = new User($user_row);
			$refreshAdmin = array(
				'tables/list_of_user_flags'=>AdminComponents::renderTableListOfUserFlags(Session::adminId(), $user, $order_by, $order_direction)
			);
			return array('refreshAdmin'=>$refreshAdmin);
		}
	}
	
	static function sortableListOfUserReports() {
		$uid = $_REQUEST['uid'];
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			Session::set('action_ok', true); // action ok
			$user = new User($user_row);
			$refreshAdmin = array(
				'tables/list_of_user_reports'=>AdminComponents::renderTableListOfUserReports(Session::adminId(), $user, $order_by, $order_direction)
			);
			return array('refreshAdmin'=>$refreshAdmin);
		}
	}
	
	static function markAsResolvedFromListOfReports() {
		$admin_report_id = $_REQUEST['admin_report_id'];
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$filter_report_type = isset($_REQUEST['filter_report_type']) ? $_REQUEST['filter_report_type'] : null;
		if ($filter_report_type != 'resolved' && $filter_report_type != 'unresolved') {
			$filter_report_type = null;
		}
		$admin_report_row = AdminReportGateway::find($admin_report_id);
		if ($admin_report_row) {
			Session::set('action_ok', true); // action ok
			AdminReportGateway::updateIsResolved($admin_report_id, true);
			$refreshAdmin = array(
				'tables/list_of_reports'=>AdminComponents::renderTableListOfReports(Session::adminId(), $page, $order_by, $order_direction, $filter_report_type)
			);
			return array('refreshAdmin'=>$refreshAdmin);
		}
	}

	static function markAsResolvedFromUserFlags() {
		$admin_report_id = $_REQUEST['admin_report_id'];
		$uid = $_REQUEST['uid'];
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			$user = new User($user_row);
			$admin_report_row = AdminReportGateway::findWithReported($admin_report_id, $user->uid);
			if ($admin_report_row) {
				Session::set('action_ok', true); // action ok
				AdminReportGateway::updateIsResolved($admin_report_id, true);
				$refreshAdmin = array(
					'tables/list_of_user_flags'=>AdminComponents::renderTableListOfUserFlags(Session::adminId(), $user, $order_by, $order_direction)
				);
				return array('refreshAdmin'=>$refreshAdmin);
			}
		}
	}
	
	static function markAsResolvedFromUserReports() {
		$admin_report_id = $_REQUEST['admin_report_id'];
		$uid = $_REQUEST['uid'];
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			$user = new User($user_row);
			$admin_report_row = AdminReportGateway::findWithReporter($admin_report_id, $user->uid);
			if ($admin_report_row) {
				Session::set('action_ok', true); // action ok
				AdminReportGateway::updateIsResolved($admin_report_id, true);
				$refreshAdmin = array(
					'tables/list_of_user_reports'=>AdminComponents::renderTableListOfUserReports(Session::adminId(), $user, $order_by, $order_direction)
				);
				return array('refreshAdmin'=>$refreshAdmin);
			}
		}
	}

	static function blockUser() {
		$uid = $_REQUEST['uid'];
		$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$is_notify_connected_users = true;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			$user = new User($user_row);
			if ($user->status == 'active' && $user->user_type !== null) {
				Session::set('action_ok', true); // action ok
				UserGateway::updateStatus($uid, 'blocked', date('Y-m-d H:i:s'));
				if ($is_notify_connected_users) {
					$connected_user_rows = array();
					$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
					if ($user->user_type == 'student') {
						$connected_user_rows = UserGateway::findAllTeachersLinkedToStudent($user->uid, $link_statuses_connected);
					}
					elseif ($user->user_type == 'teacher') {
						$connected_user_rows = UserGateway::findAllStudentsLinkedToTeacher($user->uid, $link_statuses_connected);
					}
					foreach ($connected_user_rows as $connected_user_row) {
						$priority = 2;
						NotificationGateway::insert(date('Y-m-d H:i:s'), $connected_user_row['uid'], $user->uid, 'user_blocked', $user->uid, $priority, true, true, true);
					}
				}
				$updatedUser = new User(UserGateway::find($uid));
				$refreshAdmin = array();
				$refreshAdmin['misc/selecteduser_header'] = AdminComponents::renderMiscSelecteduserHeader(Session::adminId(), $updatedUser);
				if ($tab == 'flags') {
					$refreshAdmin['tables/list_of_user_flags'] = AdminComponents::renderTableListOfUserFlags(Session::adminId(), $updatedUser, $order_by, $order_direction);
				}
				if ($tab == 'reports') {
					$refreshAdmin['tables/list_of_user_reports'] = AdminComponents::renderTableListOfUserReports(Session::adminId(), $updatedUser, $order_by, $order_direction);
				}
				$refreshAdmin['misc/selecteduser_actions'] = AdminComponents::renderMiscSelecteduserActions(Session::adminId(), $updatedUser);
				return array('refreshAdmin'=>$refreshAdmin);
			}
		}
	}
	
	static function unblockUser() {
		$uid = $_REQUEST['uid'];
		$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$is_notify_connected_users = true;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			$user = new User($user_row);
			if ($user->status == 'blocked' && $user->user_type !== null) {
				Session::set('action_ok', true); // action ok
				UserGateway::updateStatus($uid, 'active', date('Y-m-d H:i:s'));
				if ($is_notify_connected_users) {
					$connected_user_rows = array();
					$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
					if ($user->user_type == 'student') {
						$connected_user_rows = UserGateway::findAllTeachersLinkedToStudent($user->uid, $link_statuses_connected);
					}
					elseif ($user->user_type == 'teacher') {
						$connected_user_rows = UserGateway::findAllStudentsLinkedToTeacher($user->uid, $link_statuses_connected);
					}
					foreach ($connected_user_rows as $connected_user_row) {
						$priority = 2;
						NotificationGateway::insert(date('Y-m-d H:i:s'), $connected_user_row['uid'], $user->uid, 'user_unblocked', $user->uid, $priority, true, true, true);
					}
				}
				$updatedUser = new User(UserGateway::find($uid));
				$refreshAdmin = array();
				$refreshAdmin['misc/selecteduser_header'] = AdminComponents::renderMiscSelecteduserHeader(Session::adminId(), $updatedUser);
				if ($tab == 'flags') {
					$refreshAdmin['tables/list_of_user_flags'] = AdminComponents::renderTableListOfUserFlags(Session::adminId(), $updatedUser, $order_by, $order_direction);
				}
				if ($tab == 'reports') {
					$refreshAdmin['tables/list_of_user_reports'] = AdminComponents::renderTableListOfUserReports(Session::adminId(), $updatedUser, $order_by, $order_direction);
				}
				$refreshAdmin['misc/selecteduser_actions'] = AdminComponents::renderMiscSelecteduserActions(Session::adminId(), $updatedUser);
				return array('refreshAdmin'=>$refreshAdmin);
			}
		}
	}

	static function deleteUser() {
		$uid = $_REQUEST['uid'];
		$user_row = UserGateway::find($uid);
		$is_notify_connected_users = true;
		if ($user_row) {
			$user = new User($user_row);
			if ($user->status == 'active') {
				Session::set('action_ok', true); // action ok
				
				// delete user email, picture, and refresh token (all Google data except currently stored name)
				UserGateway::updateEmailNormalized($uid, null);
				UserGateway::updateGoogleData($uid, null, $user->name, $user->first_name, $user->last_name, null, null);
				// delete notifications to user
				NotificationGateway::deleteAllToUser($uid);
				// delete notifications from user
				NotificationGateway::deleteAllFromSender($uid);
				// check user_type for additional, type-specific deletions
				if ($user->user_type == 'student') {
					// delete student goals
					StudentGoalGateway::deleteAllGoalsOfStudent($uid);
					// delete student rewards
					StudentRewardGateway::deleteAllRewardsOfStudent($uid);
					// delete student attachments
					$attachment_rows = UserFileGateway::findAllUserAttachments($uid);
					foreach ($attachment_rows as $attachment_row) {
						$attachment = new Attachment($attachment_row);
						$handler = new Fileupload_UploadHandler_Cadenza();
						$handler->initiate_delete(
							$attachment->lesson_id,
							$attachment->task_id,
							$attachment->practice_id,
							$attachment->category,
							$attachment->uid,
							$attachment->file_id,
							$attachment->filename
						);
					}
					// delete comments in student practices
					CommentGateway::deleteAllInPracticesOfStudent($uid);
					// delete comments in student lesson reflections
					CommentGateway::deleteAllInLessonReflectionsOfStudent($uid);
					// check user links
					$user_link_rows = UserLinkGateway::findAllByStudent($uid);
					foreach ($user_link_rows as $user_link_row) {
						$user_link_id = $user_link_row['user_link_id'];
						$teacher_id = $user_link_row['teacher_id'];
						$user_link_status = $user_link_row['status'];
                        // notify connected teachers
                        if ($is_notify_connected_users && $user_link_status == 'connected') {
                            $priority = 2;
                            NotificationGateway::insert(date('Y-m-d H:i:s'), $teacher_id, $user->uid, 'user_deleted', $user->uid, $priority, true, true, true);
                        }
						// delete teacher attachments in tasks of student
						$attachment_rows = UserFileGateway::findAllUserAttachmentsInTasksOfStudent($teacher_id, $uid);
						foreach ($attachment_rows as $attachment_row) {
							$attachment = new Attachment($attachment_row);
							$handler = new Fileupload_UploadHandler_Cadenza();
							$handler->initiate_delete(
								$attachment->lesson_id,
								$attachment->task_id,
								$attachment->practice_id,
								$attachment->category,
								$attachment->uid,
								$attachment->file_id,
								$attachment->filename
							);
						}
						// delete teacher notes on student
						TeacherNotesGateway::deleteByTeacherStudent($teacher_id, $uid);
						// delete the user link
						UserLinkGateway::delete($user_link_id);
					}
					// delete student lessons
					// IMPORTANT: must be after the comment deletions and user links check
					/* NOTE: Each lesson delete should cascade using foreign keys and delete any associated
					 * lesson reflections, tasks, practices, etc. */
					LessonGateway::deleteAllLessonsOfStudent($uid);
				}
				elseif ($user->user_type == 'teacher') {
					// delete autocomplete data
					AutocompleteGateway::deleteAllOfUid($uid);
					// delete teacher notes
					TeacherNotesGateway::deleteAllNotesOfTeacher($uid);
					// check user links
					$user_link_rows = UserLinkGateway::findAllByTeacher($uid);
					foreach ($user_link_rows as $user_link_row) {
						$user_link_id = $user_link_row['user_link_id'];
						$student_id = $user_link_row['student_id'];
						$user_link_status = $user_link_row['status'];
                        // notify connected students
                        if ($is_notify_connected_users && $user_link_status == 'connected') {
                            $priority = 2;
                            NotificationGateway::insert(date('Y-m-d H:i:s'), $student_id, $user->uid, 'user_deleted', $user->uid, $priority, true, true, true);
                        }
						// if connected or inactive
						if ($user_link_status == 'connected' || in_array($user_link_status, UserLinkGateway::getStatusArrayInactive())) {
							// update status
							UserLinkGateway::updateStatus($user_link_id, 'disconnected-inactive', date('Y-m-d H:i:s'));
						}
						// else teacher was never connected to this student
						else {
							// delete the user link
							UserLinkGateway::delete($user_link_id);
						}
					}
				}
				// update status (set to deleted)
				UserGateway::updateStatus($uid, 'deleted', date('Y-m-d H:i:s'));
				
				// generate response
				$redirect_uri = Core::cadenzaUrl('pages/admin/users.php');
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
			}
		}
	}

	static function deleteUserData() {
		$uid = $_REQUEST['uid'];
		$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
		$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
		$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
		$user_row = UserGateway::find($uid);
		if ($user_row) {
			$user = new User($user_row);
			if ($user->status == 'blocked' && $user->user_type !== null) {
				Session::set('action_ok', true); // action ok
				
				// delete notifications to user
				NotificationGateway::deleteAllToUser($uid);
				// delete notifications from user
				NotificationGateway::deleteAllFromSender($uid);
				// check user_type for additional, type-specific deletions
				if ($user->user_type == 'student') {
					// delete student goals
					StudentGoalGateway::deleteAllGoalsOfStudent($uid);
					// delete student rewards
					StudentRewardGateway::deleteAllRewardsOfStudent($uid);
					// delete student attachments
					$attachment_rows = UserFileGateway::findAllUserAttachments($uid);
					foreach ($attachment_rows as $attachment_row) {
						$attachment = new Attachment($attachment_row);
						$handler = new Fileupload_UploadHandler_Cadenza();
						$handler->initiate_delete(
							$attachment->lesson_id,
							$attachment->task_id,
							$attachment->practice_id,
							$attachment->category,
							$attachment->uid,
							$attachment->file_id,
							$attachment->filename
						);
					}
					// delete comments in student practices
					CommentGateway::deleteAllInPracticesOfStudent($uid);
					// delete comments in student lesson reflections
					CommentGateway::deleteAllInLessonReflectionsOfStudent($uid);
					// check user links
					$user_link_rows = UserLinkGateway::findAllByStudent($uid);
					foreach ($user_link_rows as $user_link_row) {
						$user_link_id = $user_link_row['user_link_id'];
						$teacher_id = $user_link_row['teacher_id'];
						// delete teacher attachments in tasks of student
						$attachment_rows = UserFileGateway::findAllUserAttachmentsInTasksOfStudent($teacher_id, $uid);
						foreach ($attachment_rows as $attachment_row) {
							$attachment = new Attachment($attachment_row);
							$handler = new Fileupload_UploadHandler_Cadenza();
							$handler->initiate_delete(
								$attachment->lesson_id,
								$attachment->task_id,
								$attachment->practice_id,
								$attachment->category,
								$attachment->uid,
								$attachment->file_id,
								$attachment->filename
							);
						}
						// delete the user link
						UserLinkGateway::delete($user_link_id);
					}
					// delete student lessons
					// IMPORTANT: must be after the comment deletions and user links check
					/* NOTE: Each lesson delete should cascade using foreign keys and delete any associated
					 * lesson reflections, tasks, practices, etc. */
					LessonGateway::deleteAllLessonsOfStudent($uid);
				}
				elseif ($user->user_type == 'teacher') {
					// delete autocomplete data
					AutocompleteGateway::deleteAllOfUid($uid);
					// delete teacher notes
					TeacherNotesGateway::deleteAllNotesOfTeacher($uid);
					// delete teacher attachments
					$attachment_rows = UserFileGateway::findAllUserAttachments($uid);
					foreach ($attachment_rows as $attachment_row) {
						$attachment = new Attachment($attachment_row);
						$handler = new Fileupload_UploadHandler_Cadenza();
						$handler->initiate_delete(
							$attachment->lesson_id,
							$attachment->task_id,
							$attachment->practice_id,
							$attachment->category,
							$attachment->uid,
							$attachment->file_id,
							$attachment->filename
						);
					}
					// delete comments in teacher practices (i.e. student practices in lessons assigned by teacher)
					CommentGateway::deleteAllInPracticesOfStudent($uid);
					// delete comments in teacher lesson reflections (i.e. student lesson reflections in lessons assigned by teacher)
					CommentGateway::deleteAllInLessonReflectionsOfTeacher($uid);
					// check user links
					$user_link_rows = UserLinkGateway::findAllByTeacher($uid);
					foreach ($user_link_rows as $user_link_row) {
						$user_link_id = $user_link_row['user_link_id'];
						$student_id = $user_link_row['student_id'];
						// delete student attachments in practices of lessons assigned by teacher
						$attachment_rows = UserFileGateway::findAllUserAttachmentsInPracticesOfTeacher($student_id, $uid);
						foreach ($attachment_rows as $attachment_row) {
							$attachment = new Attachment($attachment_row);
							$handler = new Fileupload_UploadHandler_Cadenza();
							$handler->initiate_delete(
								$attachment->lesson_id,
								$attachment->task_id,
								$attachment->practice_id,
								$attachment->category,
								$attachment->uid,
								$attachment->file_id,
								$attachment->filename
							);
						}
						// delete the user link
						UserLinkGateway::delete($user_link_id);
					}
					// delete teacher lessons
					// IMPORTANT: must be after the comment deletions and user links check
					/* NOTE: Each lesson delete should cascade using foreign keys and delete any associated
					 * lesson reflections, tasks, practices, etc. */
					LessonGateway::deleteAllLessonsOfTeacher($uid);
				}
				$updatedUser = new User(UserGateway::find($uid));
				
				$refreshAdmin = array();
				if (($updatedUser->user_type == 'teacher' && $tab == 'students') || ($updatedUser->user_type == 'student' && $tab == 'teachers')) {
					$refreshAdmin['tables/list_of_user_connected_users'] = AdminComponents::renderTableListOfUserConnectedUsers(Session::adminId(), $updatedUser, $page, $order_by, $order_direction);
				}
				if ($tab == 'invitations') {
					$refreshAdmin['tables/list_of_user_invitations'] = AdminComponents::renderTableListOfUserInvitations(Session::adminId(), $updatedUser, $page, $order_by, $order_direction);
				}
				return array('refreshAdmin'=>$refreshAdmin);
			}
		}
	}
	
	static function getUserSearchData() {
		// admin always able to search
		Session::set('action_ok', true); // action ok
		$uid = Session::uid();
		$userSearchData = array();
		$user_rows = UserGateway::findAllExceptDeleted();
		foreach ($user_rows as $user_row) {
			$picture = ($user_row['g_picture'] != null) ? $user_row['g_picture'] : Core::cadenzaWebPath('assets/images/default_profile_picture.png');
			$userSearchData[] = array(
				'uid'=>$user_row['uid'],
				'email'=>$user_row['g_email'],
				'name'=>$user_row['g_name'],
				'picture'=>$picture
			);
		}
		return array('userSearchData'=>$userSearchData);
	}
	
}
