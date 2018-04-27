<?php
require_once dirname(__FILE__).'/../../_cadenza/Core.php';
Core::init();

if (isset($_REQUEST['action']) && $_REQUEST['action']) {
	ActionHandler::action($_REQUEST['action']);
	exit;
}

if (!Login::isLoggedInAsAdmin()) {
	Redirect::set('admin/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : null;
$tab_key = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
$isValidParams = ($uid != null && is_numeric($uid));
if (!$isValidParams) {
	Redirect::set('admin/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
$user_row = UserGateway::find($uid);
$isValidUser = $user_row && $user_row['status'] != 'deleted' ? true : false;
if (!$isValidUser) {
	Redirect::set('admin/invalid'); // will check login status and handle accordingly
	Redirect::go();
}
Redirect::done();

$admin = Login::getCurrentAdmin();
$navbar_data = AdminComponents::loadAdminNavbarData($admin);

$user = new User($user_row);
$tab_index = null;
$page_connected_users = null;
$page_invite_rows = null;
$admin_reports = null;
$pagination = null;
$sortable_options = null;
if ($user->user_type == 'student') {
	$tab_index = AdminComponents::getTabbedareaSelecteduserTabIndexByKey($user, $tab_key);
	if ($tab_index == null) {
		$tab_key = 'teachers';
	}
	
	switch ($tab_key) {
		case 'teachers':
			$sortable_action = 'paginationSortableListOfUserConnectedUsers';
			$sortable_default_column = 'uid';
			$sortable_default_direction = 'DESC';
			Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
			
			$sortable_options = array(
				'user_id' => new Sortable(Language::getText('label', 'user_id'), "uid", "ASC"),
				'email' => new Sortable(Language::getText('label', 'email'), "g_email", "ASC"),
				'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC")
			);
			
			$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
			$count_connected_teachers = UserGateway::countTeachersLinkedToStudent($user->uid, $link_statuses_connected);
			$records_per_page = Pagination::RECORDS_PER_PAGE_SHORT;
			$pagination = new Pagination('paginationSortableListOfUserConnectedUsers', $count_connected_teachers, $page, $records_per_page);
			$page_connected_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_connected_user_rows = UserGateway::findAllTeachersLinkedToStudent($user->uid, $link_statuses_connected, $page_connected_user_options);
			$page_connected_users = array();
			foreach ($page_connected_user_rows as $connected_user_row) {
				$page_connected_users[] = new User($connected_user_row);
			}
			break;
		case 'invitations':
			$sortable_action = 'paginationSortableListOfUserInvitations';
			$sortable_default_column = 'status_date';
			$sortable_default_direction = 'DESC';
			Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
			
			$sortable_options = array(
				'email' => new Sortable(Language::getText('label', 'email'), "teacher_email", "ASC"),
				'date' => new Sortable(Language::getText('label', 'date'), "status_date", "DESC")
			);
			
			$count_invites = UserLinkGateway::countInvitesOfStudent($user->uid);
			$records_per_page = Pagination::RECORDS_PER_PAGE_LONG;
			$pagination = new Pagination('paginationSortableListOfUserInvitations', $count_invites, $page, $records_per_page);
			$page_invite_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_invite_rows = UserLinkGateway::findAllInvitesByStudent($user->uid, $page_invite_options);
			break;
		case 'flags':
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
			break;
		case 'reports':
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
			break;
	}
}
elseif ($user->user_type == 'teacher') {
	$tab_index = AdminComponents::getTabbedareaSelecteduserTabIndexByKey($user, $tab_key);
	if ($tab_index == null) {
		$tab_key = 'students';
	}
	
	switch ($tab_key) {
		case 'students':
			$sortable_action = 'paginationSortableListOfUserConnectedUsers';
			$sortable_default_column = 'uid';
			$sortable_default_direction = 'DESC';
			Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
			
			$sortable_options = array(
				'user_id' => new Sortable(Language::getText('label', 'user_id'), "uid", "ASC"),
				'email' => new Sortable(Language::getText('label', 'email'), "g_email", "ASC"),
				'name' => new Sortable(Language::getText('label', 'name'), "g_name", "ASC")
			);
			
			$link_statuses_connected = UserLinkGateway::getStatusArrayConnected();
			$count_connected_students = UserGateway::countStudentsLinkedToTeacher($user->uid, $link_statuses_connected);
			$records_per_page = Pagination::RECORDS_PER_PAGE_SHORT;
			$pagination = new Pagination('paginationSortableListOfUserConnectedUsers', $count_connected_students, $page, $records_per_page);
			$page_connected_user_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_connected_user_rows = UserGateway::findAllStudentsLinkedToTeacher($user->uid, $link_statuses_connected, $page_connected_user_options);
			$page_connected_users = array();
			foreach ($page_connected_user_rows as $connected_user_row) {
				$page_connected_users[] = new User($connected_user_row);
			}
			break;
		case 'invitations':
			$sortable_action = 'paginationSortableListOfUserInvitations';
			$sortable_default_column = 'status_date';
			$sortable_default_direction = 'DESC';
			Sortable::init($sortable_action, $sortable_default_column, $sortable_default_direction, $order_by, $order_direction);
			
			$sortable_options = array(
				'email' => new Sortable(Language::getText('label', 'email'), "student_email", "ASC"),
				'date' => new Sortable(Language::getText('label', 'date'), "status_date", "DESC")
			);
			
			$count_invites = UserLinkGateway::countInvitesOfTeacher($user->uid);
			$records_per_page = Pagination::RECORDS_PER_PAGE_LONG;
			$pagination = new Pagination('paginationSortableListOfUserInvitations', $count_invites, $page, $records_per_page);
			$page_invite_options = array('orderby'=>Sortable::get_order_by_string(), 'limit'=>$pagination->get_limit_params());
			$page_invite_rows = UserLinkGateway::findAllInvitesByTeacher($user->uid, $page_invite_options);
			break;
		case 'flags':
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
			break;
		case 'reports':
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
			break;
	}
}

$twig = new Twig_Environment_Cadenza();
$context = array(
	'admin' => $admin,
	'navbar_data' => $navbar_data,
	'user' => $user,
	'page_connected_users' => $page_connected_users,
	'page_invite_rows' => $page_invite_rows,
	'admin_reports' => $admin_reports,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options,
	'tab_index' => $tab_index
);
print $twig->render('pages/admin/view_user.html.twig', $context);
