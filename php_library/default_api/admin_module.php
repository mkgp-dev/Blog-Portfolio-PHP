<?php
//require_once __DIR__ . '/../../db_sqlite3/config.php';

// Check session if user has admin rights
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'administrator') {
	http_response_code(403);
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized request']);
	exit();
}

// Function
function createSlug($title) {
	$slug = strtolower($title);
	$slug = preg_replace('/[^\w\s-]/', '', $slug);
	$slug = preg_replace('/[\s-]+/', '-', $slug);
	$slug = trim($slug, '-');
	return $slug;
}

// Data
$data = json_decode(file_get_contents('php://input'), true);
$call_action = $data['action'];

if (isset($call_action) && !empty($call_action)) {
	$db = dbConnection();

	if ($call_action === 'fetch_admin_data') {
		try {
			$sql_query = "SELECT id, username, firstname, lastname, email, registration_date FROM users WHERE username LIKE '%{$_SESSION['username']}%' LIMIT 1";
			$stmt = $db->prepare($sql_query);
			$results = $stmt->execute();

			$row = $results->fetchArray(SQLITE3_ASSOC);

			// Generate new id
			//session_regenerate_id(true);

			echo json_encode([
				'status' => 'success',
				'data' => $row
			]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'fetch_site_config') {
		try {
			$result = $db->query("SELECT * FROM site_config");
			$config = [];
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$config[$row['setting_name']] = $row['setting_value'];
			}

			// Generate new id
			//session_regenerate_id(true);

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
	} else if ($call_action === 'edit_profile') {
		try {
			$id = intval($data['userid']); // hidden so no need to check if it has data or not
			$fname = htmlspecialchars(stripslashes(trim($data['fname'])));
			$lname = htmlspecialchars(stripslashes(trim($data['lname'])));
			$email = htmlspecialchars(stripslashes(trim($data['email'])));
			$username = htmlspecialchars(stripslashes(trim($data['username'])));
			$password = isset($data['password']) ? htmlspecialchars(stripslashes(trim($data['password']))) : null;

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
				exit();
			}

			$db->exec('BEGIN TRANSACTION');
			$stmt = $db->prepare("UPDATE users SET firstname = :fname, lastname = :lname, email = :email, username = :username WHERE id = :id");
			$stmt->bindValue(':fname', $fname, SQLITE3_TEXT);
			$stmt->bindValue(':lname', $lname, SQLITE3_TEXT);
			$stmt->bindValue(':email', $email, SQLITE3_TEXT);
			$stmt->bindValue(':username', $username, SQLITE3_TEXT);
			$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
			$result = $stmt->execute();

			if (!$result) {
				$message_log = $db->lastErrorMsg();
			} else {
				$user_updated = true;
			}

			if (!empty($password) && $user_updated) {
				$hashpsswd = password_hash($password, PASSWORD_DEFAULT);
				$stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
				$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $stmt->execute();

				if (!$result) {
					$message_log = $db->lastErrorMsg();
				} else {
					// Generate new id
					session_regenerate_id(true);

					$db->exec('COMMIT');
					echo json_encode(['status' => 'success']);
				}
			} else {
				// Generate new id
				session_regenerate_id(true);

				$db->exec('COMMIT');
				echo json_encode(['status' => 'success']);
			}
		} catch (Exeception $e) {
			$db->exec('ROLLBACK');
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'edit_site') {
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

			// Generate new id
			session_regenerate_id(true);

			echo json_encode(['status' => 'success', 'message' => 'Configuration updated successfully']);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'fetch_category') {
		try {
			// Call table.categories and table.contents.blog_category
			$sql_query = "SELECT categories.name AS category_name, categories.id AS category_id, COUNT(contents.id) AS post_count FROM categories LEFT JOIN contents ON categories.name = contents.blog_category GROUP BY categories.name ORDER BY contents.id DESC";
			$results = $db->query($sql_query);

			$categories = [];
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				$categories[] = [
					'id' => $row['category_id'],
					'category_name' => $row['category_name'],
					'total' => $row['post_count']
				];
			}

			// Show results
			echo json_encode([
				'status' => 'success',
				'data' => $categories,
			]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'fetch_content') {
		try {
			$titleQuery = htmlspecialchars(stripslashes(trim($data['title'])));
			$title = isset($titleQuery) ? $titleQuery : '';

			// Pagination limit
			$view_limit = isset($data['limit']) ? (int)$data['limit'] : 10;
			$page = isset($data['page']) ? (int)$data['page'] : 1;
			$offset = ($page - 1) * $view_limit;


			// Count query
			$cSql = "SELECT COUNT(*) FROM contents WHERE blog_title LIKE :title";
			$cStmt = $db->prepare($cSql);
			$cStmt->bindValue(':title', "%{$title}%", SQLITE3_TEXT);
			$callResult = $cStmt->execute()->fetchArray(SQLITE3_ASSOC);
			$countResult = $callResult['COUNT(*)'];

			// Retrieve data
			$dSql = "SELECT id, blog_title, blog_description, blog_content, blog_slug, blog_category, date_of_creation FROM contents WHERE blog_title LIKE :title LIMIT :limit OFFSET :offset";
			$dStmt = $db->prepare($dSql);
			$dStmt->bindValue(':title', "%{$title}%", SQLITE3_TEXT);
			$dStmt->bindValue(':limit', $view_limit, SQLITE3_TEXT);
			$dStmt->bindValue(':offset', $offset, SQLITE3_TEXT);

			// Call table.contents
			/**
			if ($title) {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents WHERE blog_title LIKE '%{$title}%'");
				$sql_query = "SELECT id, blog_title, blog_description, blog_content, blog_category, blog_slug FROM contents WHERE blog_title LIKE '%{$title}%' LIMIT {$view_limit} OFFSET {$offset}";
			} else {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents");
				$sql_query = "SELECT id, blog_title, blog_description, blog_content, blog_category, blog_slug FROM contents LIMIT {$view_limit} OFFSET {$offset}";
			}
			**/

			// Count
			$totalRecords = (int) $countResult;
			$totalPages = ceil($totalRecords / $view_limit);

			$results = $dStmt->execute();

			$contents = [];
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				$contents[] = $row;
			}

			// Show results
			echo json_encode([
				'status' => 'success',
				'data' => $contents,
				'pagination' => [
					'current_page' => $page,
					'page_limit' => $view_limit,
					'overall' => $totalRecords,
					'pages' => $totalPages
				]
			]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'add_content') {
		try {
			$title = htmlspecialchars(stripslashes(trim($data['title'])));
			$description = htmlspecialchars(stripslashes(trim($data['description'])));
			$category = htmlspecialchars(stripslashes(trim($data['category'])));
			$content = $data['content'];
			$slug = createSlug($title);

			// Call table.contents
			$stmt = $db->prepare("INSERT INTO contents (blog_category, blog_title, blog_description, blog_content, blog_slug, date_of_creation) VALUES (:blog_category, :blog_title, :blog_description, :blog_content, :blog_slug, :date_of_creation)");
			$stmt->bindValue(':blog_category', $category, SQLITE3_TEXT);
			$stmt->bindValue(':blog_title', $title, SQLITE3_TEXT);
			$stmt->bindValue(':blog_description', $description, SQLITE3_TEXT);
			$stmt->bindValue(':blog_content', $content, SQLITE3_TEXT);
			$stmt->bindValue(':blog_slug', $slug, SQLITE3_TEXT);
			$stmt->bindValue(':date_of_creation', date('Y-m-d H:i:s'), SQLITE3_TEXT);
			$result = $stmt->execute();

			// Generate new id
			session_regenerate_id(true);

			if (!$result) {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => 'Error: ' . $db->lastErrorMsg()]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Content added successfully']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'edit_content') {
		try {
			$id = intval($data['id']);
			$title = htmlspecialchars(stripslashes(trim($data['title'])));
			$description = htmlspecialchars(stripslashes(trim($data['description'])));
			$category = htmlspecialchars(stripslashes(trim($data['category'])));
			$content = $data['content'];
			$slug = createSlug($title);

			$db->exec('BEGIN TRANSACTION');
			$stmt = $db->prepare("UPDATE contents SET blog_title = :blog_title, blog_description = :blog_description, blog_category = :blog_category, blog_content = :blog_content, blog_slug = :blog_slug WHERE id = :id");
			$stmt->bindValue(':blog_title', $title, SQLITE3_TEXT);
			$stmt->bindValue(':blog_description', $description, SQLITE3_TEXT);
			$stmt->bindValue(':blog_category', $category, SQLITE3_TEXT);
			$stmt->bindValue(':blog_content', $content, SQLITE3_TEXT);
			$stmt->bindValue(':blog_slug', $slug, SQLITE3_TEXT);
			$stmt->bindValue(':id', $id, SQLITE3_TEXT);
			$result = $stmt->execute();

			if (!$result) {
				//$message_log = $db->lastErrorMsg();
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => $db->lastErrorMsg()]);
				exit();
			}

			// Generate new id
			session_regenerate_id(true);

			$db->exec('COMMIT');

			echo json_encode(['status' => 'success']);
		} catch (Exception $e) {
			$db->exec('ROLLBACK');

			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'add_category') {
		try {
			$category = htmlspecialchars(stripslashes(trim($data['category'])));

			$stmt = $db->prepare("INSERT INTO categories (name) VALUES (:category_name)");
			$stmt->bindValue(':category_name', $category, SQLITE3_TEXT);
			$result = $stmt->execute();

			// Generate new id
			session_regenerate_id(true);

			if (!$result) {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => 'Error: ' . $db->lastErrorMsg()]);
				exit();
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Category added successfully']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'delete_content') {
		try {
			$id = intval($data['id']);
			$sql_query = "DELETE FROM contents WHERE id = {$id}";
			$stmt = $db->prepare($sql_query);
			$result = $stmt->execute();

			// Generate new id
			session_regenerate_id(true);

			if ($db->changes() > 0) {
				echo json_encode(['status' => 'success', 'message' => 'Content deleted successfully']);
			} else {
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Content not found or could not be deleted']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'delete_category') {
		try {
			$id = intval($data['id']);
			$sql_query = "DELETE FROM categories WHERE id = {$id}";
			$stmt = $db->prepare($sql_query);
			$result = $stmt->execute();

			// Generate new id
			session_regenerate_id(true);

			if ($db->changes() > 0) {
				echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully']);
			} else {
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Category not found or could not be deleted']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else {
		if ($db) {
			$db->close();
		}

		http_response_code(400);
		echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
	}
} else {
	// Show error response
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'Action required']);
}