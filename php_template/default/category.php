<?php
// Retrieve table.contents with category
$category_name = htmlspecialchars(stripslashes(trim($_GET['category'])));

include __DIR__ . '/header.php';
?>
<main class="col-lg-6 px-3 mt-4">
	<h1 class="text-uppercase"><?php echo $category_name; ?></h1>

	<div id="post-container"></div>
</main>
<?php include __DIR__ . '/footer.php'; ?>