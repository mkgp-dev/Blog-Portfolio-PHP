	<script>
		<?php if ($page_content) { ?>
		// Quill WYSISYG Editor
		const toolbarOptions = [
				[ { 'header': [4, 5, false] } ],
				[ 'bold', 'italic', 'underline', 'strike' ],
				[{ 'list': 'ordered' }, { 'list': 'bullet'}, { 'indent': '-1' }, { 'indent': '+1' }],
				[ 'link', 'video' ],
				[ 'clean' ]
			];

		const quill_add_content = new Quill('#editor-container', {
			modules: {
				toolbar: toolbarOptions
			},
			theme: 'snow'
		});

		const quill_edit_content = new Quill('#editor', {
			modules: {
				toolbar: toolbarOptions 
			},
			theme: 'snow'
		});

		// Modal Execution
		const addContentModal = document.getElementById('add-content');
		const editContentModal = document.getElementById('edit-content');
		const addCategoryModal = document.getElementById('add-category');
		const execaddContentModal = new bootstrap.Modal(addContentModal);
		const execeditContentModal = new bootstrap.Modal(editContentModal);
		const execaddCategoryModal = new bootstrap.Modal(addCategoryModal);
		const triggerAddContent = document.getElementById('call-add-content');
		const triggerAddCategory = document.getElementById('call-add-category');

		// Fix modal trigger
		function clearModal() {
			const openModals = document.querySelectorAll('.modal.show');
			openModals.forEach(modal => {
				bootstrap.Modal.getInstance(modal).hide();
			});
		}

		triggerAddContent.addEventListener('click', () => {
			clearModal();
			execaddContentModal.show();
		});

		triggerAddCategory.addEventListener('click', () => {
			clearModal();
			execaddCategoryModal.show();
		});

		execaddContentModal._element.addEventListener('shown.bs.modal', () => {
			document.getElementById('add-content').focus();
		});

		// Button id.table-contents
		document.getElementById('table-contents').addEventListener('click', async (e) => {
			if (e.target.classList.contains('editContent')) {
				const button = e.target;
				document.getElementById('content-title').innerHTML = button.dataset.title;
				document.getElementById('content-id').value = button.dataset.id;
				document.getElementById('edit-title').value = button.dataset.title;
				document.getElementById('edit-description').value = button.dataset.description;
				document.getElementById('edit-category').value = button.dataset.category;
				quill_edit_content.root.innerHTML = decodeURIComponent(button.dataset.content);

				execeditContentModal.show();
			} else if (e.target.classList.contains('deleteContent')) {
				const button = e.target;

				confirm(button.dataset.confirm);

				const contentData = {
					action: 'delete_content',
					id: button.dataset.id,
				};

				const response = await fetch('<?php echo $apiContent; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(contentData)
				});

				const result = await response.json();
				if (result.status === 'success') {
					fetchContent(currentPage, pageLimit);
					fetchCategory();

					const container = document.querySelector('.container-fluid.mt-3');
					let notificationContainer = container.querySelector('.notification-container');

					if (!notificationContainer) {
						notificationContainer = document.createElement('div');
						notificationContainer.className = 'notification-container';
						container.prepend(notificationContainer);
					}

					const alert = document.createElement('div');
					alert.className = 'alert alert-success alert-dismissible fade show';
					alert.role = 'alert';
					alert.innerHTML = `
						${result.message}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					`;

					notificationContainer.appendChild(alert);

					setTimeout(() => {
						alert.remove();
					}, 5000);
				} else {
					const container = document.querySelector('.container-fluid.mt-3');
					let notificationContainer = container.querySelector('.notification-container');

					if (!notificationContainer) {
						notificationContainer = document.createElement('div');
						notificationContainer.className = 'notification-container';
						container.prepend(notificationContainer);
					}

					const alert = document.createElement('div');
					alert.className = 'alert alert-danger alert-dismissible fade show';
					alert.role = 'alert';
					alert.innerHTML = `
						${result.message}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					`;

					notificationContainer.appendChild(alert);

					setTimeout(() => {
						alert.remove();
					}, 5000);
				}
			}
		});

		// Button id.category-list
		document.getElementById('category-list').addEventListener('click', async (e) => {
 			if (e.target.classList.contains('deleteCategory')) {
				const button = e.target;

				const checkConfirm = confirm(button.dataset.confirm);

				if (checkConfirm) {
					const contentData = {
						action: 'delete_category',
						id: button.dataset.id,
					};

					const response = await fetch('<?php echo $apiContent; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(contentData)
					});

					const result = await response.json();
					if (result.status === 'success') {
						fetchCategory();

						const container = document.querySelector('.container-fluid.mt-3');
						let notificationContainer = container.querySelector('.notification-container');

						if (!notificationContainer) {
							notificationContainer = document.createElement('div');
							notificationContainer.className = 'notification-container';
							container.prepend(notificationContainer);
						}

						const alert = document.createElement('div');
						alert.className = 'alert alert-success alert-dismissible fade show';
						alert.role = 'alert';
						alert.innerHTML = `
							${result.message}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						`;

						notificationContainer.appendChild(alert);

						setTimeout(() => {
							alert.remove();
						}, 5000);
					} else {
						const container = document.querySelector('.container-fluid.mt-3');
						let notificationContainer = container.querySelector('.notification-container');

						if (!notificationContainer) {
							notificationContainer = document.createElement('div');
							notificationContainer.className = 'notification-container';
							container.prepend(notificationContainer);
						}

						const alert = document.createElement('div');
						alert.className = 'alert alert-danger alert-dismissible fade show';
						alert.role = 'alert';
						alert.innerHTML = `
							${result.message}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						`;

						notificationContainer.appendChild(alert);

						setTimeout(() => {
							alert.remove();
						}, 5000);
					}
				}
			}
		});

		// Call table.categories
		fetchCategory();

		// Call table.users with JSON
		let currentPage = 1;
		let pageLimit = document.getElementById('page-limit').value;
		fetchContent(currentPage, pageLimit);

		// Update page.list
		document.getElementById('page-limit').addEventListener('change', (e) => {
			pageLimit = e.target.value;
			fetchContent(currentPage, pageLimit);
		});

		// Search user.category
		const searchButtonTrigger = document.getElementById('search-title-submit');
		searchButtonTrigger.addEventListener('click', async (e) => {
			const searchQuery = document.getElementById('search-title').value;
			fetchContent(currentPage, pageLimit, searchQuery);
		});

		async function fetchCategory() {
			try {
				let jsonReq;
				jsonReq = {
					action: 'fetch_category'
				};

				const response = await fetch('<?php echo $apiAdmin; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(jsonReq)
				});

				const result = await response.json();
				if (result.status === 'success') {
					const data = result.data;

					// List
					const categoryList = document.getElementById('category-list');
					categoryList.innerHTML = '';

					// Options
					const selectCategory = document.getElementById('select-category');
					const editCategory = document.getElementById('edit-category');
					selectCategory.innerHTML = '';
					editCategory.innerHTML = '';


					data.forEach((json) => {
						// List
						const li = document.createElement('li');
						li.className = 'list-group-item d-flex justify-content-between align-items-center';

						li.innerHTML = `
							${json.category_name}
							<div class="d-flex align-items-center ms-auto">
								<span class="badge bg-primary rounded-pill me-2">${json.total}</span>
								<button type="button" class="btn btn-sm btn-danger deleteCategory" data-id="${json.id}" data-confirm="Are you sure to remove this category?"><i class="fa-solid fa-trash"></i> Delete</button>
							</div>
						`;

						categoryList.appendChild(li);

						// Option
						const option = document.createElement('option');
						option.value = json.category_name;
						option.textContent = json.category_name;

						const optionClone = option.cloneNode(true);

						selectCategory.appendChild(option);
						editCategory.appendChild(optionClone);
					});
				}
			} catch (error) {
				// temporary
				console.error('Fetch error:', error);
			}
		}

		async function fetchContent(page = 1, limit = 10, title = null) {
			try {
				let jsonReq;
				if (title !== null) {
					jsonReq = {
						action: 'fetch_content',
						title: title,
						page: page,
						limit: limit
					};
				} else {
					jsonReq = {
						action: 'fetch_content',
						page: page,
						limit: limit
					};
				}

				const response = await fetch('<?php echo $apiAdmin; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(jsonReq)
				});

				const result = await response.json();
				if (result.status === 'success') {
					currentPage = result.pagination.current_page;
					updateTableContents(result.data);
					tablePagination(result.pagination.pages, limit);
				}
			} catch (error) {
				// temporary
				console.error('Fetch error:', error);
			}
		}

		function updateTableContents(data) {
			const tableBody = document.querySelector('#table-contents');
			tableBody.innerHTML = '';

			data.forEach((json) => {
				const row = document.createElement('tr');

				// Encrypt
				const content_encrypt = encodeURIComponent(json.blog_content);

				let actionButtons = '';
				actionButtons = `
					<a href="/p/${json.blog_slug}" target="_blank"><button type="button" class="btn btn-primary m-1"><i class="fa-solid fa-eye"></i></button></a>
					<button type="button" class="btn btn-primary m-1 editContent" data-id="${json.id}" data-title="${json.blog_title}" data-description="${json.blog_description}" data-category="${json.blog_category}" data-content="${content_encrypt}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
					<button type="button" class="btn btn-danger m-1 deleteContent" data-id="${json.id}" data-confirm="Are you sure to remove this content?"><i class="fa-solid fa-trash"></i> Delete</button>
				`;

				row.innerHTML = `
					<td>
						<p><strong>${json.blog_title}</strong></p>
						<p><i>${json.blog_description}</i></p>
					</td>
					<td>${actionButtons}</td>
				`;

				tableBody.appendChild(row);
			});
		}

		function tablePagination(totalPages, pageLimit) {
			const pagination = document.querySelector('.pagination');
			pagination.innerHTML = '';

			// Previous button
			const prevItem = document.createElement('li');
			prevItem.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
			prevItem.innerHTML = `<a class="page-link" href="#">Previous</a>`;
			prevItem.addEventListener('click', () => {
				if (currentPage > 1) {
					fetchContent(currentPage - 1, pageLimit);
				}
			});
			pagination.appendChild(prevItem);

			// Page numbers
			let startPage = Math.max(1, currentPage - 2);
			let endPage = Math.min(totalPages, currentPage + 2);

			if (currentPage <= 3) {
				startPage = 1;
				endPage = Math.min(totalPages, 5);
			} else if (currentPage > totalPages - 3) {
				startPage = Math.max(1, totalPages - 4);
				endPage = totalPages;
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
			nextItem.innerHTML = `<a class="page-link" href="#">Next</a>`;
			nextItem.addEventListener('click', () => {
				if (currentPage < totalPages) {
					fetchContent(currentPage + 1, pageLimit);
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
				fetchContent(currentPage, pageLimit);
			});
			pagination.appendChild(pageItem);
		}

		function addEllipsis(pagination) {
			const ellipsis = document.createElement('li');
			ellipsis.className = 'page-item disabled';
			ellipsis.innerHTML = `<a class="page-link" href="#">...</a>`;
			pagination.appendChild(ellipsis);
		}

		// Trigger button id.add-content-submit
		const addContentSubmit = document.getElementById('add-content-submit');
		addContentSubmit.addEventListener('click', async (e) => {
			e.preventDefault();

			// Check if all input has values
			if (!Array.from(document.querySelectorAll('#add-content input[required]')).every(input => input.reportValidity())) {
				return;
			}

			// Define values
			const add_title = document.getElementById('add-title');
			const add_description = document.getElementById('add-description');
			const add_category = document.getElementById('select-category');

			const formData = {
				action: 'add_content',
				title: add_title.value,
				description: add_description.value,
				category: add_category.value,
				content: quill_add_content.root.innerHTML,
			};

			const response = await fetch('<?php echo $apiAdmin; ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			});

			const result = await response.json();
			if (result.status === 'success') {
				fetchContent(currentPage, pageLimit);
				fetchCategory();

				const container = document.querySelector('.container-fluid.mt-3');
				let notificationContainer = container.querySelector('.notification-container');

				if (!notificationContainer) {
					notificationContainer = document.createElement('div');
					notificationContainer.className = 'notification-container';
					container.prepend(notificationContainer);
				}

				const alert = document.createElement('div');
				alert.className = 'alert alert-success alert-dismissible fade show';
				alert.role = 'alert';
				alert.innerHTML = `
					Content added successfully!
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				`;

				notificationContainer.appendChild(alert);

				setTimeout(() => {
					alert.remove();
				}, 5000);

				execaddContentModal.hide();
				[add_title, add_description].forEach(input => input.value = '');
				quill_add_content.root.innerHTML = '';

				// Remove errors
				let errorMsg = document.getElementById('error-message');
				if (errorMsg) {
					errorMsg.remove();
				}
			} else {
				let errorMsg = document.getElementById('error-message');
				// Check if error message existed, if exist remove
				if (errorMsg) {
					errorMsg.remove();
				}

				// If not, create new one
				errorMsg = (() => {
					const div = document.createElement('div');
					div.id = 'error-message';
					div.className = 'alert alert-danger';
					addContentModal.querySelector('.modal-body').prepend(div);
					return div;
				})();

				errorMsg.textContent = result.message;
			}
		});

		// Trigger button id.edit-content-submit
		const editContentSubmit = document.getElementById('edit-content-submit');
		editContentSubmit.addEventListener('click', async (e) => {
			e.preventDefault();

			const formData = {
				action: 'edit_content',
				id: document.getElementById('content-id').value,
				title: document.getElementById('edit-title').value,
				description: document.getElementById('edit-description').value,
				category: document.getElementById('edit-category').value,
				content: quill_edit_content.root.innerHTML,
			};

			const response = await fetch('<?php echo $apiAdmin; ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			});

			const result = await response.json();
			if (result.status === 'success') {
				fetchContent(currentPage, pageLimit);
				fetchCategory();

				const container = document.querySelector('.container-fluid.mt-3');
				let notificationContainer = container.querySelector('.notification-container');

				if (!notificationContainer) {
					notificationContainer = document.createElement('div');
					notificationContainer.className = 'notification-container';
					container.prepend(notificationContainer);
				}

				const alert = document.createElement('div');
				alert.className = 'alert alert-success alert-dismissible fade show';
				alert.role = 'alert';
				alert.innerHTML = `
					Content edited successfully!
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				`;

				notificationContainer.appendChild(alert);

				setTimeout(() => {
					alert.remove();
				}, 5000);

				execeditContentModal.hide();
					
				// Remove errors
				let errorMsg = document.getElementById('error-message');
				if (errorMsg) {
					errorMsg.remove();
				}
			} else {
				let errorMsg = document.getElementById('error-message');
				// Check if error message existed, if exist remove
				if (errorMsg) {
					errorMsg.remove();
				}

				// If not, create new one
				errorMsg = (() => {
					const div = document.createElement('div');
					div.id = 'error-message';
					div.className = 'alert alert-danger';
					editContentModal.querySelector('.modal-body').prepend(div);
					return div;
				})();

				errorMsg.textContent = result.message;
			}
		});

		// Trigger button id.add-category-submit
		const addCategorySubmit = document.getElementById('add-category-submit');
		addCategorySubmit.addEventListener('click', async (e) => {
			e.preventDefault();

			// Check if all input has values
			if (!Array.from(document.querySelectorAll('#add-category input[required]')).every(input => input.reportValidity())) {
				return;
			}

			const add_category = document.getElementById('add-category-value');

			const formData = {
				action: 'add_category',
				category: add_category.value,
			};

			const response = await fetch('<?php echo $apiAdmin; ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			});

			const result = await response.json();
			if (result.status === 'success') {
				fetchCategory();

				const container = document.querySelector('.container-fluid.mt-3');
				let notificationContainer = container.querySelector('.notification-container');

				if (!notificationContainer) {
					notificationContainer = document.createElement('div');
					notificationContainer.className = 'notification-container';
					container.prepend(notificationContainer);
				}

				const alert = document.createElement('div');
				alert.className = 'alert alert-success alert-dismissible fade show';
				alert.role = 'alert';
				alert.innerHTML = `
					Category added successfully!
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				`;

				notificationContainer.appendChild(alert);

				setTimeout(() => {
					alert.remove();
				}, 5000);

				execaddCategoryModal.hide();
				[add_category].forEach(input => input.value = '');

				// Remove errors
				let errorMsg = document.getElementById('error-message');
				if (errorMsg) {
					errorMsg.remove();
				}
			} else {
				let errorMsg = document.getElementById('error-message');
				// Check if error message existed, if exist remove
				if (errorMsg) {
					errorMsg.remove();
				}

				// If not, create new one
				errorMsg = (() => {
					const div = document.createElement('div');
					div.id = 'error-message';
					div.className = 'alert alert-danger';
					addCategoryModal.querySelector('.modal-body').prepend(div);
					return div;
				})();

				errorMsg.textContent = result.message;
			}
		});
		<?php } ?>

		<?php if ($page_settings) { ?>
		// Fetch tables.users.administrator
		fetchAdminProfile();

		// Fetch tables.siteconfig
		fetchSiteConfig();

		async function fetchAdminProfile() {
			try {
				const jsonReq = {
					action: 'fetch_admin_data',
				};

				const response = await fetch('<?php echo $apiAdmin; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(jsonReq)
				});

				const result = await response.json();
				if (result.status === 'success') {
					document.getElementById('default-id').value = result.data.id;
					document.getElementById('edit-firstname').value = result.data.firstname;
					document.getElementById('edit-lastname').value = result.data.lastname;
					document.getElementById('edit-email').value = result.data.email;
					document.getElementById('edit-username').value = result.data.username;
				}
			} catch (error) {
				console.error('Fetch error:', error);
			}
		}

		async function fetchSiteConfig() {
			try {
				const jsonReq = {
					action: 'fetch_site_config',
				};

				const response = await fetch('<?php echo $apiAdmin; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(jsonReq)
				});

				const result = await response.json();
				if (result.status === 'success') {
					document.getElementById('edit-title').value = result.data.site_title;
					document.getElementById('edit-description').value = result.data.site_description;
					document.getElementById('edit-profile-img').value = result.data.blog_picture;
				}
			} catch (error) {
				console.error('Fetch error:', error);
			}
		}

		// Trigger button id.edit-account-submit
		const editButtonSubmit = document.getElementById('edit-account-submit');
		editButtonSubmit.addEventListener('click', async (e) => {
			e.preventDefault();
			const formData = {
				action: 'edit_profile',
				userid: document.getElementById('default-id').value,
				fname: document.getElementById('edit-firstname').value,
				lname: document.getElementById('edit-lastname').value,
				email: document.getElementById('edit-email').value,
				username: document.getElementById('edit-username').value,
				password: document.getElementById('edit-password').value,
			};

			const response = await fetch('<?php echo $apiAdmin; ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			});

			const result = await response.json();
			if (result.status === 'success') {
				fetchAdminProfile();

				const container = document.querySelector('.container.mt-3');
				let notificationContainer = container.querySelector('.notification-container');

				if (!notificationContainer) {
					notificationContainer = document.createElement('div');
					notificationContainer.className = 'notification-container';
					container.prepend(notificationContainer);
				}

				const alert = document.createElement('div');
				alert.className = 'alert alert-success alert-dismissible fade show';
				alert.role = 'alert';
				alert.innerHTML = `
					${result.message}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				`;

				notificationContainer.appendChild(alert);

				setTimeout(() => {
					alert.remove();
				}, 5000);
			} else {
				//alert('Failed to update user: ' + result.message);
				let errorMsg = document.getElementById('error-message');
				// Check if error message existed, if exist remove
				if (errorMsg) {
					errorMsg.remove();
				}

				// If not, create new one
				errorMsg = (() => {
					const div = document.createElement('div');
					div.id = 'error-message';
					div.className = 'alert alert-danger';
					document.querySelector('.card-body.card-profile').prepend(div);
					return div;
				})();

				errorMsg.textContent = result.message;
			}
		});

		// Trigger button id.edit-account-submit
		const editProfileSubmit = document.getElementById('edit-profile-submit');
		editProfileSubmit.addEventListener('click', async (e) => {
			e.preventDefault();

			const formData = {
				action: 'edit_site',
				title: document.getElementById('edit-title').value,
				description: document.getElementById('edit-description').value,
				profile_img: document.getElementById('edit-profile-img').value,
			};

			const response = await fetch('<?php echo $apiAdmin; ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			});

			const result = await response.json();
			if (result.status === 'success') {
				fetchAdminProfile();

				const container = document.querySelector('.container.mt-3');
				let notificationContainer = container.querySelector('.notification-container');

				if (!notificationContainer) {
					notificationContainer = document.createElement('div');
					notificationContainer.className = 'notification-container';
					container.prepend(notificationContainer);
				}

				const alert = document.createElement('div');
				alert.className = 'alert alert-success alert-dismissible fade show';
				alert.role = 'alert';
				alert.innerHTML = `
					${result.message}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				`;

				notificationContainer.appendChild(alert);

				setTimeout(() => {
					alert.remove();
				}, 5000);
			} else {
				//alert('Failed to update user: ' + result.message);
				let errorMsg = document.getElementById('error-message');
				// Check if error message existed, if exist remove
				if (errorMsg) {
					errorMsg.remove();
				}

				// If not, create new one
				errorMsg = (() => {
					const div = document.createElement('div');
					div.id = 'error-message';
					div.className = 'alert alert-danger';
					document.querySelector('.card-body.card-config').prepend(div);
					return div;
				})();

				errorMsg.textContent = result.message;
			}
		});
		<?php } ?>
	</script>
</html>