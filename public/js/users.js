$(document).ready(function () {
    // Open Edit User Modal
    $(document).on('click', '.edit-user', function () { // ✅ Ensure dynamic elements work
        let userId = $(this).data('id');

        $.get('/users/' + userId + '/edit', function (data) {
            $('#editUserId').val(data.user.id);
            $('#editFullname').val(data.user.name);
            $('#editEmail').val(data.user.email);

            // Populate regions dropdown
            $('#editRegion').empty();
            data.regions.forEach(region => {
                $('#editRegion').append(`<option value="${region.id}" ${region.id == data.user.region_id ? 'selected' : ''}>${region.name}</option>`);
            });

            // Populate user types dropdown
            $('#editUserType').empty();
            data.userTypes.forEach(type => {
                $('#editUserType').append(`<option value="${type.id}" ${type.id == data.user.user_type_id ? 'selected' : ''}>${type.name}</option>`);
            });

            $('#editUserModal').modal('show'); // ✅ Open Bootstrap modal
        });
    });

    // Update User
    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();

        let userId = $('#editUserId').val();
        let formData = {
            fullname: $('#editFullname').val(),
            email: $('#editEmail').val(),
            region: $('#editRegion').val(),
            user_type: $('#editUserType').val(),
            _token: $('meta[name="csrf-token"]').attr('content') // ✅ Ensure CSRF Token is included
        };

        $.post(`/users/${userId}/update`, formData, function (response) {
            alert(response.message);
            location.reload(); // ✅ Refresh the page after update
        });
    });

    // Delete User
    $(document).on('click', '.delete-user', function () { // ✅ Ensure event is attached to dynamically added elements
        let userId = $(this).data('id');

        if (!confirm("Are you sure you want to delete this user?")) {
            return;
        }

        $.ajax({
            url: `/users/${userId}`,
            type: "DELETE",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // ✅ Include CSRF Token in headers
            success: function (response) {
                alert(response.message);
                location.reload(); // ✅ Reload page to reflect changes
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });
});
