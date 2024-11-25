<?php
//$_SESSION['page_view'] = 'dashboard';

if (!isset($_SESSION['privilege'])) {
	header('Location: /');
	exit();
}

include __DIR__ . '/header.php';
?>
	<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet"/>
	<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
	<body>
		<div class="container-fluid mt-3">
			<div class="d-flex justify-content-between mb-3">
				<div class="input-group">
					<input type="text" class="form-control" id="search-title" placeholder="Type a title">
					<button class="btn btn-primary" type="button" id="search-title-submit">Search</button>
				</div>
				<select class="form-select ms-2 w-25" id="page-limit">
					<option value="5">5</option>
					<option value="10" selected>10</option>
					<option value="25">25</option>
				</select>
				<button type="button" class="btn btn-primary ms-2 flex-shrink-0" id="call-add-content"><i class="fa-solid fa-plus"></i> Blog post</button>
			</div>

			<div class="row">
				<div class="col-md-9">
					<table class="table table-hover">
						<thead>
							<tr>
								<th class="w-75">Blog Content</th>
								<th scope="col">Action</th>
							</tr>
						</thead>
						<tbody id="table-contents"></tbody>
					</table>

					<div class="pagination-area">
						<ul class="pagination justify-content-center"></ul>
					</div>
				</div>
				<div class="col-md-3">
					<div class="d-grid gap-2 mb-3">
						<button type="button" class="btn btn-primary" id="call-add-category"><i class="fa-solid fa-folder-plus"></i> Category</button>
					</div>

					<ul class="list-group" id="category-list"></ul>
				</div>
			</div>

			<!-- Modal section for adding a new content -->
			<div class="modal modal-xl" id="add-content" role="dialog" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Add a new content</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true"></span>
							</button>
						</div>
						<div class="modal-body">
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="add-title" class="form-label">Title</label>
									<input type="text" class="form-control" id="add-title" name="title" placeholder="My first blog" required>
								</div>
								<div class="col-md-6">
									<label for="add-description" class="form-label">Description</label>
									<input type="text" class="form-control" id="add-description" name="description" placeholder="This is my first blog" required>
								</div>
							</div>
							<div class="mb-3">
								<label for="select-category">Category</label>
								<select class="form-select" id="select-category"></select>
							</div>
							<div class="mb-3">
								<div class="quill-editor">
									<div id="editor-container"></div>
								</div>
							</div>
							<div class="d-grid gap-2">
								<button type="button" id="add-content-submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Modal section for editing a content -->
			<div class="modal modal-xl" id="edit-content" role="dialog" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Edit <strong id="content-title"></strong></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true"></span>
							</button>
						</div>
						<div class="modal-body">
							<input type="hidden" name="contentid" id="content-id">
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="edit-title" class="form-label">Title</label>
									<input type="text" class="form-control" id="edit-title" name="title" placeholder="My first blog" required>
								</div>
								<div class="col-md-6">
									<label for="edit-description" class="form-label">Description</label>
									<input type="text" class="form-control" id="edit-description" name="description" placeholder="This is my first blog" required>
								</div>
							</div>
							<div class="mb-3">
								<label for="edit-category">Category</label>
								<select class="form-select" id="edit-category"></select>
							</div>
							<div class="mb-3">
								<div class="quill-editor">
									<div id="editor"></div>
								</div>
							</div>
							<div class="d-grid gap-2">
								<button type="button" id="edit-content-submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Modal section for add a category -->
			<div class="modal modal-sm" id="add-category" role="dialog" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Add a category</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true"></span>
							</button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="add-category-value" class="form-label">New category</label>
								<input type="text" class="form-control" id="add-category-value" name="category" placeholder="Uncategorized" required>
							</div>
							<div class="d-grid gap-2">
								<button type="button" id="add-category-submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</body>
<?php include __DIR__ . '/footer.php'; ?>