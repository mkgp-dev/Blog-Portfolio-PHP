<?php
error_reporting(1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
	// Avoid CSRF attacks
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => $_SERVER['HTTP_HOST'],
		'secure' => true,
		'httponly' => true,
		'samesite' => 'Strict'
	]);

	session_start();
	//session_regenerate_id(true);

	// Timeout
	if (isset($_SESSION['user_in_session']) && (time() - $_SESSION['user_in_session'] > 1800)) {
		// in 30 minutes, user will be forced to logout/all session will be destroyed
		session_unset();
		session_destroy();
		header('Location: /');
		exit();
	}

	// Basic individual user tracking
	if (!isset($_SESSION['visitor_ip'], $_SESSION['visitor_ua'])) {
		$_SESSION['visitor_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['visitor_ua'] = $_SERVER['HTTP_USER_AGENT'];
	} else {
		if ($_SESSION['visitor_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['visitor_ua'] !== $_SERVER['HTTP_USER_AGENT']) {
			session_unset();
			session_destroy();
			header('Location: /');
			exit();
		}
	}
}

// Using SQLite3 method
// Notes: make sure sqlite3 is enabled in php.ini
function dbConnection() {
	$db = new SQLite3(__DIR__ . '/default.db');

	// Create USERS table
	$db->exec("
		CREATE TABLE IF NOT EXISTS users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			username TEXT NOT NULL UNIQUE,
			firstname TEXT NOT NULL,
			lastname TEXT NOT NULL,
			email TEXT NOT NULL UNIQUE,
			password TEXT NOT NULL,
			privilege TEXT NOT NULL,
			registration_date TEXT NOT NULL
	)");

	// Create CONTENTS table
	$db->exec("
		CREATE TABLE IF NOT EXISTS contents (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			blog_category TEXT NOT NULL,
			blog_title TEXT NOT NULL UNIQUE,
			blog_description TEXT NOT NULL UNIQUE,
			blog_content TEXT NOT NULL,
			blog_slug TEXT NOT NULL UNIQUE,
			date_of_creation TEXT NOT NULL
	)");

	// Create CATEGORIES table
	$db->exec("
		CREATE TABLE IF NOT EXISTS categories (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL UNIQUE
	)");

	// Create SITE_CONFIG table
	$db->exec("
		CREATE TABLE IF NOT EXISTS site_config (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			setting_name TEXT NOT NULL UNIQUE,
			setting_value TEXT NOT NULL
	)");

	return $db;
}