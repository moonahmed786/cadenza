<?php
class NotificationFactory {
	
	// This class uses the "Factory" design pattern in order to create Notification object instances
	
	static function createNotificationObject($notification_row) {
		switch ($notification_row['ref']) {
			case 'user_link':
				return new UserLinkNotification($notification_row);
			case 'practice':
				return new PracticeNotification($notification_row);
			case 'practice_comment':
				return new PracticeCommentNotification($notification_row);
			case 'lesson_comment':
				return new LessonCommentNotification($notification_row);
			case 'annotation':
				return new AnnotationNotification($notification_row);
			case 'user_blocked':
				return new UserBlockedNotification($notification_row);
			case 'user_unblocked':
				return new UserUnblockedNotification($notification_row);
            case 'user_deleted':
				return new UserDeletedNotification($notification_row);
			default:
				trigger_error('Unknown ref: '.$notification_row['ref'], E_USER_ERROR);
		}
	}
	
}
