<?php
// Start session and define SQLite3 Database
require __DIR__ . '/db_sqlite3/db_con.php';

// Your default configuration
include __DIR__ . '/db_sqlite3/config.php';

// Setup your admin account
$your_username = 'admin';
$your_fname = 'Mark';
$your_lname = 'Pelayo';
$your_email = 'dummy@email.com';
$your_password = 'admin';

createAdminAccount($your_username, $your_fname, $your_lname, $your_email, $your_password);

// Setup your website
$your_title = 'Mark Pelayo';
$your_description = 'Just an average web developer';
$your_profile_link = '/assets/img/default.png';

createSiteConfig($your_title, $your_description, $your_profile_link);

// Create default
defaultCategory();
defaultBlogContent();

// Page routing
include __DIR__ . '/php_library/default_api/route.php';