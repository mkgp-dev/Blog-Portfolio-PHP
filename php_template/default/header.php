<?php
try {
	// Initiate DB Connection
	$db = dbConnection();
	$result = $db->query("SELECT * FROM site_config");
	$config = [];
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		$config[$row['setting_name']] = $row['setting_value'];
	}
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
	exit();
} finally {
	if ($db) {
		$db->close();
	}
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php if (isset($_GET['post'])) { ?>
		<title><?php echo $title; ?></title>
		<?php } else { ?>
		<title><?php echo htmlspecialchars($config['site_title']); ?> - <?php echo htmlspecialchars($config['site_description']); ?></title>
		<?php } ?>

		<!-- Bootstrap v5 -->
		<link href="<?php echo $linkAsset; ?>/css/default.bootstrap.css" rel="stylesheet">
		<script src="<?php echo $linkAsset; ?>/js/bootstrap.bundle.min.js"></script>

		<!-- Fontawesome (Free) -->
		<link href="<?php echo $linkAsset; ?>/fontawesome/css/all.min.css" rel="stylesheet">
	</head>
	<body>
		<header class="d-lg-none d-flex justify-content-between align-items-center p-3 bg-dark">
			<button class="btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav">
				<i class="fa-solid fa-list"></i> Menu
			</button>
		</header>

		<div class="offcanvas offcanvas-start bg-dark text-white" id="mobileNav" aria-labelledby="mobileNavLabel">
			<div class="offcanvas-header">
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
			</div>
			<div class="offcanvas-body">
				<div class="text-center">
					<img src="<?php echo htmlspecialchars($config['blog_picture']); ?>" class="img-fluid img-thumbnail rounded-circle mb-3 profile-img-container" alt="blog_picture">
					<h5><?php echo htmlspecialchars($config['site_title']); ?></h5>
					<p class="text-muted"><?php echo htmlspecialchars($config['site_description']); ?></p>
				</div>
				
				<ul class="nav nav-pills flex-column mb-auto mt-4">
					<li class="nav-item">
						<a href="/" class="nav-link text-white <?php if (isset($page_home) && $page_home) { echo 'active'; } ?>">
							<i class="fa-solid fa-house"></i><span class="ms-2">Home</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="/search" class="nav-link text-white <?php if (isset($page_search) && $page_search) { echo 'active'; } ?>">
							<i class="fa-solid fa-magnifying-glass"></i><span class="ms-2">Search a topic</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="/about-me" class="nav-link text-white <?php if (isset($page_portfolio) && $page_portfolio) { echo 'active'; } ?>">
							<i class="fa-solid fa-user"></i><span class="ms-2">About me</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="/donate" class="nav-link text-white <?php if (isset($page_donate) && $page_donate) { echo 'active'; } ?>">
							<i class="fa-solid fa-gift"></i><span class="ms-2">Donate</span>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<nav class="col-lg-3 d-none d-lg-flex sidebar flex-column position-sticky top-0">
					<div class="text-center">
						<img src="<?php echo htmlspecialchars($config['blog_picture']); ?>" class="img-fluid img-thumbnail rounded-circle mb-3 profile-img-container" alt="blog_picture">
						<h5><?php echo htmlspecialchars($config['site_title']); ?></h5>
						<p class="text-muted"><?php echo htmlspecialchars($config['site_description']); ?></p>
					</div>
					
					<ul class="nav nav-pills flex-column mb-auto mt-4">
						<li class="nav-item">
							<a href="/" class="nav-link text-white <?php if (isset($page_home) && $page_home) { echo 'active'; } ?>">
								<i class="fa-solid fa-house"></i><span class="ms-2">Home</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/search" class="nav-link text-white <?php if (isset($page_search) && $page_search) { echo 'active'; } ?>">
								<i class="fa-solid fa-magnifying-glass"></i><span class="ms-2">Search a topic</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/about-me" class="nav-link text-white <?php if (isset($page_portfolio) && $page_portfolio) { echo 'active'; } ?>">
								<i class="fa-solid fa-user"></i><span class="ms-2">About me</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/donate" class="nav-link text-white <?php if (isset($page_donate) && $page_donate) { echo 'active'; } ?>">
								<i class="fa-solid fa-gift"></i><span class="ms-2">Donate</span>
							</a>
						</li>
					</ul>
					<hr/>
					<div class="small">
						<p>Made by <a href="https://github.com/mkgp-dev">@mkgp-dev</a></p>
						<p>Built with <a href="https://www.php.net/">PHP</a>, <a href="https://getbootstrap.com/">Bootstrap</a>, <a href="https://fontawesome.com/">Fontawesome</a></p>
					</div>
				</nav>