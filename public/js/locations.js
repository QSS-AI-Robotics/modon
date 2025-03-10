$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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

                // Show modal first, then set values
                $('#addLocationModal').modal('show');

                $('#addLocationModal').on('shown.bs.modal', function () {
                    $("#locationId").val(data.id);
                    $("#name").val(data.name);
                    $("#latitude").val(data.latitude);
                    $("#longitude").val(data.longitude);
                    $("#map_url").val(data.map_url);
                    $("#description").val(data.description);
                });
            },
            error: function (xhr) {
                alert("Error fetching location data: " + xhr.responseText);
            }
        });
    });

    // Submit Form (Create)
    $('#locationForm').on('submit', function (e) {
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
                location.reload();
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
                location.reload();
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });
});
