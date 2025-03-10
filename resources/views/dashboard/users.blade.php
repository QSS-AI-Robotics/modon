<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="container mt-5">
    <h2>Users Management</h2>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Region</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr data-id="{{ $user->id }}">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->userType->name }}</td>
                    <td>{{ $user->region->name }}</td>
                    <td>
                        <button class="btn btn-warning edit-user" data-id="{{ $user->id }}">Edit</button>
                        <button class="btn btn-danger delete-user" data-id="{{ $user->id }}">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/users.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
