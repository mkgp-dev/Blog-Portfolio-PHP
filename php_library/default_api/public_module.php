<?php
//require_once __DIR__ . '/../../db_sqlite3/config.php';

// Data
$data = json_decode(file_get_contents('php://input'), true);
$call_action = $data['action'];

if (isset($call_action) && !empty($call_action)) {
	// Call SQLite3 Connection
	$db = dbConnection();

	if ($call_action === 'fetch_post') {
		try {
			$titleQuery = htmlspecialchars(stripslashes(trim($data['search_query'])));
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
			$dSql = "SELECT blog_title, blog_description, blog_slug, date_of_creation FROM contents WHERE blog_title LIKE :title LIMIT :limit OFFSET :offset";
			$dStmt = $db->prepare($dSql);
			$dStmt->bindValue(':title', "%{$title}%", SQLITE3_TEXT);
			$dStmt->bindValue(':limit', $view_limit, SQLITE3_TEXT);
			$dStmt->bindValue(':offset', $offset, SQLITE3_TEXT);

			/**
			if ($title) {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents WHERE blog_title LIKE '%{$title}%'");
				$sql_query = "SELECT blog_title, blog_description, blog_slug, date_of_creation FROM contents WHERE blog_title LIKE '%{$title}%' LIMIT {$view_limit} OFFSET {$offset}";
			} else {
				$countResult = $db->querySingle("SELECT COUNT(*) FROM contents");
				$sql_query = "SELECT blog_title, blog_description, blog_slug, date_of_creation FROM contents LIMIT {$view_limit} OFFSET {$offset}";
			}
			**/

			// Count
			$totalRecords = (int) $countResult;
			$totalPages = ceil($totalRecords / $view_limit);

			$results = $dStmt->execute();

			$post_content = [];
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				// Modify date
				$date = new DateTime($row['date_of_creation']);
				$clean_date = $date->format('F j, Y');

				$post_content[] = [
					'title' => $row['blog_title'],
					'description' => $row['blog_description'],
					'date_created' => $clean_date,
					'url' => "/p/{$row['blog_slug']}"
				];
			}

			// Check if its empty
			if (!empty($post_content)) {
				echo json_encode([
					'status' => 'success',
					'data' => $post_content,
					'pagination' => [
						'current_page' => $page,
						'page_limit' => $view_limit,
						'overall' => $totalRecords,
						'pages' => $totalPages
					]
				]);
			} else {
				echo json_encode([
					'status' => 'failed',
					'message' => 'Title not found',
				]);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'fetch_year') {
		try {
			$sql_query = "SELECT strftime('%Y', date_of_creation) AS post_year, COUNT(*) AS total_posts FROM contents GROUP BY post_year ORDER BY post_year DESC";
			$result = $db->query($sql_query);

			$year_content = [];
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$year_content[] = [
					'year' => $row['post_year'],
					'total' => $row['total_posts'],
					//'url' => 'none'
				];
			}

			echo json_encode([
				'status' => 'success',
				'data' => $year_content,
			]);
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
			$sql_query = "SELECT id, name FROM categories ORDER BY id DESC";
			$results = $db->query($sql_query);

			$category_content = [];
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				$category_content[] = [
					'category' => $row['name'],
					'url' => "/category/{$row['name']}"
				];
			}

			echo json_encode([
				'status' => 'success',
				'data' => $category_content,
			]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		} finally {
			if ($db) {
				$db->close();
			}
		}
	} else if ($call_action === 'selected_category') {
		try {
			$category_name = htmlspecialchars(stripslashes(trim($data['category'])));
			$stmt = $db->prepare("SELECT blog_title, blog_description, blog_slug, date_of_creation FROM contents WHERE blog_category = :category_name ORDER BY date_of_creation DESC");
			//$stmt = $db->prepare("SELECT contents.blog_title AS title, contents.blog_description AS description, contents.blog_slug AS default_url, contents.date_of_creation AS date_created FROM contents WHERE blog_category = :category_name ORDER BY date_created DESC");
			$stmt->bindValue(':category_name', $category_name, SQLITE3_TEXT);
			$results = $stmt->execute();

			$post_category = [];
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				// Clean date
				$date = new DateTime($row['date_of_creation']);
				$clean_date = $date->format('F j, Y');

				$post_category[] = [
					'title' => $row['blog_title'],
					'description' => $row['blog_description'],
					'date' => $clean_date,
					'url' => "/p/{$row['blog_slug']}"
				];
			}

			// Check if its empty
			if (!empty($post_category)) {
				echo json_encode([
					'status' => 'success',
					'data' => $post_category,
				]);
			} else {
				echo json_encode([
					'status' => 'failed',
					'message' => 'Category not found',
				]);
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