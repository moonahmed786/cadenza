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
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : null;
$order_direction = isset($_REQUEST['order_direction']) ? $_REQUEST['order_direction'] : null;
$filter_user_type = isset($_REQUEST['filter_user_type']) ? $_REQUEST['filter_user_type'] : null;
$filter_user_type_index = 0;
if ($filter_user_type == 'student') {
	$filter_user_type_index = 1;
}
elseif ($filter_user_type == 'teacher') {
	$filter_user_type_index = 2;
}
else {
	$filter_user_type = null;
}
Redirect::done();

$admin = Login::getCurrentAdmin();
$navbar_data = AdminComponents::loadAdminNavbarData($admin);

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
	'admin' => $admin,
	'navbar_data' => $navbar_data,
	'page_users' => $page_users,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options,
	'filter_user_type' => $filter_user_type,
	'filter_user_type_index' => $filter_user_type_index,
	'filter_user_type_action' => $pagination_sortable_filterable_action
);
print $twig->render('pages/admin/users.html.twig', $context);
