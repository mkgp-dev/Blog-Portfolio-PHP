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

if (isset($data['page'], $data['limit'])) {
	try {
		$user = $data['username'];
		$view_limit = $data['limit'];
		$page = isset($data['page']) ? (int)$data['page'] : 1;
		$offset = ($page - 1) * $view_limit;

		$db = dbConnection();

		// Call table.users
		if ($user) {
			$user = htmlspecialchars(stripslashes(trim($user)));
			$countResult = $db->querySingle("SELECT COUNT(*) FROM users WHERE username LIKE '%{$user}%'");

			$sql_query = "SELECT id, username, firstname, lastname, email, privilege, registration_date FROM users WHERE username LIKE '%{$user}%' LIMIT {$view_limit} OFFSET {$offset}";
		} else {
			$countResult = $db->querySingle("SELECT COUNT(*) FROM users");

			$sql_query = "SELECT id, username, firstname, lastname, email, privilege, registration_date FROM users LIMIT {$view_limit} OFFSET {$offset}";
		}

		// Count
		$totalRecords = (int)$countResult;
		$totalPages = ceil($totalRecords / $view_limit);
		
		$stmt = $db->prepare($sql_query);
		$results = $stmt->execute();

		$users = [];
		while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
			$users[] = $row;
		}

		// Show results
		echo json_encode([
			'status' => 'success',
			'data' => $users,
			'pagination' => [
				'current_page' => $page,
				'page_limit' => $view_limit,
				'overall' => $totalRecords,
				'pages' => $totalPages
			]
		]);
	} catch (Exception $e) {
		echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
	} finally {
		if ($db) {
			$db->close();
		}
	}
} else if (isset($data['action'])) {
	if ($data['action'] === 'fetch_admin_profile') {
		try {
			$db = dbConnection();

			$sql_query = "SELECT id, username, firstname, lastname, email, privilege, registration_date FROM users WHERE username LIKE '%{$username}%' LIMIT 1";
			$stmt = $db->prepare($sql_query);
			$results = $stmt->execute();

			$row = $results->fetchArray(SQLITE3_ASSOC);

			echo json_encode([
				'status' => 'success',
				'data' => $row
			]);
		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($data['action'] === 'fetch_site_config') {
		try {
			$db = dbConnection();
			$result = $db->query("SELECT * FROM site_config");
			$config = [];
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$config[$row['setting_name']] = $row['setting_value'];
			}

			echo json_encode([
				'status' => 'success',
				'data' => $config
			]);
		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	}
}