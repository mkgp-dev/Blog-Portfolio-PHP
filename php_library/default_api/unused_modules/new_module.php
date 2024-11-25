<?php
//require_once __DIR__ . '/../../db_sqlite3/config.php';

// Check session if user has admin rights
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'administrator') {
	http_response_code(403);
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized request']);
	exit();
}

// Data
$data = json_decode(file_get_contents('php://input'), true);

$db = dbConnection();
if (isset($data['action'])) {
	if($data['action'] === 'update') {
		try {
			$updateSettings = [
				'site_title' => $data['title'],
				'site_description' => $data['description'],
				'blog_picture' => $data['profile_img']
			];

			foreach ($updateSettings as $key => $value) {
				$stmt = $db->prepare("UPDATE site_config SET setting_value = :value WHERE setting_name = :name");
				$stmt->bindValue(':value', $value, SQLITE3_TEXT);
				$stmt->bindValue(':name', $key, SQLITE3_TEXT);
				$stmt->execute();
			}

			echo json_encode(['status' => 'success', 'message' => 'Configuration updated successfully']);
		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	}
}