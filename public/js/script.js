$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getAllusers()
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
                                <img src="./images/edit.png" alt="Edit" class="edit-location img-fluid actions" data-id="${user.id}">
                                <img src="./images/delete.png" alt="Delete" class="delete-location img-fluid actions" data-id="${user.id}">
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
                    $("#signupForm").attr("data-location-id", locationId);
                
                    // ✅ Show Cancel Button
                    $(".cancel-btn").removeClass("d-none");
                
            },
            error: function (xhr) {
                alert("Error fetching location data: " + xhr.responseText);
            }
        });
    });

    function resetForm(){
        $("#signupForm")[0].reset(); // Reset Form Fields
        $("#signupForm").removeAttr("data-location-id"); // Remove Edit Mode
        $(".form-title").text("Create New Location");
        $(".mission-btn span").text("New Location");
        $(".cancel-btn").addClass("d-none"); // Hide Cancel Button
    }
    $(document).on("click", ".cancel-btn", function () {
        resetForm()
    });


    // Submit Form (Create)
    $('#signupForm').on('submit', function (e) {
        e.preventDefault();

        let locationId = $('#locationId').val();
        let formData = {
            name: $('#name').val(),
            latitude: $('#latitude').val(),
            longitude: $('#longitude').val(),
            map_url: $('#map_url').val(),
            description: $('#description').val(),
        };

        let url = locationId ? `/locations/${locationId}/update` : `/locations/store`;

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (response) {
                alert(response.message);
                getLocations()
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });

    // Delete Location
    $(document).on('click', '.delete-location', function () {
        let locationId = $(this).data('id');

        if (!confirm("Are you sure you want to delete this location?")) {
            return;
        }

        $.ajax({
            url: `/locations/${locationId}`,
            type: "DELETE",
            success: function (response) {
                alert(response.message);
                getLocations()
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });
});
