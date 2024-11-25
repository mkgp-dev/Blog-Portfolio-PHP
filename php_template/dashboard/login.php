<?php
// Session
if ($_SESSION['is_user_login']) {
	header('Location: /dashboard/contents');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = htmlspecialchars(stripslashes(trim($_POST['username'])));
	$password = htmlspecialchars(stripslashes(trim($_POST['password'])));

	$db = dbConnection();
	$stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
	$stmt->bindValue(':username', $username, SQLITE3_TEXT);
	$result = $stmt->execute();
	$data = $result->fetchArray(SQLITE3_ASSOC);

	 if ($data) {
	 	$_SESSION['username'] = $data['username'];
	 	$_SESSION['privilege'] = $data['privilege'];
	 	//$psswd_verify = password_verify($password, $data['password']);

	 	// Protect admin account
	 	if (password_verify($password, $data['password'])) {
	 		// Set session indication
	 		$_SESSION['is_user_login'] = true;
	 		$_SESSION['user_in_session'] = time();
	 		session_regenerate_id(true);

	 		if ($_SESSION['privilege'] === 'administrator') {
	 			//echo 'user_admin';
	 			header('Location: /dashboard/contents');
	 		} else {
	 			//echo 'not_admin';
	 			header('Location: /');
	 		}
	 		exit();
	 	} else {
		 	if ($_SESSION['username'] == 'administrator' || $_SESSION['username'] == 'admin') {
		 		$message_log = 'User does not exist';
		 	} else {
		 		$message_log = 'Invalid password';
		 	}
	 	}
	 } else {
	 	$message_log = 'User does not exist';
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
						<h4 class="card-header">Login</h4>
						<div class="card-body">
							<?php if (isset($message_log)) { ?>
								<div class="alert alert-danger">
									<?php echo $message_log; ?>
								</div>
							<?php } ?>
							<form method="POST">
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