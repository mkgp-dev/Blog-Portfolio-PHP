<?php
// Some indications/variables
$linkAsset = '/assets';

// Final APIs
$apiPublic = '/api/public';
$apiAdmin = '/api/v1';

// Draft APIs
//$apiUser = '/api/user';
//$apiList = '/api/list';
//$apiContent = '/api/content';
//$apiNew = '/api/new';


function createAdminAccount($username, $fname, $lname, $email, $password) {
	// If its empty, we will make a default configuration of mine
	if (!isset($username, $fname, $lname, $email, $password)) {
		$username = 'administrator';
		$fname = 'Mark';
		$lname = 'Pelayo';
		$email = 'admin@localhost.dev';
		$password = 'admin123';
	}

	// Simple call for DB
	$db = dbConnection();
	//$result = $db->querySingle("SELECT COUNT(*) as count FROM users WHERE username = '{$username}'");
	$result = $db->querySingle("SELECT COUNT(*) as count FROM users");
	if ($result == 0) {
		$hashpsswd = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, email, password, privilege, registration_date) VALUES (:username, :firstname, :lastname, :email, :password, :privilege, :registration_date)");
		$stmt->bindValue(':username', $username, SQLITE3_TEXT);
		$stmt->bindValue(':firstname', $fname, SQLITE3_TEXT);
		$stmt->bindValue(':lastname', $lname, SQLITE3_TEXT);
		$stmt->bindValue(':email', $email, SQLITE3_TEXT);
		$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
		$stmt->bindValue(':privilege', 'administrator', SQLITE3_TEXT);
		$stmt->bindValue(':registration_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
		$stmt->execute();
	}
}

function createSiteConfig($title, $description, $profile_link) {
	// If its empty, we will make a default configuration of mine
	if (!isset($title, $description, $profile_link)) {
		$title = 'Mark Pelayo';
		$description = 'Just an amateur web developer';
		$profile_link = '/assets/img/default.png';
	}

	// Simple call for DB
	$db = dbConnection();
	$insertSettings = [
		'site_title' => $title,
		'site_description' => $description,
		'blog_picture' => $profile_link
	];

	foreach ($insertSettings as $name => $value) {
		$stmt = $db->prepare("INSERT OR IGNORE INTO site_config (setting_name, setting_value) VALUES (:name, :value)");
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':value', $value, SQLITE3_TEXT);
		$stmt->execute();
	}
}

/**
function defaultAdminAccount() {
	$db = dbConnection();
	$result = $db->querySingle("SELECT COUNT(*) as count FROM users WHERE username = 'administrator'");
	if ($result == 0) {
		$hashpsswd = password_hash('admin123', PASSWORD_DEFAULT);
		$stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, email, password, privilege, registration_date) VALUES (:username, :firstname, :lastname, :email, :password, :privilege, :registration_date)");
		$stmt->bindValue(':username', 'administrator', SQLITE3_TEXT);
		$stmt->bindValue(':firstname', 'Mark', SQLITE3_TEXT);
		$stmt->bindValue(':lastname', 'Pelayo', SQLITE3_TEXT);
		$stmt->bindValue(':email', 'admin@localhost.com', SQLITE3_TEXT);
		$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
		$stmt->bindValue(':privilege', 'administrator', SQLITE3_TEXT);
		$stmt->bindValue(':registration_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
		$stmt->execute();
	}
}


function defaultSiteConfig() {
	$db = dbConnection();
	$defaultSettings = [
		'site_title' => 'Mark Pelayo',
		'site_description' => 'Just an amateur web developer',
		'blog_picture' => 'https://avatars.githubusercontent.com/u/188409627?v=4'
	];

	foreach ($defaultSettings as $name => $value) {
		$stmt = $db->prepare("INSERT OR IGNORE INTO site_config (setting_name, setting_value) VALUES (:name, :value)");
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':value', $value, SQLITE3_TEXT);
		$stmt->execute();
	}
}

**/

function defaultCategory() {
	$db = dbConnection();
	$result = $db->querySingle("SELECT COUNT(*) as count FROM categories");
	if ($result == 0) {
		$stmt = $db->prepare("INSERT INTO categories (name) VALUES (:category_name)");
		$stmt->bindValue(':category_name', 'Uncategorized', SQLITE3_TEXT);
		$stmt->execute();
	}
}

function defaultBlogContent() {
	$db = dbConnection();
	$result = $db->querySingle("SELECT COUNT(*) as count FROM contents");
	if ($result == 0) {
		$stmt = $db->prepare("INSERT INTO contents (blog_category, blog_title, blog_description, blog_content, blog_slug, date_of_creation) VALUES (:blog_category, :blog_title, :blog_description, :blog_content, :blog_slug, :date_of_creation)");
		$stmt->bindValue(':blog_category', 'Uncategorized', SQLITE3_TEXT);
		$stmt->bindValue(':blog_title', 'Hello, World!', SQLITE3_TEXT);
		$stmt->bindValue(':blog_description', 'This is my first blog post', SQLITE3_TEXT);
		$stmt->bindValue(':blog_content', '<p>This is a sample content with lorem no ipsum</p><p><i>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eu laoreet libero. Duis orci eros, eleifend mollis eleifend dapibus, ullamcorper at elit. Integer euismod, risus ut pellentesque posuere, lacus odio aliquet nisi, malesuada lacinia risus turpis non augue. Aliquam at malesuada turpis, vitae suscipit nulla. Vestibulum egestas rhoncus mi, at lacinia magna fermentum et. Vivamus cursus convallis aliquet. Maecenas eu tortor est. Donec efficitur sed nulla a ultricies. In mollis volutpat sem non blandit. Ut ultricies aliquet posuere. Maecenas auctor orci non erat ultrices, quis tincidunt libero rutrum. Etiam scelerisque ornare vulputate. Nam elit leo, malesuada ac turpis non, aliquam lobortis erat. Cras dictum egestas quam vel vestibulum. Morbi rhoncus aliquet consequat.</i></p>', SQLITE3_TEXT);
		$stmt->bindValue(':blog_slug', 'hello-world', SQLITE3_TEXT);
		$stmt->bindValue(':date_of_creation', date('Y-m-d H:i:s'), SQLITE3_TEXT);
		$stmt->execute();
	}
}