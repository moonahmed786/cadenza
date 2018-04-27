<?php
class AdminComponents {
	
	static function loadAdminNavbarData(Admin $admin) {
		return array(
			'admin' => $admin
		);
	}
	
	static function getTabbedareaSelecteduserTabIndexByKey(User $user, $tab_key) {
		if ($user->user_type == 'student') {
			switch ($tab_key) {
				case 'teachers':	return 0;
				case 'invitations':	return 1;
				case 'flags':		return 2;
				case 'reports':		return 3;
				default:			return null;
			}
		}
		elseif ($user->user_type == 'teacher') {
			switch ($tab_key) {
				case 'students':	return 0;
				case 'invitations':	return 1;
				case 'flags':		return 2;
				case 'reports':		return 3;
				default:			return null;
			}
		}
		return null;
	}
	
	static function renderMiscSelecteduserActions($admin_id, User $user) {
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user
		);
		return $twig->render('admin_components/misc/selecteduser_actions.html.twig', $context);
	}
	
	static function renderMiscSelecteduserHeader($admin_id, User $user) {
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user'=>$user
		);
		return $twig->render('admin_components/misc/selecteduser_header.html.twig', $context);
	}
	
	static function renderNavbarAdmin($admin_id) {
		$debug = "renderNavbarAdmin($admin_id)";
		$admin = new Admin(AdminGateway::find($admin_id));
		$navbar_data = static::loadAdminNavbarData($admin);
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'navbar_data'=>$navbar_data,
			'debug'=>$debug
		);
		return $twig->render('admin_components/navbars/admin.html.twig', $context);
	}
	
	static function renderTableListOfBlockedUsers($admin_id, $page=null, $order_by=null, $order_direction=null, $filter_user_type=null) {
		$pagination_sortable_filterable_action = 'paginationSortableFilterableListOfBlockedUsers';
		
		$sortable_default_column = 'status_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($pagination_sortable_filterable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'user_id' => new Sortable(Language::getText('label', 'user_id'), "uid", "ASC"),
			'email' => new Sortable(Language::getText('label', 'email'), "g_email", "ASC"),
			'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC"),
			'date_blocked' => new Sortable(Language::getText('label', 'date_blocked'), "status_date", "DESC"),
		);
		
		$pagination = null;
		$page_user_rows = array();
		$user_status = 'blocked';
		if ($filter_user_type == 'student') {
			$count_users = UserGateway::countStudentsWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllStudentsWithStatus($user_status, $page_user_options);
		}
		elseif ($filter_user_type == 'teacher') {
			$count_users = UserGateway::countTeachersWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllTeachersWithStatus($user_status, $page_user_options);
		}
		else {
			$filter_user_type = null;
			$count_users = UserGateway::countAllWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllWithStatus($user_status, $page_user_options);
		}
		$page_users = array();
		foreach ($page_user_rows as $user_row) {
			$user = new User($user_row);
			$page_users[] = $user;
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'page_users' => $page_users,
			'pagination' => $pagination,
			'sortable_options' => $sortable_options,
			'filter_user_type' => $filter_user_type
		);
		return $twig->render('admin_components/tables/list_of_blocked_users.html.twig', $context);
	}
	
	static function renderTableListOfUsers($admin_id, $page=null, $order_by=null, $order_direction=null, $filter_user_type=null) {
		$pagination_sortable_filterable_action = 'paginationSortableFilterableListOfUsers';
		
		$sortable_default_column = 'uid';
		$sortable_default_direction = 'DESC';
		Sortable::init($pagination_sortable_filterable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'user_id' => new Sortable(Language::getText('label', 'user_id'), "uid", "ASC"),
			'email' => new Sortable(Language::getText('label', 'email'), "g_email", "ASC"),
			'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC")
		);
		
		$pagination = null;
		$page_user_rows = array();
		$user_status = 'active';
		if ($filter_user_type == 'student') {
			$count_users = UserGateway::countStudentsWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllStudentsWithStatus($user_status, $page_user_options);
		}
		elseif ($filter_user_type == 'teacher') {
			$count_users = UserGateway::countTeachersWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllTeachersWithStatus($user_status, $page_user_options);
		}
		else {
			$filter_user_type = null;
			$count_users = UserGateway::countAllWithStatus($user_status);
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_users, $page);
			$page_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_user_rows = UserGateway::findAllWithStatus($user_status, $page_user_options);
		}
		$page_users = array();
		foreach ($page_user_rows as $user_row) {
			$user = new User($user_row);
			$page_users[] = $user;
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'page_users' => $page_users,
			'pagination' => $pagination,
			'sortable_options' => $sortable_options,
			'filter_user_type' => $filter_user_type
		);
		return $twig->render('admin_components/tables/list_of_users.html.twig', $context);
	}
	
	static function renderTableListOfReports($admin_id, $page=null, $order_by=null, $order_direction=null, $filter_report_type=null) {
		$pagination_sortable_filterable_action = 'paginationSortableFilterableListOfReports';
		
		$sortable_default_column = 'report_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($pagination_sortable_filterable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'date' => new Sortable(Language::getText('label', 'date'), "report_date", "DESC")
		);
		
		$pagination = null;
		$page_admin_report_rows = array();
		if ($filter_report_type == 'resolved') {
			$count_admin_reports = AdminReportGateway::countResolved();
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_admin_reports, $page);
			$page_admin_report_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_admin_report_rows = AdminReportGateway::findAllResolved($page_admin_report_options);
		}
		elseif ($filter_report_type == 'unresolved') {
			$count_admin_reports = AdminReportGateway::countUnresolved();
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_admin_reports, $page);
			$page_admin_report_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_admin_report_rows = AdminReportGateway::findAllUnresolved($page_admin_report_options);
		}
		else {
			$count_admin_reports = AdminReportGateway::countAll();
			$pagination = new Pagination($pagination_sortable_filterable_action, $count_admin_reports, $page);
			$page_admin_report_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_admin_report_rows = AdminReportGateway::findAll($page_admin_report_options);
		}
		$page_admin_reports = array();
		foreach ($page_admin_report_rows as $admin_report_row) {
			$page_admin_reports[] = new AdminReport($admin_report_row);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'page_admin_reports' => $page_admin_reports,
			'pagination' => $pagination,
			'sortable_options' => $sortable_options,
			'filter_report_type' => $filter_report_type
		);
		return $twig->render('admin_components/tables/list_of_reports.html.twig', $context);
	}

	static function renderTableListOfUserConnectedUsers($admin_id, User $user, $page=null, $order_by=null, $order_direction=null) {
		$sortable_action = 'paginationSortableListOfUserConnectedUsers';
		$sortable_default_column = 'uid';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'user_id' => new Sortable(Language::getText('label', 'user_id'), "uid", "ASC"),
			'email' => new Sortable(Language::getText('label', 'email'), "g_email", "ASC"),
			'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC")
		);
		
		$pagination = null;
		$page_connected_user_rows = array();
		$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
		if ($user->user_type == 'student') {
			$count_connected_teachers = UserGateway::countTeachersLinkedToStudent($user->uid, $link_statuses_connected);
			$records_per_page = Pagination::RECORDS_PER_PAGE_SHORT;
			$pagination = new Pagination('paginationSortableListOfUserConnectedUsers', $count_connected_teachers, $page, $records_per_page);
			$page_connected_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_connected_user_rows = UserGateway::findAllTeachersLinkedToStudent($user->uid, $link_statuses_connected, $page_connected_user_options);
		}
		elseif ($user->user_type == 'teacher') {
			$count_connected_students = UserGateway::countStudentsLinkedToTeacher($user->uid, $link_statuses_connected);
			$records_per_page = Pagination::RECORDS_PER_PAGE_SHORT;
			$pagination = new Pagination('paginationSortableListOfUserConnectedUsers', $count_connected_students, $page, $records_per_page);
			$page_connected_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_connected_user_rows = UserGateway::findAllStudentsLinkedToTeacher($user->uid, $link_statuses_connected, $page_connected_user_options);
		}
		$page_connected_users = array();
		foreach ($page_connected_user_rows as $connected_user_row) {
			$page_connected_users[] = new User($connected_user_row);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user' => $user,
			'page_connected_users' => $page_connected_users,
			'pagination' => $pagination,
			'sortable_options' => $sortable_options
		);
		return $twig->render('admin_components/tables/list_of_user_connected_users.html.twig', $context);
	}

	static function renderTableListOfUserInvitations($admin_id, User $user, $page=null, $order_by=null, $order_direction=null) {
		$sortable_action = 'paginationSortableListOfUserInvitations';
		$sortable_default_column = 'status_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = null;
		$page_invite_rows = null;
		if ($user->user_type == 'student') {
			$sortable_options = array(
				'email' => new Sortable(Language::getText('label', 'email'), "teacher_email", "ASC"),
				'date' => new Sortable(Language::getText('label', 'date'), "status_date", "DESC")
			);
			
			$count_invites = UserLinkGateway::countInvitesOfStudent($user->uid);
			$records_per_page = Pagination::RECORDS_PER_PAGE_LONG;
			$pagination = new Pagination('paginationSortableListOfUserInvitations', $count_invites, $page, $records_per_page);
			$page_invite_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_invite_rows = UserLinkGateway::findAllInvitesByStudent($user->uid, $page_invite_options);
		}
		elseif ($user->user_type == 'teacher') {
			$sortable_options = array(
				'email' => new Sortable(Language::getText('label', 'email'), "student_email", "ASC"),
				'date' => new Sortable(Language::getText('label', 'date'), "status_date", "DESC")
			);
			
			$count_invites = UserLinkGateway::countInvitesOfTeacher($user->uid);
			$records_per_page = Pagination::RECORDS_PER_PAGE_LONG;
			$pagination = new Pagination('paginationSortableListOfUserInvitations', $count_invites, $page, $records_per_page);
			$page_invite_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_invite_rows = UserLinkGateway::findAllInvitesByTeacher($user->uid, $page_invite_options);
		}
		
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user' => $user,
			'page_invite_rows' => $page_invite_rows,
			'pagination' => $pagination,
			'sortable_options' => $sortable_options
		);
		return $twig->render('admin_components/tables/list_of_user_invitations.html.twig', $context);
	}
	
	static function renderTableListOfUserFlags($admin_id, User $user, $order_by=null, $order_direction=null) {
		$sortable_action = 'sortableListOfUserFlags';
		$sortable_default_column = 'report_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'date' => new Sortable(Language::getText('label', 'date'), "report_date", "DESC")
		);
		
		$admin_report_rows = AdminReportGateway::findAllByReportedUser($user->uid, array('orderby'=>Sortable::get_order_by_string()));
		$admin_reports = array();
		foreach ($admin_report_rows as $admin_report_row) {
			$admin_reports[] = new AdminReport($admin_report_row);
		}
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user' => $user,
			'admin_reports' => $admin_reports,
			'sortable_options' => $sortable_options
		);
		return $twig->render('admin_components/tables/list_of_user_flags.html.twig', $context);
	}
	
	static function renderTableListOfUserReports($admin_id, User $user, $order_by=null, $order_direction=null) {
		$sortable_action = 'sortableListOfUserReports';
		$sortable_default_column = 'report_date';
		$sortable_default_direction = 'DESC';
		Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
		
		$sortable_options = array(
			'date' => new Sortable(Language::getText('label', 'date'), "report_date", "DESC")
		);
		
		$admin_report_rows = AdminReportGateway::findAllByReporterUser($user->uid, array('orderby'=>Sortable::get_order_by_string()));
		$admin_reports = array();
		foreach ($admin_report_rows as $admin_report_row) {
			$admin_reports[] = new AdminReport($admin_report_row);
		}
		$twig = new Twig_Environment_Cadenza();
		$context = array(
			'user' => $user,
			'admin_reports' => $admin_reports,
			'sortable_options' => $sortable_options
		);
		return $twig->render('admin_components/tables/list_of_user_reports.html.twig', $context);
	}
		
}