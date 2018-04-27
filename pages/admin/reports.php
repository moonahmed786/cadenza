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
$filter_report_type = isset($_REQUEST['filter_report_type']) ? $_REQUEST['filter_report_type'] : null;
$filter_report_type_index = 0;
if ($filter_report_type == 'resolved') {
	$filter_report_type_index = 1;
}
elseif ($filter_report_type == 'unresolved') {
	$filter_report_type_index = 2;
}
else {
	$filter_report_type = null;
}
Redirect::done();

$admin = Login::getCurrentAdmin();
$navbar_data = AdminComponents::loadAdminNavbarData($admin);

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
	'admin' => $admin,
	'navbar_data' => $navbar_data,
	'page_admin_reports' => $page_admin_reports,
	'pagination' => $pagination,
	'sortable_options' => $sortable_options,
	'filter_report_type' => $filter_report_type,
	'filter_report_type_index' => $filter_report_type_index,
	'filter_report_type_action' => $pagination_sortable_filterable_action
);
print $twig->render('pages/admin/reports.html.twig', $context);
