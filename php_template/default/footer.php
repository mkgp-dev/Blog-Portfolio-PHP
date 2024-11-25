				<footer class="d-block d-lg-none py-3 small">
					<p>Made by <a href="https://github.com/mkgp-dev">@mkgp-dev</a></p>
					<p>Built with <a href="https://www.php.net/">PHP</a>, <a href="https://getbootstrap.com/">Bootstrap</a>, <a href="https://fontawesome.com/">Fontawesome</a></p>
				</footer>

<?php if (!isset($slug_title) && !$page_portfolio && !$page_donate) { ?>
				<aside class="col-lg-3 d-none d-lg-block sidebar position-sticky top-0">
					<div>
						<h4 class="text-uppercase">Archives</h4>
						<ul class="list-group" id="year-container"></ul>
					</div>
					<div>
						<h4 class="text-uppercase mt-4">Categories</h4>
						<div id="category-container"></div>
					</div>
				</aside>
<?php } ?>
			</div>
		</div>
		<script>
			// Default config
			let currentPage = 1;
			let pageLimit = 10;

<?php if (isset($page_home) && $page_home) { ?>
			// Section.post
			fetchPost(currentPage, pageLimit);
<?php } ?>

<?php if (isset($page_search) && $page_search) { ?>
			// Section.search
			// Clicking search button
			const searchButtonTrigger = document.getElementById('search-title-submit');
			searchButtonTrigger.addEventListener('click', async (e) => {
				const searchQuery = document.getElementById('search-title').value;
				fetchPost(currentPage, pageLimit, searchQuery);
			});

			// Pressing enter key
			document.querySelector('#search-title').addEventListener('keypress', async function (e) {
				if (e.key === 'Enter') {
					const searchQuery = document.getElementById('search-title').value;
					fetchPost(currentPage, pageLimit, searchQuery);
				}
			});
<?php } ?>

			async function fetchPost(page, limit, title = null) {
				try {
					let jsonReq;
					if (title !== null) {
						jsonReq = {
							action: 'fetch_post',
							search_query: title,
							page: page,
							limit: limit
						};
					} else {
						jsonReq = {
							action: 'fetch_post',
							page: page,
							limit: limit
						};
					}

					const response = await fetch('<?php echo $apiPublic; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(jsonReq)
					});

					const result = await response.json();
					if (result.status === 'success') {
						currentPage = result.pagination.current_page;
						updatePostContainer(result.data);
						pagination(result.pagination.pages, limit);
					} else {
						updatePostContainer(0);
						pagination(0, 0);
					}
				} catch (error) {
					console.error('Fetch error:', error);
				}
			}

			function updatePostContainer(data) {
				const postContainer = document.querySelector('#post-container');
				postContainer.innerHTML = '';

				if (data && data.length !== 0) {
					data.forEach((json) => {
						const card  = document.createElement('div');
						card.className = 'card mb-3';

						card.innerHTML = `
							<div class="card-body">
								<h4 class="card-title"><a href="${json.url}">${json.title}</a></h4>
								<h6 class="card-subtitle mb-2">${json.description}</h6>
								<p class="text-muted mb-0"><i class="fa-regular fa-calendar"></i> ${json.date_created}</p>
							</div>
						`;

						postContainer.appendChild(card);
					});
				} else {
					const card  = document.createElement('div');
					card.className = 'card mb-3';
					card.innerHTML = `
						<div class="card-body">
							<h4 class="card-title">Not found.</h4>
							<h6 class="card-subtitle mb-2">You can suggest that topic to me tho...</h6>
						</div>
					`;
					postContainer.appendChild(card);
				}
			}

			function pagination(totalPages, pageLimit) {
				const pagination = document.querySelector('.pagination');
				pagination.innerHTML = '';

				if (totalPages === 0) {
					return;
				}

				// Previous button
				const prevItem = document.createElement('li');
				prevItem.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
				prevItem.innerHTML = `<a class="page-link" href="#"><i class="fa-solid fa-angles-left"></i></a>`;
				prevItem.addEventListener('click', () => {
					if (currentPage > 1) {
						currentPage--;
						fetchPost(currentPage, pageLimit);
						//pagination(totalPages, pageLimit);
					}
				});
				pagination.appendChild(prevItem);

				// Page numbers
				let startPage, endPage;

				if (currentPage <= 2) {
					startPage = 1;
					endPage = Math.min(totalPages, 3);
				} else if (currentPage > totalPages - 1) {
					startPage = Math.max(1, totalPages - 2);
					endPage = totalPages;
				} else {
					startPage = currentPage - 1;
					endPage = currentPage + 1;
				}

				if (startPage > 1) {
					addPageNumber(1, pagination, pageLimit);
					if (startPage > 2) {
						addEllipsis(pagination);
					}
				}

				for (let i = startPage; i <= endPage; i++) {
					addPageNumber(i, pagination, pageLimit);
				}

				if (endPage < totalPages) {
					if (endPage < totalPages - 1) {
						addEllipsis(pagination);
					}
					addPageNumber(totalPages, pagination, pageLimit);
				}

					// Next button
				const nextItem = document.createElement('li');
				nextItem.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
				nextItem.innerHTML = `<a class="page-link" href="#"><i class="fa-solid fa-angles-right"></i></a>`;
				nextItem.addEventListener('click', () => {
					if (currentPage < totalPages) {
						currentPage++;
						fetchPost(currentPage, pageLimit);
						//pagination(totalPages, pageLimit);
					}
				});
				pagination.appendChild(nextItem);
			}

			function addPageNumber(page, pagination, pageLimit) {
				const pageItem = document.createElement('li');
				pageItem.className = 'page-item' + (page === currentPage ? ' active' : '');
				pageItem.innerHTML = `<a class="page-link" href="#">${page}</a>`;
				pageItem.addEventListener('click', () => {
					currentPage = page;
					fetchPost(currentPage, pageLimit);
				});
				pagination.appendChild(pageItem);
			}

			function addEllipsis(pagination) {
				const ellipsis = document.createElement('li');
				ellipsis.className = 'page-item disabled';
				ellipsis.innerHTML = `<a class="page-link" href="#">...</a>`;
				pagination.appendChild(ellipsis);
			}

<?php if (isset($page_category) && $page_category) { ?>
			// Section.specific.category
			fetchSpecificCategory();

			async function fetchSpecificCategory() {
				try {
					const jsonReq = {
						action: 'selected_category',
						category: '<?php echo $category_name; ?>'
					};

					const response = await fetch('<?php echo $apiPublic; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(jsonReq)
					});

					// Define post container
					const postContainer = document.querySelector('#post-container');
					postContainer.innerHTML = '';

					const result = await response.json();
					if (result.status === 'success') {
						result.data.forEach((json) => {
							const card  = document.createElement('div');
							card.className = 'card mb-3';

							card.innerHTML = `
								<div class="card-body">
									<h4 class="card-title"><a href="${json.url}">${json.title}</a></h4>
									<h6 class="card-subtitle mb-2">${json.description}</h6>
									<p class="text-muted mb-0"><i class="fa-regular fa-calendar"></i> ${json.date}</p>
								</div>
							`;

							postContainer.appendChild(card);
						});
					} else {
						const card  = document.createElement('div');
						card.className = 'card mb-3';
						card.innerHTML = `
							<div class="card-body">
								<h4 class="card-title">Not found.</h4>
								<h6 class="card-subtitle mb-2">You sure this is the right category?</h6>
							</div>
						`;
						postContainer.appendChild(card);
					}
				} catch (error) {
					console.error('Fetch error:', error);
				}
			}
<?php } ?>

<?php if (!isset($slug_title) && !$page_portfolio && !$page_donate) { ?>
			// Section.year
			fetchYear();

			async function fetchYear() {
				try {
					const jsonReq = {
						action: 'fetch_year'
					};

					const response = await fetch('<?php echo $apiPublic; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(jsonReq)
					});

					const result = await response.json();
					if (result.status === 'success') {
						const yearContainer = document.getElementById('year-container');
						yearContainer.innerHTML = '';

						result.data.forEach(yearData => {
							const listItem = document.createElement('li');
							listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
							listItem.innerHTML = `
								${yearData.year}
								<span class="badge bg-primary rounded-pill">${yearData.total}</span>
							`;

							yearContainer.appendChild(listItem);
						});
					}
				} catch (error) {
					console.error('Fetch error:', error);
				}
			}

			// Section.category
			fetchCategory();

			async function fetchCategory() {
				try {
					const jsonReq = {
						action: 'fetch_category'
					};

					const response = await fetch('<?php echo $apiPublic; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(jsonReq)
					});

					const result = await response.json();
					if (result.status === 'success') {
						const categoryContainer = document.getElementById('category-container');
						categoryContainer.innerHTML = '';

						result.data.forEach(ctgData => {
							const buttonList = document.createElement('a');
							buttonList.href = `${ctgData.url}`;
							buttonList.className = 'btn btn-primary btn-sm m-1';
							buttonList.innerHTML = `${ctgData.category}`;

							categoryContainer.appendChild(buttonList);
						});
					}
				} catch (error) {
					console.error('Fetch error:', error);
				}
			}
<?php } ?>
		</script>
	</body>
</html>