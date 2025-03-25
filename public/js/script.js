$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });




    getAllusers();
    function getAllusers() {
        resetForm();
        $.ajax({
            url: "/dashboard/users",
            type: "GET",
            success: function (response) {
                console.log("Users Response:", response);
                $('#userTableBody').empty();
    
                if (!response.users || response.users.length === 0) {
                    $('#userTableBody').append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">No users available.</td>
                        </tr>
                    `);
                    return;
                }
    
                $.each(response.users, function (index, user) {
                    let row = `
                        <tr data-id="${user.id}">
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.user_type?.name || "N/A"}</td>
                            <td>${user.region?.name || "N/A"}</td>
                        
                            <td>
                                <img src="./images/edit.png" alt="Edit" class="edit-user img-fluid actions" data-id="${user.id}">
                                <img src="./images/delete.png" alt="Delete" class="delete-user img-fluid actions" data-id="${user.id}">
                            </td>
                        </tr>
                    `;
                    $('#userTableBody').append(row);
                });
            },
            error: function (xhr) {
                console.error("❌ Error fetching users:", xhr.responseText);
            }
        });
    }
    $(document).on('submit', '#signupForm', function (e) {
        e.preventDefault();
    
        const formData = {
            id: $('#userId').val(),
            name: $('#fullname').val(),
            email: $('#email').val(),
            password: $('#password').val(),
            region_id: $('#region').val(),
            user_type_id: $('#user_type').val()
        };
    
        const $submitBtn = $(this).find('button[type="submit"]');
        const isUpdate = $submitBtn.text().trim().toLowerCase() === 'update user';
        const url = isUpdate ? `/dashboard/users/${formData.id}` : '/dashboard/users/storeuser';
        const method = isUpdate ? 'PUT' : 'POST';
    
        $.ajax({
            url,
            type: method,
            data: formData,
            success: function (response) {
                alert(response.message);
                $(".cancel-btn").addClass("d-none");
                resetForm();
                getAllusers();
            },
            error: function (xhr) {
                const error = xhr.responseJSON?.message || 'An error occurred.';
                alert("❌ " + error);
            }
        });
    });
    
    $(document).on('click', '.edit-user', function () 
    {
        $(".cancel-btn").removeClass("d-none");
        const row = $(this).closest('tr');
        const userId = $(this).data('id');
        const name = row.find('td:eq(0)').text().trim();
        const email = row.find('td:eq(1)').text().trim();
        const userType = row.find('td:eq(2)').text().trim();
        const region = row.find('td:eq(3)').text().trim();
    
        // Fill form fields
        $('#userId').val(userId);
        $('#fullname').val(name);
        $('#email').val(email);
        $('#password').val(''); // Clear password field for security
    
        // Set region select
        $('#region option').each(function () {
            if ($(this).text().trim() === region) {
                $(this).prop('selected', true);
            }
        });
    
        // Set user type select
        $('#user_type option').each(function () {
            if ($(this).text().trim() === userType) {
                $(this).prop('selected', true);
            }
        });
    
        // Change button text to "Update User"
        $('#signupForm button[type="submit"]').text('Update User');
    });
 

    function resetForm() {

        $('#signupForm')[0].reset();
        $('#userId').val('');
        $('#signupForm button[type="submit"]').text('Create User');
    }
    $(document).on("click", ".cancel-btn", function () {
        $(".cancel-btn").addClass("d-none");
        resetForm()
    });


   

    // Delete user
    $(document).on('click', '.delete-user', function () {
        let userId = $(this).data('id');

        if (!confirm("Are you sure you want to delete this User?")) {
            return;
        }

        $.ajax({
            url: `/dashboard/user/${userId}`,
            type: "POST",
            success: function (response) {
                alert(response.message);
                getAllusers();
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });
});
