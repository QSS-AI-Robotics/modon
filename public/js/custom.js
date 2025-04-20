document.addEventListener('DOMContentLoaded', function () {
    const editProfileButton = document.getElementById('editProfileButton');
    const passwordResetModal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
    const passwordResetForm = document.getElementById('passwordResetForm');
    const submitButton = document.querySelector('.passbtn'); // Button for adding loader

    // Show the modal when "Edit Profile" is clicked
    editProfileButton.addEventListener('click', function () {
        console.log('Edit Profile button clicked'); // Log button click
        passwordResetModal.show();
    });

    // Handle form submission
    passwordResetForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmNewPassword = document.getElementById('confirmNewPassword').value;

        console.log('Password reset form submitted'); // Log form submission
        console.log('Current Password:', currentPassword);
        console.log('New Password:', newPassword);
        console.log('Confirm New Password:', confirmNewPassword);

        // Clear previous error messages
        clearErrors();

        if (newPassword !== confirmNewPassword) {
            console.error('Passwords do not match'); // Log password mismatch
            showError('confirmNewPassword', 'Passwords do not match. Please try again.');
            return;
        }

        console.log('Passwords match. Proceeding with submission.'); // Log password match

        // Add spinning loader to the button
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

        const formData = new FormData(passwordResetForm);
        const response = await fetch('/reset-password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        });

        const result = await response.json();

        // Remove spinning loader
        submitButton.disabled = false;
        submitButton.innerHTML = 'Update Password';

        if (response.ok) {
            console.log('Password reset successful:', result.message); // Log success
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message,
                confirmButtonColor: '#105A7E',
            });
            passwordResetModal.hide();
            passwordResetForm.reset();
        } else {
            console.error('Password reset failed:', result.error || 'Unknown error'); // Log failure
            if (result.details) {
                // Display validation errors
                for (const [field, messages] of Object.entries(result.details)) {
                    showError(field, messages[0]); // Show the first error message for each field
                }
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.error || 'An error occurred. Please try again.',
                confirmButtonColor: '#d33',
            });
        }
    });

    // Function to display error messages
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
    }

    // Function to clear previous error messages
    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }
});