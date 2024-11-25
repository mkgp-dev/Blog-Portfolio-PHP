<?php
// Routing
$page = $_GET['page'];
$admin = $_GET['admin'];
$module = $_GET['module'];
$post = $_GET['post'];
$category = $_GET['category'];

if (isset($page)) {
	switch ($page) {
		case 'search':
			$page_search = true;
			include 'php_template/default/search.php';
			break;
		case 'about-me':
			$page_portfolio = true;
			include 'php_template/default/portfolio.php';
			break;
		case 'donate':
			$page_donate = true;
			include 'php_template/default/donate.php';
			break;
		default:
			$page_home = true;
			include 'php_template/default/home.php';
			break;
	}
} else if (isset($admin)) {
	switch ($admin) {
		case 'contents':
			$page_content = true;
			include 'php_template/dashboard/content.php';
			break;
		case 'settings':
			$page_settings = true;
			include 'php_template/dashboard/settings.php';
			break;
		case 'logout':
			include 'php_template/dashboard/logout.php';
			break;
		default:
			include 'php_template/dashboard/login.php';
			break;
	}
} else if (isset($module)) {
	switch ($module) {
		/**
		case 'user':
			include 'php_library/default_api/user_module.php';
			break;
		case 'list':
			include 'php_library/default_api/list_module.php';
			break;
		case 'content':
			include 'php_library/default_api/content_module.php';
			break;
		case 'new':
			include 'php_library/default_api/new_module.php';
			break;
		**/
		// Main APIs
		case 'public':
			include 'php_library/default_api/public_module.php';
			break;
		case 'v1':
			include 'php_library/default_api/admin_module.php';
			break;
		default:
			echo json_encode(['status' => 'error', 'message' => 'Invalid API request']);
			break;
	}
} else if (isset($post)) {
	include 'php_template/default/post.php';
} else if (isset($category)) {
	$page_category = true;
	include 'php_template/default/category.php';
} else {
	$page_home = true;
	include 'php_template/default/home.php';
}