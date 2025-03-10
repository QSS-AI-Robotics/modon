<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- ✅ Ensure CSRF Token is present -->

</head>
<body>
<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    @csrf
                    <input type="hidden" id="editUserId">

                    <div class="mb-3">
                        <label for="editFullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editFullname" required>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRegion" class="form-label">Region</label>
                        <select class="form-select" id="editRegion"></select>
                    </div>

                    <div class="mb-3">
                        <label for="editUserType" class="form-label">User Type</label>
                        <select class="form-select" id="editUserType"></select>
                    </div>

                    <button type="submit" class="btn btn-success">Update User</button>
                </form>
            </div>
        </div>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Modon Dashboard</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link">Welcome, {{ $user->name }}</a>
                </li>
                <li class="nav-item">
                    <button id="logoutButton" class="btn btn-danger">Logout</button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Welcome, {{ $user->name }}!</h2>
    <p>You are now logged in.</p>

    @if($user->userType->name === 'qss_admin')
        <h3 class="mt-4">All Users</h3>
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Region</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->userType->name }}</td>
                        <td>{{ $u->region->name }}</td>
                        <td>
                            <button class="btn btn-warning edit-user" data-id="{{ $u->id }}">Edit</button>
                            <button class="btn btn-danger delete-user" data-id="{{ $u->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/modon.js') }}"></script>
<script src="{{ asset('js/users.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
