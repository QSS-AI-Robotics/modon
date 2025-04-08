$(document).ready(function () {


    $(document).on('click', '.delete-drone', function () {
        const droneId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: 'This drone will be deleted permanently!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/delete-drone/${droneId}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $(`#drone-row-${droneId}`).remove(); // ✅ Remove row from DOM
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message || 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
    

    
    $('#addDroneForm').on('submit', function (e) {
        e.preventDefault();

        const modal = $('#modal').val().trim();
        const srno = $('#srno').val().trim();
        const user_id = $('#user_type').val();

        if (!modal || !srno || !user_id) {
            Swal.fire({
                icon: 'warning',
                title: 'All fields are required',
                text: 'Please complete the form before submitting.'
            });
            return;
        }

        $.ajax({
            url: '/drones', // Ensure this route exists (POST)
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                model: modal,
                sr_no: srno,
                user_id: user_id
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Drone Added',
                    text: 'The drone was successfully added.'
                }).then(() => {
                    location.reload(); // reload to update the list
                });
            },
            error: function (xhr) {
                let message = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        });
    });
});
