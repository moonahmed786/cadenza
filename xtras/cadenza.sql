CREATE DATABASE `cadenza` DEFAULT CHARACTER SET utf8;
USE `cadenza`;

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `admin_reports` (
  `admin_report_id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_uid` int(11) NOT NULL,
  `reported_uid` int(11) DEFAULT NULL,
  `report_type` ENUM('issue', 'delete') NOT NULL,
  `report_text` text DEFAULT NULL,
  `report_date` datetime NOT NULL,
  `is_resolved` tinyint(1) NOT NULL,
  PRIMARY KEY (`admin_report_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `autocomplete` (
  `autocomplete_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `autocomplete_field` ENUM('task_title', 'task_category_other', 'task_checklist_item') NOT NULL,
  `autocomplete_text` varchar(255) NOT NULL,
  `autocomplete_date` datetime NOT NULL,
  PRIMARY KEY (`autocomplete_id`),
  UNIQUE KEY `uid_field_text_unique` (`uid`, `autocomplete_field`, `autocomplete_text`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `checklist_items` (
  `checklist_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `text` varchar(255) DEFAULT NULL,
  `target_type` int(11) DEFAULT 1,
  `target_val` int(11) DEFAULT NULL,
  PRIMARY KEY (`checklist_item_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` ENUM('practice', 'lesson') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `author_uid` int(11) NOT NULL,
  `comment_text` text DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`comment_id`),
  INDEX `ref` (`ref`),
  KEY `ref_id` (`ref_id`),
  KEY `author_uid` (`author_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `is_saved` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`lesson_id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `lesson_reflections` (
  `lesson_reflection_id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `reflection_index` int(11) DEFAULT NULL,
  `reflection_text` text NOT NULL,
  `reflection_prompt` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`lesson_reflection_id`),
  UNIQUE KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_date`  datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `sender_uid` int(11) NOT NULL,
  `ref` ENUM('user_link', 'practice', 'practice_comment', 'lesson_comment', 'annotation', 'user_blocked', 'user_unblocked', 'user_deleted') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `is_unread` tinyint(1) DEFAULT NULL,
  `is_sent` tinyint(1) NOT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `uid` (`uid`),
  KEY `sender_uid` (`sender_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `practices` (
  `practice_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `timer_mins` int(11) DEFAULT NULL,
  `reflection` int(11) DEFAULT NULL,
  `is_notified` tinyint(1) NOT NULL,
  `annotator_file_id` varchar(255) DEFAULT NULL,
  `annotator_title` varchar(255) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`practice_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `practice_fields` (
  `practice_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `practice_id` int(11) NOT NULL,
  `ref` ENUM('checklist_item') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `field_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`practice_field_id`),
  KEY `practice_id` (`practice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `student_goals` (
  `student_goal_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `is_completed` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`student_goal_id`),
  KEY `uid` (`uid`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `student_rewards` (
  `student_reward_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `reward_points` int(11) NOT NULL,
  `reward_event` ENUM('task_practice', 'task_target', 'lesson_target', 'lesson_reflection', 'goal') NOT NULL,
  `reward_date` datetime NOT NULL,
  PRIMARY KEY (`student_reward_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `target` int(11) DEFAULT 1,
  `category` int(11) DEFAULT 0,
  `category_other` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_saved` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `teacher_notes` (
  `teacher_note_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `notes_on_student` text NOT NULL,
  PRIMARY KEY (`teacher_note_id`),
  UNIQUE KEY `uid_studentid_unique` (`uid`, `student_id`),
  KEY `uid` (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email_normalized` varchar(255) DEFAULT NULL,
  `g_email` varchar(255) DEFAULT NULL,
  `g_name` varchar(100) DEFAULT NULL,
  `g_given_name` varchar(100) DEFAULT NULL,
  `g_family_name` varchar(100) DEFAULT NULL,
  `g_picture` varchar(255) DEFAULT NULL,
  `g_refresh_token` varchar(255) DEFAULT NULL,
  `user_type` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` ENUM('active', 'deleted', 'blocked') NOT NULL,
  `status_date` datetime NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `email_normalized` (`email_normalized`),
  UNIQUE KEY `g_email` (`g_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user_files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `practice_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `filetype` varchar(255) DEFAULT NULL,
  `filesize` int(20) DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user_links` (
  `user_link_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `status` ENUM('pending', 'rejected', 'connected', 'disconnected-inactive', 'pending-inactive', 'rejected-inactive') NOT NULL,
  `status_date` datetime NOT NULL,
  `last_lesson_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_link_id`),
  UNIQUE KEY `studentid_teacherid_unique` (`student_id`, `teacher_id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `admin_reports`
  ADD CONSTRAINT `admin_reports_fk_reporter_uid` FOREIGN KEY (`reporter_uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_reports_fk_reported_uid` FOREIGN KEY (`reported_uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `autocomplete`
  ADD CONSTRAINT `autocomplete_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `checklist_items`
  ADD CONSTRAINT `checklist_items_fk_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE;

ALTER TABLE `comments`
  ADD CONSTRAINT `comments_fk_uid` FOREIGN KEY (`author_uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_fk_student_id` FOREIGN KEY (`student_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_fk_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `lesson_reflections`
  ADD CONSTRAINT `lesson_reflections_fk_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_fk_sender_uid` FOREIGN KEY (`sender_uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `practices`
  ADD CONSTRAINT `practices_fk_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `practices_fk_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

ALTER TABLE `practice_fields`
  ADD CONSTRAINT `practice_fields_fk_practice_id` FOREIGN KEY (`practice_id`) REFERENCES `practices` (`practice_id`) ON DELETE CASCADE;

ALTER TABLE `student_goals`
  ADD CONSTRAINT `student_goals_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_goals_fk_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `student_rewards`
  ADD CONSTRAINT `student_rewards_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_fk_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

ALTER TABLE `teacher_notes`
  ADD CONSTRAINT `teacher_notes_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_notes_fk_student_id` FOREIGN KEY (`student_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `user_files`
  ADD CONSTRAINT `user_files_fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

ALTER TABLE `user_links`
  ADD CONSTRAINT `user_links_fk_student_id` FOREIGN KEY (`student_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_links_fk_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

