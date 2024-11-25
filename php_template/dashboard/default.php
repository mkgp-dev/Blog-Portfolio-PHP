<?php
$_SESSION['page_view'] = 'dashboard';

if (!isset($_SESSION['privilege'])) {
	header('Location: /?page=home');
	exit();
}

include __DIR__ . '/header.php';
?>
	<body>
		<div class="container-fluid mt-3">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active">Dashboard</li>
			</ol>
			Statistics area
		</div>
	</body>
<?php include __DIR__ . '/footer.php'; ?>