<?php
// Session
if (isset($_SESSION['username'])) {
	header('Location: ?page=home');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$firstname = htmlspecialchars(stripslashes(trim($_POST['firstname'])));
	$lastname = htmlspecialchars(stripslashes(trim($_POST['lastname'])));
	$email = htmlspecialchars(stripslashes(trim($_POST['email'])));
	$username = htmlspecialchars(stripslashes(trim($_POST['username'])));
	$password = htmlspecialchars(stripslashes(trim($_POST['password'])));

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$message_log = 'Invalid email address';
	} else if (strlen($password) < 6) {
        // Improve password creation, right now its in beta (required to have atleast 6 string value password)
        $message_log = 'Please make your password more secured';
    } else if ($username == 'administrator' || $username == 'admin') {
    	// Disable registering administrative accounts
    	$message_log = 'This username is disabled';
    } else {
		$hashpsswd = password_hash($password, PASSWORD_DEFAULT);
		try {
			$db = dbConnection();
			$stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, email, password, privilege, registration_date) VALUES (:username, :firstname, :lastname, :email, :password, :privilege, :registration_date)");
			$stmt->bindValue(':username', $username, SQLITE3_TEXT);
			$stmt->bindValue(':firstname', $firstname, SQLITE3_TEXT);
			$stmt->bindValue(':lastname', $lastname, SQLITE3_TEXT);
			$stmt->bindValue(':email', $email, SQLITE3_TEXT);
			$stmt->bindValue(':password', $hashpsswd, SQLITE3_TEXT);
			$stmt->bindValue(':privilege', 'member', SQLITE3_TEXT);
			$stmt->bindValue(':registration_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
			$result = $stmt->execute();
			
			if (!$result) {
				$message_log = $db->lastErrorMsg();
				//$message_log = $db->errorInfo();
			} else {
				$message_success = '<strong>Successfully registered!</strong> You can now login <a href="#">here</a>';
			}
		} catch (Exception $e) {
			$message_log = $e->getMessage();
		}
	}
}

include __DIR__ . '/header.php';
?>
	<body>
		<div class="container mt-3">
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<div class="card">
						<h4 class="card-header">Register</h4>
						<div class="card-body">
							<?php if (isset($message_log)) { ?>
								<div class="alert alert-danger">
									<?php echo $message_log; ?>
								</div>
							<?php } else if (isset($message_success)) { ?>
								<div class="alert alert-success">
									<?php echo $message_success; ?>
								</div>
							<?php } ?>
							<form method="POST">
								<div class="row mb-3">
									<div class="col-md-6">
										<label for="firstname" class="form-label">First name</label>
										<input type="text" class="form-control" id="firstname" name="firstname" placeholder="Johnny" required>
									</div>
									<div class="col-md-6">
										<label for="lastname" class="form-label">Last name</label>
										<input type="text" class="form-control" id="lastname" name="lastname" placeholder="Silverhand" required>
									</div>
								</div>
								<div class="mb-3">
									<label for="email" class="form-label">Email</label>
									<input type="email" class="form-control" id="email" name="email" placeholder="johnny.silverhand@cyberpunk.cpdr" required>
								</div>
								<div class="mb-3">
									<label for="username" class="form-label">Username</label>
									<input type="text" class="form-control" id="username" name="username" placeholder="johnny.silverhand" required>
								</div>
								<div class="mb-3">
									<label for="password" class="form-label">Password</label>
									<input type="password" class="form-control" id="password" name="password" placeholder="huntdownlikesupreme!" required>
								</div>
								<div class="d-grid gap-2">
									<button type="submit" id="checkout-submit" class="btn btn-primary">Submit</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-4"></div>
			</div>
		</div>
	</body>
<?php include __DIR__ . '/footer.php'; ?>