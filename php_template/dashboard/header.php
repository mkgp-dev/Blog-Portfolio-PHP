<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>MKGP Basic Blog CMS</title>

		<!-- Bootstrap v5 -->
		<link href="<?php echo $linkAsset; ?>/css/bootstrap.css" rel="stylesheet">
		<script src="<?php echo $linkAsset; ?>/js/bootstrap.bundle.min.js"></script>

		<!-- Fontawesome (Free) -->
		<link href="<?php echo $linkAsset; ?>/fontawesome/css/all.min.css" rel="stylesheet">
	</head>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container-fluid">
			<span class="navbar-brand">MKGP Basic Blog CMS</span>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
				<?php if (isset($_SESSION['privilege']) && $_SESSION['privilege'] === 'administrator') { ?>
					<li class="nav-item">
						<a class="nav-link <?php if (isset($page_content) && $page_content) { echo 'active'; } ?>" href="/dashboard/contents">Contents</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?php if (isset($page_settings) && $page_settings) { echo 'active'; } ?>" href="/dashboard/settings">Settings</a>
					</li>
				<?php } ?>
				</ul>
				<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
					<!-- Temporary login link -->
					<?php if ($_SESSION['is_user_login']) { ?>
					<li class="nav-item">
						<a class="nav-link" target="_blank" href="/">View your blog</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/dashboard/logout">Logout</a>
					</li>
					<?php } ?>
					</li>
				</ul>
			</div>
		</div>
	</nav>