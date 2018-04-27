<?php
class ActionHelper {
	
	static function isTeacherTaskFormBlank(User $user, $task_id, $title, $target, $category, $category_other, $description, $checklist) {
		$isTitleBlank = ($title == "");
		$isTargetNumMin = ($target == 1);
		$isCategoryZero = ($category == 0);
		$isCategoryOtherBlank = ($category_other == "");
		$isDescriptionBlank = ($description == "");
		$isChecklistTextBlank = true;
		foreach ($checklist as $checklist_item) {
			if (trim($checklist_item['text']) != "") {
				$isChecklistTextBlank = false;
				break;
			}
		}
		$task_teacher_attachments = UserFileGateway::findAllUserAttachmentsInTask($user->uid, $task_id);
		$isNoAttachmentsUploaded = (count($task_teacher_attachments) == 0);
		return ($isTitleBlank && $isTargetNumMin && $isCategoryZero && $isCategoryOtherBlank && $isDescriptionBlank && $isChecklistTextBlank && $isNoAttachmentsUploaded);
	}
	
	static function saveTeacherTaskForm(User $user, Task $existingTask, $title, $target, $category, $category_other, $description, $checklist) {
		if ($category != '0') {
			$category_other = null;
		}
		TaskGateway::update($existingTask->task_id, $existingTask->lesson_id, $title, $target, $category, $category_other, $description, true, $existingTask->created_date, date('Y-m-d H:i:s'));
		$keep_checklist_item_ids = array();
		$saved_checklist_texts = array();
		foreach ($checklist as $checklist_item) {
			$checklist_item_id = $checklist_item['checklist_item_id'];
			$text = trim($checklist_item['text']);
			$target_type = $checklist_item['target_type'];
			$target_val = $checklist_item['target_val'] ? $checklist_item['target_val'] : null;
			// if target_val is invalid for the given target_type, convert to regular "check" (target_type=1)
			if ($target_type != 1 && $target_val == null) {
				$target_type = 1;
			}
			// only keep checklist items with non-empty text
			if ($text != "") {
				if ($checklist_item_id == 'new') {
					$keep_checklist_item_ids[] = ChecklistItemGateway::insert($existingTask->task_id, $text, $target_type, $target_val);
				}
				else {
					$keep_checklist_item_ids[] = $checklist_item_id;
					ChecklistItemGateway::update($checklist_item_id, $existingTask->task_id, $text, $target_type, $target_val);
				}
				$saved_checklist_texts[] = $text;
			}
		}
		ChecklistItemGateway::deleteAllInTaskExcept($existingTask->task_id, $keep_checklist_item_ids);
		// ensure lesson is now marked as saved
		LessonGateway::updateIsSaved($existingTask->lesson_id, true);
		// update latest lesson
		$last_lesson_row = LessonGateway::findLastSaved($existingTask->student_id, $existingTask->teacher_id);
		UserLinkGateway::updateLastLessonByStudentTeacher($existingTask->student_id, $existingTask->teacher_id, $last_lesson_row['lesson_id']);
		// update autocomplete
		if ($title != null) {
			$autocomplete_row = AutocompleteGateway::findTaskTitleByUidText($user->uid, $title);
			if ($autocomplete_row) {
				AutocompleteGateway::updateDate($autocomplete_row['autocomplete_id'], date('Y-m-d H:i:s'));
			}
			else {
				AutocompleteGateway::insertTaskTitle($user->uid, $title, date('Y-m-d H:i:s'));
			}
		}
		if ($category_other != null) {
			$autocomplete_row = AutocompleteGateway::findTaskCategoryOtherByUidText($user->uid, $category_other);
			if ($autocomplete_row) {
				AutocompleteGateway::updateDate($autocomplete_row['autocomplete_id'], date('Y-m-d H:i:s'));
			}
			else {
				AutocompleteGateway::insertTaskCategoryOther($user->uid, $category_other, date('Y-m-d H:i:s'));
			}
		}
		foreach ($saved_checklist_texts as $text) {
			$autocomplete_row = AutocompleteGateway::findTaskChecklistItemByUidText($user->uid, $text);
			if ($autocomplete_row) {
				AutocompleteGateway::updateDate($autocomplete_row['autocomplete_id'], date('Y-m-d H:i:s'));
			}
			else {
				AutocompleteGateway::insertTaskChecklistItem($user->uid, $text, date('Y-m-d H:i:s'));
			}
		}
		// return the updated task
		return new Task(TaskGateway::find($existingTask->task_id));
	}
	
}
