$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getRegionManagerMissions();
    function getRegionManagerMissions() {
        $.ajax({
            url: "/getmanagermissions", // Matches Laravel route for fetching missions
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty(); // Clear previous data
                // console.log("mission data", response.missions);
                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">No missions available.</td>
                        </tr>
                    `);
                    return;
                }
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypes = mission.inspection_types.map(type => type.name).join("<br>");
                    let locations = mission.locations.map(loc => loc.name).join("<br>");
    
                    // ✅ Mission Status Display
                    let statusBadge = "";
                    if (mission.status === "Pending") {
                        statusBadge = `<span class="badge bg-danger">Pending</span>`;
                    } else if (mission.status === "In Progress") {
                        statusBadge = `<span class="badge bg-warning text-dark">In Progress</span>`;
                    } else if (mission.status === "Awaiting Report") {
                        statusBadge = `<span class="badge bg-primary">Awaiting Report</span>`;
                    } else if (mission.status === "Completed") {
                        statusBadge = `<span class="badge bg-success">Completed</span>`;
                    }
    
                    let row = `
                        <tr id="missionRow-${mission.id}">
                            <td>${inspectionTypes}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td>${locations}</td>
                            <td>${mission.note}</td>
                            <td>${statusBadge}</td>
                            <td>
                             <button class="btn btn-warning edit-mission" data-id="${mission.id}">Edit</button>
                             <button class="btn btn-danger delete-mission" data-id="${mission.id}">Delete</button>
                            </td>
                        </tr>
                    `;
    
                    $('#missionTableBody').append(row);
                });
            },
            error: function (xhr) {
                console.error("❌ Error fetching missions:", xhr.responseText);
                alert("Error fetching missions. Please try again.");
            }
        });
    }
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
                getRegionManagerMissions(); // Refresh mission table
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

        // Handle Edit Mission Button Click
        $(document).on("click", ".edit-mission", function () {
            let missionId = $(this).data("id");
    
            $.ajax({
                url: `/missions/${missionId}/edit`,
                type: "GET",
                success: function (response) {
                    console.log("✅ Loaded Mission Data:", response);
    
                    // Populate Modal Fields
                    $("#edit_mission_id").val(response.mission.id);
                    $("#edit_start_datetime").val(response.mission.start_datetime.replace(" ", "T"));
                    $("#edit_end_datetime").val(response.mission.end_datetime.replace(" ", "T"));
                    $("#edit_note").val(response.mission.note);
    
                    // Populate Inspection Type Checkboxes
                    $("#editInspectionTypeCheckboxes").empty();
                    response.all_inspection_types.forEach(type => {
                        let checked = response.selected_inspections.some(selected => selected.id === type.id) ? "checked" : "";
                        $("#editInspectionTypeCheckboxes").append(`
                            <div class="form-check me-3">
                                <input class="form-check-input edit-inspection-type-checkbox" type="checkbox" name="inspection_types[]" value="${type.id}" ${checked}>
                                <label class="form-check-label">${type.name}</label>
                            </div>
                        `);
                    });
    
                    // Populate Location Checkboxes
                    $("#editLocationCheckboxes").empty();
                    response.all_locations.forEach(location => {
                        let checked = response.selected_locations.some(selected => selected.id === location.id) ? "checked" : "";
                        $("#editLocationCheckboxes").append(`
                            <div class="form-check">
                                <input class="form-check-input edit-location-checkbox" type="checkbox" name="locations[]" value="${location.id}" ${checked}>
                                <label class="form-check-label">${location.name}</label>
                            </div>
                        `);
                    });
    
                    // Show the Edit Modal
                    $("#editMissionModal").modal("show");
                },
                error: function () {
                    alert("❌ Error fetching mission details.");
                }
            });
        });

          // Submit Edit Mission Form via AJAX
    $("#editMissionForm").on("submit", function (e) {
        e.preventDefault();

        let formData = {
            mission_id: $("#edit_mission_id").val(),
            start_datetime: $("#edit_start_datetime").val(),
            end_datetime: $("#edit_end_datetime").val(),
            note: $("#edit_note").val(),
            inspection_types: $(".edit-inspection-type-checkbox:checked").map(function () { return this.value; }).get(),
            locations: $(".edit-location-checkbox:checked").map(function () { return this.value; }).get()
        };

        $.ajax({
            url: "/missions/update",
            type: "POST",
            data: formData,
            success: function (response) {
                alert(response.message);
                $("#editMissionModal").modal("hide");
                getRegionManagerMissions();
            },
            error: function (xhr) {
                alert("❌ Error updating mission: " + xhr.responseText);
            }
        });
    });

});
