$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Submit Add Mission Form via AJAX
    $('#addMissionForm').on('submit', function (e) {
        e.preventDefault();

        // Get selected inspection types
        let selectedInspectionTypes = [];
        $('.inspection-type-checkbox:checked').each(function () {
            selectedInspectionTypes.push($(this).val());
        });

        // Get selected locations
        let selectedLocations = [];
        $('.location-checkbox:checked').each(function () {
            selectedLocations.push($(this).val());
        });

        let formData = {
            inspection_types: selectedInspectionTypes, // ✅ Sending multiple inspection types
            start_datetime: $('#start_datetime').val(),
            end_datetime: $('#end_datetime').val(),
            note: $('#note').val(),
            locations: selectedLocations, // ✅ Sending multiple locations
        };

        $.ajax({
            url: "/missions/store",
            type: "POST",
            data: formData,
            success: function (response) {
                alert(response.message);

                // Append new row to table dynamically
                let newRow = `<tr id="missionRow-${response.mission.id}">
                    <td>${response.mission.inspection_types.map(type => type.name).join("<br>")}</td>
                    <td>${response.mission.start_datetime}</td>
                    <td>${response.mission.end_datetime}</td>
                    <td>${response.mission.locations.map(loc => loc.name).join("<br>")}</td>
                    <td>
                        <button class="btn btn-danger delete-mission" data-id="${response.mission.id}">Delete</button>
                    </td>
                </tr>`;

                $('#missionTableBody').append(newRow);
                $('#addMissionForm')[0].reset(); // Reset form
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });

    // Delete Mission
    $(document).on('click', '.delete-mission', function () {
        let missionId = $(this).data('id');

        if (!confirm("Are you sure you want to delete this mission?")) return;

        $.ajax({
            url: `/missions/${missionId}`,
            type: "DELETE",
            success: function (response) {
                alert(response.message);
                $('#missionRow-' + missionId).remove(); // Remove row from table
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    });
});
