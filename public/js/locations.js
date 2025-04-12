$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getLocations()
    function getLocations() {
        resetForm();
        $.ajax({
            url: "/get-locations", // Route to fetch locations
            type: "GET",
            success: function (response) {
                console.log(response);
                $('#locationTableBody').empty(); // Clear previous data
    
                if (!response.locations || response.locations.length === 0) {
                    $('#locationTableBody').append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted">No locations available.</td>
                        </tr>
                    `);
                    return;
                }
    
                // ✅ Loop through locations and append to table
                $.each(response.locations, function (index, location) {
                    let regionDisplay = location.region 
                        ? location.region.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) 
                        : "N/A";
    
                    let row = `
                        <tr data-id="${location.id}">
                            <td>${location.name}</td>
                            <td>${location.latitude}</td>
                            <td>${location.longitude}</td>
                            <td class="text-capitalize">${regionDisplay}</td>
                            <td>${location.map_url ? `<a href="${location.map_url}" target="_blank">View</a>` : 'N/A'}</td>
                            <td>${location.description || 'N/A'}</td>
                            <td>
                                <img src="./images/edit.png" alt="Edit" class="edit-location img-fluid actions" data-id="${location.id}">
                                <img src="./images/delete.png" alt="Delete" class="delete-location img-fluid actions" data-id="${location.id}">
                            </td>
                        </tr>
                    `;
                    $('#locationTableBody').append(row);
                });
            },
            error: function (xhr) {
                console.error("❌ Error fetching locations:", xhr.responseText);
            }
        });
    }
    


    // Open Edit Modal
    $(document).on('click', '.edit-location', function () {
        let locationId = $(this).data('id');
      
        $.ajax({
            url: `/locations/${locationId}/edit`,
            type: "GET",
            success: function (data) {
                console.log("Edit Response:", data);

                if (data.error) {
                    alert(data.error);
                    return;
                }

             

               
                    $("#locationId").val(data.id);
                    $("#name").val(data.name);
                    $("#latitude").val(data.latitude);
                    $("#longitude").val(data.longitude);
                    $("#map_url").val(data.map_url);
                    $("#description").val(data.description);

                    $(".form-title").text("Edit Location");
                    $(".mission-btn span").text("Update Location");
                    $(".mission-btn svg").attr({ "width": "20", "height": "20" }); // Increase SVG size
                
                    // ✅ Store Location ID in Form (Hidden Input)
                    $("#locationForm").attr("data-location-id", locationId);
                
                    // ✅ Show Cancel Button
                    $(".cancel-btn").removeClass("d-none");
                
            },
            error: function (xhr) {
                alert("Error fetching location data: " + xhr.responseText);
            }
        });
    });

    function resetForm(){
        $('#location-validation-errors').addClass('d-none').text('');
        $("#locationForm")[0].reset(); // Reset Form Fields
        $("#locationForm").removeAttr("data-location-id"); // Remove Edit Mode
        $(".form-title").text("Create New Location");
        $(".mission-btn span").text("New Location");
        $(".cancel-btn").addClass("d-none"); // Hide Cancel Button
    }
    $(document).on("click", ".cancel-btn", function () {
        resetForm()
    });


    // Submit Form (Create)

    $('#locationForm').on('submit', function (e) {
        e.preventDefault();
    
        const $errorDiv = $('#location-validation-errors');
        $errorDiv.addClass('d-none').text(''); // Clear previous
    
        const formData = {
            name: $('#name').val().trim(),
            latitude: $('#latitude').val().trim(),
            longitude: $('#longitude').val().trim(),
            map_url: $('#map_url').val().trim(),
            description: $('#description').val().trim()
        };
    
        const hasEmpty = Object.values(formData).some(value => !value);
    
        if (hasEmpty) {
            $errorDiv.removeClass('d-none').text('All fields are required.');
            return;
        }
    
        const locationId = $('#locationId').val();
        const url = locationId ? `/locations/${locationId}/update` : `/locations/store`;
    
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Location saved.',
                    timer: 2000,
                    showConfirmButton: false
                });
                getLocations();
                
                $errorDiv.addClass('d-none').text('');
            },
            error: function (xhr) {
               
                // $errorDiv.removeClass('d-none').text(errorMsg);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Something went wrong.',
                });
            }
        });
    });
    
    


    // Delete Location
    $(document).on('click', '.delete-location', function () {
        let locationId = $(this).data('id');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This location will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/locations/${locationId}`,
                    type: "DELETE",
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Location has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        getLocations();
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
