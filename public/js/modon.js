$(document).ready(function () {

    $('#signupForm').on('submit', function (e) {
        e.preventDefault(); // Prevent form from reloading

        let formData = {
            fullname: $('#fullname').val(),
            email: $('#email').val(),
            password: $('#password').val(),
            region: $('#region').val(),
            user_type: $('#user_type').val(),
            _token: $('input[name="_token"]').val() // CSRF Token
        };

        $.ajax({
            url: "/signup", // Use relative URL
            type: "POST",
            data: formData,
            success: function (response) {
                $('#successMessage').text(response.message).removeClass('d-none');
                $('#signupForm')[0].reset(); // Reset form after success
                $('.text-danger').text(''); // Clear previous errors
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;
                $('#fullname_error').text(errors.fullname ? errors.fullname[0] : '');
                $('#email_error').text(errors.email ? errors.email[0] : '');
                $('#password_error').text(errors.password ? errors.password[0] : '');
                $('#region_error').text(errors.region ? errors.region[0] : '');
                $('#user_type_error').text(errors.user_type ? errors.user_type[0] : '');
            }
        });
    });

    $('#signinForm').on('submit', function (e) {
        e.preventDefault();

        let formData = {
            email: $('#email').val(),
            password: $('#password').val(),
            _token: $('input[name="_token"]').val()
        };

        $.ajax({
            url: "/signin",
            type: "POST",
            data: formData,
            success: function (response) {
                window.location.href = response.redirect; // Redirect on success
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;
                $('#email_error').text(errors.email ? errors.email[0] : '');
                $('#password_error').text(errors.password ? errors.password[0] : '');
                
                if (xhr.responseJSON.error) {
                    $('#errorMessage').text(xhr.responseJSON.error).removeClass('d-none');
                }
            }
        });
    });

    // Logout AJAX Request
    $('#logoutButton').on('click', function () {
        $.ajax({
            url: "/logout",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                window.location.href = response.redirect; // Redirect to signin page
            }
        });
    });

    
});
