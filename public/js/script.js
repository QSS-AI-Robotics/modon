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
                                <img src="../images/edit.png" alt="Edit" class="edit-user img-fluid actions" data-id="${user.id}">
                                <img src="../images/delete.png" alt="Delete" class="delete-user img-fluid actions" data-id="${user.id}">
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
    
        const $errorDiv = $('#users-validation-errors');
        $errorDiv.addClass('d-none');
    
        const formData = {
            id: $('#userId').val(),
            name: $('#fullname').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val(),
            region_id: $('#region').val(),
            user_type_id: $('#user_type').val()
        };
    
        const hasError =
            !formData.name ||
            !formData.email || !/^\S+@\S+\.\S+$/.test(formData.email) ||
            !formData.password || formData.password.length < 6 ||
            !formData.region_id ||
            !formData.user_type_id;
    
        if (hasError) {
            $errorDiv.removeClass('d-none').text("All fields are required.");
            return;
        }
    
        const $submitBtn = $(this).find('button[type="submit"]');
        const isUpdate = $submitBtn.text().trim().toLowerCase().includes('update');
        const url = isUpdate ? `/dashboard/users/${formData.id}` : '/dashboard/users/storeuser';
        const method = isUpdate ? 'PUT' : 'POST';
    
        $.ajax({
            url,
            type: method,
            data: formData,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'User saved successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
    
                $(".cancel-btn").addClass("d-none");
                resetForm();
                getAllusers();
                $errorDiv.addClass('d-none');
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
    
                if (response?.errors) {
                    // Laravel-style validation errors
                    const messages = Object.values(response.errors).flat();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: `<ul style="text-align:left;">${messages.map(msg => `<li>${msg}</li>`).join('')}</ul>`
                    });
                } else {
                    // General server error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response?.message || 'Something went wrong.',
                    });
                }
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
        $('#users-validation-errors').addClass('d-none').text('');;
        $('#signupForm button[type="submit"]').text('Create User');
    }
    $(document).on("click", ".cancel-btn", function () {
        $(".cancel-btn").addClass("d-none");
        resetForm()
    });


   

    // Delete user
    $(document).on('click', '.delete-user', function () {
        const userId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/dashboard/user/${userId}`,
                    type: "POST",
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'User has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        getAllusers();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.',
                        });
                    }
                });
            }
        });
    });
    
});
