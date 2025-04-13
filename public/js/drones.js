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
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // ✅ CSRF token (best practice)
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $(`#drone-row-${droneId}`).fadeOut(300, function () {
                            $(this).remove(); // ✅ Remove the row
                        
                            // 🔍 If no more rows remain, show "No drones found" message
                            if ($('#DroneTableBody tr[id^="drone-row"]').length === 0) {
                                $('#DroneTableBody').append(`
                                    <tr id="no-drones-row">
                                        <td colspan="4" class="text-center text-light">No drones found.</td>
                                    </tr>
                                `);
                            }
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
    
    
    $(document).on('click', '.edit-drone', function () {
        const row = $(this).closest('tr');
        const droneId = $(this).data('id');
        const model = row.find('.drone-model').text().trim();
        const serialNo = row.find('.drone-serial').text().trim();
        const userId = row.find('.drone-user').data('user-id');
    
        // Fill form fields
        $('#droneId').val(droneId);
        $('#modal').val(model).prop('disabled', true);
        $('#srno').val(serialNo);
        $('#user_type').val(userId);
    
        // Change button text and show cancel button
        $('#submitDroneBtn').text('Update Drone');
        $('.cancel-btn').removeClass('d-none');
    });
    
        
    $(document).on('click', '.cancel-btn', function () {

    
        // Fill form fields
        $('#droneId').val('');
        $('#modal').val('Dji Mavic 4').prop('disabled', true);
        $('#srno').val('');
        $('#user_type').val('');
    
        // Change button text and show cancel button
        $('#submitDroneBtn').text('Add Drone');
        $('.cancel-btn').removeClass('d-none');
    });
    $('#addDroneForm').on('submit', function (e) {
        e.preventDefault();
    
        const modal = $('#modal').val().trim();
        const srno = $('#srno').val().trim();
        const user_id = $('#user_type').val();
        const droneId = $('#droneId').val();
        const isUpdate = $('#submitDroneBtn').text().toLowerCase().includes('update');
    
        if (!modal || !srno || !user_id) {
            Swal.fire({
                icon: 'warning',
                title: 'All fields are required',
                text: 'Please complete the form before submitting.'
            });
            return;
        }
    
        const url = isUpdate
            ? `/update-drone/${droneId}/update` // Route::put
            : '/drones'; // Route::post
    
        const method = isUpdate ? 'PUT' : 'POST';
    
        $.ajax({
            url: url,
            type: 'POST', // Laravel handles PUT via POST + _method
            data: {
                _token: $('input[name="_token"]').val(),
                _method: method,
                model: modal,
                sr_no: srno,
                user_id: user_id
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: isUpdate ? 'Drone Updated' : 'Drone Added',
                    text: response.message || 'Operation successful.'
                }).then(() => {
                    location.reload(); // or optionally call getDrones()
                });
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || 'Something went wrong!';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        });
    });
    

    // $('#addDroneForm').on('submit', function (e) {
    //     e.preventDefault();

    //     const modal = $('#modal').val().trim();
    //     const srno = $('#srno').val().trim();
    //     const user_id = $('#user_type').val();

    //     if (!modal || !srno || !user_id) {
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'All fields are required',
    //             text: 'Please complete the form before submitting.'
    //         });
    //         return;
    //     }

    //     $.ajax({
    //         url: '/drones', // Ensure this route exists (POST)
    //         type: 'POST',
    //         data: {
    //             _token: $('input[name="_token"]').val(),
    //             model: modal,
    //             sr_no: srno,
    //             user_id: user_id
    //         },
    //         success: function (response) {
    //             Swal.fire({
    //                 icon: 'success',
    //                 title: 'Drone Added',
    //                 text: 'The drone was successfully added.'
    //             }).then(() => {
    //                 location.reload(); // reload to update the list
    //             });
    //         },
    //         error: function (xhr) {
    //             let message = 'Something went wrong!';
    //             if (xhr.responseJSON && xhr.responseJSON.message) {
    //                 message = xhr.responseJSON.message;
    //             }

    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Error',
    //                 text: message
    //             });
    //         }
    //     });
    // });
});
