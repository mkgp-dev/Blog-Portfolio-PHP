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
			<div class="d-flex justify-content-between mb-3">
				<div class="input-group">
					<input type="text" class="form-control" id="search-account" placeholder="Type a username">
					<button class="btn btn-primary" type="button" id="search-account-submit">Search</button>
				</div>
				<select class="form-select ms-2 w-25" id="page-limit">
					<option value="5">5</option>
					<option value="10" selected>10</option>
					<option value="25">25</option>
				</select>
				<button type="button" class="btn btn-primary ms-2 flex-shrink-0" id="call-add-user">Add a new user</button>
			</div>
			<table class="table table-hover">
				<thead>
					<tr>
						<th scope="col">Fullname</th>
						<th scope="col">Username</th>
						<th scope="col">Email</th>
						<th scope="col">Role</th>
						<th scope="col">Actions</th>
					</tr>
				</thead>
				<tbody id="table-users"></tbody>
			</table>

			<div class="pagination-area">
				<ul class="pagination justify-content-center"></ul>
			</div>
		</div>

		<!-- Modal section for editing a user -->
		<div class="modal" id="edit-user" role="dialog" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Edit <strong id="uname"></strong></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true"></span>
						</button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="userid" id="default-id">
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="edit-firstname" class="form-label">First name</label>
								<input type="text" class="form-control" id="edit-firstname" name="firstname" placeholder="Johnny" required>
							</div>
							<div class="col-md-6">
								<label for="edit-lastname" class="form-label">Last name</label>
								<input type="text" class="form-control" id="edit-lastname" name="lastname" placeholder="Silverhand" required>
							</div>
						</div>
						<div class="mb-3">
							<label for="edit-email" class="form-label">Email</label>
							<input type="email" class="form-control" id="edit-email" name="email" placeholder="johnny.silverhand@cyberpunk.cpdr" required>
						</div>
						<div class="mb-3">
							<label for="edit-username" class="form-label">Username</label>
							<input type="text" class="form-control" id="edit-username" name="username" placeholder="johnny.silverhand" required>
						</div>
						<div class="mb-3">
							<label for="edit-password" class="form-label">Password (<i>leave blank to keep current password</i>)</label>
							<input type="password" class="form-control" id="edit-password" name="password" placeholder="huntdownlikesupreme!" required>
						</div>
						<div class="d-grid gap-2">
							<button type="submit" id="edit-account-submit" class="btn btn-primary">Submit</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal section for adding a user -->
		<div class="modal" id="add-user" role="dialog" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add a new user</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true"></span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="add-firstname" class="form-label">First name</label>
								<input type="text" class="form-control" id="add-firstname" name="firstname" placeholder="Johnny" required>
							</div>
							<div class="col-md-6">
								<label for="add-lastname" class="form-label">Last name</label>
								<input type="text" class="form-control" id="add-lastname" name="lastname" placeholder="Silverhand" required>
							</div>
						</div>
						<div class="mb-3">
							<label for="add-email" class="form-label">Email</label>
							<input type="email" class="form-control" id="add-email" name="email" placeholder="johnny.silverhand@cyberpunk.cpdr" required>
						</div>
						<div class="mb-3">
							<label for="add-username" class="form-label">Username</label>
							<input type="text" class="form-control" id="add-username" name="username" placeholder="johnny.silverhand" required>
						</div>
						<div class="mb-3">
							<label for="add-password" class="form-label">Password</label>
							<input type="password" class="form-control" id="add-password" name="password" placeholder="huntdownlikesupreme!" required>
						</div>
						<div class="d-grid gap-2">
							<button type="button" id="add-account-submit" class="btn btn-primary">Submit</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
			// Call table.users with JSON
			let currentPage = 1;
			let pageLimit = document.getElementById('page-limit').value;
			fetchUsers(currentPage, pageLimit);

			// Update page.list
			document.getElementById('page-limit').addEventListener('change', (e) => {
				pageLimit = e.target.value;
				fetchUsers(currentPage, pageLimit);
			});

			// Search user.search
			const searchButtonTrigger = document.getElementById('search-account-submit');
			searchButtonTrigger.addEventListener('click', async (e) => {
				const searchQuery = document.getElementById('search-account').value;
				fetchUsers(currentPage, pageLimit, searchQuery);
			});

			async function fetchUsers(page = 1, limit = 10, username = null) {
				try {
					let jsonReq;
					if (username !== null) {
						jsonReq = {
							username: username,
							page: page,
							limit: limit
						};
					} else {
						jsonReq = {
							page: page,
							limit: limit
						};
					}

					const response = await fetch('<?php echo $apiList; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(jsonReq)
					});

					const result = await response.json();
					if (result.status === 'success') {
						updateTableUsers(result.data);
						tablePagination(result.pagination.pages, limit);
					}
				} catch (error) {
					// temporary
					console.error('Fetch error:', error);
				}
			}

			function updateTableUsers(data) {
				const tableBody = document.querySelector('#table-users');
				tableBody.innerHTML = '';

				data.forEach((json) => {
					const row = document.createElement('tr');

					let actionButtons = '';
					if (json.privilege !== 'administrator') {
						actionButtons = `
							<button type="button" class="btn btn-primary editUser" data-id="${json.id}" data-firstname="${json.firstname}" data-lastname="${json.lastname}" data-username="${json.username}" data-email="${json.email}">Edit</button>
							<button type="button" class="btn btn-danger deleteUser" data-id="${json.id}" data-firstname="${json.firstname}" data-lastname="${json.lastname}" data-username="${json.username}" data-email="${json.email}" data-confirm="Are you sure to remove this user?">Delete</button>
						`;
					} else {
						actionButtons = `
							<button type="button" class="btn btn-primary editUser" data-id="${json.id}" data-firstname="${json.firstname}" data-lastname="${json.lastname}" data-username="${json.username}" data-email="${json.email}">Edit</button>
						`;
					}

					row.innerHTML = `
						<td>${json.firstname} ${json.lastname}</td>
						<td>${json.username}</td>
						<td>${json.email}</td>
						<td>${json.privilege}</td>
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
					fetchUsers(currentPage - 1, pageLimit);
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
						fetchUsers(currentPage + 1, pageLimit);
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
					fetchUsers(currentPage, pageLimit);
				});
				pagination.appendChild(pageItem);
			}

			function addEllipsis(pagination) {
				const ellipsis = document.createElement('li');
				ellipsis.className = 'page-item disabled';
				ellipsis.innerHTML = `<a class="page-link" href="#">...</a>`;
				pagination.appendChild(ellipsis);
			}

			// Modal Execution
			// Variable/s
			const addAccountModal = document.getElementById('add-user');
			const editAccountModal = document.getElementById('edit-user');
			const execaddAccountModal = new bootstrap.Modal(addAccountModal);
			const execeditAccountModal = new bootstrap.Modal(editAccountModal);
			const triggerAddAccount = document.getElementById('call-add-user');

			triggerAddAccount.addEventListener('click', () => {
				execaddAccountModal.show();
			});

			// Button id.editUser
			document.getElementById('table-users').addEventListener('click', async (e) => {
				if (e.target.classList.contains('editUser')) {
					const button = e.target;
					document.getElementById('uname').innerHTML = button.dataset.username;
					document.getElementById('default-id').value = button.dataset.id;
					document.getElementById('edit-firstname').value = button.dataset.firstname;
					document.getElementById('edit-lastname').value = button.dataset.lastname;
					document.getElementById('edit-email').value = button.dataset.email;
					document.getElementById('edit-username').value = button.dataset.username;

					// Show modal
					execeditAccountModal.show();
				} else if (e.target.classList.contains('deleteUser')) {
					const button = e.target;

					confirm(button.dataset.confirm);

					const userData = {
						action: 'delete',
						userid: button.dataset.id,
						fname: button.dataset.firstname,
						lname: button.dataset.lastname,
						email: button.dataset.email,
						username: button.dataset.username,
					};

					const response = await fetch('<?php echo $apiUser; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(userData)
					});

					const result = await response.json();
					if (result.status === 'success') {
						fetchUsers(currentPage, pageLimit);

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

			// Trigger button id.edit-account-submit
			const editButtonSubmit = document.getElementById('edit-account-submit');
			editButtonSubmit.addEventListener('click', async (e) => {
				e.preventDefault();
				const formData = {
					action: 'edit',
					userid: document.getElementById('default-id').value,
					fname: document.getElementById('edit-firstname').value,
					lname: document.getElementById('edit-lastname').value,
					email: document.getElementById('edit-email').value,
					username: document.getElementById('edit-username').value,
					password: document.getElementById('edit-password').value,
				};

				const response = await fetch('<?php echo $apiUser; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(formData)
				});

				const result = await response.json();
				if (result.status === 'success') {
					fetchUsers(currentPage, pageLimit);
					/**
					const userId = formData.userid;
					const userRow = document.querySelector(`#tableBody tr[data-id="${userId}"]`);
					if (userRow) {
						userRow.children[0].textContent = `${formData.fname} ${formData.lname}`;
						userRow.children[1].textContent = formData.username;
						userRow.children[2].textContent = formData.email;
						userRow.children[3].textContent = result.privilege || userRow.children[3].textContent;
					}
					**/


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
						User edited successfully!
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					`;

					notificationContainer.appendChild(alert);

					setTimeout(() => {
						alert.remove();
					}, 5000);

					// Clear all
					execeditAccountModal.hide();
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
						addAccountModal.querySelector('.modal-body').prepend(div);
						return div;
					})();

					errorMsg.textContent = result.message;
				}
			});

			// Trigger button id.add-account-submit
			const addButtonSubmit = document.getElementById('add-account-submit');
			addButtonSubmit.addEventListener('click', async (e) => {
				e.preventDefault();

				// Check if all input has values
				if (!Array.from(document.querySelectorAll('#add-user input[required]')).every(input => input.reportValidity())) {
					return;
				}

				// Define values (no repetition)
				const add_fname = document.getElementById('add-firstname');
				const add_lname = document.getElementById('add-lastname');
				const add_email = document.getElementById('add-email');
				const add_username = document.getElementById('add-username');
				const add_password = document.getElementById('add-password');

				const formData = {
					action: 'add',
					fname: add_fname.value,
					lname: add_lname.value,
					email: add_email.value,
					username: add_username.value,
					password: add_password.value,
				};

				const response = await fetch('<?php echo $apiUser; ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(formData)
				});

				const result = await response.json();
				if (result.status === 'success') {
					fetchUsers(currentPage, pageLimit);

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
						User added successfully!
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					`;

					notificationContainer.appendChild(alert);

					setTimeout(() => {
						alert.remove();
					}, 5000);

					/**
					const default_table = document.getElementById('table-users');
					const addRow = document.createElement('tr');
					addRow.innerHTML = `
						<td>${add_fname.value} ${add_lname.value}</td>
						<td>${add_username.value}</td>
						<td>${add_email.value}</td>
						<td>${result.privilege_default}</td>
						<td>
							<button type="button" class="btn btn-primary editUser" data-bs-toggle="modal" data-bs-target="#edit-user">Edit</button>
							<button type="button" class="btn btn-warning">Delete</button>
						</td>
					`;

					// Patch editButton
					const editButton = addRow.querySelector('.editUser');
					editButton.setAttribute('data-id', result.fetchId);
					editButton.setAttribute('data-firstname', add_fname.value);
					editButton.setAttribute('data-lastname', add_lname.value);
					editButton.setAttribute('data-username', add_username.value);
					editButton.setAttribute('data-email', add_email.value);

					default_table.appendChild(addRow);
					**/

					// Clear all
					execaddAccountModal.hide();
					[add_fname, add_lname, add_email, add_username, add_password].forEach(input => input.value = '');
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
						addAccountModal.querySelector('.modal-body').prepend(div);
						return div;
					})();

					errorMsg.textContent = result.message;
				}
			});
		</script>
	</body>
<?php include __DIR__ . '/footer.php'; ?>