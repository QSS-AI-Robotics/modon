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
});