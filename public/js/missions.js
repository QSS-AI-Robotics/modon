$(document).ready(function () {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getRegionManagerMissions();
    function getRegionManagerMissions() {
        $(".mission-btn svg").attr({ "width": "16", "height": "16" });
        $("#addMissionForm").removeAttr("data-mission-id");
        $(".cancel-btn").addClass("d-none");

        $.ajax({
            url: "/getmanagermissions", // Matches Laravel route for fetching missions
            type: "GET",
            success: function (response) {
                $('#missionTableBody').empty(); // Clear previous data
                
                if (response.missions.length === 0) {
                    $('#missionTableBody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted">No missions available.</td>
                        </tr>
                    `);
                    return;
                }
    
                $.each(response.missions, function (index, mission) {
                    let inspectionTypesArray = mission.inspection_types.map(type => type.name);
                    let locationsArray = mission.locations.map(loc => loc.name);
    
                    // ✅ Bootstrap Dropdown for Inspection Types
                    let inspectionTypesHTML = `
                        <div class="dropdown d-flex align-items-center justify-content-center">
                            <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                ${inspectionTypesArray[0] || 'N/A'}...
                            </span>
                            <ul class="dropdown-menu text-center">
                                ${inspectionTypesArray.map(type => `<li class="dropdown-item">${type}</li>`).join("")}
                            </ul>
                        </div>

                        `;
    
                        let locationsHTML = `
                        <div class="dropdown d-flex align-items-center justify-content-center">
                            <span class="dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                ${locationsArray[0] || 'N/A'}...
                            </span>
                            <ul class="dropdown-menu text-center">
                                ${locationsArray.map(loc => `<li class="dropdown-item">${loc}</li>`).join("")}
                            </ul>
                        </div>
                    `;

                    // ✅ Mission Note (Only First 3 Words + Hover Dropdown)
                    let noteArray = mission.note ? mission.note.split(" ") : [];
                    let shortNote = noteArray.slice(0, 2).join(" ") || "No Notes";
                    let fullNote = noteArray.join(" ") || "No Notes Available";

                    let noteHTML = `
                    <div class="dropdown d-flex align-items-center justify-content-center w-100">
                        <button class="btn dropdown-toggle note-preview w-100 text-truncate" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            ${shortNote}...
                        </button>
                        <ul class="dropdown-menu text-start note-dropdown">
                            <li class="dropdown-item">${fullNote}</li>
                        </ul>
                    </div>
                `;
                

    
                    // ✅ Mission Status Display
                    let statusBadge = "";
                    if (mission.status === "Pending") {
                        statusBadge = `<span class="badge p-2 bg-danger">Pending</span>`;
                    } else if (mission.status === "In Progress") {
                        statusBadge = `<span class="badge p-2  bg-warning text-dark">In Progress</span>`;
                    } else if (mission.status === "Awaiting Report") {
                        statusBadge = `<span class="badge p-2 bg-primary">Awaiting Report</span>`;
                    } else if (mission.status === "Completed") {
                        statusBadge = `<span class="badge p-2  bg-success">Completed</span>`;
                    }
                    // ✅ Show Edit Button Only if Status is "Pending"
                    let editButton = mission.status === "Pending"
                    ? `<img src="./images/edit.png" alt="" class="edit-mission img-fluid actions" data-id="${mission.id}">`
                    : "";
                    let row = `
                        <tr id="missionRow-${mission.id}">
                            <td>${inspectionTypesHTML}</td>
                            <td>${mission.start_datetime}</td>
                            <td>${mission.end_datetime}</td>
                            <td>${locationsHTML}</td>
                            <td class="align-middle">${noteHTML}</td>
                            <td>${statusBadge}</td>
                            
                            <td>                                          
                                ${editButton}
                                <img src="./images/delete.png" alt="" class="delete-mission img-fluid actions" data-id="${mission.id}">
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
    
        let missionId = $(this).attr("data-mission-id"); // Get Mission ID (if editing)
        let url = missionId ? "/missions/update" : "/missions/store"; // Update or Create URL
        let buttonText = missionId ? "Updating..." : "Creating...";
        
        $(".mission-btn span").text(buttonText); // Change button text
        $(".mission-btn svg").attr({ "width": "20", "height": "20" }); // Increase SVG size
    
        // ✅ Collect Selected Checkboxes
        let selectedInspectionTypes = [];
        $('.inspection-type-checkbox:checked').each(function () {
            selectedInspectionTypes.push($(this).val());
        });
    
        let selectedLocations = [];
        $('.location-checkbox:checked').each(function () {
            selectedLocations.push($(this).val());
        });
    
        // ✅ Prepare Form Data
        let formData = {
            mission_id: missionId, // Send mission ID if updating
            inspection_types: selectedInspectionTypes,
            start_datetime: $('#start_datetime').val(),
            end_datetime: $('#end_datetime').val(),
            note: $('#note').val(),
            locations: selectedLocations
        };
    
        // ✅ Send AJAX Request
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (response) {
                alert(response.message);
    
                // ✅ Reset form after adding/updating
                $('#addMissionForm')[0].reset();
                $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
                $("#addMissionForm").removeAttr("data-mission-id"); // Remove edit mode
    
                // ✅ Restore Title & Button Text
                $("h6").text("Create New Mission");
                $(".mission-btn span").text("New Mission");
    
                // ✅ Refresh Mission List
                getRegionManagerMissions(); 
            },
            error: function (xhr) {
                alert("❌ Error processing mission. Please try again.");
                console.log(xhr.responseText);
            }
        });
    });

    
    // $('#addMissionForm').on('submit', function (e) {
    //     e.preventDefault();

    //     // Get selected inspection types
    //     let selectedInspectionTypes = [];
    //     $('.inspection-type-checkbox:checked').each(function () {
    //         selectedInspectionTypes.push($(this).val());
    //     });

    //     // Get selected locations
    //     let selectedLocations = [];
    //     $('.location-checkbox:checked').each(function () {
    //         selectedLocations.push($(this).val());
    //     });

    //     let formData = {
    //         inspection_types: selectedInspectionTypes, // ✅ Sending multiple inspection types
    //         start_datetime: $('#start_datetime').val(),
    //         end_datetime: $('#end_datetime').val(),
    //         note: $('#note').val(),
    //         locations: selectedLocations, // ✅ Sending multiple locations
    //     };

    //     $.ajax({
    //         url: "/missions/store",
    //         type: "POST",
    //         data: formData,
    //         success: function (response) {
    //             alert(response.message);
    //             getRegionManagerMissions(); // Refresh mission table
    //             $('#addMissionForm')[0].reset(); // Reset form
    //         },
    //         error: function (xhr) {
    //             alert("Error: " + xhr.responseText);
    //         }
    //     });
    // });

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
            $(".cancel-btn").removeClass("d-none");
            let missionId = $(this).data("id"); // Get mission ID
            let row = $(`#missionRow-${missionId}`); // Get the row
        
            // Extract Data from Row
            let inspectionTypes = row.find("td:nth-child(1) .dropdown-menu li").map(function () {
                return $(this).text().trim();
            }).get();
        
            let startDatetime = row.find("td:nth-child(2)").text().trim();
            let endDatetime = row.find("td:nth-child(3)").text().trim();
            let locations = row.find("td:nth-child(4) .dropdown-menu li").map(function () {
                return $(this).text().trim();
            }).get();
            let note = row.find("td:nth-child(5) .dropdown-menu li").text().trim();
        
            // ✅ Update Form Fields
            $("#start_datetime").val(startDatetime);
            $("#end_datetime").val(endDatetime);
            $("#note").val(note);
        
            // ✅ Check Inspection Type Checkboxes
            $(".inspection-type-checkbox").each(function () {
                let typeValue = $(this).siblings("label").text().trim();
                $(this).prop("checked", inspectionTypes.includes(typeValue));
            });
        
            // ✅ Check Location Checkboxes
            $(".location-checkbox").each(function () {
                let locationValue = $(this).siblings("label").text().trim();
                $(this).prop("checked", locations.includes(locationValue));
            });
         
            // ✅ Update Title and Button Text
            $(".form-title").text("Edit Mission");
           // Show Cancel Button
            // Change Button Text
            $(".mission-btn svg").attr({ "width": "30", "height": "30" });
            $(".mission-btn span").text("Update Mission"); // Change Button Text
        

        

        
            // ✅ Store Mission ID for Updating
            $("#addMissionForm").attr("data-mission-id", missionId);
        });
        
        
        $(document).on("click", ".cancel-btn", function () {
            // ✅ Reset Form Fields
            $("#addMissionForm")[0].reset();
        
            // ✅ Uncheck All Checkboxes
            $(".inspection-type-checkbox, .location-checkbox").prop("checked", false);
        
            // ✅ Restore Title & Button Text
            $(".form-title").text("Create New Mission");
            $(".mission-btn span").text("New Mission");
        
            // ✅ Remove Cancel Button
          
            $(".mission-btn svg").attr({ "width": "16", "height": "16" });
            // ✅ Clear Mission ID
            $("#addMissionForm").removeAttr("data-mission-id");
            $(".cancel-btn").addClass("d-none");
        });
        
        // $(document).on("click", ".edit-mission", function () {
        //     let missionId = $(this).data("id");
    
        //     $.ajax({
        //         url: `/missions/${missionId}/edit`,
        //         type: "GET",
        //         success: function (response) {
        //             console.log("✅ Loaded Mission Data:", response);
    
        //             // Populate Modal Fields
        //             $("#edit_mission_id").val(response.mission.id);
        //             $("#edit_start_datetime").val(response.mission.start_datetime.replace(" ", "T"));
        //             $("#edit_end_datetime").val(response.mission.end_datetime.replace(" ", "T"));
        //             $("#edit_note").val(response.mission.note);
    
        //             // Populate Inspection Type Checkboxes
        //             $("#editInspectionTypeCheckboxes").empty();
        //             response.all_inspection_types.forEach(type => {
        //                 let checked = response.selected_inspections.some(selected => selected.id === type.id) ? "checked" : "";
        //                 $("#editInspectionTypeCheckboxes").append(`
        //                     <div class="form-check me-3">
        //                         <input class="form-check-input edit-inspection-type-checkbox" type="checkbox" name="inspection_types[]" value="${type.id}" ${checked}>
        //                         <label class="form-check-label">${type.name}</label>
        //                     </div>
        //                 `);
        //             });
    
        //             // Populate Location Checkboxes
        //             $("#editLocationCheckboxes").empty();
        //             response.all_locations.forEach(location => {
        //                 let checked = response.selected_locations.some(selected => selected.id === location.id) ? "checked" : "";
        //                 $("#editLocationCheckboxes").append(`
        //                     <div class="form-check">
        //                         <input class="form-check-input edit-location-checkbox" type="checkbox" name="locations[]" value="${location.id}" ${checked}>
        //                         <label class="form-check-label">${location.name}</label>
        //                     </div>
        //                 `);
        //             });
    
        //             // Show the Edit Modal
        //             $("#editMissionModal").modal("show");
        //         },
        //         error: function () {
        //             alert("❌ Error fetching mission details.");
        //         }
        //     });
        // });

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
