<?php
// Grab HTMLPurifier
require_once __DIR__ . '/../../php_library/html_purifier/HTMLPurifier.auto.php';

// Retrieve table.contents
$slug_title = $_GET['post'];
if (isset($slug_title) && !empty($slug_title)) {
	$db = dbConnection();
	$stmt = $db->prepare("SELECT * FROM contents WHERE blog_slug = :slug LIMIT 1");
	$stmt->bindValue(':slug', $slug_title, SQLITE3_TEXT);
	$result = $stmt->execute();
	$post = $result->fetchArray(SQLITE3_ASSOC);

	if (!$post) {
		// Temporary
		header('Location: /');
		exit();
	}

	// Fix date
	$date = new DateTime($row['date_of_creation']);
	$clean_date = $date->format('F j, Y');

	// Content modification
	$title = htmlspecialchars($post['blog_title']);
	$description = htmlspecialchars($post['blog_description']);
	$date = htmlspecialchars($clean_date);
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$clean_html = $purifier->purify($post['blog_content']);
} else {
	header('Location: /');
	exit();
}

include __DIR__ . '/header.php';
?>
<main class="col-lg-9 px-3 mt-4">
	<div class="card mb-3">
		<div class="card-body">
			<h4 class="card-title"><?php echo $title; ?></h4>
			<h6 class="card-subtitle mb-2"><?php echo $description; ?></h6>
			<p class="text-muted mb-0"><i class="fa-regular fa-calendar"></i> <?php echo $date; ?></p>
		</div>
	</div>

	<div class="card mb-3">
		<div class="card-body">
			<?php echo $clean_html; ?>
		</div>
	</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>