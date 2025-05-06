$(document).ready(function(){
    $('#signinForm').on('submit', function (e) {
        e.preventDefault();

        // UI update: Show spinner, hide text, disable button
        $('#signInText').addClass('d-none');
        $('#spinner').removeClass('d-none');
        $('#signInBtn').prop('disabled', true);

        // Hide any old messages
        $('#response-message').text('').addClass('d-none');

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
                window.location.href = response.redirect;
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;

                if (errors) {
                    if (errors.email) {
                        $('#response-message').text(errors.email[0]);
                    } else if (errors.password) {
                        $('#response-message').text(errors.password[0]);
                    }
                }

                if (xhr.responseJSON.error) {
                    $('#response-message').text(xhr.responseJSON.error);
                }

                $('#response-message').removeClass('d-none');

                // Revert UI: hide spinner, show text, enable button
                $('#signInText').removeClass('d-none');
                $('#spinner').addClass('d-none');
                $('#signInBtn').prop('disabled', false);
            }
        });
    });


    // Show the Forget Password Modal
    $('#forgetPasswordLink').on('click', function (e) {
        e.preventDefault();

        $('#forgetPasswordModal').modal('show');
    });
// Handle Forget Password Form Submission
$('#forgetPasswordForm').on('submit', function (e) {
    e.preventDefault();

    const email = $('#forgetEmail').val().trim();
    const errorDiv = $('#forgetPasswordError');
    const successDiv = $('#forgetPasswordSuccess');

    // Clear previous messages
    errorDiv.text('');
    successDiv.text('');

    // Show spinner and hide button text
    $('#resetPasswordText').addClass('d-none');
    $('#resetPasswordspinner').removeClass('d-none');

    // Disable button
    $('.resetPasswordbtn').prop('disabled', true);

    $.ajax({
        url: '/forget-password',
        type: 'POST',
        data: { email },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // CSRF token
        },
        success: function (response) {
            successDiv.text(response.message);
            $('#forgetEmail').val(''); // Clear email input

            // Show text, hide spinner, enable button
            $('#resetPasswordText').removeClass('d-none');
            $('#resetPasswordspinner').addClass('d-none');
            $('.resetPasswordbtn').prop('disabled', false);
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors && errors.email) {
                errorDiv.text(errors.email[0]);
            } else if (xhr.responseJSON?.message) {
                errorDiv.text(xhr.responseJSON.message);
            } else {
                errorDiv.text('حدث خطأ ما. الرجاء المحاولة مرة أخرى.');
            }

            // Show text, hide spinner, enable button
            $('#resetPasswordText').removeClass('d-none');
            $('#resetPasswordspinner').addClass('d-none');
            $('.resetPasswordbtn').prop('disabled', false);
        }
    });
});

    // Handle Forget Password Form Submission
//     $('#forgetPasswordForm').on('submit', function (e) {
//         e.preventDefault();
    
//         const email = $('#forgetEmail').val().trim();
//         const errorDiv = $('#forgetPasswordError');
//         const successDiv = $('#forgetPasswordSuccess');
        
//         $('#resetPasswordText').addClass('d-none');
//         $('#resetPasswordspinner').removeClass('d-none');
//         $('#resetPasswordbtn').prop('disabled', true);


//         errorDiv.text('');
//         successDiv.text('');
    
//         $.ajax({
//             url: '/forget-password',
//             type: 'POST',
//             data: { email },
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // Ensure CSRF token is included
//             },
//             success: function (response) {
//                 successDiv.text(response.message);
//                 $('#forgetEmail').val('');
//                 $('#resetPasswordText').removeClass('d-none');
//                 $('#resetPasswordspinner').addClass('d-none');
//                 $('#resetPasswordbtn').prop('disabled', false);
//             },
//             error: function (xhr) {
//                 const errors = xhr.responseJSON.errors;
//                 if (errors && errors.email) {
//                     errorDiv.text(errors.email[0]);
//                 } else {
//                     errorDiv.text('An error occurred. Please try again.');
//                     $('#resetPasswordText').removeClass('d-none');
//                     $('#resetPasswordbtn').prop('disabled', false);
//                     $('#resetPasswordspinner').addClass('d-none');
//                 }
//             },
//         });
//      });
});