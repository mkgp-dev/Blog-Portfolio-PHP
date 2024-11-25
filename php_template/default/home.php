<?php
$_SESSION['page_view'] = 'default';

include __DIR__ . '/header.php';
?>
<main class="col-lg-6 px-3 mt-4">
	<div id="post-container"></div>

	<div class="pagination-area">
		<ul class="pagination justify-content-center"></ul>
	</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>