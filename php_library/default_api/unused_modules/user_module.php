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

$fieldsToCheck = ['action', 'fname', 'lname', 'email', 'username'];
if (count(array_filter(array_intersect_key($data, array_flip($fieldsToCheck)), fn($value) => !empty($value))) === count($fieldsToCheck)) {
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

	$db = dbConnection();
	if ($data['action'] === 'edit') {
		try {
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
			}

			if (!empty($password)) {
				$hashpsswd = password_hash($password, PASSWORD_DEFAULT);
				$stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
				$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $stmt->execute();

				if (!$result) {
					$message_log = $db->lastErrorMsg();
				}
			}

			$db->exec('COMMIT');

			echo json_encode(['status' => 'success']);
		} catch (Exeception $e) {
			$db->exec('ROLLBACK');
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $e->getMessage()]);
			exit();
		}
	} else if ($data['action'] === 'add') {
		try {
			$hashpsswd = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, email, password, privilege, registration_date) VALUES (:username, :firstname, :lastname, :email, :password, :privilege, :registration_date)");
			$stmt->bindValue(':username', $username, SQLITE3_TEXT);
			$stmt->bindValue(':firstname', $fname, SQLITE3_TEXT);
			$stmt->bindValue(':lastname', $lname, SQLITE3_TEXT);
			$stmt->bindValue(':email', $email, SQLITE3_TEXT);
			$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
			$stmt->bindValue(':privilege', 'member', SQLITE3_TEXT);
			$stmt->bindValue(':registration_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
			$result = $stmt->execute();

			if (!$result) {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => 'Error creating user: ' . $db->lastErrorMsg()]);
			} else {
				$newUserId = $db->lastInsertRowID();
				echo json_encode(['status' => 'success', 'fetchId' => $newUserId, 'privilege_default' => 'member']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => 'Error creating user: ' . $e->getMessage()]);
		}
	} else if ($data['action'] === 'delete') {
		try {
			$sql_query = "DELETE FROM users WHERE id = {$id}";
			$stmt = $db->prepare($sql_query);

			$result = $stmt->execute();

			if ($db->changes() > 0) {
				echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'User not found or could not be deleted']);
			}
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
		}
	} else {
		http_response_code(400);
		echo json_encode(['status' => 'error', 'message' => 'No action']);
	}

} else {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
	exit();
}