<?php
$_SESSION['page_view'] = 'default';

include __DIR__ . '/header.php';
?>
<main class="col-lg-6 px-3 mt-4">
	<div class="card mb-3">
		<div class="card-body">
			<div class="input-group">
				<input type="text" class="form-control" id="search-title" placeholder="Search">
				<button class="btn btn-primary" type="button" id="search-title-submit"><i class="fa-solid fa-magnifying-glass"></i></button>
			</div>
		</div>
	</div>

	<div id="post-container"></div>

	<div class="pagination-area">
		<ul class="pagination justify-content-center"></ul>
	</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>