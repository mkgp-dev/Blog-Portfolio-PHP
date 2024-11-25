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

$db = dbConnection();
if (isset($data['action'])) {
	if ($data['action'] === 'fetch_content') {
		try {
			$title = htmlspecialchars(stripslashes(trim($data['title'])));
			$view_limit = isset($data['limit']) ? (int)$data['limit'] : 10;
			$page = isset($data['page']) ? (int)$data['page'] : 1;
			$offset = ($page - 1) * $view_limit;

			// Call table.contents
			if ($title) {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents WHERE blog_title LIKE '%{$title}%'");
				$sql_query = "SELECT id, blog_title, blog_description, blog_content, blog_category, blog_slug FROM contents WHERE blog_title LIKE '%{$title}%' LIMIT {$view_limit} OFFSET {$offset}";
			} else {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents");
				$sql_query = "SELECT id, blog_title, blog_description, blog_content, blog_category, blog_slug FROM contents LIMIT {$view_limit} OFFSET {$offset}";
			}			

			// Count
			//$countResult = $db->querySingle("SELECT COUNT(*) FROM contents");
			$totalRecords = (int) $countResult;
			$totalPages = ceil($totalRecords / $view_limit);

			$stmt = $db->prepare($sql_query);
			$results = $stmt->execute();

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
	} else if ($data['action'] === 'fetch_category') {
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
	} else if ($data['action'] === 'add_content') {
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
	} else if ($data['action'] === 'edit_content') {
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
	} else if ($data['action'] === 'delete_content') {
		try {
			$id = intval($data['id']);
			$sql_query = "DELETE FROM contents WHERE id = {$id}";
			$stmt = $db->prepare($sql_query);
			$result = $stmt->execute();

			if ($db->changes() > 0) {
				echo json_encode(['status' => 'success', 'message' => 'Content deleted successfully']);
			} else {
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
	} else if ($data['action'] === 'add_category') {
		try {
			$category = htmlspecialchars(stripslashes(trim($data['category'])));

			$stmt = $db->prepare("INSERT INTO categories (name) VALUES (:category_name)");
			$stmt->bindValue(':category_name', $category, SQLITE3_TEXT);
			$result = $stmt->execute();

			if (!$result) {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => 'Error: ' . $db->lastErrorMsg()]);
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
	} else if ($data['action'] === 'delete_category') {
		try {
			$id = intval($data['id']);
			$sql_query = "DELETE FROM categories WHERE id = {$id}";
			$stmt = $db->prepare($sql_query);
			$result = $stmt->execute();

			if ($db->changes() > 0) {
				echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully']);
			} else {
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
		echo 'missing.2';
	}
} else {
	echo 'missing.1';
}
