<?php
//$_SESSION['page_view'] = 'dashboard';

if (!isset($_SESSION['privilege'])) {
	header('Location: /');
	exit();
}

include __DIR__ . '/header.php';
?>
	<body>
		<div class="container mt-3">
			<div class="card mb-3">
				<h4 class="card-header">Website configuration</h4>
				<div class="card-body card-config">
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="edit-title" class="form-label">Title</label>
							<input type="text" class="form-control" id="edit-title" name="title" placeholder="Your title" required>
						</div>
						<div class="col-md-6">
							<label for="edit-description" class="form-label">Description</label>
							<input type="text" class="form-control" id="edit-description" name="description" placeholder="Your description" required>
						</div>
					</div>
					<div class="mb-3">
						<label for="edit-profile-img" class="form-label">Profile link</label>
						<input type="text" class="form-control" id="edit-profile-img" name="profile-img" placeholder="Your image placeholder" required>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" id="edit-profile-submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
			
			<div class="card mb-3">
				<h4 class="card-header">Profile</h4>
				<div class="card-body card-profile">
					<input type="hidden" name="userid" id="default-id">
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="edit-firstname" class="form-label">First name</label>
							<input type="text" class="form-control" id="edit-firstname" name="firstname" placeholder="Johnny" required>
						</div>
						<div class="col-md-6">
							<label for="edit-lastname" class="form-label">Last name</label>
							<input type="text" class="form-control" id="edit-lastname" name="lastname" placeholder="Silverhand" required>
						</div>
					</div>
					<div class="mb-3">
						<label for="edit-email" class="form-label">Email</label>
						<input type="email" class="form-control" id="edit-email" name="email" placeholder="johnny.silverhand@cyberpunk.cpdr" required>
					</div>
					<div class="mb-3">
						<label for="edit-username" class="form-label">Username</label>
						<input type="text" class="form-control" id="edit-username" name="username" placeholder="johnny.silverhand" required>
					</div>
					<div class="mb-3">
						<label for="edit-password" class="form-label">Password (<i>leave blank to keep current password</i>)</label>
						<input type="password" class="form-control" id="edit-password" name="password" placeholder="huntdownlikesupreme!" required>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" id="edit-account-submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</div>
	</body>
<?php include __DIR__ . '/footer.php'; ?>