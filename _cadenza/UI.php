<?php
class UI {
	
	static $icons = null;
	
	static function getIcons() {
		if (static::$icons == null) {
			// button icons
			$btnIcons = array(
				'chk_off'			=> '<i class="fa fa-square-o"></i>',
				'chk_on'			=> '<i class="fa fa-check-square"></i>',
				'chk_on_ro'			=> '<i class="fa fa-check-square-o"></i>',
				'chk_partial'		=> '<i class="fa fa-minus-square"></i>',
				'chk_ul_ro'			=> '<strong>&bull;</strong>',
				'close'				=> '<i class="fa fa-times"></i>',
				'close_circle'		=> '<i class="fa fa-times-circle"></i>',
				'delete'			=> '<i class="fa fa-trash-o"></i>',
				'disconnect'		=> '<i class="fa fa-chain-broken"></i>',
				'edit'				=> '<i class="fa fa-pencil"></i>',
				'file'				=> '<i class="fa fa-file-o"></i>',
				'go'				=> '<i class="fa fa-chevron-right"></i>',
				'goals'				=> '<strong>G</strong>',
				'lesson'			=> '<i class="fa fa-music"></i>',
				'lessonlist'		=> '<i class="fa fa-th-list"></i>',
				'loading'			=> '<i class="fa fa-spinner fa-spin"></i>',
				'new'				=> '<i class="fa fa-plus-circle"></i>',
				'next'				=> '<i class="fa fa-caret-right"></i>',
				'notes'				=> '<i class="fa fa-file-text"></i>',
				'notify'			=> '<i class="fa fa-share-square fa-flip-horizontal"></i>',
				'pause'				=> '<i class="fa fa-pause-circle"></i>',
				'prev'				=> '<i class="fa fa-caret-left"></i>',
				'quickreflection'	=> '<i class="fa fa-smile-o"></i>',
				'reflection'		=> '<i class="fa fa-book"></i>',
				'refresh'			=> '<i class="fa fa-refresh"></i>',
				'remove'			=> '<i class="fa fa-times-circle"></i>',
				'reply'				=> '<i class="fa fa-reply fa-flip-vertical"></i>',
				'reply_indicator'	=> '<i class="fa fa-reply fa-rotate-180"></i>',
				'report'			=> '<i class="fa fa-exclamation-triangle"></i>',
				'resume'			=> '<i class="fa fa-play-circle"></i>',
				'save'				=> '<i class="fa fa-check-circle"></i>',
				'start'				=> '<i class="fa fa-flag"></i>',
				'targettype1'		=> '<i class="fa fa-check"></i>',
				'targettype2'		=> '<i class="fa fa-repeat"></i>',
				'targettype3'		=> '<i class="fa fa-star"></i>',
				'targettype4'		=> '<i class="fa fa-clock-o"></i>',
				'timer'				=> '<i class="fa fa-clock-o"></i>',
				'toggle_hide'		=> '<i class="fa fa-caret-square-o-up"></i>',
				'toggle_show'		=> '<i class="fa fa-caret-square-o-down"></i>'
			);
			// data icons
			$dataIcons = array(
				'targets'			=> '<i class="fa fa-bullseye"></i>',
				'time_spent'		=> '<i class="fa fa-clock-o"></i>'
			);
			// indicator icons
			$indicatorIcons = array(
				'has_annotator'		=> '<i class="fa fa-video-camera"></i>',
				'has_attachment'	=> '<i class="fa fa-paperclip fa-flip-horizontal fa-flip-vertical"></i>',
				'has_comment'		=> '<i class="fa fa-comment"></i>',
				'is_notified'		=> $btnIcons['notify']
			);
			// menu icons
			$mnuIcons = array(
				'add_students'		=> '<i class="fa fa-user-plus"></i>',
				'blocked_users'		=> '<i class="fa fa-user-times"></i>',
				'help'				=> '<i class="fa fa-question-circle "></i>',
				'latest_lesson'		=> $btnIcons['lesson'],
				'list_of_lessons'	=> $btnIcons['lessonlist'],
				'list_of_reports'	=> '<i class="fa fa-th-list"></i>',
				'list_of_students'	=> '<i class="fa fa-user"></i>',
				'list_of_teachers'	=> '<i class="fa fa-user"></i>',
				'list_of_users'		=> '<i class="fa fa-user"></i>',
				'logout'			=> '<i class="fa fa-sign-out"></i>',
				'my_account'		=> '<i class="fa fa-info-circle"></i>',
				'switch_accounts' 	=> '<i class="fa fa-exchange"></i>'
			);
			// navbar icons
			$navbarIcons = array(
				'home'				=> '<i class="fa fa-home"></i>',
				'menu'				=> '<i class="fa fa-bars"></i>',
				'notifications'		=> '<i class="fa fa-bell"></i>',
				'rewards'			=> '<i class="fa fa-star"></i>',
				'search'			=> '<i class="fa fa-search"></i>'
			);
			// sort icons
			$sortIcons = array(
				'sortable'			=> '<i class="fa fa-sort"></i>',
				'sorted-asc'		=> '<i class="fa fa-sort-asc"></i>',
				'sorted-desc'		=> '<i class="fa fa-sort-desc"></i>'
			);
			static::$icons = array('btn'=>$btnIcons, 'data'=>$dataIcons, 'indicator'=>$indicatorIcons, 'mnu'=>$mnuIcons, 'navbar'=>$navbarIcons, 'sort'=>$sortIcons);
		}
		return static::$icons;
	}
	
	static function getIconsInCategory($category) {
		$icons = static::getIcons(); // before returning anything, call getIcons to ensure the icons get loaded
		return isset($icons[$category]) ? $icons[$category] : null;
	}
	
}
