<?php
class UserActions {
	
	static function getAccessToken() {
		if (Session::is('access_token')) {
			Session::set('action_ok', true); // action ok
			$is_action = true;
			$client = new Google_Client_Cadenza($is_action); // instantiating client will ensure access token is good (refreshes it if expired, etc.)
			$access_token = Session::get('access_token');
			
			return array(
				'access_token' => array(
					'access_token' => $access_token['access_token'],
					'expires_in' => $access_token['expires_in'],
					'state' => ""
				)
			);
		}
	}
	
	static function viewAttachment() {
		$file_id = $_REQUEST['file_id'];
		$user_file_row = UserFileGateway::find($file_id);
		if ($user_file_row && $user_file_row['category'] == 'attachment') {
			$attachment = new Attachment($user_file_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedViewAttachment($attachment)) {
				Session::set('action_ok', true); // action ok
				$handler = new Fileupload_UploadHandler_Cadenza();
				$handler->initiate_download(
					$attachment->lesson_id,
					$attachment->task_id,
					$attachment->practice_id,
					$attachment->category,
					$attachment->uid,
					$attachment->file_id,
					$attachment->filename,
					$attachment->filetype_long
				);
				exit;
			}
			else {
				// user might be trying to view an attachment that they *were* but are no longer able to - redirect instead of error
				Session::set('action_ok', true); // action ok
				$redirect_uri = Core::cadenzaUrl('index.php');
				return array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL));
			}
		}
	}
	
	static function deleteAttachment() {
		$file_id = $_REQUEST['file_id'];
		$user_file_row = UserFileGateway::find($file_id);
		if ($user_file_row && $user_file_row['category'] == 'attachment') {
			$attachment = new Attachment($user_file_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedDeleteAttachment($attachment)) {
				Session::set('action_ok', true); // action ok
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
				$response = $handler->get_response();
				return $response;
			}
		}
	}
	
	static function deleteComment() {
		$comment_id = $_REQUEST['comment_id'];
		$comment_row = CommentGateway::find($comment_id);
		if ($comment_row) {
			$comment = new Comment($comment_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedDeleteComment($comment)) {
				Session::set('action_ok', true); // action ok
				$response = array();
				CommentGateway::delete($comment->comment_id); // delete the comment
				// delete notifications for deleted comment
				$notifcaition_ref = $comment->ref == 'practice' ? 'practice_comment' : 'lesson_comment';
				$notification_ref_id = $comment->comment_id;
				NotificationGateway::deleteAllInRefId($notifcaition_ref, $notification_ref_id);
				if ($comment->ref == 'practice') {
					$practice_id = $comment->ref_id;
					// TODO: place this refresh in an "if practicelog" (if it's possible to determine here)
					$response['refresh'] = array(
						'misc/practicelog_indicators'=>Components::renderMiscPracticelogIndicators($user->uid, $practice_id)
					);
				}
				return $response;
			}
		}
	}
	
	static function updateComment() {
		$comment_id = $_REQUEST['comment_id'];
		$comment_text = trim($_REQUEST['comment_text']);
		$comment_row = CommentGateway::find($comment_id);
		if ($comment_row) {
			$comment = new Comment($comment_row);
			$user = Login::getCurrentUser();
			if ($user->isAllowedEditComment($comment)) {
				Session::set('action_ok', true); // action ok
				if ($comment_text == "") {
					return array('updated'=>false);
				}
				CommentGateway::updateCommentText($comment_id, $comment_text, date('Y-m-d H:i:s'));
				$updatedComment = new Comment(CommentGateway::find($comment_id));
				return array('updated'=>true, 'savedCommentText'=>$updatedComment->comment_text);
			}
		}
	}
	
	static function markAllNotificationsAsRead() {
		$uid = Session::uid();
		$last_notification_id = $_REQUEST['last_notification_id'];
		$last_notification_row = NotificationGateway::findSentToUser($last_notification_id, $uid);
		if ($last_notification_row) {
			Session::set('action_ok', true); // action ok
			NotificationGateway::markUnreadSentToUserAsReadUpToNotification($uid, $last_notification_id);
		}
	}
	
	static function markNotificationAsRead() {
		$uid = Session::uid();
		$notification_id = $_REQUEST['notification_id'];
		
		$notification_row = NotificationGateway::findSentToUser($notification_id, $uid);
		if ($notification_row) {
			Session::set('action_ok', true); // action ok
			NotificationGateway::updateIsUnread($notification_id, false);
			$updatedNotification = NotificationFactory::createNotificationObject(NotificationGateway::find($notification_id));
			$count_unread = NotificationGateway::countUnreadSentToUser($uid);
			return array('updatedNotification'=>$updatedNotification, 'countUnread'=>$count_unread);
		}
	}
	
	static function markNotificationAsUnread() {
		$uid = Session::uid();
		$notification_id = $_REQUEST['notification_id'];
		
		$notification_row = NotificationGateway::findSentToUser($notification_id, $uid);
		if ($notification_row) {
			Session::set('action_ok', true); // action ok
			NotificationGateway::updateIsUnread($notification_id, true);
			$updatedNotification = NotificationFactory::createNotificationObject(NotificationGateway::find($notification_id));
			$count_unread = NotificationGateway::countUnreadSentToUser($uid);
			return array('updatedNotification'=>$updatedNotification, 'countUnread'=>$count_unread);
		}
	}
	
	static function requestDeleteAccount() {
		$uid = Session::uid();
		$report_text = $_REQUEST['report_text'];
		Session::set('action_ok', true); // action ok
		AdminReportGateway::insertNewDeleteRequest($uid, $report_text, date('Y-m-d H:i:s'));
	}
	
}
